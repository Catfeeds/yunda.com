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
        $access_token = $this->request->header('access-token');
        $access_token_data = json_decode($this->sign_help->base64url_decode($access_token),true);
        $this->person_code = $access_token_data['person_code'];
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
     */
    public function jointLogin(){
        $input = $this->request->all();
        $return_data =[];
        $webapi_route = 'http://'.$_SERVER['HTTP_HOST'].config('yunda.webapi_route');
        $input = '{"channel_code":"YD","insured_name":"王磊","insured_code":"4108811994060565141234","insured_phone":"15701681527","insured_email":"wangs@inschos.com","insured_province":"北京市","insured_city":"北京市","insured_county":"东城区","insured_address":"夕照寺中街19号","bank_name":"工商银行","bank_code":"6222022002006651860 ","bank_phone":"15701681527","bank_address":"北京市东城区广渠门内广渠路支行"}';
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
        //姓名，身份证信息，手机号判空
        if(!$insured_name||!$insured_code||!$insured_phone){
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'empty';
            $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
            $return_data['data']['content'] = '个人信息不完善，请完善信息！';
            $return_data['data']['url'] = $webapi_route.'ins_error/empty';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        //银行卡信息判空
        if(!$bank_code){
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'no_bank';
            $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
            $return_data['data']['content'] = '银行卡信息缺失，请绑定银行卡！';
            $return_data['data']['url'] = $webapi_route.'ins_error/no_bank';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        //用用户身份证信息查询授权状态//todo  联合登录有两种情况；未开免密；保险生效中
        $user_setup_res = ChannelInsureSeting::where('cust_cod',$insured_code)
            ->select('authorize_status','authorize_start','authorize_bank','auto_insure_status','auto_insure_type','auto_insure_price','auto_insure_time','warranty_id','insure_days','insure_start')
            ->first();
        if(empty($user_setup_res)){//未授权(首次购买)
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'no_authorize';
            $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
            $return_data['data']['content'] = '免密授权未开启，请授权！';
            $return_data['data']['url'] = $webapi_route.'ins_error/no_authorize';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        if(!$user_setup_res['authorize_status']||!$user_setup_res['authorize_status']){
            $return_data['code'] = '5100';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'no_authorize';
            $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
            $return_data['data']['content'] = '免密授权未开启，请授权！';
            $return_data['data']['url'] = $webapi_route.'ins_error/no_authorize';
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
        //todo 查询保单生效状态（连续购买的保单是否还在保障期）
        if(empty($user_setup_res['warranty_id'])){//没有保单
            $insure_status = false;
        }
        if($user_setup_res['insure_start']+$user_setup_res['insure_days']*24*3600<time()){//保单过期
            $insure_status = false;
        }else{//保单在保
            $insure_status = true;
        }
        if(!$insure_status){//需要购买新保单
            //todo 当前投保状态，今天有没有进行投保操作????
           $cust_res = Person::where('papers_code',$insured_code)->select('id')->first();
           if(empty($cust_res)){
               $current_insurance_status = false;
           }
           $cust_warranty_res = CustWarranty::where('user_id',$cust_res['id'])
               ->select('warranty_uuid','warranty_code','created_at','check_status','pay_status','warranty_status')
               ->first();
            if(empty($cust_warranty_res)){
                $current_insurance_status = false;
            }
            if($cust_warranty_res['created_at']>strtotime(date('Y-m-d', time()))){
                $current_insurance_status = false;
            }else{
                $current_insurance_status = true;
            }
            if(!$current_insurance_status){//没有进行过投保操作
                $biz_content['insured_days'] = $user_setup_res['auto_insure_type'];
                $biz_content['price'] = '2';
                switch ($biz_content['insured_days']){
                    case '1':
                        $biz_content['price'] = $user_setup_res['auto_insure_price'];
                        break;
                    case '3':
                        $biz_content['price'] = $user_setup_res['auto_insure_price'];
                        break;
                    case '10':
                        $biz_content['price'] = $user_setup_res['auto_insure_price'];
                        break;
                }
                dispatch(new YunDaPayInsure($biz_content));//TODO 投保操作（异步队列）
                $return_data['code'] = '200';
                $return_data['message']['digest'] = 'default';
                $return_data['message']['details'] = 'insuring';
                $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
                $return_data['data']['content'] = '投保中';
                $return_data['data']['url'] = $webapi_route.'to_insure';
                return json_encode($return_data,JSON_UNESCAPED_UNICODE);
            }else{
                //查询投保状态
                $check_status = $cust_warranty_res['check_status'];//核保状态（默认0,1核保中, 2核保失败，3核保成功）
                $pay_status = $cust_warranty_res['pay_status'];//支付状态 （默认0，1支付中,2支付失败,3支付成功）
                $warranty_status = $cust_warranty_res['warranty_status'];//保单状态 （1待处理, 2待支付,3待生效, 4保障中,5可续保，6已失效，7已退保  8已过保）
                //TODO  匹配状态,组合查状态
                if($warranty_status=='3'||$warranty_status=='4'){
                    $return_data['code'] = '200';
                    $return_data['message']['digest'] = 'default';
                    $return_data['message']['details'] = 'insured';
                    $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
                    $return_data['data']['content'] = '保障中';
                    $return_data['data']['url'] = $webapi_route.'do_insure';
                    return json_encode($return_data,JSON_UNESCAPED_UNICODE);
                }else{
                    $return_data['code'] = '500';
                    $return_data['message']['digest'] = 'default';
                    $return_data['message']['details'] = 'isured_fail';
                    $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
                    $return_data['data']['content'] = '投保失败！';
                    $return_data['data']['url'] = $webapi_route.'ins_error/isured_fail';
                    return json_encode($return_data,JSON_UNESCAPED_UNICODE);
                }
            }
        }
        $return_data['code'] = '200';
        $return_data['message']['digest'] = 'default';
        $return_data['message']['details'] = 'insured';
        $return_data['data']['status'] = config('yunda.joint_status.yes');//（01显示/02不显示）
        $return_data['data']['content'] = '保障中';
        $return_data['data']['url'] = $webapi_route.'do_insure';
        return json_encode($return_data,JSON_UNESCAPED_UNICODE);
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
        $input = '{"channel_code":"YD","insured_name":"王磊","insured_code":"4108811994060565141234","insured_phone":"15701681527"}';
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
        $insured_code = $input['insured_code'];
        $insured_name = $input['insured_name'];
        $insured_phone = $input['insured_phone'];
        $return_data =[];
        $return_data['code'] = '200';
        $return_data['message']['digest'] = 'default';
        $person_res = Person::where('papers_code',$insured_code)
            ->where('name',$insured_name)
            ->where('phone',$insured_phone)
            ->select('id')
            ->first();
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
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }else{
            $authorize_status = config('yunda.authorize_status.yes');
            $return_data['message']['details'] = '已授权';
            $return_data['data']['status'] = $authorize_status;
            return json_encode($return_data,JSON_UNESCAPED_UNICODE);
        }
    }
}