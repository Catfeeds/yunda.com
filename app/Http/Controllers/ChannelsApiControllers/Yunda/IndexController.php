<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/3/28
 * Time: 10:12
 * 韵达快递保--主流程
 */
namespace App\Http\Controllers\ChannelsApiControllers\Yunda;


use App\Models\Bank;
use App\Models\CustWarranty;
use Illuminate\Http\Request;
use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Jobs\YunDaPayInsure;
use App\Models\Person;
use App\Models\ChannelInsureSeting;
use App\Models\ChannelContract;
use App\Models\ChannelOperate;
use App\Helper\TokenHelper;


class IndexController
{

	protected $request;

	protected $log_helper;

	protected $sign_help;

	protected $input;

	/**
	 * 初始化
	 * @access public
	 *
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->log_helper = new LogHelper();
		$this->sign_help = new RsaSignHelp();
		$this->input = $this->request->all();
	}

	/**
	 * 保险详情页
	 * @access public
	 * @return view
	 *
	 */
	public function InsInfo()
	{
		$token_data = TokenHelper::getData($this->input['token']);
		$person_code = $token_data['insured_code'];
//		$person_phone = $token_data['insured_phone'];
//		$user_seting = ChannelInsureSeting::where('cust_cod', $person_code)
//			->select('cust_id', 'authorize_status', 'authorize_start')
//			->first();
//		$authorize_status = $user_seting['authorize_status'];//免密开通状态
//		if (!$authorize_status) {
//			$cust_id = '';
//			$cust_name = '';
//			$cust_phone = '';
//			$user_res = Person::where('phone', $person_phone)
//				->select('id', 'name', 'phone')
//				->first();
//			if (!empty($user_res)) {
//				$cust_id = $user_res['id'];
//				$cust_name = $user_res['name'];
//				$cust_phone = $user_res['phone'];
//			}
//			$insure_seting = ChannelInsureSeting::where('cust_cod', $person_code)
//				->select('authorize_bank')
//				->first();
//			$bank = [];
//			if (!empty($insure_seting)) {
//				$bank['code'] = $insure_seting['authorize_bank'];
//			}
//			$bank_res = Bank::where('cust_id', $cust_id)
//				->select('bank', 'bank_code', 'bank_city', 'phone', 'bank_deal_type')
//				->get();
//			if (!empty($bank_res)) {
//				foreach ($bank_res as $value) {
//					if ($value['bank_deal_type'] == '1') {
//						$bank['code'] = $value['bank_code'];
//						$bank['name'] = $value['bank'];
//						$bank['city'] = $value['bank_city'];
//						$bank['phone'] = $value['phone'];
//					}
//				}
//			}
//			$params = [];
//			$params['person_code'] = $person_code ?? "";
//			$params['person_phone'] = $cust_phone ?? "";
//			$params['person_name'] = $cust_name ?? "";
//			$wechat_res = $this->getWechatAuthorize($params);//微信签约显示状态
//			if ($wechat_res['status']) {
//				$wechat_status = $wechat_res['status'];//微信签约按钮显示状态
//				$wechat_url = $wechat_res['url'];//签约URL
//			} else {
//				$wechat_status = $wechat_res['status'];//微信签约按钮显示状态
//				$wechat_url = '';//签约URL
//			}
//			//签约页面上会显示签约人的相关信息
//			return view('channels.yunda.insure_authorize', compact('bank', 'cust_id', 'cust_name', 'cust_phone', 'person_code', 'wechat_status', 'wechat_url'));
//		}
		return view('channels.yunda.insure_info', compact('person_code'));
	}

	/**
	 * 出新单操作
	 * @access public
	 * @return view
	 *
	 */
	public function doNewInusred()
	{
		$token_data = TokenHelper::getData($this->input['token']);
		$person_code = $token_data['insured_code'];
		$person_phone = $token_data['insured_phone'];
		$user_res = Person::where('phone', $person_phone)
			->select('id', 'name', 'papers_type', 'papers_code', 'phone', 'email', 'address', 'address_detail')
			->first();
		//姓名，身份证信息，手机号判空
		if (!$user_res['name'] || !$user_res['papers_code'] || !$user_res['phone']) {
			$ins_status = '500';//投保状态：成功200/失败500/投保中100
			$ins_msg = '用户信息不完善，请完善用户信息';//备注信息
			$target_url = config('yunda.server_host') . config('view_url.channel_yunda_target_url') . 'ins_info?token='.$this->input['token'];//跳转URL
			$warranty_res = [];//保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
			return $this->insResult($person_code, $ins_status, $ins_msg, $target_url, $warranty_res);
		}
		$user_setup_res = ChannelInsureSeting::where('cust_cod', $person_code)
			->select('authorize_status', 'authorize_start', 'authorize_bank', 'auto_insure_status', 'auto_insure_type', 'auto_insure_price', 'auto_insure_time')
			->first();
		if (!$user_setup_res || !$user_setup_res['authorize_bank']) {
			$ins_status = '500';//投保状态：成功200/失败500/投保中100
			$ins_msg = '开启快递保免密支付,每日出行有保障>>';//备注信息
			$target_url = config('yunda.server_host') . config('view_url.channel_yunda_target_url') . 'insure_authorize?token='.$this->input['token'];//跳转URL
			$warranty_res = [];//保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
			return $this->insResult($person_code, $ins_status, $ins_msg, $target_url, $warranty_res);
		}
		$bank_res = Bank::where('cust_id', $user_res['id'])
			->where('bank_code', $user_setup_res['authorize_bank'])
			->select('bank', 'bank_code', 'bank_city', 'phone')
			->first();
		$biz_content['channel_code'] = 'YD';
		$biz_content['courier_state'] = '';
		$biz_content['courier_start_time'] = '';
		$biz_content['p_code'] = '';
		$biz_content['is_insure'] = '';
		$biz_content['insured_name'] = $user_res['name'];
		$biz_content['insured_code'] = $user_res['papers_code'];
		$biz_content['insured_phone'] = $user_res['phone'];
		$biz_content['insured_email'] = $user_res['email'];
		$biz_content['insured_province'] = $user_res['address_detail'];
		$biz_content['insured_city'] = $user_res['address_detail'];
		$biz_content['insured_county'] = $user_res['address_detail'];
		$biz_content['insured_address'] = $user_res['address_detail'];
		$biz_content['bank_code'] = $user_setup_res['authorize_bank'];
		$biz_content['bank_name'] = $bank_res['bank'];
		$biz_content['bank_address'] = $bank_res['bank_city'];
		$biz_content['bank_phone'] = $user_res['phone'];
		$biz_content['channel_order_code'] = "";
		$biz_content['insured_days'] = $user_setup_res['auto_insure_type'] ?? "1";
		$biz_content['price'] = '2';
		switch ($biz_content['insured_days']) {
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
		$ins_status = '100';//投保状态：成功200/失败500/投保中100
		$ins_msg = '投保中，请稍等~';//备注信息
		$target_url = config('yunda.server_host') . config('view_url.channel_yunda_target_url') . 'warranty_list?token='.$this->input['token'];//跳转URL
		$warranty_res = [];//保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
		return $this->insResult($person_code, $ins_status, $ins_msg, $target_url, $warranty_res);
	}

	/**
	 * 我的保险
	 * @access public
	 *  TODO  完善功能
	 * @return view
	 *
	 */
	public function insureCenter()
	{
		$token_data = TokenHelper::getData($this->input['token']);
		$person_code = $token_data['insured_code'];
		if ($person_code) {
			$user_seting_res = ChannelInsureSeting::where('cust_cod', $person_code)
				->select('authorize_status', 'auto_insure_status')
				->first();
			$person_res = Person::where('papers_code', $person_code)
				->select('id')
				->first();
			if (empty($user_seting_res) && empty($person_res)) {
				$auto_insure_status = '0';//自动投保状态
				$insured_status = '0';//保障状态
			} else {
				$auto_insure_status = $user_seting_res['auto_insure_status'];
				$warranty_res = CustWarranty::where('user_id', $person_res['id'])
					->where('warranty_status', '4')//生效的订单
					->where('created_at', '>', strtotime(date('Y-m-d')) . '000')//今天凌晨的时间戳
					->select('warranty_uuid', 'warranty_code', 'created_at', 'check_status', 'pay_status', 'warranty_status')
					->orderBy('updated_at', 'desc')
					->first();
				if (empty($warranty_res)) {
					$insured_status = '0';//投保失败
				} else {
					$insured_status = '1';//保障中
				}
			}
		} else {
			$insured_status = '0';//保障状态
			$auto_insure_status = '0';//自动投保状态
		}
		return view('channels.yunda.insure_center', compact('person_code', 'insured_status', 'auto_insure_status', 'authorize_status'));
	}

	/**
	 * 投保操作
	 * @access public
	 * @return view
	 *
	 */
	public function doInsured()
	{
		$token_data = TokenHelper::getData($this->input['token']);
		$person_code = $token_data['insured_code'];
		$person_phone = $token_data['insured_phone'];
		$user_res = Person::where('phone', $person_phone)
			->select('id', 'name', 'papers_type', 'papers_code', 'phone', 'email', 'address', 'address_detail')
			->first();
		//姓名，身份证信息，手机号判空
		if (!$user_res['name'] || !$user_res['papers_code'] || !$user_res['phone']) {
			$ins_status = '500';//投保状态：成功200/失败500/投保中100
			$ins_msg = '用户信息不完善，请完善用户信息';//备注信息
			$target_url = config('yunda.server_host') . config('view_url.channel_yunda_target_url') . 'ins_info?token='.$this->input['token'];//跳转URL
			$warranty_res = [];//保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
			return $this->insResult($person_code, $ins_status, $ins_msg, $target_url, $warranty_res);
		}
		$user_setup_res = ChannelInsureSeting::where('cust_cod', $person_code)
			->select('authorize_status', 'authorize_start', 'authorize_bank', 'auto_insure_status', 'auto_insure_type', 'auto_insure_price', 'auto_insure_time')
			->first();
		if (!$user_setup_res || !$user_setup_res['authorize_bank']) {
			$ins_status = '500';//投保状态：成功200/失败500/投保中100
			$ins_msg = '开启快递保免密支付,每日出行有保障>>';//备注信息
			$target_url = config('yunda.server_host') . config('view_url.channel_yunda_target_url') . 'insure_authorize?token='.$this->input['token'];//跳转URL
			$warranty_res = [];//保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
			return $this->insResult($person_code, $ins_status, $ins_msg, $target_url, $warranty_res);
		}
		$cust_warranty_res = CustWarranty::where('user_id', $user_res['id'])
			//->where('warranty_status', '<>', '6')//失效的订单
			->where('created_at', '>', strtotime(date('Y-m-d')) . '000')//今天凌晨的时间戳
			->select('warranty_uuid', 'warranty_code', 'created_at', 'check_status', 'pay_status', 'warranty_status','resp_insure_msg','resp_pay_msg','start_time','end_time')
			->orderBy('created_at', 'desc')
			->first();
		if (empty($cust_warranty_res)) {//今天还没有投过保
			$ins_status = '500';
			$ins_msg = "投保失败";//备注信息
			$target_url = config('yunda.server_host') . config('view_url.channel_yunda_target_url') . 'ins_info?token='.$this->input['token'];//跳转URL
			$warranty_res = [];//保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
			return $this->insResult($person_code, $ins_status, $ins_msg, $target_url, $warranty_res);
		} else {
			//查询投保状态
			$check_status = $cust_warranty_res['check_status'];//核保状态（默认0,1核保中, 2核保失败，3核保成功）
			$pay_status = $cust_warranty_res['pay_status'];//支付状态 （默认0，1支付中,2支付失败,3支付成功）
			$warranty_status = $cust_warranty_res['warranty_status'];//保单状态 1待处理, 2待支付,3待生效, 4保障中,5可续保，6已失效，7已退保  8已过保
			$check_msg = $cust_warranty_res['resp_insure_msg'];//投保回执信息
			$pay_msg = $cust_warranty_res['resp_pay_msg'];//支付回执信息
			//TODO  匹配状态,组合查状态
			if ($warranty_status == '3' || $warranty_status == '4') {
				$ins_status = '200';//投保状态：成功200/失败500/投保中100
				$ins_msg = '今日快递保生效中>>';//备注信息
				$target_url = config('yunda.server_host') . config('view_url.channel_yunda_target_url') . 'warranty_list?token='.$this->input['token'];//跳转URL
				$warranty_res = $cust_warranty_res;//保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
				return $this->insResult($person_code, $ins_status, $ins_msg, $target_url, $warranty_res);
			} else {
				if(!empty($pay_msg)){
					$ins_status = '500';
					$ins_msg = $pay_msg ?? "支付失败";//备注信息
					$target_url = config('yunda.server_host') . config('view_url.channel_yunda_target_url') . 'ins_center?token='.$this->input['token'];//跳转URL
					$warranty_res = [];//保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
					return $this->insResult($person_code, $ins_status, $ins_msg, $target_url, $warranty_res);
				}elseif(!empty($check_msg)){
					$ins_status = '500';
					$ins_msg = $check_msg ?? "核保失败";//备注信息
					$target_url = config('yunda.server_host') . config('view_url.channel_yunda_target_url') . 'ins_center?token='.$this->input['token'];//跳转URL
					$warranty_res = [];//保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
					return $this->insResult($person_code, $ins_status, $ins_msg, $target_url, $warranty_res);
				}else{
					$ins_status = '500';
					$ins_msg = '投保失败';//备注信息
					$target_url = config('yunda.server_host') . config('view_url.channel_yunda_target_url') . 'ins_center?token='.$this->input['token'];//跳转URL
					$warranty_res = [];//保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
					return $this->insResult($person_code, $ins_status, $ins_msg, $target_url, $warranty_res);
				}
			}
		}
	}

    /**
     * 保险条款页--英大
     * @access public
     * @return view
     *
     */
    public function insYdClause(){
        return view('channels.yunda.insure_yd_clause');
    }

    /**
     * 保险条款页-泰康
     * @access public
     * @return view
     *
     */
    public function insTkClause(){
        return view('channels.yunda.insure_tk_clause');
    }

    /**
     * 投保告知页-泰康
     * @access public
     * @return view
     *
     */
    public function insTkNotice(){
        return view('channels.yunda.insure_tk_notice');
    }

    /**
     * 投保告知页-英大
     * @access public
     * @return view
     *
     */
    public function insYdNotice(){
        return view('channels.yunda.insure_yd_notice');
    }

    /**
     * 投保结果页（成功/失败）
     * @access public
     * @param $ins_status|投保状态：成功/失败
     * @param $ins_msg|备注信息
     * @param $target_url|跳转URL
     * @param $warranty_res|保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
     * @return view
     *
     */
    public function insResult($person_code,$ins_status,$ins_msg,$target_url,$warranty_res){
        $user_res = Person::where('papers_code',$person_code)->select('name','papers_type','papers_code','phone','address')->first();
        return view('channels.yunda.insure_result',compact('person_code','ins_status','ins_msg','target_url','warranty_res','user_res'));
    }

    /**
     * 错误提示页（成功/失败）
     * @access public
     * @param $ins_status|投保状态：成功/失败
     * @param $ins_msg|备注信息
     * @param $target_url|跳转URL
     * @param $warranty_res|保单信息：产品，被保人，保障期限，保单号，保费，保障起止时间
     * @return view
     *
     */
    public function insError($error_type){
		$token_data = TokenHelper::getData($this->input['token']);
		$person_code = $token_data['insured_code'];
		$person_phone = $token_data['insured_phone'];
        switch ($error_type){
//            case 'empty'://投保参数不完善
//                $ins_msg = '用户信息不完善，请完善用户信息';//备注信息
//                $target_url = config('yunda.server_host').config('view_url.channel_yunda_target_url').'ins_info';//跳转URL
//                break;
            case 'no_bank'://没有绑定银行卡
                $ins_msg = '没有银行卡信息，请绑定银行卡';//备注信息
                $target_url = config('yunda.server_host').config('view_url.channel_yunda_target_url').'bank_index';//跳转URL
                break;
            case 'no_authorize'://没有授权
                $ins_msg = '银行卡没有授权免密支付，请授权';//备注信息
                $target_url = config('yunda.server_host').config('view_url.channel_yunda_target_url').'insure_authorize';//跳转URL
                break;
            case 'insured_fail'://投保失败（系统错误）
                $ins_msg = '投保失败,请重新尝试';//备注信息
                $target_url = config('yunda.server_host').config('view_url.channel_yunda_target_url').'ins_info';//跳转URL
                break;
            default:
                $ins_msg = '投保失败,请重新尝试';//备注信息
                $target_url = config('yunda.server_host').config('view_url.channel_yunda_target_url').'ins_info';//跳转URL
        }
        $ins_status = '500';
        $warranty_res = [];
        $user_res = Person::where('phone',$person_phone)->select('name','papers_type','papers_code','phone','address')->first();
        return view('channels.yunda.insure_result',compact('person_code','ins_status','ins_msg','target_url','warranty_res','user_res'));
    }

	/**
	 * 获取是否可以微信免密授权
	 * 筛选条件：
	 * 是否签约过
	 * 是否预投保
	 * @access public
	 * @return json
	 */
	private function getWechatAuthorize($params){
		$person_code =  $params['person_code'];
		$person_phone =  $params['person_phone'];
		$person_name =  $params['person_name'];
		$return_data = [];
		//查询签约情况
		$contrant_res = ChannelContract::where('channel_user_code',$person_code)
			->select('openid','contract_id','contract_expired_time')
			->first();
		if(!empty($contrant_res)){
			$return_data['status'] = false;
			$return_data['message'] = '用户已开通微信免密支付';
			return $return_data;
		}
		$channel_res = ChannelOperate::where('channel_user_code',$person_code)
			->where('prepare_status','200')
			->where('operate_time',date('Y-m-d',time()-24*3600))
			->select('proposal_num')
			->first();
		if(empty($channel_res)){
			$return_data['status'] = false;
			$return_data['message'] = '用户没有预投保单';
			return $return_data;
		}
		$union_order_code = $channel_res['proposal_num'];
		$data = [];
		$data['price'] = '2';
		$data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
		$data['quote_selected'] = '';
		$data['insurance_attributes'] = '';
		$data['union_order_code'] = $union_order_code;
		$data['pay_account'] = $person_name.$person_phone;
		$data['clientIp'] = IPHelper::getIP();
		$data = $this->sign_help->tySign($data);
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
			//LogHelper::logError($response->content, 'YD_pay_order_'.$union_order_code);
			$return_data['status'] = false;
			$return_data['message'] = '用户获取签约链接失败';
			return $return_data;
		}
		$response_data =  json_decode($response->content,true);//签约返回数据
		$return_data['status'] = true;
		$return_data['message'] = '用户获取签约链接成功';
		$return_data['url'] =  $response_data['result_content']['contracturl'];//禁止转义
		return $return_data;
	}

}