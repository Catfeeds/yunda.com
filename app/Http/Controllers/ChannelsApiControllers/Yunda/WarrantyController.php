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
use App\Models\CustWarrantyPolicy;
use Illuminate\Http\Request;
use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Models\Warranty;
use App\Models\Person;

class WarrantyController
{

    protected $request;

    protected $log_helper;

    protected $person_code;

    /**
     * 初始化
     * @access public
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->sign_help = new RsaSignHelp();
        set_time_limit(0);//永不超时
        $access_token = $this->request->header('access-token');
        $access_token_data = json_decode($this->sign_help->base64url_decode($access_token),true);
        $this->person_code = $access_token_data['person_code'];
    }

    /**
     * 保单列表
     * @access public
     */
    public function warrantyList(){
        $person_code = $this->person_code;
        $person_code = '410881199406056514';
        $user_res = Person::where('papers_code',$person_code)->select('id')->first();
        $warranty_ok_res = CustWarranty::where('user_id',$user_res['id'])
            ->where('warranty_status','7')//保障中
            ->select('id')
            ->get();
        $warranty_paying_res = CustWarranty::where('user_id',$user_res['id'])
            ->where('warranty_status','3')//待支付
            ->select('id')
            ->get();
        $warranty_timeout_res = CustWarranty::where('user_id',$user_res['id'])
            ->where('warranty_status','10')//已失效
            ->select('id')
            ->get();
        $input = $this->request->all();
        $status = $input['status']??"7";//默认保障中
        //保单状态（ 1待核保，2核保失败，3未支付-核保成功，4支付中,5支付失败,6支付成功，7保障中,8待生效,9待续保，10已失效，11已退保）',
        $warranty_res = CustWarranty::where('user_id',$user_res['id'])
            ->where('warranty_status',$status)//已失效
            ->select('id','warranty_code','warranty_uuid','start_time','end_time','warranty_status')
            ->get();
        return view('channels.yunda.warranty_list',compact('warranty_ok_res','warranty_paying_res','warranty_timeout_res','warranty_res','person_code'));
    }

    /**
     * 保单详情
     * @access public
     */
    public function warrantyDetail($warranty_id){
        $person_code = $this->person_code;
        $person_code = '410881199406056514';
        $user_res = Person::where('papers_code',$person_code)
            ->select('id','papers_code','papers_type','name','phone')
            ->first();
        $warranty_res = CustWarranty::where('id',$warranty_id)
            ->select('id','warranty_code','warranty_uuid','start_time','end_time','warranty_status')
            ->first();
        $cust_policy_res = CustWarrantyPolicy::where('warranty_uuid',$warranty_res['warranty_uuid'])
            ->get();
        $policy_res = [];
        $inusred_res = [];
        foreach ($cust_policy_res as $value){
            if($value['type']=='1'){//投保人
                $policy_res['name'] = $value['name'];
                $policy_res['card_type'] = $value['card_type'];
                $policy_res['card_code'] = $value['card_code'];
                $policy_res['phone'] = $value['phone'];
            }
            if($value['type']=='2'){//被保人
                $inusred_res['name'] = $value['name'];
                $inusred_res['card_type'] = $value['card_type'];
                $inusred_res['card_code'] = $value['card_code'];
                $inusred_res['phone'] = $value['phone'];
            }
        }
        return view('channels.yunda.warranty_detail',compact('warranty_res','user_res','policy_res','inusred_res'));
    }

}