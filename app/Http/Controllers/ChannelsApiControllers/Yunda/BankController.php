<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/3/29
 * Time: 14:12
 * 韵达快递保--银行卡管理
 */

namespace App\Http\Controllers\ChannelsApiControllers\Yunda;

use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\ChannelInsureSeting;
use App\Models\ChannelContract;
use App\Models\ChannelOperate;
use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Helper\IPHelper;
use App\Models\Person;
use Ixudra\Curl\Facades\Curl;
use App\Helper\TokenHelper;
use Illuminate\Support\Facades\DB;
use App\Jobs\YunDaPayInsure;
use \Illuminate\Support\Facades\Redis;

class BankController
{

	protected $request;

	protected $log_helper;

	protected $sign_help;

	protected $input;

	/**
	 * 初始化
	 * @access public
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->log_helper = new LogHelper();
		$this->sign_help = new RsaSignHelp();
		$this->input = $this->request->all();
	}

	/**
	 * 银行卡管理页面
	 * @access public
	 * @return view
	 *
	 */
	public function bankIndex()
	{
		$token_data = TokenHelper::getData($this->input['token']);
		$person_phone = $token_data['insured_phone'];
		$user_res = Person::where('phone', $person_phone)
			->select('id', 'name', 'papers_type', 'papers_code', 'phone', 'address')
			->first();
		$cust_id = $user_res['id'];
		$bank_authorize = ChannelInsureSeting::where('cust_id',$cust_id)
			->with(['bank'=>function ($a){
				$a->where('state','1');
			}])
			->select('authorize_bank')
			->first();
		if(!empty($bank_authorize)&&!empty($bank_authorize['bank'])){
			Bank::where('bank_code',$bank_authorize['authorize_bank'])->update([
				'state'=>'0'
			]);
		}
		$bank_res = Bank::where('cust_id', $cust_id)
			->where('state','<>','1')
			->select('id', 'bank', 'bank_code', 'bank_city', 'phone')
			->get()->toArray();
		return view('channels.yunda.bank_index', compact('bank_res'));
	}

	/**
	 * 银行卡添加页面
	 * @access public
	 * @return view
	 *
	 */
	public function bankBind()
	{
		$token_data = TokenHelper::getData($this->input['token']);
		return view('channels.yunda.bank_bind', compact('token_data'));
	}

	/**
	 * 银行卡添加操作
	 * @access public
	 * @return json
	 *
	 */
	public function doBankBind()
	{
		$input = $this->request->all();
		$person_data = json_decode($input['person_data'], true);
		$bank_cod = $input['bank_code'];
		$bank = $input['bank_name']??" ";
		$bank_city = $input['bank_city']??"";
		$bank_phone = $input['bank_phone']??"";
		$verify_code = $input['verify_code']??"";
		$verify_data = [];
		$verify_data['phone'] = $bank_phone;
		$verify_data['verify_code'] = $verify_code;
		$verify_res = $this->doBankVerify($verify_data);
		if(!$verify_res){
			return json_encode(['status' => '500', 'msg' => '验证码校验失败']);
		}
		$cust_res = Person::where('phone', $person_data['insured_phone'])
			->select('id')
			->first();
		DB::beginTransaction();
		try{
			if (empty($cust_res)) {
				Person::insert([
					'name' => $person_data['insured_name'],
					'papers_type' => '1',
					'papers_code' => $person_data['insured_code'],
					'phone' => $person_data['insured_phone'],
					'cust_type' => '1',
					'authentication' => '1',
					'del' => '0',
					'status' => '1',
					'created_at' => time(),
					'updated_at' => time(),
				]);
			}
			$bank_repeat = Bank::where('bank_code', $bank_cod)
				->where('state','0')
				->select('id')
				->first();
			if (!empty($bank_repeat)) {
				return json_encode(['status' => '500', 'msg' => '银行卡已存在，请更换银行卡！']);
			}
			$insert_res = Bank::insert([
				'cust_id' => $cust_res['id'],
				'cust_type' => '1',
				'bank' => $bank,
				'bank_code' => $bank_cod,
				'bank_city' => $bank_city,
				'phone' => '',
				'created_at'=>time(),
				'updated_at'=>time(),
			]);
			if ($insert_res) {
				$bank_insure_res = $this->doBankInsured($person_data['insured_phone']);
				if($bank_insure_res['status']=='200'){
					DB::commit();
					return json_encode(['status' => '200', 'msg' => '银行卡添加成功']);
				}else{
					DB::rollBack();
					return json_encode(['status' => '500', 'msg' => '银行卡添加失败']);
				}
			} else {
				DB::rollBack();
				return json_encode(['status' => '500', 'msg' => '银行卡添加失败']);
			}
		}catch (\Exception $e){
			DB::rollBack();
			return json_encode(['status' => '500', 'msg' => '银行卡添加失败']);
		}

	}

	/**
	 * 获取银行卡验证码
	 * @access public
	 * @return json
	 *
	 */
	public function getBankVerify(){
		$input = $this->request->all();
//		$input = [];
//		$input['bank_code'] = '';
//		$input['person_data'] = '{"insured_name":"\u66f9\u6865\u6865","insured_code":"342225199504065369","insured_phone":"15856218334","time":1529378970,"expiry_date":0}';
//		$input['bank_phone'] = '15701681524';
		$person_data = json_decode($input['person_data'], true);
		$requset_data = [];
		$requset_data['name'] = $person_data['insured_name'];
		$requset_data['idCard'] = $person_data['insured_code'];
//		$requset_data['name'] = '王石磊';
//		$requset_data['idCard'] = '410881199406056514';
		$requset_data['phone'] = $input['bank_phone'];
		$requset_data['bankCode'] = $input['bank_code'];
		$requset_url = config('yunda.bank_verify_url');
		$key = "bank_verify_code_".$requset_data['phone'];
		if(Redis::exists($key)){
			$return_data['status'] = '500';
			$return_data['content'] = '您已经获取验证码成功，请稍后重试';
			return json_encode($return_data,JSON_UNESCAPED_UNICODE);
		}
		$response = Curl::to($requset_url)
			->returnResponseObject()
			->withData(json_encode($requset_data))
			->withTimeout(60)
			->post();
		$return_data = [];
		if($response->status != 200){
			$return_data['status'] = '500';
			$return_data['content'] = '获取验证码失败';
			return json_encode($return_data,JSON_UNESCAPED_UNICODE);
		}else{
			$content = $response->content;
			if(!json_decode($content,true)){
				$return_data['status'] = '500';
				$return_data['content'] = '获取验证码失败';
				return json_encode($return_data,JSON_UNESCAPED_UNICODE);
			}else{
				$response = json_decode($content,true);
				if($response['code']==200||$response['code']=="200"){
					$return_data['status'] = '200';
					$return_data['content'] = '获取验证码成功';
					$return_data['data'] = $response['data'];
					//TODO 使用缓存
					$expiresAt = 60;
					Redis::setex($key,$expiresAt,$content);
					return json_encode($return_data,JSON_UNESCAPED_UNICODE);
				}else{
					$return_data['status'] = '500';
					$return_data['content'] = '获取验证码失败';
					return json_encode($return_data,JSON_UNESCAPED_UNICODE);
				}
			}
		}
	}

	/**
	 * 校验银行卡绑定验证码
	 * @param $data
	 * @return bool|string
	 */
	public function doBankVerify($data){
		$key = "bank_verify_code_".$data['phone'];
		$content = Redis::get($key);
		$requset_id = json_decode($content,true)??"";
		if(empty($requset_id)){
			return false;
		}
		$requset_id = isset(json_decode($content,true)['data']['requestId'])?json_decode($content,true)['data']['requestId']:"";
		if(empty($requset_id)){
			return false;
		}
		$requset_data = [];
		$requset_data['requestId'] = $requset_id;
		$requset_data['vdCode'] = $data['verify_code'];
		$requset_url = config('yunda.check_bank_verify_url');
		$response = Curl::to($requset_url)
			->returnResponseObject()
			->withData(json_encode($requset_data))
			->withTimeout(60)
			->post();
		$return_data = [];
		if($response->status != 200){
			$return_data['status'] = '500';
			$return_data['content'] = '检验验证码失败';
			return false;
		}else{
			$content = $response->content;
			if(!json_decode($content,true)){
				$return_data['status'] = '500';
				$return_data['content'] = '检验验证码失败';
				return false;
			}else{
				$response = json_decode($content,true);
				if($response['code']==200||$response['code']=="200"){
					return true;
				}else{
					return false;
				}
			}
		}
	}

	/**
	 * 绑定银行卡触发投保操作
	 * @access public
	 * @return json
	 *
	 */
	public function doBankInsured($person_phone){
		$user_res = Person::where('phone', $person_phone)
			->select('id', 'name', 'papers_type', 'papers_code', 'phone', 'email', 'address', 'address_detail')
			->first();
		$return_data = [];
		//姓名，身份证信息，手机号判空
		if (!$user_res['name'] || !$user_res['papers_code'] || !$user_res['phone']) {
			$return_data['status'] = '500';
			$return_data['msg'] = '个人信息不完善';
			return $return_data;
		}
		$person_code = $user_res['papers_code'];
		$user_setup_res = ChannelInsureSeting::where('cust_cod', $person_code)
			->select('authorize_status', 'authorize_start', 'authorize_bank', 'auto_insure_status', 'auto_insure_type', 'auto_insure_price', 'auto_insure_time')
			->first();
		if (!$user_setup_res || !$user_setup_res['authorize_bank']) {
			$return_data['status'] = '500';
			$return_data['msg'] = '没有开启快递保免密支付';
			return $return_data;
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
		$return_data['status'] = '200';
		$return_data['msg'] = '支付中，请稍等~';
		return $return_data;
	}

	/**
	 * 银行卡查看详情
	 * @access public
	 * @params cust_id
	 * @params bank_id
	 * @return view
	 * 用户不能删除的银行卡类型：1.从韵达传递过来的数据中获取的银行卡信息 2.银行卡列表中还剩最后一张银行卡时
	 * 当满足这两种情况时，不显示删除按钮
	 * bank_type  add：用户添加，own：韵达数据本身
	 *
	 */
	public function bankInfo($bank_id)
	{
		$bank_res = Bank::where('id', $bank_id)
			->select('id','cust_id', 'bank', 'bank_code', 'bank_city', 'bank_deal_type', 'phone')
			->first();
		$cust_id = $bank_res['cust_id'];
		$bank_num = Bank::where('cust_id', $cust_id)
			->where('state','<>','1')
			->select('bank_code')
			->get();
		$bank_del_status = true;//删除按钮显示状态，默认显示
		if (count($bank_num) <= 1) {//只剩最后一张银行卡
			$bank_del_status = false;
		}
//		if ($bank_res['bank_deal_type'] == '1') {//从韵达传递过来的数据中获取的银行卡信息
//			$bank_del_status = false;
//		}
		return view('channels.yunda.bank_info', compact('cust_id', 'bank_res', 'bank_del_status'));
	}

	/**
	 * 银行卡删除操作
	 * @access public
	 * @params cust_id
	 * @params bank_id
	 * @return json
	 * 用户不能删除的银行卡类型：1.从韵达传递过来的数据中获取的银行卡信息 2.银行卡列表中还剩最后一张银行卡时
	 *
	 */
	public function bankDel()
	{
		$input = $this->request->all();
		$cust_id = $input['cust_id'];
		$bank_cod = $input['bank_code'];
		$bank_id = $input['bank_id'];
		$bank_num = Bank::where('cust_id', $cust_id)
			->where('state','<>','1')
			->select('bank_code')
			->get();
		$bank_authorize = ChannelInsureSeting::where('authorize_bank',$bank_cod)
			->where('cust_id', $cust_id)
			->select('id')
			->first();
		if (count($bank_num) <= 1) {//只剩最后一张银行卡
			return json_encode(['status' => '500', 'msg' => '最后一张银行卡，不能删除']);
		}
//		if ($bank_res['bank_type'] == '1') {//从韵达传递过来的数据中获取的银行卡信息
//			return json_encode(['status' => '500', 'msg' => '系统银行卡数据，不能删除']);
//		}
		DB::beginTransaction();
		try{
			$update_res = Bank::where('id', $bank_id)
				->update([
					'state'=>1
				]);
			if(!empty($bank_authorize)){
				$bank_res = Bank::where('cust_id', $cust_id)
					->where('state','<>','1')
					->select('bank_code')
					->get();
				$insure_seting = ChannelInsureSeting::where('id',$bank_authorize['id'])
					->update([
						'authorize_bank'=>$bank_res[0]['bank_code']
					]);
			}else{
				$insure_seting = '1';
			}
			if($update_res&&$insure_seting){
				DB::commit();
				return json_encode(['status' => '200', 'msg' => '银行卡删除成功']);
			}else{
				DB::rollBack();
				return json_encode(['status' => '500', 'msg' => '银行卡删除失败']);
			}
		}catch (\Exception $e){
			DB::rollBack();
			return json_encode(['status' => '500', 'msg' => '银行卡删除失败']);
		}
	}

	/**
	 * 免密授权页面
	 * 1.是否有银行卡，优先银行卡支付
	 * 2.判断有没有预投保，然后显示是否开通微信免密支付
	 * @access public
	 * @return view
	 *
	 */
	public function bankAuthorize()
	{
		$token_data = TokenHelper::getData($this->input['token']);
		$person_code = $token_data['insured_code'];
		$person_phone = $token_data['insured_phone'];
		$cust_id = '';
		$cust_name = '';
		$cust_phone = '';
		$user_res = Person::where('phone', $person_phone)
			->select('id', 'name', 'phone', 'papers_code')
			->first();
		if (!empty($user_res)) {
			$cust_id = $user_res['id'];
			$cust_name = $user_res['name'];
			$cust_phone = $user_res['phone'];
		}
		$insure_seting = ChannelInsureSeting::where('cust_cod', $person_code)
			->select('authorize_bank')
			->first();
		$authorize_status = false;
		$bank = [];
		if (!empty($insure_seting)) {
			$bank['code'] = $insure_seting['authorize_bank'];
			$authorize_status = true;
		}
		$bank_res = Bank::where('cust_id', $cust_id)
			->select('bank', 'bank_code', 'bank_city', 'phone', 'bank_deal_type')
			->get();
		if (!empty($bank_res)) {
			foreach ($bank_res as $value) {
				if ($value['bank_deal_type'] == '1') {
					$bank['code'] = $value['bank_code'];
					$bank['name'] = $value['bank'];
					$bank['city'] = $value['bank_city'];
					$bank['phone'] = $value['phone'];
				}
			}
		}
		$params = [];
		$params['person_code'] = $person_code ?? "";
		$params['person_phone'] = $cust_phone ?? "";
		$params['person_name'] = $cust_name ?? "";
		$wechat_res = $this->getWechatAuthorize($params);//微信签约显示状态
		if ($wechat_res['status']) {
			$wechat_status = $wechat_res['status'];//微信签约按钮显示状态
			$wechat_url = $wechat_res['url'];//签约URL
		} else {
			$wechat_status = $wechat_res['status'];//微信签约按钮显示状态
			$wechat_url = '';//签约URL
		}
		//签约页面上会显示签约人的相关信息
		return view('channels.yunda.bank_authorize', compact('token_data','bank', 'cust_id', 'cust_name', 'cust_phone', 'person_code', 'authorize_status', 'wechat_status', 'wechat_url'));
	}

	/**
	 * 获取是否可以微信免密授权
	 * 筛选条件：
	 * 是否签约过
	 * 是否预投保
	 * @access public
	 * @return json
	 */
	private function getWechatAuthorize($params)
	{
		$person_code = $params['person_code'];
		$person_phone = $params['person_phone'];
		$person_name = $params['person_name'];
		$return_data = [];
		//查询签约情况
		$contrant_res = ChannelContract::where('channel_user_code', $person_code)
			->select('openid', 'contract_id', 'contract_expired_time')
			->first();
		if (!empty($contrant_res)) {
			$return_data['status'] = false;
			$return_data['message'] = '用户已开通微信免密支付';
			return $return_data;
		}
		$channel_res = ChannelOperate::where('channel_user_code', $person_code)
			->where('prepare_status', '200')
			->where('operate_time', date('Y-m-d', time() - 24 * 3600))
			->select('proposal_num')
			->first();
		if (empty($channel_res)) {
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
		$data['pay_account'] = $person_name . $person_phone;
		$data['clientIp'] = IPHelper::getIP();
		$data = $this->sign_help->tySign($data);
		//发送请求
		$response = Curl::to(env('TY_API_SERVICE_URL') . '/ins_curl/contract_ins')
			->returnResponseObject()
			->withData($data)
			->withTimeout(60)
			->post();
		if ($response->status != 200) {
			ChannelOperate::where('channel_user_code', $person_code)
				->where('proposal_num', $union_order_code)
				->update(['pay_status' => '500', 'pay_content' => $response->content]);
			//LogHelper::logError($response->content, 'YD_pay_order_' . $union_order_code);
			$return_data['status'] = false;
			$return_data['message'] = '用户获取签约链接失败';
			return $return_data;
		}
		$response_data = json_decode($response->content, true);//签约返回数据
		$return_data['status'] = true;
		$return_data['message'] = '用户获取签约链接成功';
		$return_data['url'] = $response_data['result_content']['contracturl'];//禁止转义
		return $return_data;
	}

	/**
	 * 免密授权详情页面
	 * @access public
	 * @return view
	 *
	 */
	public function bankAuthorizeInfo()
	{
		$token_data = TokenHelper::getData($this->input['token']);
		$person_code = $token_data['insured_code'];
		$person_name = $token_data['insured_name'];
		$person_phone = $token_data['insured_phone'];
		$user_res = Person::where('phone', $person_phone)->select('id')->first();
		$cust_id = $user_res['id'];
		$bank_res = Bank::where('cust_id', $cust_id)
			->select('bank', 'bank_code', 'bank_city', 'phone', 'bank_deal_type')
			->first();
		$insured_name = $user_res['name'] ?? $person_name;
		$insured_code = $user_res['papers_code'] ?? $person_code;
		$insured_phone = $user_res['phone'] ?? $person_phone;
		$bank_name = $bank_res['bank'] ?? "";
		$bank_code = $bank_res['bank_code'] ?? "";
		$repeat_res = ChannelInsureSeting::where('cust_id', $cust_id)
			->select('id')->first();
		$authorize_status = true;//授权按钮显示状态
		if (!empty($repeat_res)) {
			$authorize_status = false;
		}
		//签约页面上会显示签约人的相关信息
		return view('channels.yunda.bank_authorize_info', compact('insured_name', 'insured_code', 'insured_phone', 'bank_code', 'bank_name', 'authorize_status', 'cust_id'));
	}

	/**
	 * 免密授权页面
	 * 1.是否有银行卡，优先银行卡支付
	 * 2.判断有没有预投保，然后显示是否开通微信免密支付
	 * @access public
	 * @return view
	 *
	 */
	public function insureAuthorize()
	{
		$token_data = TokenHelper::getData($this->input['token']);
		$person_code = $token_data['insured_code'];
		$person_phone = $token_data['insured_phone'];
		$cust_id = '';
		$cust_name = '';
		$cust_phone = '';
		$user_res = Person::where('phone', $person_phone)
			->select('id', 'name', 'phone', 'papers_code')
			->first();
		if (!empty($user_res)) {
			$cust_id = $user_res['id'];
			$cust_name = $user_res['name'];
			$cust_phone = $user_res['phone'];
		}
		$insure_seting = ChannelInsureSeting::where('cust_cod', $person_code)
			->select('authorize_bank')
			->first();
		$authorize_status = false;
		$bank = [];
		if (!empty($insure_seting)) {
			$bank['code'] = $insure_seting['authorize_bank'];
			$authorize_status = true;
		}
		$bank_res = Bank::where('cust_id', $cust_id)
			->select('bank', 'bank_code', 'bank_city', 'phone', 'bank_deal_type')
			->get();
		if (!empty($bank_res)) {
			foreach ($bank_res as $value) {
				if ($value['bank_deal_type'] == '1') {
					$bank['code'] = $value['bank_code'];
					$bank['name'] = $value['bank'];
					$bank['city'] = $value['bank_city'];
					$bank['phone'] = $value['phone'];
				}
			}
		}
		$params = [];
		$params['person_code'] = $person_code ?? "";
		$params['person_phone'] = $cust_phone ?? "";
		$params['person_name'] = $cust_name ?? "";
		$wechat_res = $this->getWechatAuthorize($params);//微信签约显示状态
		if ($wechat_res['status']) {
			$wechat_status = $wechat_res['status'];//微信签约按钮显示状态
			$wechat_url = $wechat_res['url'];//签约URL
		} else {
			$wechat_status = $wechat_res['status'];//微信签约按钮显示状态
			$wechat_url = '';//签约URL
		}
		//签约页面上会显示签约人的相关信息
		return view('channels.yunda.insure_authorize', compact('token_data','bank', 'cust_id', 'cust_name', 'cust_phone', 'person_code', 'authorize_status', 'wechat_status', 'wechat_url'));
	}

	/**
	 * 免密授权详情页面
	 * @access public
	 * @return view
	 *
	 */
	public function insureAuthorizeInfo()
	{
		$token_data = TokenHelper::getData($this->input['token']);
		$person_code = $token_data['insured_code'];
		$person_name = $token_data['insured_name'];
		$person_phone = $token_data['insured_phone'];
		$user_res = Person::where('phone', $person_phone)->select('id')->first();
		$cust_id = $user_res['id'];
		$bank_res = Bank::where('cust_id', $cust_id)
			->select('bank', 'bank_code', 'bank_city', 'phone', 'bank_deal_type')
			->first();
		$insured_name = $user_res['name'] ?? $person_name;
		$insured_code = $user_res['papers_code'] ?? $person_code;
		$insured_phone = $user_res['phone'] ?? $person_phone;
		$bank_name = $bank_res['bank'] ?? "";
		$bank_code = $bank_res['bank_code'] ?? "";
		$repeat_res = ChannelInsureSeting::where('cust_id', $cust_id)
			->select('id')->first();
		$authorize_status = true;//授权按钮显示状态
		if (!empty($repeat_res)) {
			$authorize_status = false;
		}
		//签约页面上会显示签约人的相关信息
		return view('channels.yunda.insure_authorize_info', compact('insured_name', 'insured_code', 'insured_phone', 'bank_code', 'bank_name', 'authorize_status', 'cust_id'));
	}

	/**
	 * 免密授权设置
	 * @access public
	 * @return view
	 *
	 */
	public function doBankAuthorize()
	{
		$input = $this->request->all();
		$person_name = $input['person_name'];
		$bank_phone = $input['bank_phone'];
		$bank_code = $input['bank_code'];
		$verify_code = $input['verify_code'];
		$person_data = json_decode($input['person_data'], true);
		$person_phone = $person_data['insured_phone'];
		$person_code = $person_data['insured_code'];
		$verify_data = [];
		$verify_data['phone'] = $bank_phone;
		$verify_data['verify_code'] = $verify_code;
		$verify_res = $this->doBankVerify($verify_data);
		if(!$verify_res){
			return json_encode(['status' => '500', 'msg' => '验证码校验失败']);
		}
		$user_res = Person::where('phone', $person_phone)
			->select('id', 'name', 'papers_type', 'papers_code', 'phone', 'address')
			->first();
		DB::beginTransaction();
		try{
		if (empty($user_res)) {
			$user_res['id'] = Person::insertGetId([
				'name' => $person_name,
				'papers_type' => '1',
				'papers_code' => $person_code,
				'phone' => $person_phone,
				'cust_type' => '1',
				'authentication' => '1',
				'del' => '0',
				'status' => '1',
				'created_at' => time(),
				'updated_at' => time(),
			]);
		}
		$cust_id = $user_res['id'];
		$seting_res = ChannelInsureSeting::where('cust_id', $cust_id)
			->select('id')
			->first();
		if (empty($seting_res)) {
			$bank_res = Bank::where('bank_code', $bank_code)
				->select('id')
				->first();
			if (empty($bank_res)) {
				Bank::insert([
					'cust_type' => '1',
					'cust_id' => $cust_id,
					'bank' => '',
					'bank_code' => $bank_code,
					'bank_city' => '',
					'bank_deal_type' => '1',
					'phone' => '',
					'created_at'=>time(),
					'updated_at'=>time(),
				]);
			}else{
				return json_encode(['status' => '500', 'msg' => '此银行卡已开通免密授权']);
			}
			ChannelInsureSeting::insert([
				'cust_id' => $cust_id,
				'cust_cod' => $person_code ?? "0",
				'cust_type' => 'user',
				'authorize_bank' => $bank_code,
				'authorize_status' => '1',
				'authorize_start' => time(),
				'auto_insure_status' => '1',
				'auto_insure_type' => '1',
				'auto_insure_price' => '2',
				'auto_insure_time' => time(),
				'updated_at'=>date('Y-m-d H:i:s',time()),
			]);
		} else {
			ChannelInsureSeting::where('cust_cod', $person_code)->update([
				'authorize_status' => '1',
				'authorize_start' => time(),
				'authorize_bank' => $bank_code,
				'auto_insure_status'=>'1',
			]);
		}
			$bank_insure_res = $this->doBankInsured($person_phone);
			if($bank_insure_res['status']=='200'){
				DB::commit();
				return json_encode(['status' => '200', 'msg' => '开通免密支付成功']);
			}else{
				DB::rollBack();
				return json_encode(['status' => '500', 'msg' => '开通免密支付失败']);
			}
		}catch (\Exception $e){
			DB::rollBack();
			return json_encode(['status' => '500', 'msg' => '开通免密支付失败']);
		}
	}
}
