<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/6/6
 * Time: 18:32
 */

namespace App\Helper;


use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Models\CustWarranty;
use App\Models\ChannelContract;
use App\Models\ChannelOperate;
use App\Models\Person;
use Ixudra\Curl\Facades\Curl;
use Validator, DB, Image, Schema;
use Session,Cache;


class WechatPayHelper
{
	//微信代扣支付
	public function WechatPay($person_code){
		$return_data = [];
		$person_res = Person::where('papers_code',$person_code)
			->select('id','phone')
			->first();
		if(empty($person_res)){
			$return_data['status'] = '500';
			$return_data['content'] = 'no person message';
			return $return_data;
		}
		//查询签约
		$contract_res = ChannelContract::where('channel_user_code',$person_code)
			->select(['openid','contract_id'])
			->first();
		if(empty($contract_res)){
			$return_data['status'] = '500';
			$return_data['content'] = 'no contract';
			return $return_data;
		}
		//查询预投保
		$prepare_res = ChannelOperate::where('channel_user_code',$person_code)
			->where('prepare_status','200')
			->where('operate_time',date('Y-m-d',time()-24*3600))
			->select('proposal_num')
			->first();
		if(empty($prepare_res)){
			$return_data['status'] = '500';
			$return_data['content'] = 'no prepare';
			return $return_data;
		}
		$union_order_code = $prepare_res['proposal_num'];
		$data = [];
		$data['price'] = '2';
		$data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
		$data['quote_selected'] = '';
		$data['insurance_attributes'] = '';
		$data['union_order_code'] = $union_order_code??"";
		$data['pay_account'] = $contract_res['openid']??"";
		$data['contract_id'] = $contract_res['contract_id']??"";
		$signhelp = new RsaSignHelp();
		$data = $signhelp->tySign($data);
		//发送请求
		$response = Curl::to(env('TY_API_SERVICE_URL') . '/ins_curl/wechat_pay_ins')
			->returnResponseObject()
			->withData($data)
			->withTimeout(60)
			->post();
		// print_r($response);die;
		if($response->status != 200){
			ChannelOperate::where('channel_user_code',$person_code)
				->where('proposal_num',$union_order_code)
				->update(['pay_status'=>'500','pay_content'=>$response->content]);
			$return_data['status'] = '500';
			$return_data['content'] = 'faild to pay';
			return $return_data;
		}
		//TODO  可以改变订单表的状态
		DB::beginTransaction();//开启事务
		try{
			ChannelOperate::where('channel_user_code',$person_code)
				->where('proposal_num',$union_order_code)
				->update(['pay_status'=>'200']);
			CustWarranty::where('user_id',$person_res['id'])
				->where('pro_policy_no',$union_order_code)
				->update([
						'pay_status'=>'3',//支付完成
						'warranty_status'=>'4',//保障中
				]);
			DB::commit();
			$content =  json_decode($response->content,true);//返回数据
			$return_data['status'] = '200';
			$return_data['content'] = $content;
			return $return_data;
		}catch (\Exception $e)
		{
			DB::rollBack();
			$return_data['status'] = '500';
			$return_data['content'] = '写库失败';
			return $return_data;
		}
	}
}