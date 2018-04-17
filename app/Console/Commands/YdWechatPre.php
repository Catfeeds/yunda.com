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
        $this->sign_help = new DoChannelsSignHelp();
        $this->signhelp = new RsaSignHelp();
		$this->add_order_helper = new AddOrderHelper();
        $this->request = $request;
        set_time_limit(0);//永不超时
    }

    /**
     *
     * 已签约用户预投保操作
     *
     */
    public function handle()
    {
        LogHelper::logChannelSuccess(date('Y-m-d H:i:s',time()), 'YD_prepara_start');
        LogHelper::logChannelSuccess(date('Y-m-d H:i:s',time()), 'YD_prepara_end');
        $contract_res = ChannelContract::with(['channel_user_info'=>function($a){
            $a->select('channel_user_name','channel_user_code','channel_user_phone','courier_start_time','courier_state','channel_user_address','channel_provinces','channel_city','channel_county');
        }])
            ->select('is_auto_pay','openid','contract_id','contract_expired_time')
            ->get();//查询所有已签约的客户
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
        $toubaoren['ty_toubaoren_name'] = $prepare['channel_user_name'];//投保人姓名
        $toubaoren['ty_toubaoren_id_type'] = $prepare['channel_user_type']??'1';//证件类型
        $toubaoren['ty_toubaoren_id_number'] = $prepare['channel_user_code'];//证件号
        $toubaoren['ty_toubaoren_birthday'] = substr($toubaoren['ty_toubaoren_id_number'],6,4).'-'.substr($toubaoren['ty_toubaoren_id_number'],10,2).'-'.substr($toubaoren['ty_toubaoren_id_number'],12,2);
        if(substr($toubaoren['ty_toubaoren_id_number'],16,1)%2=='0'){
            $toubaoren['ty_toubaoren_sex'] = '女';
        }else{
            $toubaoren['ty_toubaoren_sex'] = '男';
        }
        $toubaoren['ty_toubaoren_phone'] = $prepare['channel_user_phone'];
        $toubaoren['ty_toubaoren_email'] = $prepare['channel_user_email'];
        $toubaoren['ty_toubaoren_provinces'] = $prepare['channel_provinces'];
        $toubaoren['ty_toubaoren_city'] = $prepare['channel_city'];
        $toubaoren['ty_toubaoren_county'] = $prepare['channel_county'];
        $toubaoren['channel_user_address'] = $prepare['channel_user_address'];
        $toubaoren['courier_state'] = $prepare['courier_state'];
        $toubaoren['courier_start_time'] = $prepare['courier_start_time'];
        $beibaoren = [];
        $beibaoren[0]['ty_beibaoren_name'] = $prepare['channel_user_name'];
        $beibaoren[0]['ty_relation'] = '1';//必须为本人
        $beibaoren[0]['ty_beibaoren_id_type'] = $prepare['channel_user_type']??'1';
        $beibaoren[0]['ty_beibaoren_id_number'] = $prepare['channel_user_code'];
        $beibaoren[0]['ty_beibaoren_birthday'] = substr($toubaoren['ty_toubaoren_id_number'],6,4).'-'.substr($toubaoren['ty_toubaoren_id_number'],10,2).'-'.substr($toubaoren['ty_toubaoren_id_number'],12,2);
        if(substr($toubaoren['ty_toubaoren_id_number'],16,1)%2=='0'){
            $beibaoren[0]['ty_beibaoren_sex'] = '女';
        }else{
            $beibaoren[0]['ty_beibaoren_sex'] = '男';
        }
        $beibaoren[0]['ty_beibaoren_phone'] = $prepare['channel_user_phone'];
        $insurance_attributes['ty_base'] = $base;
        $insurance_attributes['ty_toubaoren'] = $toubaoren;
        $insurance_attributes['ty_beibaoren'] = $beibaoren;
        $data['price'] = '2';
        $data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
        $data['quote_selected'] = '';
        $data['insurance_attributes'] = $insurance_attributes;
        $data = $this->signhelp->tySign($data);
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
        $prepare['user_id'] = $prepare['channel_user_code'];
        $prepare['identification'] = '0';
        $prepare['union_order_code'] = '0';
        $return_data = json_decode($response->content, true);
        //todo  本地订单录入
		$add_res = $this->add_order_helper->doAddOrder($return_data, $prepare,$toubaoren,$beibaoren);
        if($add_res){
            $return_data =  json_encode(['status'=>'200','content'=>'投保完成'],JSON_UNESCAPED_UNICODE);
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
            LogHelper::logChannelError([$return_data, $prepare], $e->getMessage(), 'addOrder');
            return false;
        }
    }


	/**
	 * 添加投保返回信息
	 * @access public
	 * @param $return_data|订单返回数据
	 * @param $prepare|预投保信息
	 * @param $policy_res|投保人信息
	 * @param $holder_res|被保人信息
	 * @return mixed
	 * 新版表结构,保单返回数据只需要添加cust_warranty、cust_warranty_person、channel_operate、user
	 */
	protected function testaddOrder($return_data, $prepare, $policy_res,$holder_res)
	{
		DB::beginTransaction();//开启事务
		try{
			$policy_check_res  = Person::where('papers_code',$policy_res['ty_toubaoren_id_number'])
				->select('id','cust_type')
				->first();
			if(empty($policy_check_res)){
				$user_policy_res = new Person();
				$user_policy_res->name = $policy_res['ty_toubaoren_name'];
				$user_policy_res->papers_type = $policy_res['ty_toubaoren_id_type'];
				$user_policy_res->papers_code = $policy_res['ty_toubaoren_id_number'];
				$user_policy_res->papers_start = '';
				$user_policy_res->papers_end = '';
				$user_policy_res->sex = $policy_res['ty_toubaoren_sex'];
				$user_policy_res->birthday = $policy_res['ty_toubaoren_birthday'];
				$user_policy_res->address = $policy_res['ty_toubaoren_provinces'].'-'.$policy_res['ty_toubaoren_city'].'-'.$policy_res['ty_toubaoren_county'];
				$user_policy_res->address_detail = $policy_res['channel_user_address'];
				$user_policy_res->phone = $policy_res['ty_toubaoren_phone'];
				$user_policy_res->email = $policy_res['ty_toubaoren_email'];
				$user_policy_res->postcode = '';
				$user_policy_res->cust_type = '1';//客户类型，1：普通用户，2：代理人
				$user_policy_res->authentication = '1';//认证状态，1：未认证，2：已认证
				$user_policy_res->up_url = '';
				$user_policy_res->down_url = '';
				$user_policy_res->person_url = '';
				$user_policy_res->head = '';
				$user_policy_res->company_id = '';
				$user_policy_res->del = '0';
				$user_policy_res->status = '1';
				$user_policy_res->created_at = time();
				$user_policy_res->save();
			}
			foreach($holder_res as $value){
				$holder_check_res = Person::where('papers_code',$value['ty_beibaoren_id_number'])
					->select('id','cust_type')
					->first();
				if(empty($holder_check_res)){
					$user_holder_res = new Person();
					$user_holder_res->name = $value['ty_beibaoren_name'];
					$user_holder_res->papers_type = $value['ty_beibaoren_id_type'];
					$user_holder_res->papers_code = $value['ty_beibaoren_id_number'];
					$user_holder_res->papers_start = '';
					$user_holder_res->papers_end = '';
					$user_holder_res->sex = $value['ty_beibaoren_sex'];
					$user_holder_res->birthday = $value['ty_beibaoren_birthday'];
					$user_holder_res->address = $policy_res['ty_beibaoren_provinces'].'-'.$policy_res['ty_beibaoren_city'].'-'.$policy_res['ty_beibaoren_county'];
					$user_holder_res->address_detail = $value['ty_beibaoren_address'];
					$user_holder_res->phone = $value['ty_beibaoren_phone'];
					$user_holder_res->email = $value['ty_beibaoren_email'];
					$user_holder_res->postcode = '';
					$user_holder_res->cust_type = '1';//客户类型，1：普通用户，2：代理人
					$user_holder_res->authentication = '1';//认证状态，1：未认证，2：已认证
					$user_holder_res->up_url = '';
					$user_holder_res->down_url = '';
					$user_holder_res->person_url = '';
					$user_holder_res->head = '';
					$user_holder_res->company_id = '';
					$user_holder_res->del = '0';
					$user_holder_res->status = '1';
					$user_holder_res->created_at = time();
					$user_holder_res->updated_at = time();
					$user_holder_res->save();
				}
			}
			$user_res = Person::where('papers_code',$policy_res['ty_toubaoren_id_number'])
				->select('id','cust_type')
				->first();
			$cust_warranty = new CustWarranty();
			$cust_warranty->warranty_uuid = '';//内部保单唯一标识
			$cust_warranty->pro_policy_no = $return_data['union_order_code'];//投保单号
			$cust_warranty->warranty_code = '';//保单号
			$cust_warranty->company_id = '';//公司id,固定值
			$cust_warranty->user_id = $user_res['id'];//用户id
			$cust_warranty->user_type = $user_res['cust_type'];//用户类型
			$cust_warranty->agent_id = '';//代理人id
			$cust_warranty->ditch_id = '';//渠道id
			$cust_warranty->plan_id = '';//计划书id
			$cust_warranty->product_id = $prepare['private_p_code'];//产品id
			$cust_warranty->premium = $return_data['total_premium'];//价格
			$cust_warranty->start_time = '';//起保时间
			$cust_warranty->end_time = '';//保障结束时间
			$cust_warranty->ins_company_id = '';//保险公司id
			$cust_warranty->count = '1';//购买份数
			$cust_warranty->pay_time = '';//支付时间
			$cust_warranty->pay_way = '3';//支付方式1 银联 2 支付宝 3 微信 4现金
			$cust_warranty->by_stages_way = '';//分期方式
			$cust_warranty->is_settlement = '0';//佣金 0表示未结算，1表示已结算
			$cust_warranty->warranty_url = '';//电子保单下载地址
			$cust_warranty->warranty_from = '2';//保单来源 1 自购 2线上成交 3线下成交 4导入
			$cust_warranty->type = '1';//保单类型,1表示个人保单，2表示团险保单，3表示车险保单
			$cust_warranty->check_status = '3';//核保状态
			$cust_warranty->pay_status = '0';//支付状态
			$cust_warranty->warranty_status = '2';//保单状态
			$cust_warranty->created_at = time();//创建时间
			$cust_warranty->updated_at = time();//更新时间
			$cust_warranty->state = '1';//删除标识 0删除 1可用
			$cust_warranty->save();
			//投保人信息
			$cust_warranty_person = new CustWarrantyPerson();
			$cust_warranty_person->warranty_uuid = '';//内部保单唯一标识
			$cust_warranty_person->out_order_no = $return_data['union_order_code'];//被保人单号
			$cust_warranty_person->type = '1';//人员类型: 1投保人 2被保人 3受益人
			$cust_warranty_person->relation_name = '';//被保人 投保人的（关系）
			$cust_warranty_person->name = $policy_res['ty_toubaoren_name'];//姓名
			$cust_warranty_person->card_type = $policy_res['ty_toubaoren_id_type'];//证件类型（1为身份证，2为护照，3为军官证）
			$cust_warranty_person->card_code = $policy_res['ty_toubaoren_id_number'];//证件号
			$cust_warranty_person->phone = $policy_res['ty_toubaoren_phone'];//手机号
			$cust_warranty_person->occupation = '';//职业
			$cust_warranty_person->birthday = $policy_res['ty_toubaoren_birthday'];//生日
			$cust_warranty_person->sex = $policy_res['ty_toubaoren_sex'];//性别 1 男 2 女 '
			$cust_warranty_person->age = '';//年龄
			$cust_warranty_person->email = $policy_res['ty_toubaoren_email'];//邮箱
			$cust_warranty_person->nationality = '中国';//国籍
			$cust_warranty_person->annual_income = '';//年收入
			$cust_warranty_person->height = '';//身高
			$cust_warranty_person->weight = '';//体重
			$cust_warranty_person->area = $policy_res['ty_toubaoren_provinces'].'-'.$policy_res['ty_toubaoren_city'].'-'.$policy_res['ty_toubaoren_county'];//地区
			$cust_warranty_person->address = $policy_res['channel_user_address'];//详细地址
			$cust_warranty_person->start_time = '';//起保时间
			$cust_warranty_person->end_time = '';//保障结束时间
			$cust_warranty_person->created_at = time();//创建时间
			$cust_warranty_person->updated_at = time();//更新时间
			$cust_warranty_person->save();
			//被保人信息
			if(count($holder_res)>0){//多个被保人
				foreach($holder_res as $value){
					$cust_warranty_person = new CustWarrantyPerson();
					$cust_warranty_person->warranty_uuid = '';//内部保单唯一标识
					$cust_warranty_person->out_order_no = $return_data['union_order_code'];//被保人单号
					$cust_warranty_person->type = '2';//人员类型: 1投保人 2被保人 3受益人
					$cust_warranty_person->relation_name = '';//被保人 投保人的（关系）
					$cust_warranty_person->name = $value['ty_beibaoren_name'];//姓名
					$cust_warranty_person->card_type = $value['ty_beibaoren_id_type'];//证件类型（1为身份证，2为护照，3为军官证）
					$cust_warranty_person->card_code = $value['ty_beibaoren_id_number'];//证件号
					$cust_warranty_person->phone = $value['ty_beibaoren_phone'];//手机号
					$cust_warranty_person->occupation = '';//职业
					$cust_warranty_person->birthday = $value['ty_beibaoren_birthday'];//生日
					$cust_warranty_person->sex = $value['ty_beibaoren_sex'];//性别 1 男 2 女 '
					$cust_warranty_person->age = '';//年龄
					$cust_warranty_person->email = $value['ty_beibaoren_email'];//邮箱
					$cust_warranty_person->nationality = '中国';//国籍
					$cust_warranty_person->annual_income = '';//年收入
					$cust_warranty_person->height = '';//身高
					$cust_warranty_person->weight = '';//体重
					$cust_warranty_person->area = $value['ty_beibaoren_provinces'].'-'.$value['ty_beibaoren_city'].'-'.$value['ty_beibaoren_county'];//地区
					$cust_warranty_person->address = $value['channel_user_address'];//详细地址
					$cust_warranty_person->start_time = '';//起保时间
					$cust_warranty_person->end_time = '';//保障结束时间
					$cust_warranty_person->created_at = time();//创建时间
					$cust_warranty_person->updated_at = time();//更新时间
					$cust_warranty_person->save();
				}
			}
			//渠道操作表
			$ChannelOperate = new ChannelOperate();
			$ChannelOperate->channel_user_code = $policy_res['ty_toubaoren_id_number'];
			$ChannelOperate->order_id = $cust_warranty->id;
			$ChannelOperate->proposal_num = $return_data['union_order_code'];
			$ChannelOperate->prepare_status = '200';
			$ChannelOperate->operate_time = date('Y-m-d',time());
			$ChannelOperate->save();
			DB::commit();
			return true;
		}catch (\Exception $e)
		{
			DB::rollBack();
			LogHelper::logChannelError([$return_data, $prepare], $e->getMessage(), 'addOrder');
			return false;
		}
	}
}
