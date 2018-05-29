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
		$person_code = $token_data['insured_code'];
		$person_phone = $token_data['insured_phone'];
		$user_res = Person::where('phone', $person_phone)
			->select('id', 'name', 'papers_type', 'papers_code', 'phone', 'address')
			->first();
		$cust_id = $user_res['id'];
		$bank_authorize = ChannelInsureSeting::where('cust_cod',$person_code)
			->with('bank')
			->select('authorize_bank')
			->first();
		if(!empty($bank_authorize)&&empty($bank_authorize['bank'])){
			Bank::insert([
				'cust_id'=>$user_res['id'],
				'cust_type'=>'1',
				'bank'=>"",
				'bank_code'=>$bank_authorize['authorize_bank'],
				'bank_city'=>"",
				'phone'=>"",
				'created_at'=>time(),
				'updated_at'=>time(),
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
		$bank = $input['bank_name']??" ";
		$bank_cod = $input['bank_code'];
		$bank_city = $input['bank_city']??" ";
		$cust_res = Person::where('papers_code', $person_data['insured_code'])->select()->first();
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
		$cust_res = Person::where('papers_code', $person_data['insured_code'])->select()->first();
		$bank_repeat = Bank::where('cust_id', $cust_res['id'])
			->where('bank', $bank)
			->where('bank_code', $bank_cod)
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
			DB::commit();
		}catch (\Exception $e){
			DB::rollBack();
			return json_encode(['status' => '500', 'msg' => '银行卡添加失败']);
		}
		if ($insert_res) {
			return json_encode(['status' => '200', 'msg' => '银行卡添加成功']);
		} else {
			return json_encode(['status' => '500', 'msg' => '银行卡添加失败']);
		}
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
			->select('cust_id', 'bank', 'bank_code', 'bank_city', 'bank_deal_type', 'phone')
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
		$bank_num = Bank::where('cust_id', $cust_id)
			->where('state','<>','1')
			->select('bank_code')
			->get();
		$bank_authorize = ChannelInsureSeting::where('authorize_bank',$bank_cod)
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
		$del_res = Bank::where('cust_id', $cust_id)
			->where('bank_code', $bank_cod)
			->update([
				'state'=>'1'
			]);
		if(!empty($bank_authorize)){
			ChannelInsureSeting::where('id',$bank_authorize['id'])
				->update([
					'authorize_bank'=>$bank_num[0]['bank_code']
				]);
		}
		DB::commit();
		}catch (\Exception $e){
			DB::rollBack();
			return json_encode(['status' => '500', 'msg' => '银行卡删除失败']);
		}
		if ($del_res) {
			return json_encode(['status' => '200', 'msg' => '银行卡删除成功']);
		} else {
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
		return view('channels.yunda.bank_authorize', compact('bank', 'cust_id', 'cust_name', 'cust_phone', 'person_code', 'authorize_status', 'wechat_status', 'wechat_url'));
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
		return view('channels.yunda.insure_authorize', compact('bank', 'cust_id', 'cust_name', 'cust_phone', 'person_code', 'authorize_status', 'wechat_status', 'wechat_url'));
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
		$person_code = $input['person_code'];
		$person_name = $input['person_name'];
		$bank_code = $input['bank_code'];
		$user_res = Person::where('papers_code', $person_code)->select('id', 'name', 'papers_type', 'papers_code', 'phone', 'address')->first();
		DB::beginTransaction();
		try{
		if (empty($user_res)) {
			$user_res['id'] = Person::insertGetId([
				'name' => $person_name,
				'papers_type' => '1',
				'papers_code' => $person_code,
				'phone' => '',
				'cust_type' => '1',
				'authentication' => '1',
				'del' => '0',
				'status' => '1',
				'created_at' => time(),
				'updated_at' => time(),
			]);
		}
		$cust_id = $user_res['id'];
		$seting_res = ChannelInsureSeting::where('cust_cod', $person_code)
			->select('id')->first();
		$bank_res = Bank::where('bank_code', $bank_code)->select('id')->first();
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
		}
		if (empty($seting_res)) {
			ChannelInsureSeting::insert([
				'cust_id' => $cust_id,
				'cust_cod' => $person_code ?? "0",
				'cust_type' => '',
				'authorize_bank' => $bank_code,
				'authorize_status' => '1',
				'authorize_start' => time(),
				'auto_insure_status' => '1',
				'auto_insure_type' => '1',
				'auto_insure_price' => '2',
				'auto_insure_time' => time(),
			]);
		} else {
			ChannelInsureSeting::where('cust_cod', $person_code)->update([
				'authorize_status' => '1',
				'authorize_start' => time(),
				'authorize_bank' => $bank_code,
				'auto_insure_status'=>'1',
			]);
		}
			DB::commit();
			return json_encode(['status' => '200', 'msg' => '开通免密支付成功']);
		}catch (\Exception $e){
			DB::rollBack();
			return json_encode(['status' => '500', 'msg' => '开通免密支付成功']);
		}
	}
}
