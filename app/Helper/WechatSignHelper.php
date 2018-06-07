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
use Ixudra\Curl\Facades\Curl;
use Validator, DB, Image, Schema;
use Session,Cache;

class WechatSignHelper
{

	/**
	 * 签约操作
	 * @param $union_order_code
	 * @param $cip
	 * @param $phone
	 * @param $person_code
	 * @return $this|bool
	 */
	public function wechatSign($union_order_code,$cip,$phone,$person_code){
		$data = [];
		$data['price'] = '2';
		$data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
		$data['quote_selected'] = '';
		$data['insurance_attributes'] = '';
		$data['union_order_code'] = $union_order_code;
		$data['pay_account'] = $phone??$union_order_code;
		$data['clientIp'] = $cip??'222.131.24.108';
		$sign_help = new RsaSignHelp();
		$data = $sign_help->tySign($data);
		//发送请求
		$response = Curl::to(env('TY_API_SERVICE_URL') . '/ins_curl/contract_ins')
			->returnResponseObject()
			->withData($data)
			->withTimeout(60)
			->post();
		$return_data = [];
		if($response->status != 200){
			ChannelOperate::where('channel_user_code',$person_code)
				->where('proposal_num',$union_order_code)
				->update(['pay_status'=>'500','pay_content'=>$response->content]);
			$return_data['status'] = '500';
			$return_data['content'] = '支付签约失败';
			return $return_data;
		}else{
			$content =  json_decode($response->content,true);//签约返回数据
			$return_data['status'] = '200';
			$return_data['content'] = $content;
			return $return_data;
		}
	}
}