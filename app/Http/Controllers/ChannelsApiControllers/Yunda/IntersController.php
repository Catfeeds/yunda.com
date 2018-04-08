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
use App\Jobs\YunDaPay;
use App\Models\Person;
use App\Models\ChannelInsureSeting;

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
     *
     */
    public function jointLogin(){
        $input = $this->request->all();
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
        $return_data =[];
        $webapi_route = config('yunda.webapi_route');
        //姓名，身份证信息，手机号判空
        if(!$insured_name||!$insured_code||!$insured_phone){
            $return_data['code'] = '500';
            $return_data['message']['digest'] = 'default';
            $return_data['message']['details'] = 'empty';
            $return_data['data']['status'] = '01';//（01显示/02不显示）
            $return_data['data']['content'] = '个人信息不完善，请完善信息！';
            $return_data['data']['url'] = 'http://'.$_SERVER['HTTP_HOST'];
            return json_encode($return_data);
        }
        //银行卡信息判空
        if(!$bank_code){
            return json_encode(['status'=>'500','msg'=>'请绑定银行卡','url'=>'http://'.$_SERVER['HTTP_HOST']],JSON_UNESCAPED_UNICODE);
        }
        //todo  联合登录有两种情况；未开免密；保险生效中
        //用用户身份证信息查询授权状态
        $user_setup_res = ChannelInsureSeting::where('cust_cod',$insured_code)
            ->select('authorize_status','authorize_start','authorize_bank','auto_insure_status','auto_insure_type','auto_insure_price','auto_insure_time')
            ->first();
        $authorize_status = $user_setup_res['auto_insure_status'];//todo 查询免密授权状态
        if(!$authorize_status){//未授权(首次购买)
            return json_encode(['status'=>'200','msg'=>'投保失败,请前往授权页面，开通授权','url'=>'http://'.$_SERVER['HTTP_HOST'].self::INSURE_ERROR_URL.'/no_authorize'],JSON_UNESCAPED_UNICODE);
        }
        $insure_status = false;//todo 查询保单生效状态（连续购买的保单是否还在保障期）
        if(!$insure_status){//需要购买新保单
            $current_insurance_status = true;//当前投保状态，今天有没有进行投保操作
            if($current_insurance_status){//没有进行过投保操作
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
                dispatch(new YunDaPay($biz_content));//TODO 投保操作（异步队列）
                return json_encode(['status'=>'200','msg'=>'投保中'],JSON_UNESCAPED_UNICODE);
            }
            return json_encode(['status'=>'500','msg'=>'投保失败','url'=>'http://'.$_SERVER['HTTP_HOST'].self::INSURE_ERROR_URL.'/isured_fail'],JSON_UNESCAPED_UNICODE);
        }
        return json_encode(['status'=>'200','msg'=>'保障中','url'=>'http://'.$_SERVER['HTTP_HOST'].self::DO_INSURE_URL],JSON_UNESCAPED_UNICODE);
    }

    /**
     * 授权查询接口(未授权01/已授权02)
     * @access public
     * @param channel_code|渠道代号
     * @param insured_name|姓名
     * @param insured_code|证件号
     * @param insured_phone|电话
     *
     * @return json
     *
     */
    public function authorizationQuery(){
        $input = $this->request->all();
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
            $authorize_status = '01';
            $return_data['message']['details'] = '未授权';
            $return_data['data']['status'] = $authorize_status;
            return json_encode($return_data);
        }
        $user_setup_res = ChannelInsureSeting::where('cust_cod',$insured_code)
            ->select('authorize_status','authorize_start','authorize_bank','auto_insure_status','auto_insure_type','auto_insure_price','auto_insure_time')
            ->first();
        $authorize_status = $user_setup_res['auto_insure_status'];//todo 查询免密授权状态
        if(!$authorize_status){//未授权(首次购买)
            $authorize_status = '01';
            $return_data['message']['details'] = '未授权';
            $return_data['data']['status'] = $authorize_status;
            return json_encode($return_data);
        }else{
            $authorize_status = '02';
            $return_data['message']['details'] = '已授权';
            $return_data['data']['status'] = $authorize_status;
            return json_encode($return_data);
        }
    }
}