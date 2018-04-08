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
     * @param $ins_status|投保状态：成功/失败
     * @param $ins_msg|备注信息
     * @param $target_url|跳转URL
     * @param $warranty_res|保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
     * @return view
     *
     */
    public function jointLogin(){
        $person_code = $this->person_code;
        $person_code = config('yunda.test_person_code');
        $user_seting = ChannelInsureSeting::where('cust_cod',$person_code)
            ->select('cust_id','authorize_status','authorize_start')
            ->first();
        $cust_id = $user_seting['cust_id'];
        $authorize_status  = $user_seting['authorize_status'];//免密开通状态
        if(!$authorize_status){
            return view('channels.yunda.bank_authorize',compact('cust_id','person_code'));
        }
        return view('channels.yunda.insure_info',compact('person_code'));
    }

    /**
     * 授权查询接口
     * @access public
     * @param $ins_status|投保状态：成功/失败
     * @param $ins_msg|备注信息
     * @param $target_url|跳转URL
     * @param $warranty_res|保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
     * @return view
     *
     */
    public function authorizationQuery(){

    }

}