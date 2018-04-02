<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/3/29
 * Time: 17:12
 * 韵达快递保新流程--理赔管理
 */
namespace App\Http\Controllers\ChannelsApiControllers\Yunda;

use Illuminate\Http\Request;
use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Models\Person;

class ClaimController
{

    protected $request;

    protected $log_helper;

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
     * 银行卡管理页面
     * @access public
     * @return view
     *
     */
    public function claimIndex(){
        $access_token = $this->request->header('access-token');
        $access_token_data = json_decode($this->sign_help->base64url_decode($access_token),true);
        $person_code = $access_token_data['person_code'];
        $person_code = '410881199406056514';
        $user_res = Person::where('id_code',$person_code)->select('id','name','id_type','id_code','phone','address')->first();
        $cust_id = $user_res['id'];
        return view('channels.yunda.claim_index',compact('person_code','cust_id'));
    }



}