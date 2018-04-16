<?php

namespace App\Http\Controllers\ChannelsApiControllers;


use Illuminate\Http\Request;
use App\Helper\DoChannelsSignHelp;
use App\Helper\RsaSignHelp;
use App\Helper\AesEncrypt;
use Ixudra\Curl\Facades\Curl;
use Validator, DB, Image, Schema;
use App\Models\Channel;
use App\Models\UserChannel;
use App\Models\User;
use App\Models\UserContact;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Session,Cache;
use App\Models\Order;
use App\Models\OrderParameter;
use App\Models\WarrantyPolicy;
use App\Models\WarrantyRecognizee;
use App\Models\WarrantyRule;
use \Illuminate\Support\Facades\Redis;
use App\Models\ChannelPrepareInfo;
use App\Models\ChannelOperate;
use App\Models\ChannelContract;
use App\Models\TimedTask;
use App\Helper\LogHelper;
use App\Helper\IdentityCardHelp;
use App\Helper\Issue;
use App\Jobs\YunDaPrepare;

use App\Models\Person;
use App\Models\CustWarranty;
use App\Models\CustWarrantyPerson;


class ChannelInfosController extends BaseController
{
    /**
     * 初始化
     *
     */
    public function __construct(Request $request)
    {
        $this->sign_help = new DoChannelsSignHelp();
        $this->signhelp = new RsaSignHelp();
        $this->request = $request;
    }

    /**
     * 
     * 获取预投保信息，存入redis队列
     * @param $this->request->all()
     * @return json
     * todo 接受信息(入队)
     */
    public function getPrepare()
    {
        set_time_limit(0);//永不超时
        $params = $this->request->all();
        $biz_content = $params['biz_content'];
        Redis::rPush("prepare_info",$biz_content);//入队操作
        //dispatch(new YunDaPrepare());
        return json_encode(['status' => '200', 'content' => '预订单信息已收到'],JSON_UNESCAPED_UNICODE);
    }

    /**
     *
     * 预投保信息处理
     * 出队，变形，投保，入库
     * todo  定时任务，处理信息（出队，变形，投保，入库）
     */
    public function insertPrepare()
   {

        $count = Redis::Llen('prepare_info');
		LogHelper::logChannelSuccess($count, 'YD_prepara_count');
        if($count<1){
            // TimedTask::insert([
                    // 'task_name'=>'yd_insure',
                    // 'task_type'=>'minutes',
                    // 'service_ip'=>'10.1.210.13',
                    // 'start_time'=>time(),
                    // 'task_time'=>'60',
                    // 'end_time'=>time(),
                    // 'timestamp'=>time(),
                    // 'status'=>'0',//执行结束
                // ]);
            die;
        }
        set_time_limit(0);//永不超时
        echo '处理开始时间'.date('Y-m-d H:i:s', time()).'<br/>';
        // LogHelper::logChannelSuccess(date('Y-m-d H:i:s', time()), 'YD_check_insure_start_time');
        $file_area = "/var/www/html/yunda.inschos.com/public/Tk_area.json";
        $file_bank = "/var/www/html/yunda.inschos.com/public/Tk_bank.json";
        $json_area = file_get_contents($file_area);
        $json_bank = file_get_contents($file_bank);
        $area = json_decode($json_area,true);
        $bank = json_decode($json_bank,true);
        for($i=0;$i<$count;$i++) {
            $value = json_decode(base64_decode(Redis::lpop('prepare_info')),true);
            foreach($value as $key=>$item){//每次1000条数据
                if(key_exists($item['channel_provinces'],$area)) {
                    $item['channel_provinces'] = $area[$item['channel_provinces']];
                }
                if(key_exists($item['channel_city'],$area)){
                    $item['channel_city'] = $area[$item['channel_city']];
                }
                if(key_exists($item['channel_county'],$area)){
                    $item['channel_county'] = $area[$item['channel_county']];
                }
                if(key_exists($item['channel_bank_name'],$bank)){
                    $item['channel_bank_name'] = $bank[$item['channel_bank_name']];
                }
                $item['operate_time'] = date('Y-m-d',time());
                //预投保操作，批量操作（定时任务）
                $idCard_status = IdentityCardHelp::getIDCardInfo($item['channel_user_code']);
                if($idCard_status['status']=='2') {
                    //TODO 判断是否已经投保
                    $channel_insure_res = ChannelOperate::where('channel_user_code',$item['channel_user_code'])
                        ->where('operate_time',$item['operate_time'])
                        ->where('prepare_status','200')
                        ->select('proposal_num')
                        ->first();
                    //已经投保的，不再投保
                    if(!empty($channel_insure_res)){
                        return 'end';
                    }
                    $insure_status = $this->doInsurePrepare($item);
					$item['operate_code'] = '实名信息正确,预投保成功';
                    // ChannelPrepareInfo::insert($item);
                }else{
					$item['operate_code'] = '实名信息出错:身份证号';	
				}
					ChannelPrepareInfo::insert($item);	
            }
        }
//        TimedTask::where('task_name','yd_insure')->update([
//            'service_ip'=>$_SERVER['SERVER_ADDR'],
//            'end_time'=>time(),
//            'status'=>'0',//执行结束
//        ]);
        echo '<br/>处理结束<br/>';
        echo '<br/>处理结束时间'.date('Y-m-d H:i:s', time());
        // LogHelper::logChannelSuccess(date('Y-m-d H:i:s', time()), 'YD_check_insure_end_time');
        return 'end';
//        }elseif($timed_task_res->status=='1'){
//            TimedTask::where('task_name','yd_insure')->update([
//                'timestamp'=>time(),
//            ]);
//            $time = $timed_task_res->timestamp;
//            if($time>time()){
//                TimedTask::where('task_name','yd_insure')->update([
//                    'end_time'=>time(),
//                    'status'=>'0',//执行结束
//                ]);
//                return 'end';
//            }
//        }
    }

    /**
     *
     * 签约用户预投保操作
     * @param $this->request->all()
     * @return json
     *
     */
    public function testWechatPre(){
        set_time_limit(0);//永不超时
        LogHelper::logChannelSuccess(date('Y-m-d H:i:s', time()), 'YD_check_insure_start_time');
        $contract_res_key = 'wechat_pre';
        $prepare_info_key = 'wechat_pre_info';
        if(!Redis::exists($contract_res_key)){
            $contract_res = ChannelContract::with('channel_user_info')
                ->select('is_auto_pay','openid','contract_id','contract_expired_time','channel_user_code')
                ->get();//查询所有已签约的客户
            Redis::set($contract_res_key,$contract_res);
        }
        $contract_res = Redis::get($contract_res_key);
        if(empty($contract_res)){
            return false;
        }
        $contract_res  = json_decode($contract_res,true);
        $contract_count = count($contract_res);
        $prepare_info_count = Redis::Llen($prepare_info_key);
        if($prepare_info_count<1){
            foreach ($contract_res as $value){
                $value = json_encode($value);
                Redis::rPush($prepare_info_key,$value);//入队操作
            }
        }
        if($prepare_info_count<1){
            die;
        }
        $file_area = "./Tk_area.json";
        $file_bank = "./Tk_bank.json";
        $json_area = file_get_contents($file_area);
        $json_bank = file_get_contents($file_bank);
        $area = json_decode($json_area,true);
        $bank = json_decode($json_bank,true);
        for($i=0;$i<$prepare_info_count;$i++) {//遍历出队
            $item = Redis::lpop($prepare_info_key);
            $item = json_decode($item,true);
            $item['channel_user_info']['operate_time'] = date('Y-m-d',time());
            //预投保操作，批量操作（定时任务）
            $idCard_status = IdentityCardHelp::getIDCardInfo($item['channel_user_info']['channel_user_code']);
            if($idCard_status['status']=='2') {
                    //TODO 判断是否已经投保
                    $channel_insure_res = ChannelOperate::where('channel_user_code',$item['channel_user_info']['channel_user_code'])
                        ->where('operate_time',$item['channel_user_info']['operate_time'])
                        ->where('prepare_status','200')
                        ->select('proposal_num')
                        ->first();
                    //已经投保的，不再投保
                if(!empty($channel_insure_res)){
                    return 'end';
                }
                $insure_status = $this->doInsurePrepare($item['channel_user_info']);
				if($insure_status){
					$item['operate_code'] = '实名信息正确,预投保成功';
				}else{
					$item['operate_code'] = '实名信息正确,预投保失败';
				}
            }else{
                $item['operate_code'] = '实名信息出错:身份证号';
            }
                ChannelPrepareInfo::insert($item['channel_user_info']);
            }
        LogHelper::logChannelSuccess(date('Y-m-d H:i:s', time()), 'YD_check_insure_end_time');
        return 'end';
    }

    /**
     * 预投保操作
     *
     */
    public function doInsurePrepare($prepare){
		set_time_limit(0);//永不超时
        $data = [];
        $insurance_attributes = [];
        $base = [];
        $base['ty_start_date'] = $prepare['operate_time'];
        $toubaoren = [];
        $toubaoren['ty_toubaoren_name'] = $prepare['channel_user_name'];//投保人姓名
        $toubaoren['ty_toubaoren_id_type'] = $prepare['channel_user_type']??"01";//证件类型
        $toubaoren['ty_toubaoren_id_number'] = $prepare['channel_user_code'];;//证件号
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
        $beibaoren[0]['ty_beibaoren_id_type'] = $prepare['channel_user_type']??"01";
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
            return false;
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
        $add_res = $this->addOrder($return_data, $prepare,$toubaoren,$beibaoren);
        if($add_res){
            $return_data =  json_encode(['status'=>'200','content'=>'投保完成'],JSON_UNESCAPED_UNICODE);
            return true;
        }else{
			$return_data =  json_encode(['status'=>'500','content'=>'投保失败'],JSON_UNESCAPED_UNICODE);
			return false;
		}
    }

    /**
     * 对象转化数组
     *
     */
    public function object2array($object) {
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
		DB::beginTransaction();//开启事务
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
                $order->private_p_code = $private_p_code??"VGstMTEyMkEwMUcwMQ";
                $order->ty_product_id = $prepare['ty_product_id']??"15";
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
            $WarrantyRule->ty_product_id = "15";
            $WarrantyRule->private_p_code = "VGstMTEyMkEwMUcwMQ";
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
     * 测试处理预投保信息
     *
     */
    public  function testPrepare(){
        $res = ChannelPrepareInfo::where('channel_user_name','林敏丽')->first();
        $res = json_encode($res);
        print_r($res);
    }

    /**
     * 微信代扣支付
     * 定时任务，跑支付
     */
    public function insureWechatPay(){
        set_time_limit(0);//永不超时
        $channel_contract_info = ChannelContract::where('is_valid','0')//有效签约
            ->where('is_auto_pay','0')
            ->select('openid','contract_id','contract_expired_time','channel_user_code')
            //openid,签约协议号,签约过期时间,签约人身份证号
            ->get();
        //循环请求，免密支付
        foreach ($channel_contract_info as $value){
            $person_code  = $value['channel_user_code'];
            $channel_res = ChannelOperate::where('channel_user_code',$person_code)
                ->where('prepare_status','200')//预投保成功
                ->where('operate_time',date('Y-m-d',time()-24*3600))//前一天的订单
                ->where('is_work','1')//已上工
                ->select('proposal_num')
                ->first();
            $union_order_code = $channel_res['proposal_num'];
            $data = [];
            $data['price'] = '2';
            $data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
            $data['quote_selected'] = '';
            $data['insurance_attributes'] = '';
            $data['union_order_code'] = $union_order_code;
            $data['pay_account'] = $value['openid'];
            $data['contract_id'] = $value['contract_id'];
            $data = $this->signhelp->tySign($data);
            //发送请求
            $response = Curl::to(env('TY_API_SERVICE_URL') . '/ins_curl/wechat_pay_ins')
                ->returnResponseObject()
                ->withData($data)
                ->withTimeout(60)
                ->post();
            // print_r($response);die;
            if($response->status != 200){
                ChannelOperate::where('channel_user_code',$person_code)
                    ->where('proposal_num',$union_order_code)
                    ->update(['pay_status'=>'500','pay_content'=>$response->content]);
                //TODO 签约链接失效（业务员自己取消签约了）
                //TODO 网络延迟等错误，没有判断
//                ChannelContract::where('channel_user_code',$person_code)
//                     ->update([
//                         'is_valid'=>1,//签约失败
//                     ]);
            }
            $return_data =  json_decode($response->content,true);//返回数据
            //TODO  可以改变订单表的状态
            ChannelOperate::where('channel_user_code',$person_code)
                ->where('proposal_num',$union_order_code)
                ->update(['pay_status'=>'200']);
            WarrantyRule::where('union_order_code',$union_order_code)
                ->update(['status'=>'1']);
            Order::where('order_code',$union_order_code)
                ->update(['status'=>'1']);
        }
    }

    /**
     * 微信代扣支付
     * 定时任务，跑支付
     */
    public function doInsureWechatPay(){
        set_time_limit(0);//永不超时
        $channel_contract_info = ChannelContract::where('is_valid','0')//有效签约
        ->where('is_auto_pay','0')
            ->select('openid','contract_id','contract_expired_time','channel_user_code')
            //openid,签约协议号,签约过期时间,签约人身份证号
            ->groupBy('openid')
            ->get();
        //循环请求，免密支付
        foreach ($channel_contract_info as $value){
            $person_code  = $value['channel_user_code'];
            $channel_res = ChannelOperate::where('channel_user_code',$person_code)
                ->where('prepare_status','200')//预投保成功
                ->where('operate_time',date('Y-m-d',time()-24*3600))//前一天的订单
                ->where('is_work','1')//已上工
                ->select('proposal_num')
                ->first();
            $union_order_code = $channel_res['proposal_num'];
            $data = [];
            $data['price'] = '2';
            $data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
            $data['quote_selected'] = '';
            $data['insurance_attributes'] = '';
            $data['union_order_code'] = $union_order_code;
            $data['pay_account'] = $value['openid'];
            $data['contract_id'] = $value['contract_id'];
            $data = $this->signhelp->tySign($data);
            //发送请求
            $response = Curl::to(env('TY_API_SERVICE_URL') . '/ins_curl/wechat_pay_ins')
                ->returnResponseObject()
                ->withData($data)
                ->withTimeout(60)
                ->post();
            // print_r($response);die;
            if($response->status != 200){
                ChannelOperate::where('channel_user_code',$person_code)
                    ->where('proposal_num',$union_order_code)
                    ->update(['pay_status'=>'500','pay_content'=>$response->content]);
                //TODO 签约链接失效（业务员自己取消签约了）
                //TODO 网络延迟等错误，没有判断
//                ChannelContract::where('channel_user_code',$person_code)
//                     ->update([
//                         'is_valid'=>1,//签约失败
//                     ]);
            }
            $return_data =  json_decode($response->content,true);//返回数据
            //TODO  可以改变订单表的状态
            ChannelOperate::where('channel_user_code',$person_code)
                ->where('proposal_num',$union_order_code)
                ->update(['pay_status'=>'200']);
            WarrantyRule::where('union_order_code',$union_order_code)
                ->update(['status'=>'1']);
            Order::where('order_code',$union_order_code)
                ->update(['status'=>'1']);
        }
    }

    /**
     * 查询签约客户详情
     * 根据签约信息表和预订单表查询谁签约了
     * 并查询预订单是否成功
     */
    public function selContractInfo(){
        $sql = "select channel_user_code,contract_id,openid,contract_code, count(distinct openid) from com_channel_contract_info group by openid";
        $res = ChannelContract::where('is_valid','<>','1')//有效签约
        ->where('is_auto_pay','<>','1')//同意自动扣费
        ->with([
            'channelOperateInfo'=>function($a){//前一日有预投保信息，投保信息是OK的
                $a->where('operate_time',date('Y-m-d',time()-24*3600))
                    ->where('prepare_status','200');
            },
            'channel_user_info'=>function($a){
                $a->select('channel_user_name','channel_user_phone','channel_user_code');
            },
        ])
            ->distinct('openid')
            ->groupBy('openid')
            ->select('openid','contract_id','contract_code','contract_expired_time','channel_user_code')
            ->get();
        dump(count($res));
        dump($res);
    }

    /**
     *
     * 定时出单
     *
     */
    public function issueAuto(){
        //        保单入库
        $data = Order::join('warranty_rule', 'order.id', 'warranty_rule.order_id')
            ->where('order.status', 1)
            ->where('warranty_rule.warranty_id', null)
            ->select('warranty_rule.*')
            ->get();
        foreach ($data as $v) {
            $insure = new Issue();
            $res = $insure->issue($v);
        }
    }

    /**
     *
     * 轮询出单
     *
     */
    public function insureIssue($person_code){
        $channel_operate_res = ChannelOperate::where('channel_user_code',$person_code)
            ->where('operate_time',date('Y-m-d',time()))
            ->select('proposal_num')
            ->first();
        if(empty($channel_operate_res)){
            return false;
        }
        $union_order_code = $channel_operate_res['proposal_num'];
        \Redis::rPush("issue_data",json_encode($union_order_code));//入队操作
        $count = \Redis::Llen('issue_data');
        for($i=0;$i<$count;$i++) {+
        $insure_data = json_decode(\Redis::lpop('issue_data'),true);//出队
            $issue_status = $this->doInsureIssue($insure_data);
            if(!$issue_status){
                LogHelper::logChannelError($insure_data, 'YD_TK_Insure_issue_'.$insure_data);//记录日志
            }
        }
    }
    /**
     *
     * 出单操作
     *
     */
    public function doInsureIssue($union_order_code){
        $warranty_rule = WarrantyRule::where('union_order_code', $union_order_code)->first();
        if(empty($warranty_rule)){
        return false;
        }
        $i = new Issue();
        $result = $i->issue($warranty_rule);
        if(!$result){
            $respose =  json_encode(['status'=>'503','content'=>'出单失败'],JSON_UNESCAPED_UNICODE);
            return false;
        }
        ChannelOperate::where('proposal_num',$union_order_code)
            ->update(['issue_status'=>'200']);
        $respose =  json_encode(['status'=>'200','content'=>'出单完成'],JSON_UNESCAPED_UNICODE);
        return true;
    }

	/**
	 * 测试预投保
	 *
	 */
    public function testPre(){
    	$params = '{"channel_user_name":"\u738b\u77f3\u78ca","channel_user_type":"01","channel_user_code":"342921199408271616","channel_user_phone":"15701681524","channel_user_email":"wangsl@inschos.com","channel_user_address":"\u5317\u4eac\u5e02\u4e1c\u57ce\u533a\u5915\u7167\u5bfa\u4e2d\u885714\u53f7","channel_bank_name":"\u4e2d\u56fd\u5efa\u8bbe\u94f6\u884c","channel_bank_address":"\u5317\u4eac\u5e02\u4e1c\u57ce\u533a","channel_bank_code":"621710007000065287892","channel_bank_phone":"15701681524","channel_provinces":"110000","channel_city":"110000","channel_county":"110014","courier_state":"\u56de\u9f99\u89c2\u4e1c\u5927\u8857","courier_start_time":"0000-00-00 00:00:00","channel_back_url":"","channel_account_id":"","channel_code":"","operate_code":"","operate_time":"2018-04-16","p_code":"","is_insure":"","created_at":null,"updated_at":null}';
    	$params = json_decode($params,true);
    	$pre_status = $this->testInsurePrepare($params);
	}

	/**
	 * 预投保操作
	 *
	 */
	public function testInsurePrepare($prepare){
		set_time_limit(0);//永不超时
		$data = [];
		$insurance_attributes = [];
		$base = [];
		$base['ty_start_date'] = $prepare['operate_time'];
		$toubaoren = [];
		$toubaoren['ty_toubaoren_name'] = $prepare['channel_user_name'];//投保人姓名
		$toubaoren['ty_toubaoren_id_type'] = $prepare['channel_user_type']??"01";//证件类型
		$toubaoren['ty_toubaoren_id_number'] = $prepare['channel_user_code'];;//证件号
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
		$beibaoren[0]['ty_beibaoren_id_type'] = $prepare['channel_user_type']??"01";
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
			return false;
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
		$add_res = $this->testaddOrder($return_data, $prepare,$toubaoren);
		if($add_res){
			$return_data =  json_encode(['status'=>'200','content'=>'投保完成'],JSON_UNESCAPED_UNICODE);
			return true;
		}else{
			$return_data =  json_encode(['status'=>'500','content'=>'投保失败'],JSON_UNESCAPED_UNICODE);
			return false;
		}
	}

	public function testOrder(){
		$return_data = '{"order_list":[{"out_order_no":"000021122201824029647205616","premium":200,"union_order_code":"000021122201824029647205616","name":"\u738b\u77f3\u78ca","card_type":"01","card_id":"342921199408271616","relation":"1","ins_start_time":"2018-04-17 07:00:00","ins_end_time":"2018-04-17 23:59:59"}],"total_premium":200,"union_order_code":"000021122201824029647205616","pay_way":{"pc":{"cardPay":"\u94f6\u884c\u5361\u652f\u4ed8"},"mobile":{"cardPay":"\u94f6\u884c\u5361\u652f\u4ed8"}}}';
		$prepare = '{"channel_user_name":"\u738b\u77f3\u78ca","channel_user_type":"01","channel_user_code":"342921199408271616","channel_user_phone":"15701681524","channel_user_email":"wangsl@inschos.com","channel_user_address":"\u5317\u4eac\u5e02\u4e1c\u57ce\u533a\u5915\u7167\u5bfa\u4e2d\u885714\u53f7","channel_bank_name":"\u4e2d\u56fd\u5efa\u8bbe\u94f6\u884c","channel_bank_address":"\u5317\u4eac\u5e02\u4e1c\u57ce\u533a","channel_bank_code":"621710007000065287892","channel_bank_phone":"15701681524","channel_provinces":"110000","channel_city":"110000","channel_county":"110014","courier_state":"\u56de\u9f99\u89c2\u4e1c\u5927\u8857","courier_start_time":"0000-00-00 00:00:00","channel_back_url":"","channel_account_id":"","channel_code":"","operate_code":"","operate_time":"2018-04-16","p_code":"","is_insure":"","created_at":null,"updated_at":null,"parameter":"0","private_p_code":"VGstMTEyMkEwMUcwMQ","ty_product_id":"VGstMTEyMkEwMUcwMQ","agent_id":"0","ditch_id":"0","user_id":"342921199408271616","identification":"0","union_order_code":"0"} ';
		$policy_res = '{"ty_toubaoren_name":"\u738b\u77f3\u78ca","ty_toubaoren_id_type":"01","ty_toubaoren_id_number":"342921199408271616","ty_toubaoren_birthday":"1994-08-27","ty_toubaoren_sex":"\u7537","ty_toubaoren_phone":"15701681524","ty_toubaoren_email":"wangsl@inschos.com","ty_toubaoren_provinces":"110000","ty_toubaoren_city":"110000","ty_toubaoren_county":"110014","channel_user_address":"\u5317\u4eac\u5e02\u4e1c\u57ce\u533a\u5915\u7167\u5bfa\u4e2d\u885714\u53f7","courier_state":"\u56de\u9f99\u89c2\u4e1c\u5927\u8857","courier_start_time":"0000-00-00 00:00:00"}';
		$holder_res = '[{"ty_beibaoren_name":"\u738b\u77f3\u78ca","ty_beibaoren_id_type":"01","ty_beibaoren_id_number":"342921199408271616","ty_beibaoren_birthday":"1994-08-27","ty_beibaoren_sex":"\u7537","ty_beibaoren_phone":"15701681524","ty_beibaoren_email":"wangsl@inschos.com","ty_beibaoren_provinces":"110000","ty_beibaoren_city":"110000","ty_beibaoren_county":"110014","channel_user_address":"\u5317\u4eac\u5e02\u4e1c\u57ce\u533a\u5915\u7167\u5bfa\u4e2d\u885714\u53f7","courier_state":"\u56de\u9f99\u89c2\u4e1c\u5927\u8857","courier_start_time":"0000-00-00 00:00:00"}]';
		$return_data = json_decode($return_data,true);
		$prepare = json_decode($prepare,true);
		$policy_res = json_decode($policy_res,true);
		$holder_res = json_decode($holder_res,true);
		$add_res = $this->testaddOrder($return_data, $prepare, $policy_res,$holder_res);
		dd($add_res);
	}

	/**
	 * 添加投保返回信息
	 * @access public
	 * @param $return_data|订单返回数据
	 * @param $prepare|预投保信息
	 * @param $policy_res|投保人信息
	 * @return mixed
	 * 新版表结构,保单返回数据只需要添加cust_warranty、cust_warranty_person、channel_operate、user
	 */
	protected function testaddOrder($return_data, $prepare, $policy_res,$holder_res)
	{
//		dump($return_data);
//		dump($prepare);
//		dump($policy_res);
//		dump($holder_res);
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
		$cust_warranty_person->out_order_no = '';//被保人单号
		$cust_warranty_person->type = '';//人员类型: 1投保人 2被保人 3受益人
		$cust_warranty_person->relation_name = '';//被保人 投保人的（关系）
		$cust_warranty_person->name = '';//姓名
		$cust_warranty_person->card_type = '';//证件类型（1为身份证，2为护照，3为军官证）
		$cust_warranty_person->card_code = '';//证件号
		$cust_warranty_person->phone = '';//手机号
		$cust_warranty_person->occupation = '';//职业
		$cust_warranty_person->birthday = '';//生日
		$cust_warranty_person->sex = '';//性别 1 男 2 女 '
		$cust_warranty_person->age = '';//年龄
		$cust_warranty_person->email = '';//邮箱
		$cust_warranty_person->nationality = '';//国籍
		$cust_warranty_person->annual_income = '';//年收入
		$cust_warranty_person->height = '';//身高
		$cust_warranty_person->weight = '';//体重
		$cust_warranty_person->area = '';//地区
		$cust_warranty_person->address = '';//详细地址
		$cust_warranty_person->start_time = '';//起保时间
		$cust_warranty_person->end_time = '';//保障结束时间
		$cust_warranty_person->created_at = time();//创建时间
		$cust_warranty_person->updated_at = time();//更新时间
		$cust_warranty_person->save();
		//被保人信息
		if(count($holder_res)>1){//多个被保人
			foreach($holder_res as $value){
				$cust_warranty_person = new CustWarrantyPerson();
				$cust_warranty_person->warranty_uuid = '';//内部保单唯一标识
				$cust_warranty_person->out_order_no = '';//被保人单号
				$cust_warranty_person->type = '';//人员类型: 1投保人 2被保人 3受益人
				$cust_warranty_person->relation_name = '';//被保人 投保人的（关系）
				$cust_warranty_person->name = '';//姓名
				$cust_warranty_person->card_type = '';//证件类型（1为身份证，2为护照，3为军官证）
				$cust_warranty_person->card_code = '';//证件号
				$cust_warranty_person->phone = '';//手机号
				$cust_warranty_person->occupation = '';//职业
				$cust_warranty_person->birthday = '';//生日
				$cust_warranty_person->sex = '';//性别 1 男 2 女 '
				$cust_warranty_person->age = '';//年龄
				$cust_warranty_person->email = '';//邮箱
				$cust_warranty_person->nationality = '';//国籍
				$cust_warranty_person->annual_income = '';//年收入
				$cust_warranty_person->height = '';//身高
				$cust_warranty_person->weight = '';//体重
				$cust_warranty_person->area = '';//地区
				$cust_warranty_person->address = '';//详细地址
				$cust_warranty_person->start_time = '';//起保时间
				$cust_warranty_person->end_time = '';//保障结束时间
				$cust_warranty_person->created_at = time();//创建时间
				$cust_warranty_person->updated_at = time();//更新时间
				$cust_warranty_person->save();
			}
		}else{
			//只有一个被保人
			$cust_warranty_person = new CustWarrantyPerson();
			$cust_warranty_person->warranty_uuid = '';//内部保单唯一标识
			$cust_warranty_person->out_order_no = '';//被保人单号
			$cust_warranty_person->type = '';//人员类型: 1投保人 2被保人 3受益人
			$cust_warranty_person->relation_name = '';//被保人 投保人的（关系）
			$cust_warranty_person->name = '';//姓名
			$cust_warranty_person->card_type = '';//证件类型（1为身份证，2为护照，3为军官证）
			$cust_warranty_person->card_code = '';//证件号
			$cust_warranty_person->phone = '';//手机号
			$cust_warranty_person->occupation = '';//职业
			$cust_warranty_person->birthday = '';//生日
			$cust_warranty_person->sex = '';//性别 1 男 2 女 '
			$cust_warranty_person->age = '';//年龄
			$cust_warranty_person->email = '';//邮箱
			$cust_warranty_person->nationality = '';//国籍
			$cust_warranty_person->annual_income = '';//年收入
			$cust_warranty_person->height = '';//身高
			$cust_warranty_person->weight = '';//体重
			$cust_warranty_person->area = '';//地区
			$cust_warranty_person->address = '';//详细地址
			$cust_warranty_person->start_time = '';//起保时间
			$cust_warranty_person->end_time = '';//保障结束时间
			$cust_warranty_person->created_at = time();//创建时间
			$cust_warranty_person->updated_at = time();//更新时间
			$cust_warranty_person->save();
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


