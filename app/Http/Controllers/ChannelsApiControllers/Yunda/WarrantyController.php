<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/3/29
 * Time: 17:12
 * 韵达快递保新流程--保单管理
 */
namespace App\Http\Controllers\ChannelsApiControllers\Yunda;

use App\Models\CustWarranty;
use Illuminate\Http\Request;
use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Models\Warranty;
use App\Models\Person;

class WarrantyController
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
        $this->sign_help = new RsaSignHelp();
        set_time_limit(0);//永不超时
    }

    /**
     * 保单列表
     * @access public
     */
    public function warrantyList(){
        $input = $this->request->all();
        $status = $input['status']??"7";//默认保障中
        //保单状态（ 1待核保，2核保失败，3未支付-核保成功，4支付中,5支付失败,6支付成功，7保障中,8待生效,9待续保，10已失效，11已退保）',
        $access_token = $this->request->header('access-token');
        $access_token_data = json_decode($this->sign_help->base64url_decode($access_token),true);
        $person_code = $access_token_data['person_code'];
        $person_code = '410881199406056514';
        $user_res = Person::where('id_code',$person_code)->select('id')->first();
        $warranty_ok_res = CustWarranty::where('user_id',$user_res['id'])
            ->where('status','7')//保障中
            ->select('id')
            ->get();
        $warranty_paying_res = CustWarranty::where('user_id',$user_res['id'])
            ->where('status','3')//待支付
            ->select('id')
            ->get();
        $warranty_timeout_res = CustWarranty::where('user_id',$user_res['id'])
            ->where('status','10')//已失效
            ->select('id')
            ->get();
        $warranty_res = CustWarranty::where('user_id',$user_res['id'])
            ->where('status',$status)//已失效
            ->select('id','warranty_code','start_time','end_time','status')
            ->get();
        return view('channels.yunda.warranty_list',compact('warranty_ok_res','warranty_paying_res','warranty_timeout_res','warranty_res','person_code'));
    }

    /**
     * 保单详情
     * @access public
     */
    public function warrantyDetail($warranty_id){
        $access_token = $this->request->header('access-token');
        $access_token_data = json_decode($this->sign_help->base64url_decode($access_token),true);
        $person_code = $access_token_data['person_code'];
        $person_code = '410881199406056514';
        $user_res = Person::where('id_code',$person_code)
            ->select('id','id_code','id_type','name','phone')
            ->first();
        $warranty_res = CustWarranty::where('id',$warranty_id)
            ->select('id','warranty_code','start_time','end_time','status')
            ->first();
        return view('channels.yunda.warranty_detail',compact('warranty_res','user_res'));
    }

}