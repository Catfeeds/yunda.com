<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/5/22
 * Time: 11:51
 */

namespace App\Http\Controllers\ChannelsApiControllers;

use App\Models\ChannelContract;
use App\Models\Person;
use Illuminate\Http\Request;
use App\Helper\RsaSignHelp;
use App\Helper\AesEncrypt;
use Ixudra\Curl\Facades\Curl;
use Validator, DB, Image, Schema;
use App\Models\ChannelOperate;
use Session,Cache;
use App\Helper\LogHelper;
use App\Helper\IdentityCardHelp;
use App\Models\ChannelJointLogin;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderParameter;
use App\Models\WarrantyPolicy;
use App\Models\WarrantyRecognizee;
use App\Models\WarrantyRule;

use \Illuminate\Support\Facades\Redis;

class WechatPreController extends BaseController
{

	/**
	 * Create a new command instance.
	 * @return void
	 * 初始化
	 *
	 */
	public function __construct(Request $request)
	{
		set_time_limit(0);//永不超时
		$this->request = $request;
		$this->log_helper = new LogHelper();
		$this->sign_help = new RsaSignHelp();
	}

	/**
	 * 测试已签约&联合登录的用户的预投保操作
	 * 匹配出前一天的联合登录用户中已经微信签约的，进行预投保操作（定时任务）
	 * 第二天联合登陆后进行投保操作（代扣，异步操作）
	 */
	public function wechatPre(){
		$channel_contract_info = ChannelContract::with(['person'=>function($a){
				$a->select('name','papers_type','papers_code','phone','email','address','address_detail');
			}])
			//openid,签约人身份证号
			->select('openid','channel_user_code')
			->groupBy('channel_user_code')
			->get();
		if(empty($channel_contract_info)){
			echo json_encode(['msg'=>'投保失败,没有签约信息','status'=>'500'],JSON_UNESCAPED_UNICODE);
		}
		foreach ($channel_contract_info as $key=>$value){
			$channel_operate_res = ChannelOperate::where('channel_user_code',$value['channel_user_code'])
				->where('operate_time',date('Y-m-d',time()))
				->where('prepare_status','200')
				->select('proposal_num')
				->first();
			if(empty($channel_operate_res)) {
				if (!empty($value['person'])) {
					$card_info = IdentityCardHelp::getIDCardInfo($value['channel_user_code']);
					if ($card_info['status'] = 2) {
						$joint_login = ChannelJointLogin::where('phone', $value['person']['phone'])->select('login_start')->first();
						$value['person']['operate_time'] = date('Y-m-d', time());
						$value['person']['sex'] = $card_info['sex'];
						$value['person']['birthday'] = $card_info['birthday'];
						$value['person']['province'] = $value['person']['address'];
						$value['person']['city'] = $value['person']['address'];
						$value['person']['county'] = $value['person']['address'];
						$value['person']['courier_state'] = $value['person']['address_detail'];//站点地址
						$value['person']['courier_start_time'] = date('Y-m-d H:i:s', $joint_login['login_start']);//上工时间
						$this->doInsurePrepare($value['person']);
					}else{
						echo json_encode(['msg'=>'投保失败,身份证格式不对','status'=>'500'],JSON_UNESCAPED_UNICODE);
					}
				}else{
					echo json_encode(['msg'=>'投保失败,没有投保人信息','status'=>'500'],JSON_UNESCAPED_UNICODE);
				}
			}else{
				echo json_encode(['msg'=>'投保成功，投保单号'.$channel_operate_res['proposal_num'],'status'=>'200'],JSON_UNESCAPED_UNICODE);
			}
		}
	}

	/**
	 * 全员预投保
	 */
	public function doAllPersonPre(){
		$key = 'prepare_params';
		if(!Redis::exists($key)){//redis是否存在这个键
			$person_res = Person::select('name','papers_code','phone','papers_type','email','address','address_detail')
				->limit(100000)
				->get();
			foreach ($person_res as $value){
				Redis::rPush("prepare_params",$value);//右侧存入队列
			}
		}
		$count = Redis::Llen($key);//队列的长度
		if($count>0){
			for($i=1;$i<=50;$i++){
				$prepare_params = Redis::rpop($key); //右侧出队列
				$prepare_params = json_decode($prepare_params,true);
				$channel_operate_res = ChannelOperate::where('channel_user_code',$prepare_params['papers_code'])
					->where('operate_time',date('Y-m-d',time()))
					->where('prepare_status','200')
					->select('proposal_num')
					->first();
				if(empty($channel_operate_res)) {
					$card_info = IdentityCardHelp::getIDCardInfo($prepare_params['papers_code']);
					if ($card_info['status'] = 2) {
						$joint_login = ChannelJointLogin::where('phone', $prepare_params['phone'])->select('login_start')->first();
						$params = [];
						$params['operate_time'] =date('Y-m-d', time());
						$params['name'] = $prepare_params['name'];
						$params['papers_code'] = $prepare_params['papers_code'];
						$params['phone'] = $prepare_params['phone'];
						$params['email'] = $prepare_params['email'];
						$params['sex'] = $card_info['sex'];
						$params['birthday'] = $card_info['birthday'];
						$params['province'] = $prepare_params['address'];
						$params['city'] = $prepare_params['address'];
						$params['county'] = $prepare_params['address'];
						$params['courier_state'] = $prepare_params['address_detail'];//站点地址
						$params['courier_start_time'] = date('Y-m-d H:i:s', $joint_login['login_start']);//上工时间
						$this->doInsurePrepare($params);
						Loghelper::logChannelSuccess($prepare_params['papers_code'], 'YD_prepara_end');
					}else{
						echo json_encode(['msg'=>'投保失败,身份证格式不对','status'=>'500'],JSON_UNESCAPED_UNICODE);
					}
				}else{
					echo json_encode(['msg'=>'投保成功，投保单号'.$channel_operate_res['proposal_num'],'status'=>'200'],JSON_UNESCAPED_UNICODE);
				}
			}
		}
	}

	/**
	 * 预投保操作
	 *
	 */
	public function doInsurePrepare($prepare){
		$data = [];
		$insurance_attributes = [];
		$base = [];
		$base['ty_start_date'] = $prepare['operate_time'];
		$toubaoren = [];
		$toubaoren['ty_toubaoren_name'] = $prepare['name'];//投保人姓名
		$toubaoren['ty_toubaoren_id_type'] = '1';//证件类型
		$toubaoren['ty_toubaoren_id_number'] = $prepare['papers_code'];//证件号
		$toubaoren['ty_toubaoren_birthday'] = $prepare['birthday'];
		$toubaoren['ty_toubaoren_sex'] = $prepare['sex'];
		$toubaoren['ty_toubaoren_phone'] = $prepare['phone'];
		$toubaoren['ty_toubaoren_email'] = $prepare['email'];
		$toubaoren['ty_toubaoren_provinces'] = $prepare['province']??"";
		$toubaoren['ty_toubaoren_city'] = $prepare['city']??"";
		$toubaoren['ty_toubaoren_county'] = $prepare['county']??"";
		$toubaoren['channel_user_address'] = $prepare['address_detail']??"";
		$toubaoren['courier_state'] = $prepare['courier_state']??"";
		$toubaoren['courier_start_time'] = $prepare['courier_start_time'];
		$beibaoren = [];
		$beibaoren[0]['ty_beibaoren_name'] = $prepare['name'];
		$beibaoren[0]['ty_relation'] = '1';//必须为本人
		$beibaoren[0]['ty_beibaoren_id_type'] = '1';//身份证
		$beibaoren[0]['ty_beibaoren_id_number'] = $prepare['papers_code'];
		$beibaoren[0]['ty_beibaoren_birthday'] = $prepare['birthday'];
		$beibaoren[0]['ty_beibaoren_sex'] = $prepare['sex'];
		$beibaoren[0]['ty_beibaoren_phone'] = $prepare['phone'];
		$beibaoren[0]['ty_beibaoren_email'] = $prepare['email'];
		$beibaoren[0]['ty_beibaoren_provinces'] = $prepare['province']??"";
		$beibaoren[0]['ty_beibaoren_city'] = $prepare['city']??"";
		$beibaoren[0]['ty_beibaoren_county'] = $prepare['county']??"";
		$beibaoren[0]['channel_user_address'] = $prepare['address_detail']??"";
		$insurance_attributes['ty_base'] = $base;
		$insurance_attributes['ty_toubaoren'] = $toubaoren;
		$insurance_attributes['ty_beibaoren'] = $beibaoren;
		$data['price'] = '2';
		$data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
		$data['quote_selected'] = '';
		$data['insurance_attributes'] = $insurance_attributes;
		$data = $this->sign_help->tySign($data);
		//发送请求
		$response = Curl::to(env('TY_API_SERVICE_URL') . '/ins_curl/buy_ins')
			->returnResponseObject()
			->withData($data)
			->withTimeout(60)
			->post();
		if($response->status != 200){
			ChannelOperate::insert([
				'channel_user_code'=>$prepare['papers_code']??"00",
				'prepare_status'=>'500',
				'prepare_content'=>$response->content,
				'operate_time'=>date('Y-m-d',time()),
				'created_at'=>date('Y-m-d H:i:s',time()),
				'updated_at'=>date('Y-m-d H:i:s',time())
			]);
			$content = $response->content;
			$return_data =  json_encode(['status'=>'501','content'=>$content],JSON_UNESCAPED_UNICODE);
			print_r($return_data);
		}
		$prepare['parameter'] = '0';
		$prepare['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
		$prepare['ty_product_id'] = 'VGstMTEyMkEwMUcwMQ';
		$prepare['agent_id'] = '0';
		$prepare['ditch_id'] = '0';
		$prepare['user_id'] = $prepare['papers_code'];
		$prepare['identification'] = '0';
		$prepare['union_order_code'] = '0';
		$return_data = json_decode($response->content, true);
		//todo  本地订单录入
		$add_res = $this->addOrder($return_data, $prepare,$toubaoren);
		if($add_res){
			$return_data =  json_encode(['status'=>'200','content'=>'投保完成'],JSON_UNESCAPED_UNICODE);
			print_r($return_data);
		}else{
			$return_data =  json_encode(['status'=>'500','content'=>'投保失败'],JSON_UNESCAPED_UNICODE);
			print_r($return_data);
		}
	}

	/**
	 * 对象转化数组
	 *
	 */
	function object2array($object) {
		if (is_object($object)) {
			foreach ($object as $key => $value) {
				$array[$key] = $value;
			}
		}
		else {
			$array = $object;
		}
		return $array;
	}

	/**
	 * 添加投保返回信息
	 *
	 */
	protected function addOrder($return_data, $prepare, $policy_res)
	{
		DB::beginTransaction();
		try{
			//查询是否在竞赛方案中
			$private_p_code = $prepare['private_p_code'];
			$competition_id = 0;
			$is_settlement = 0;
			$ditch_id = $prepare['ditch_id'];
			$agent_id = $prepare['agent_id'];
			//订单信息录入
			foreach ($return_data['order_list'] as $order_value){
				$order = new Order();
				$order->order_code = $order_value['union_order_code']; //订单编号
				$order->user_id = isset($_COOKIE['user_id'])?$_COOKIE['user_id']:' ';//用户id
				$order->agent_id = $agent_id;
				$order->competition_id = $competition_id;//竞赛方案id，没有则为0
				$order->private_p_code = $private_p_code;
				$order->ty_product_id = $prepare['ty_product_id'];
				$order->start_time = isset($order_value['start_time'])?$order_value['start_time']: ' ';
				$order->claim_type = 'online';
				$order->deal_type = 0;
				$order->is_settlement = $is_settlement;
				$order->premium = $order_value['premium'];
				$order->status = config('attribute_status.order.unpayed');
				$order->pay_way = json_encode($return_data['pay_way']);
				$order->save();
			}
			//投保人信息录入
			$warrantyPolicy = new WarrantyPolicy();
			$warrantyPolicy->name = isset($policy_res['ty_toubaoren_name'])?$policy_res['ty_toubaoren_name']:'';
			$warrantyPolicy->card_type = isset($policy_res['ty_toubaoren_id_type'])?$policy_res['ty_toubaoren_id_type']:'';
			$warrantyPolicy->occupation = isset($policy_res['ty_toubaoren_occupation'])?$policy_res['ty_toubaoren_occupation']:'';//投保人职业？？
			$warrantyPolicy->code = isset($policy_res['ty_toubaoren_id_number'])?$policy_res['ty_toubaoren_id_number']:'';
			$warrantyPolicy->phone =  isset($policy_res['ty_toubaoren_phone'])?$policy_res['ty_toubaoren_phone']:'';
			$warrantyPolicy->email =  isset($policy_res['ty_toubaoren_email'])?$policy_res['ty_toubaoren_email']:'';
			$warrantyPolicy->area =  isset($policy_res['ty_toubaoren_area'])?$policy_res['ty_toubaoren_area']:'';
			$warrantyPolicy->status = config('attribute_status.order.check_ing');
			$warrantyPolicy->save();
			//用户信息录入
			$user_check_res  = User::where('code',$policy_res['ty_toubaoren_id_number'])
				->where('phone',$policy_res['ty_toubaoren_phone'])
				->first();
			if(empty($user_check_res)){
				$user_res = new User();
				$user_res->name = isset($policy_res['ty_toubaoren_name'])?$policy_res['ty_toubaoren_name']:'';
				$user_res->real_name = isset($policy_res['ty_toubaoren_name'])?$policy_res['ty_toubaoren_name']:'';
				$user_res->phone = isset($policy_res['ty_toubaoren_phone'])?$policy_res['ty_toubaoren_phone']:'';
				$user_res->code = isset($policy_res['ty_toubaoren_id_number'])?$policy_res['ty_toubaoren_id_number']:'';
				$user_res->email =  isset($policy_res['ty_toubaoren_email'])?$policy_res['ty_toubaoren_email']:'';
				$user_res->occupation = isset($policy_res['ty_toubaoren_occupation'])?$policy_res['ty_toubaoren_occupation']:'';
				$user_res->address = isset($policy_res['ty_toubaoren_area'])?$policy_res['ty_toubaoren_area']:'';
				$user_res->type = 'user';
				$user_res->password = bcrypt('123qwe');
			}

			//被保人信息录入
			foreach ($return_data['order_list'] as $recognizee_value){
				$warrantyRecognizee = new WarrantyRecognizee();
				$warrantyRecognizee->name = $recognizee_value['name'];
				$warrantyRecognizee->order_id = $order->id;
				$warrantyRecognizee->order_code = $recognizee_value['out_order_no'];
				$warrantyRecognizee->relation = $recognizee_value['relation'];
				$warrantyRecognizee->occupation =isset($recognizee_value['occupation'])?$recognizee_value['occupation']: '';
				$warrantyRecognizee->card_type = isset($recognizee_value['card_type'])?$recognizee_value['card_type']: '';
				$warrantyRecognizee->code = isset($recognizee_value['card_id'])?$recognizee_value['card_id']: '';
				$warrantyRecognizee->phone = isset($recognizee_value['phone'])?$recognizee_value['phone']: '';
				$warrantyRecognizee->email = isset($recognizee_value['email'])?$recognizee_value['email']: '';
				$warrantyRecognizee->start_time = isset($recognizee_value['start_time'])?$recognizee_value['start_time']: '';
				$warrantyRecognizee->end_time = isset($recognizee_value['end_time'])?$recognizee_value['end_time']: '';
				$warrantyRecognizee->status = config('attribute_status.order.unpayed');
				$warrantyRecognizee->save();
				//用户信息录入
				$user_check_res  = User::where('code',$recognizee_value['card_id'])
					->where('real_name',$recognizee_value['name'])
					->first();
				if(empty($user_check_res)){
					$user_res = new User();
					$user_res->name = $recognizee_value['name'];
					$user_res->real_name = $recognizee_value['name'];
					$user_res->phone = isset($recognizee_value['phone'])?$recognizee_value['phone']: '';
					$user_res->code = isset($recognizee_value['card_id'])?$recognizee_value['card_id']: '';
					$user_res->email =  isset($recognizee_value['email'])?$recognizee_value['email']: '';
					$user_res->occupation = isset($recognizee_value['occupation'])?$recognizee_value['occupation']: '';
					$user_res->address =isset($recognizee_value['address'])?$recognizee_value['address']: '';
					$user_res->type = 'user';
					$user_res->password = bcrypt('123qwe');
				}
			}
			//添加投保参数到参数表
			$orderParameter = new OrderParameter();
			$orderParameter->parameter = $prepare['parameter'];
			$orderParameter->order_id = $order->id;
			$orderParameter->ty_product_id = $order->ty_product_id;
			$orderParameter->private_p_code = $private_p_code;
			$orderParameter->save();
			//添加到关联表记录
			$WarrantyRule = new WarrantyRule();
			$WarrantyRule->agent_id = $agent_id;
			$WarrantyRule->ditch_id = $ditch_id;
			$WarrantyRule->order_id = $order->id;
			$WarrantyRule->ty_product_id = $order->ty_product_id;
			$WarrantyRule->premium = $order->premium;
			$WarrantyRule->union_order_code = $return_data['union_order_code'];//总订单号
			$WarrantyRule->parameter_id = $orderParameter->id;
			$WarrantyRule->policy_id = $warrantyPolicy->id;
			$WarrantyRule->private_p_code = $private_p_code;   //预留
			$WarrantyRule->save();
			//添加到渠道用户操作表
			$ChannelOperate = new ChannelOperate();
			$ChannelOperate->channel_user_code = $policy_res['ty_toubaoren_id_number'];
			$ChannelOperate->order_id = $order->id;
			$ChannelOperate->proposal_num = $return_data['union_order_code'];
			$ChannelOperate->prepare_status = '200';
			$ChannelOperate->operate_time = date('Y-m-d',time());
			$ChannelOperate->save();
			DB::commit();
			return true;
		}catch (\Exception $e)
		{
			DB::rollBack();
			////Loghelper::logChannelError([$return_data, $prepare], $e->getMessage(), 'addOrder');
			return false;
		}
	}
}
