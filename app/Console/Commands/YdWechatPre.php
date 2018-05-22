<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/04/12
 * Time: 12:03
 * 韵达已签约业务员预投保定时任务-从凌晨开始
 */
namespace App\Console\Commands;

use App\Models\ChannelContract;
use App\Models\ChannelPrepareInfo;
use Illuminate\Http\Request;
use App\Helper\DoChannelsSignHelp;
use App\Helper\RsaSignHelp;
use App\Helper\AesEncrypt;
use Ixudra\Curl\Facades\Curl;
use Validator, DB, Image, Schema;
use App\Models\ChannelOperate;
use App\Models\User;
use Session,Cache;
use App\Models\Order;
use App\Models\OrderParameter;
use App\Models\WarrantyPolicy;
use App\Models\WarrantyRecognizee;
use App\Models\WarrantyRule;
use App\Helper\LogHelper;
use App\Models\UserBank;
use App\Models\Competition;
use App\Helper\IdentityCardHelp;
use Illuminate\Console\Command;
use \Illuminate\Support\Facades\Redis;
use App\Helper\AddOrderHelper;
use App\Models\Bank;
use App\Jobs\YunDaPayInsure;
use App\Models\Person;
use App\Models\ChannelInsureSeting;
use App\Models\CustWarranty;
use App\Models\CustWarrantyPerson;
use App\Models\ChannelJointLogin;
use App\Jobs\YdWechatPay;
use App\Helper\TokenHelper;

class YdWechatPre extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yunda_wechat_prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'yunda_wechat_prepare Command description';


    /**
     * Create a new command instance.
     * @return void
     * 初始化
     *
     */
    public function __construct(Request $request)
    {
        parent::__construct();
        set_time_limit(0);//永不超时
		$this->request = $request;
		$this->log_helper = new LogHelper();
		$this->sign_help = new RsaSignHelp();
		$this->add_order_helper = new AddOrderHelper();
    }

	/**
	 * 测试已签约&联合登录的用户的预投保操作
	 * 匹配出前一天的联合登录用户中已经微信签约的，进行预投保操作（定时任务）
	 * 第二天联合登陆后进行投保操作（代扣，异步操作）
	 */
	public function handle(){

//		存入缓存，减少数据库查询次数
//		存值取值
//		Redis::exists('key') //redis是否存在这个键
//		Redis::set('key','value'); //存入redis
//		Redis::get('key'); //获取redis中的值
//      队列操作
//		Redis::rPush("prepare_info",$biz_content);//入队操作
//		Redis::lpop('prepare_info')//出队操作
//		Redis::lLen('key'); //队列的长度
//		Redis::rpop('key'); //右侧出队列
		if(!Redis::exits('login_person')){
			$login_person = ChannelJointLogin::where('login_start','>=',strtotime(date('Y-m-d',strtotime('-1 day'))))
				->where('login_start','<',strtotime(date('Y-m-d')))
				->with(['person'=>function($a){
					$a->select('name','papers_type','papers_code','phone','email','address','address_detail');
				}])
				->select('phone','login_start')
				->get();
		}else{
			$login_person = Redis::get('login_person');
		}
		foreach ($login_person as $value){
			if(!isset($value['person'])&&empty($value['person'])){
				return false;
			}
			$card_info = IdentityCardHelp::getIDCardInfo($value['person']['papers_code']);
			if($card_info['status']!=2){
				return false;
			}
			//查询签约状态
			$contract_res = ChannelContract::where('channel_user_code',$value['person']['papers_code'])
				->select('is_auto_pay','openid','contract_id','contract_expired_time')
				->first();
			if(empty($contract_res)){
				return 'end';
			}
			$value['person']['operate_time'] = date('Y-m-d',time());
			$value['person']['sex'] = $card_info['sex'];
			$value['person']['birthday'] = $card_info['birthday'];
			$value['person']['province'] = $value['person']['address'];
			$value['person']['city'] = $value['person']['address'];
			$value['person']['county'] = $value['person']['address'];
			$value['person']['courier_state'] = $value['person']['address_detail'];//站点地址
			$value['person']['courier_start_time'] = date('Y-m-d H:i:s',$value['login_start']);//上工时间
			$prepare_res = $this->doInsurePrepare($value['person']);
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
		$toubaoren['ty_toubaoren_provinces'] = $prepare['province'];
		$toubaoren['ty_toubaoren_city'] = $prepare['city'];
		$toubaoren['ty_toubaoren_county'] = $prepare['county'];
		$toubaoren['channel_user_address'] = $prepare['address_detail'];
		$toubaoren['courier_state'] = $prepare['courier_state'];
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
		$beibaoren[0]['ty_beibaoren_provinces'] = $prepare['province'];
		$beibaoren[0]['ty_beibaoren_city'] = $prepare['city'];
		$beibaoren[0]['ty_beibaoren_county'] = $prepare['county'];
		$beibaoren[0]['channel_user_address'] = $prepare['address_detail'];
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
				'channel_user_code'=>$prepare['channel_user_code'],
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
		$add_res = $this->add_order_helper->doAddOrder($return_data, $prepare,$toubaoren,$beibaoren);
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

}
