<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/4/08
 * Time: 17:12
 * 韵达快递保--外接接口
 */
namespace App\Http\Controllers\ChannelsApiControllers\Yunda;

use App\Models\Bank;
use Illuminate\Http\Request;
use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Jobs\YunDaPayInsure;
use App\Models\Person;
use App\Models\ChannelInsureSeting;
use App\Models\CustWarranty;
use App\Models\CustWarrantyPerson;
use App\Models\ChannelContract;
use App\Models\ChannelOperate;
use App\Models\ChannelJointLogin;
use App\Jobs\YdWechatPay;
use App\Helper\TokenHelper;
use App\Helper\IdentityCardHelp;
use DateTime;

class IntersController
{

    protected $request;

    protected $person_code;

    /**
     * 初始化
     * @access public
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->log_helper = new LogHelper();
        $this->sign_help = new RsaSignHelp();
    }

    /**
     * 联合登录接口
     * @access public
     * @param channel_code|渠道代号
     * @param insured_name|姓名
     * @param insured_code|证件号
     * @param insured_phone|电话
     * @param insured_email|y邮箱
     * @param insured_province|省
     * @param insured_city|市
     * @param insured_county|县
     * @param insured_address|详细地址
     * @param bank_name|银行名称
     * @param bank_code|银行卡号
     * @param bank_phone|预留手机号
     * @param bank_address|开户行地址
     * @return json
     * joint_status(自定义banner顶部显示状态)
	 * 联合登录功能介绍：
	 * 1.判断当日投保状态，未投保，去投保
	 * 2.判断当日投保状态，已投保，查询投保状态，显示投保
	 *
     */
    public function jointLogin(){
        $input = $this->request->all();
        //LogHelper::logChannelSuccess($input, 'YD_joint_login_params');
        $return_data =[];
        $webapi_route = config('yunda.server_host').config('yunda.webapi_route');
        if(empty($input)){
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'empty';
            $return_data['data']['status'] = config('yunda.joint_status.no');//（01显示/02不显示）
            $return_data['data']['content'] = 'empty';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        if(!is_array($input)){
            $input = json_decode($input,true);
        }
        $insured_name = isset($input['insured_name'])?empty($input['insured_name'])?"":$input['insured_name']:"";
        $insured_code =isset($input['insured_code'])?empty($input['insured_code'])?"":$input['insured_code']:"";
        $insured_phone = isset($input['insured_phone'])?empty($input['insured_phone'])?"":$input['insured_phone']:"";
        $channel_order_code = isset($input['channel_order_code'])?empty($input['channel_order_code'])?"":$input['channel_order_code']:"";
        //姓名，身份证信息，手机号判空
        if(!$insured_name||!$insured_code||!$insured_phone){
			$return_data['code'] = '500';
			$return_data['message']['digest'] = 'default';
			$return_data['message']['details'] = 'empty';
			$return_data['data']['status'] = config('yunda.joint_status.no');//（01显示/02不显示）
			$return_data['data']['content'] = 'insured_name or insured_code or insured_phone  is empty';
			return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        //TODO  联合登录记录信息值
		//先判断person表里有没有值-插入
		$person_result = Person::where('phone',$insured_phone)->select('id','phone')->first();
        if(empty($person_result)){
			Person::insert([
				'name'=>$insured_name,
				'papers_type'=>'1',
				'papers_code'=>$insured_code,
				'phone'=>$insured_phone,
				'cust_type'=>'1',
				'authentication'=>'1',
				'del'=>'0',
				'status'=>'1',
				'created_at'=>time(),
				'updated_at'=>time(),
			]);
		}
		$person_result = Person::where('phone',$insured_phone)->select('id','phone','papers_code')->first();
        //TODO 判断泰康是否签约
		//TODO 没有签约，签约接口
		//TODO 签约失败，直接去英大

		//TODO 已签约，直接去支付
		//TODO 支付失败，订单置失效，去英大

		//TODO 英大操作
		//再判断channel_joint_login表里有没有值-插入(今天的值)
		$channel_login_result = ChannelJointLogin::where('phone',$insured_phone)
			->where('login_start','>=',strtotime(date('Y-m-d')))
			->where('login_start','<',strtotime(date('Y-m-d',strtotime('+1 day'))))
			->select('phone')
			->first();
		if(empty($channel_login_result)){
			ChannelJointLogin::insert([
				'phone'=>$insured_phone,
				'login_start'=>time(),
			]);
		}
        $bank_local = Bank::where('cust_id',$person_result['id'])->select('bank_code')->first();
        if(empty($bank_local)){
        	$bank_code = '';
        }else{
			$bank_code = $bank_local['bank_code'];
		}
		$bank_code = isset($input['bank_code'])?empty($input['bank_code'])?$bank_code:$input['bank_code']:$bank_code;
        $token = TokenHelper::getToken($input)['token'];
        //银行卡信息判空
        if(!$bank_code){
            $return_data['code'] = '202';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'no_bank';
            $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
            $return_data['data']['content'] = '绑定银行卡,开启快递保免密支付,每日出行有保障>>';
            $return_data['data']['target_url'] = $webapi_route.'ins_error/no_bank?token='.$token;
			$return_data['data']['local_url'] = $webapi_route.'ins_center?token='.$token;
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        //银行卡入库
		$bank_res = Bank::where('bank_code',$bank_code)->select('id')->first();
        if(empty($bank_res)){
			Bank::insert([
				'cust_id'=>$person_result['id'],
				'cust_type'=>'1',
				'bank'=>$input['bank_name']??"",
				'bank_code'=>$bank_code,
				'bank_city'=>$input['bank_address']??"",
				'phone'=>$input['bank_phone']??"",
				'created_at'=>time(),
				'updated_at'=>time(),
			]);
		}
        //用用户身份证信息查询授权状态
		//todo  联合登录有两种情况；未开免密；保险生效中
		//走到这一步，基本都已经授权
        $user_setup_res = ChannelInsureSeting::where('cust_cod',$insured_code)
            ->select('authorize_status','authorize_start','authorize_bank','auto_insure_status','auto_insure_type','auto_insure_price','auto_insure_time','warranty_id','insure_days','insure_start')
            ->first();
        if(empty($user_setup_res)){//未授权(首次购买)
            $return_data['code'] = '203';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'no_authorize';
            $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
            $return_data['data']['content'] = '开启快递保免密支付,每日出行有保障>>';
            $return_data['data']['target_url'] = $webapi_route.'ins_error/no_authorize?token='.$token;
			$return_data['data']['local_url'] = $webapi_route.'ins_center?token='.$token;
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        if(!$user_setup_res['authorize_status']||!$user_setup_res['auto_insure_status']){
            $return_data['code'] = '204';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'no_authorize';
            $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
            $return_data['data']['content'] = '开启快递保免密支付,每日出行有保障>>';
            $return_data['data']['target_url'] = $webapi_route.'ins_error/no_authorize?token='.$token;
			$return_data['data']['local_url'] = $webapi_route.'ins_center?token='.$token;
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        $input['bank_code'] =  $user_setup_res['authorize_bank']; 
        $input['bank_phone'] =  $person_result['phone'];
        $input['channel_order_code'] =  $channel_order_code;
		//todo 查询保单生效状态（连续购买的保单是否还在保障期）
		if(empty($user_setup_res['warranty_id'])){//没有保单
			$insure_status = false;
		}else{
			if(empty($user_setup_res['insure_start'])||$user_setup_res['insure_days']){
				$insure_status = false;
			}else{
				if($user_setup_res['insure_start']+$user_setup_res['insure_days']*24*3600<time()){//保单过期
					$insure_status = false;
				}else{//保单在保
					$insure_status = true;
				}
			}
		}
        if(!$insure_status){//需要购买新保单
            //todo 当前投保状态，今天有没有进行投保操作
           $cust_res = Person::where('papers_code',$insured_code)->select('id')->first();
           if(empty($cust_res)){
               $current_insurance_status = false;
           }else{
			   $cust_warranty_res = CustWarranty::where('user_id',$cust_res['id'])
				   ->where('warranty_status','<>','6')//失效的订单
				   ->where('created_at','>',strtotime(date('Y-m-d')).'000')//今天凌晨的时间戳
				   ->select('warranty_uuid','warranty_code','created_at','check_status','pay_status','warranty_status')
				   ->orderBy('created_at','desc')
				   ->first();
			   if(empty($cust_warranty_res)){
			   	 $current_insurance_status = false;
			   }else{
			   	 $current_insurance_status = true;
			   }
		   }
            if(!$current_insurance_status){//TODO 没有进行过投保操作
				$input['insured_days'] = empty($user_setup_res['auto_insure_type'])?'1':$user_setup_res['auto_insure_type'];
				$input['price'] = '2';
                switch ($input['insured_days']){
                    case '1':
						$input['price'] = $user_setup_res['auto_insure_price'];
                        break;
                    case '3':
						$input['price'] = $user_setup_res['auto_insure_price'];
                        break;
                    case '10':
                        $input['price'] = $user_setup_res['auto_insure_price'];
                        break;
                }
                //LogHelper::logSuccess($input,'YD_pay_insure1_params');
                dispatch(new YunDaPayInsure($input));//TODO 投保操作（异步队列）
                $return_data['code'] = '200';
                $return_data['message']['digest'] = 'default';
                $return_data['message']['details'] = 'insuring';
                $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
                $return_data['data']['content'] = '今日快递保未生效,点击查看原因>>';
                $return_data['data']['target_url'] = $webapi_route.'do_insured?token='.$token;
				$return_data['data']['local_url'] = $webapi_route.'ins_center?token='.$token;
                return json_encode($return_data,JSON_UNESCAPED_UNICODE);
            }else{
                //查询投保状态
                $check_status = $cust_warranty_res['check_status'];//核保状态（默认0,1核保中, 2核保失败，3核保成功）
                $pay_status = $cust_warranty_res['pay_status'];//支付状态 （默认0，1支付中,2支付失败,3支付成功）
                $warranty_status = $cust_warranty_res['warranty_status'];//保单状态 1待处理, 2待支付,3待生效, 4保障中,5可续保，6已失效，7已退保  8已过保
                //TODO  匹配状态,组合查状态
                if($warranty_status=='3'||$warranty_status=='4'){
                    $return_data['code'] = '200';
                    $return_data['message']['digest'] = 'default';
                    $return_data['message']['details'] = 'insured';
                    $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
                    $return_data['data']['content'] = '今日快递保生效中>>';
                    $return_data['data']['target_url'] = $webapi_route.'ins_center?token='.$token;
					$return_data['data']['local_url'] = $webapi_route.'ins_center?token='.$token;
                    return json_encode($return_data,JSON_UNESCAPED_UNICODE);
                }else{
                	if($check_status=='2'){
						$return_data['code'] = '205';
						$return_data['message']['digest'] = 'default';
						$return_data['message']['details'] = 'isured_fail';
						$return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
						$return_data['data']['content'] = '今日快递保未生效,点击查看原因>>';
						$return_data['data']['target_url'] = $webapi_route.'ins_error/isured_fail?token='.$token;
						$return_data['data']['local_url'] = $webapi_route.'ins_center?token='.$token;
						return json_encode($return_data,JSON_UNESCAPED_UNICODE);
					}
					if($pay_status=='2'){
						$return_data['code'] = '205';
						$return_data['message']['digest'] = 'default';
						$return_data['message']['details'] = 'isured_fail';
						$return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
						$return_data['data']['content'] = '今日快递保未生效,点击查看原因>>';
						$return_data['data']['target_url'] = $webapi_route.'ins_error/isured_fail?token='.$token;
						$return_data['data']['local_url'] = $webapi_route.'ins_center?token='.$token;
						return json_encode($return_data,JSON_UNESCAPED_UNICODE);
					}
                    $return_data['code'] = '205';
                    $return_data['message']['digest'] = 'default';
                    $return_data['message']['details'] = 'isured_fail';
                    $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
                    $return_data['data']['content'] = '今日快递保未生效,点击查看原因>>';
                    $return_data['data']['target_url'] = $webapi_route.'ins_error/isured_fail?token='.$token;
					$return_data['data']['local_url'] = $webapi_route.'ins_center?token='.$token;
                    return json_encode($return_data,JSON_UNESCAPED_UNICODE);
                }
            }
        }
        $return_data['code'] = '200';
        $return_data['message']['digest'] = 'default';
        $return_data['message']['details'] = 'insured';
        $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
        $return_data['data']['content'] = '今日快递保生效中>>';
        $return_data['data']['target_url'] = $webapi_route.'ins_center?token='.$token;
        $return_data['data']['local_url'] = $webapi_route.'ins_center?token='.$token;
        return json_encode($return_data,JSON_UNESCAPED_UNICODE);
    }

    public function getTkInsure($person_result){
    	if(empty($person_result)){
    		return false;
		}
		//TODO 判断有无预投保
		$prepareRes = CustWarranty::where('user_id',$person_result['id'])
			->where('end_time',strtotime(date('Y-m-d',time()).' 23:59:59').'999')//昨天的
			->where('warranty_status','2')//状态
			->select('pro_policy_no')
			->first();
    	if(empty($prepareRes)){
			return false;
		}
		//TODO 判断泰康是否签约
		$contractRes = ChannelContract::where('channel_user_code',$person_result['papers_code'])
			->select('openid','contract_id','contract_expired_time','channel_user_code')
			->first();
		if(empty($contractRes)){
			//TODO 没有签约，签约接口


			//TODO 签约失败，直接去英大
			return false;
		}

		//TODO 已签约，直接去支付
		//TODO 支付失败，订单置失效，去英大

	}

    /**
     * 授权查询接口
     * @access public
     * @param channel_code|渠道代号
     * @param insured_name|姓名
     * @param insured_code|证件号
     * @param insured_phone|电话
     * @return json
     * authorize_status(未授权01/已授权02)
     */
    public function authorizationQuery(){
        $input = $this->request->all();
        $return_data =[];
        if(empty($input)){
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'empty';
            $return_data['data']['status'] = config('yunda.authorize_status.no');//（01显示/02不显示）
            $return_data['data']['content'] = 'empty';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        if(!is_array($input)){
            $input = json_decode($input,true);
        }
		$insured_name = isset($input['insured_name'])?empty($input['insured_name'])?"":$input['insured_name']:"";
		$insured_code =isset($input['insured_code'])?empty($input['insured_code'])?"":$input['insured_code']:"";
		$insured_phone = isset($input['insured_phone'])?empty($input['insured_phone'])?"":$input['insured_phone']:"";
		//姓名，身份证信息，手机号判空
		if(!$insured_name||!$insured_code||!$insured_phone){
			$return_data['code'] = '500';
			$return_data['message']['digest'] = 'default';
			$return_data['message']['details'] = 'empty';
			$return_data['data']['status'] = config('yunda.joint_status.no');//（01显示/02不显示）
			$return_data['data']['content'] = 'insured_name or insured_code or insured_phone  is empty';
			return json_encode($return_data,JSON_UNESCAPED_UNICODE);
		}
		//TODO 先查询微信签约
		//TODO 有预投保，就走签约接口

		//TODO 再查询银行卡授权
		//TODO 授权状态根据微信签约状态和银行卡授权来确定
        $return_data =[];
        $return_data['code'] = '200';
        $return_data['message']['digest'] = 'default';
        $person_res = Person::where('papers_code',$insured_code)
            ->where('phone',$insured_phone)
            ->select('id')
            ->first();
		$token = isset(TokenHelper::getToken($input)['token'])?TokenHelper::getToken($input)['token']:"";
		$webapi_route = config('yunda.server_host').config('yunda.webapi_route').'bank_authorize?token='.$token;
        if(empty($person_res)){//没有此人信息，先插入信息，然后再授权
            Person::insert([
                'name'=>$insured_name,
                'papers_type'=>'1',
                'papers_code'=>$insured_code,
                'phone'=>$insured_phone,
                'cust_type'=>'1',
                'authentication'=>'1',
                'del'=>'0',
                'status'=>'1',
                'created_at'=>time(),
                'updated_at'=>time(),
            ]);
            $authorize_status = config('yunda.authorize_status.no');
            $return_data['message']['details'] = '未授权';
            $return_data['data']['status'] = $authorize_status;
			$return_data['data']['url'] =$webapi_route;
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        $user_setup_res = ChannelInsureSeting::where('cust_cod',$insured_code)
            ->select('authorize_status','authorize_start','authorize_bank','auto_insure_status','auto_insure_type','auto_insure_price','auto_insure_time')
            ->first();
        $authorize_status = $user_setup_res['auto_insure_status'];//todo 查询免密授权状态
        if(!$authorize_status){//未授权(首次购买)
            $authorize_status = config('yunda.authorize_status.no');
            $return_data['message']['details'] = '未授权';
            $return_data['data']['status'] = $authorize_status;
            $return_data['data']['url'] = $webapi_route;
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }else{
//			$authorize_status = config('yunda.authorize_status.no');
//			$return_data['message']['details'] = '未授权';
//			$return_data['data']['status'] = $authorize_status;
//			$return_data['data']['url'] = $webapi_route;
//			return json_encode($return_data,JSON_UNESCAPED_UNICODE);
            $authorize_status = config('yunda.authorize_status.yes');
            $return_data['message']['details'] = '已授权';
            $return_data['data']['status'] = $authorize_status;
            $return_data['data']['url'] = '';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 微信支付接口
     * 提供接口给银行卡口矿失败的人用，触发此接口默认使用微信扣款
     * @access public
     * @param channel_code|渠道代号
     * @param insured_name|姓名
     * @param insured_code|证件号
     * @param insured_phone|电话
     * @param insured_email|y邮箱
     * @param insured_province|省
     * @param insured_city|市
     * @param insured_county|县
     * @param insured_address|详细地址
     * @param bank_name|银行名称
     * @param bank_code|银行卡号
     * @param bank_phone|预留手机号
     * @param bank_address|开户行地址
     * @return json
     * 参数处理
     * 1.投保要素判空 姓名，证件号，手机号
     * 2.判断是否开通自动投保
     * 3.判断当天的保单是否生效
     * 4.判断是否已经绑定过微信
     * 5.判断是否有预投保单号
     */
    public function doWechatpay(){
        $input =  $this->request->all();
//        $input = '{"channel_code":"YD","insured_name":"王磊","insured_code":"6201031990121719172","insured_phone":"15701681527","insured_email":"wangs@inschos.com","insured_province":"北京市","insured_city":"北京市","insured_county":"东城区","insured_address":"夕照寺中街19号","bank_name":"工商银行","bank_code":"6222022002006651860 ","bank_phone":"15701681527","bank_address":"北京市东城区广渠门内广渠路支行"}';
        $return_data =[];
        if(empty($input)){
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'No Parameters';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        if(!is_array($input)){
            $input = json_decode($input,true);
        }
        $insured_name = $input['insured_name'];
        $insured_code = $input['insured_code'];
        $insured_phone = $input['insured_phone'];
        $insured_province = $input['insured_province'];
        $insured_city = $input['insured_city'];
        $insured_county = $input['insured_county'];
        $insured_address = $input['insured_address'];
        $bank_name = $input['bank_name'];
        $bank_code = $input['bank_code'];
        $bank_phone = $input['bank_phone'];
        $bank_address = $input['bank_address'];
        //投保要素判空 姓名，证件号，手机号
        if(!$insured_name||!$insured_code||!$insured_phone){
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'insured_name or insure_code or insured_phone is empty';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        $user_setup_res = ChannelInsureSeting::where('cust_cod',$insured_code)
            ->where('auto_insure_status','1')//开通自动投保
            ->select('warranty_id','insure_days','insure_start')
            ->first();
        //判断是否开通自动投保
        if(empty($user_setup_res)){
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] ='No Auto-insure';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        //判断当天的保单是否生效
        if(!empty($user_setup_res['warranty_id'])&&$user_setup_res['insure_start']+$user_setup_res['insure_days']*24*3600>time()){
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] ='Insurance Protecting';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        $wechat_bind = ChannelContract::where('channel_user_code',$insured_code)
            ->where('is_valid','0')//有效签约
            ->where('is_auto_pay','0')//开通自动投保
            ->select('openid','contract_id','contract_expired_time')
            ->first();
        //判断是否已经绑定过微信
        if(empty($wechat_bind)){
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'No Wechat Pay';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        $insure_prepare = ChannelOperate::where('channel_user_code',$insured_code)
            ->where('prepare_status','200')//预投保成功
            ->where('operate_time',date('Y-m-d',time()-24*3600))//前一天的订单
            ->select('proposal_num')
            ->first();
        //判断是否有预投保单号
        if(empty($insure_prepare)){
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'No Pre-insured';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        $params = [];
        $params['person_code'] = $insured_code;
        $params['union_order_code'] = $insure_prepare['proposal_num'];
        $params['openid'] = $wechat_bind['open_id'];
        $params['contract_id'] = $wechat_bind['contract_id'];
        dispatch(new YdWechatPay($params));//TODO 投保操作（异步队列）
        $return_data['code'] = '200';
        $return_data['message']['digest'] = 'default';
        $return_data['message']['details'] = 'insuring';
        return json_encode($return_data,JSON_UNESCAPED_UNICODE);
    }


	//签约操作
	public function insureSign($union_order_code,$phone,$person_code){
		if(!empty($_SERVER["HTTP_CLIENT_IP"])){
			$cip = $_SERVER["HTTP_CLIENT_IP"];
		}
		elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
			$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		elseif(!empty($_SERVER["REMOTE_ADDR"])){
			$cip = $_SERVER["REMOTE_ADDR"];
		}
		else{
			$cip = "无法获取！";
		}
		$data = [];
		$data['price'] = '2';
		$data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
		$data['quote_selected'] = '';
		$data['insurance_attributes'] = '';
		$data['union_order_code'] = $union_order_code;
		$data['pay_account'] = $phone??$union_order_code;
		$data['clientIp'] = $cip??'222.131.24.108';
		$data = $this->signhelp->tySign($data);
		//发送请求
		$response = Curl::to(env('TY_API_SERVICE_URL') . '/ins_curl/contract_ins')
			->returnResponseObject()
			->withData($data)
			->withTimeout(60)
			->post();
		if($response->status != 200){
			ChannelOperate::where('channel_user_code',$person_code)
				->where('proposal_num',$union_order_code)
				->update(['pay_status'=>'500','pay_content'=>$response->content]);
			$respose =  json_encode(['status'=>'502','content'=>'支付签约失败'],JSON_UNESCAPED_UNICODE);
			return false;
		}
		$return_data =  json_decode($response->content,true);//签约返回数据
		$url =  $return_data['result_content']['contracturl'];//禁止转义
		return view('frontend.channels.test_url')->with('url',$url);
	}
	//签约回调  TODO  签约回调
	public function contractCallBack(){
		$input = $this->request->all();
		LogHelper::logChannelSuccess($input, 'contractCallBack');
		$contract_code = $input['contract_code'];
		$union_order_code = substr($contract_code,0,-8);
		$channel_res = ChannelOperate::where('proposal_num',$union_order_code)->select('channel_user_code')->first();
		$channel_user_code = $channel_res['channel_user_code'];
		$channel_contract_res = ChannelContract::where('channel_user_code',$channel_user_code)
			->where('is_valid',0)//TODO 签约是正常的
			->select('openid','contract_id','contract_expired_time')//openid,签约协议号,签约过期时间
			->first();
		$contract_expired_time = strtotime($channel_contract_res['contract_expired_time']);
		if(empty($channel_contract_res)){//没签约
			$channel_contract = new ChannelContract();
			$channel_contract->operate_time = $input['operate_time'];
			$channel_contract->request_serial = $input['request_serial'];
			$channel_contract->contract_expired_time = $input['contract_expired_time'];
			$channel_contract->contract_id = $input['contract_id'];
			$channel_contract->change_type = $input['change_type'];
			$channel_contract->contract_code = $input['contract_code'];
			$channel_contract->openid = $input['openid'];
			$channel_contract->channel_user_code = $channel_user_code;
			$channel_contract->save();
		}elseif($contract_expired_time<time()) {//签约已过期,更新签约
			ChannelContract::where('channel_user_code',$channel_user_code)->update([
				'operate_time' => $input['operate_time'],
				'request_serial' => $input['request_serial'],
				'contract_expired_time' => $input['contract_expired_time'],
				'contract_id' => $input['contract_id'],
				'change_type' => $input['change_type'],
				'contract_code' => $input['contract_code'],
				'openid' => $input['openid'],
			]);
		}
		return json_encode(['status'=>'200','msg'=>'回调成功']);
	}
	//微信代扣支付
	public function insureWechatPay(){
		set_time_limit(0);//永不超时
		$access_token = $this->request->header('access-token');
		$access_token_data = json_decode($this->sign_help->base64url_decode($access_token),true);
		$channel_code  = $access_token_data['channel_code'];
		$person_code  = $access_token_data['person_code'];
		$person_phone  = $access_token_data['person_phone'];
		$channel_contract_info = ChannelContract::where('channel_user_code',$person_code)->select(['openid','contract_id'])->first();
		$channel_res = ChannelOperate::where('channel_user_code',$person_code)
			->where('prepare_status','200')
			->where('operate_time',date('Y-m-d',time()-24*3600))
			->select('proposal_num')
			->first();
		$union_order_code = $channel_res['proposal_num'];
		$data = [];
		$data['price'] = '2';
		$data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
		$data['quote_selected'] = '';
		$data['insurance_attributes'] = '';
		$data['union_order_code'] = $union_order_code;
		$data['pay_account'] = $channel_contract_info['openid']??"oalT50N9lxHbWhGBDTF3FMCYhTx8";
		$data['contract_id'] = $channel_contract_info['contract_id']??"201801050468783068";
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
			return redirect('/channelsapi/to_insure')->with('status','支付失败');
		}
		$return_data =  json_decode($response->content,true);//返回数据
		// LogHelper::logChannelSuccess($return_data, 'pay_return_data');
		//TODO  可以改变订单表的状态
		ChannelOperate::where('channel_user_code',$person_code)
			->where('proposal_num',$union_order_code)
			->update(['pay_status'=>'200']);
		WarrantyRule::where('union_order_code',$union_order_code)
			->update(['status'=>'1']);
		Order::where('order_code',$union_order_code)
			->update(['status'=>'1']);
		$respose =  json_encode(['status'=>'200','content'=>'支付成功'],JSON_UNESCAPED_UNICODE);
		return redirect('/channelsapi/do_insure')->with('status','支付成功');
	}
}