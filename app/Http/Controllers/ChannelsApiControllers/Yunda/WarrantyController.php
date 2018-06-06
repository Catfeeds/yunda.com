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
use App\Models\CustWarrantyPerson;
use Illuminate\Http\Request;
use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Models\Warranty;
use App\Models\Person;
use App\Helper\TokenHelper;

class WarrantyController
{

    protected $request;

    protected $sign_help;

    protected $person_code;

    /**
     * 初始化
     * @access public
     */
    public function __construct(Request $request)
    {
		set_time_limit(0);//永不超时
        $this->request = $request;
        $this->sign_help = new RsaSignHelp();
		$this->input = $this->request->all();
    }

    /**
     * 保单列表
     * @access public
     */
    public function warrantyList(){
		$token_data = TokenHelper::getData($this->input['token']);
		$person_phone = $token_data['insured_phone'];
        $user_res = Person::where('phone',$person_phone)->select('id')->first();
        $warranty_ok_res = CustWarranty::where('user_id',$user_res['id'])
            ->where('warranty_status','4')//保障中
            ->select('id')
            ->get();
        $warranty_paying_res = CustWarranty::where('user_id',$user_res['id'])
            ->where('warranty_status','2')//待支付
            ->select('id')
            ->get();
        $warranty_timeout_res = CustWarranty::where('user_id',$user_res['id'])
            ->where('warranty_status','6')//已失效
			->orWhere('warranty_status','8')
            ->select('id')
            ->get();
        $input = $this->request->all();
        $status = $input['status']??"4";//默认保障中
        //保单状态 1待处理, 2待支付,3待生效, 4保障中,5可续保，6已失效，7已退保  8已过保
		if($status=="6"){
			$warranty_res = CustWarranty::where('user_id',$user_res['id'])
				->where('warranty_status',$status)//保障中
				->orWhere('warranty_status','8')
				->select('id','warranty_code','warranty_uuid','pro_policy_no','start_time','end_time','check_status','pay_status','warranty_status')
				->get();
		}else{
			$warranty_res = CustWarranty::where('user_id',$user_res['id'])
				->where('warranty_status',$status)//保障中
				->select('id','warranty_code','warranty_uuid','pro_policy_no','start_time','end_time','check_status','pay_status','warranty_status')
				->get();
		}
        $warranty_status = config('status_setup.warranty_status');//保单状态
        $pay_status = config('status_setup.pay_status');//支付状态
        $check_status = config('status_setup.check_status');//核保状态
        return view('channels.yunda.warranty_list',compact('warranty_status','pay_status','check_status','warranty_ok_res','warranty_paying_res','warranty_timeout_res','warranty_res','person_code'));
    }

    /**
     * 保单详情
     * @access public
     */
    public function warrantyDetail($warranty_id){
		$token_data = TokenHelper::getData($this->input['token']);
		$person_phone = $token_data['insured_phone'];
        $user_res = Person::where('phone',$person_phone)
            ->select('id','papers_code','papers_type','name','phone')
            ->first();
        $warranty_res = CustWarranty::where('id',$warranty_id)
            ->select('id','warranty_code','warranty_uuid','pro_policy_no','start_time','end_time','check_status','pay_status','warranty_status')
            ->first();
        $cust_policy_res = CustWarrantyPerson::where('warranty_uuid',$warranty_res['warranty_uuid'])
            ->select('out_order_no','type','relation_name','name','card_type','card_code','phone','occupation','birthday','sex','age','email','nationality','annual_income','height','weight','area','address','start_time','end_time')
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
        //sleep(30);
		$warranty_status = config('status_setup.warranty_status');//保单状态
		$pay_status = config('status_setup.pay_status');//支付状态
		$check_status = config('status_setup.check_status');//核保状态
        return view('channels.yunda.warranty_detail',compact('warranty_status','pay_status','check_status','warranty_res','user_res','policy_res','inusred_res'));
    }

}