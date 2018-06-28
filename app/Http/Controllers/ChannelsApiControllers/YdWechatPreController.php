<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/04/12
 * Time: 12:03
 * 韵达已签约业务员预投保定时任务-从凌晨开始
 */
namespace App\Http\Controllers\ChannelsApiControllers;

use Illuminate\Http\Request;
use App\Helper\RsaSignHelp;
use App\Helper\AesEncrypt;
use Ixudra\Curl\Facades\Curl;
use Validator, DB, Image, Schema;
use App\Models\ChannelOperate;
use Session,Cache;
use App\Helper\LogHelper;
use App\Helper\IdentityCardHelp;
use \Illuminate\Support\Facades\Redis;
use App\Helper\AddOrderHelper;
use App\Models\ChannelJointLogin;
use App\Models\Person;
use App\Models\CustWarranty;
use App\Models\CustWarrantyPerson;



class YdWechatPreController
{
	/**
	 * 初始化
	 */
    public function __construct(Request $request)
    {
        set_time_limit(0);//永不超时
		$this->request = $request;
		$this->log_helper = new LogHelper();
		$this->sign_help = new RsaSignHelp();
		$this->add_order_helper = new AddOrderHelper();
    }

	/**
	 * 全员预投保
	 */
	public function doAllPersonPre(){
		$key = 'prepare_params';
		if(!Redis::exists($key)){//redis是否存在这个键
			$person_res = Person::select('id','name','papers_code','phone','papers_type','email','address','address_detail')
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
				$cust_warranty_res = CustWarranty::where('user_id',$prepare_params['id'])
						->where('warranty_status','<>','6')//失效的订单
						->where('created_at','>',strtotime(date('Y-m-d')).'000')//今天凌晨的时间戳
						->select('warranty_uuid','warranty_code','created_at','check_status','pay_status','warranty_status')
						->orderBy('created_at','desc')
						->first();
				if(empty($cust_warranty_res)) {
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
						$prepare_return_data = $this->doInsurePrepare($params);
						if(empty($prepare_return_data)){
							echo json_encode(['msg'=>'接口返回,投保失败','status'=>'500'],JSON_UNESCAPED_UNICODE).'<br/>';
						}else{
							if($prepare_return_data['status']=='200'){
								echo $prepare_return_data['content'].'<br/>';
							}else{
								echo $prepare_return_data['content'].'<br/>';
							}
						}

					}else{
						echo json_encode(['msg'=>'投保失败,身份证格式不对','status'=>'500'],JSON_UNESCAPED_UNICODE).'<br/>';
					}
				}else{
					echo json_encode(['msg'=>'投保成功，投保单号'.$cust_warranty_res['warranty_uuid'],'status'=>'200'],JSON_UNESCAPED_UNICODE).'<br/>';
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
		$prepare_return_data = [];
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
				'channel_user_code'=>$prepare['channel_user_code']??"00",
				'prepare_status'=>'500',
				'prepare_content'=>$response->content,
				'operate_time'=>date('Y-m-d',time()),
				'created_at'=>date('Y-m-d H:i:s',time()),
				'updated_at'=>date('Y-m-d H:i:s',time())
			]);
			$content = $response->content;
			$prepare_return_data ['status']='501';
			$prepare_return_data['content']=$content;
			return $prepare_return_data;
		}else{
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
			$add_res = $this->add_order_helper->doAddOrder($return_data, $prepare,$toubaoren,$beibaoren);
			if($add_res){
				$prepare_return_data ['status']='200';
				$prepare_return_data['content']='投保完成';
				return $prepare_return_data;
			}else{
				$prepare_return_data ['status']='500';
				$prepare_return_data['content']='投保失败';
				return $prepare_return_data;
			}
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

}
