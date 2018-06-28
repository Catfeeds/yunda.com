<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/5/23
 * Time: 11:09
 * 韵达投保微信代扣定时任务
 */

namespace App\Http\Controllers\ChannelsApiControllers;

use App\Models\ChannelPrepareInfo;
use App\Models\Warranty;
use Illuminate\Http\Request;
use App\Helper\DoChannelsSignHelp;
use App\Helper\RsaSignHelp;
use App\Helper\AesEncrypt;
use Ixudra\Curl\Facades\Curl;
use Validator, DB, Image, Schema;
use App\Models\Channel;
use App\Models\ChannelOperate;
use App\Models\UserChannel;
use App\Models\UserChannels;
use App\Models\User;
use App\Models\UserContact;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Session, Cache;
use App\Models\Order;
use App\Models\OrderParameter;
use App\Models\WarrantyPolicy;
use App\Models\WarrantyRecognizee;
use App\Models\WarrantyRule;
use App\Models\OrderBrokerage;
use App\Helper\LogHelper;
use App\Models\Product;
use App\Models\ApiInfo;
use App\Models\Bank;
use App\Models\UserBank;
use App\Models\Competition;
use App\Models\CompanyBrokerage;
use App\Models\OrderPrepareParameter;
use App\Models\ChannelClaimApply;
use App\Models\ChannelInsureInfo;
use App\Helper\Issue;
use App\Helper\UploadFileHelper;
use App\Helper\IdentityCardHelp;
use App\Models\ChannelContract;
use Illuminate\Console\Command;

class WechatPayController
{
	/**
	 * Create a new command instance.
	 * @return void
	 * 初始化
	 *
	 */
	public function __construct(Request $request)
	{
		$this->sign_help = new DoChannelsSignHelp();
		$this->signhelp = new RsaSignHelp();
		$this->request = $request;
		set_time_limit(0);//永不超时
	}

	/**
	 * 微信代扣支付
	 * 定时任务，跑支付
	 * 避免重复支付，在操作表里添加一个字段，表示今天已经支付。
	 * 如果没有支付成功，用定时任务六小时做一次轮训
	 */
	public function wechatPay()
	{
		set_time_limit(0);//永不超时
		$channel_operate_info = ChannelOperate::where('operate_time', date('Y-m-d', time() - 24 * 3600))//前一天的订单
			->where('is_work', '1')//已上工
			->where('prepare_status', '200')//预投保成功
			->select('proposal_num')
			->get();
		if (count($channel_operate_info) == '0') {
			die;
		}
		$channel_contract_info = ChannelContract::where('is_auto_pay', '0')
			->select('openid', 'contract_id', 'contract_expired_time', 'channel_user_code')
			//openid,签约协议号,签约过期时间,签约人身份证号
			->groupBy('channel_user_code')
			->get();
		//循环请求，免密支付
		foreach ($channel_contract_info as $value) {
			$person_code = $value['channel_user_code'];
			$channel_res = ChannelOperate::where('channel_user_code', $person_code)
				->where('prepare_status', '200')//预投保成功
				->where('pay_status','')//预投保成功
				->orWhere('pay_status','0')
				->orWhere('pay_status',0)
				->where('operate_time', date('Y-m-d', time() - 24 * 3600))//前一天的订单
				->where('is_work','1')//已上工
				->select('proposal_num')
				->first();
			if(empty($channel_res)){
				return 'end';die;
			}
			$union_order_code = $channel_res['proposal_num'];
			$data = [];
			$data['price'] = '2';
			$data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
			$data['quote_selected'] = '';
			$data['insurance_attributes'] = '';
			$data['union_order_code'] = $union_order_code;
			$data['pay_account'] = $value['openid'];
			$data['contract_id'] = $value['contract_id'];
			//Loghelper::logPay($data, 'YD_pay_data');
			$data = $this->signhelp->tySign($data);
			//发送请求
			$response = Curl::to(env('TY_API_SERVICE_URL') . '/ins_curl/wechat_pay_ins')
				->returnResponseObject()
				->withData($data)
				->withTimeout(60)
				->post();
			if ($response->status != 200) {
				ChannelOperate::where('channel_user_code', $person_code)
					->where('proposal_num', $union_order_code)
					->update(['pay_status' => '500', 'pay_content' => $response->content]);
			}else{
				DB::beginTransaction();
				try {
					$return_data = json_decode($response->content, true);//返回数据
					//TODO  可以改变订单表的状态
					ChannelOperate::where('channel_user_code', $person_code)
						->where('proposal_num', $union_order_code)
						->update(['pay_status' => '200']);
					WarrantyRule::where('union_order_code', $union_order_code)
						->update(['status' => '1']);
					Order::where('order_code', $union_order_code)
						->update(['status' => '1']);
					DB::commit();
					//Loghelper::logPay(date('Y-m-d H:i:s',time()), 'YD_pay_end_'.$person_code);
				} catch (\Exception $e) {
					DB::rollBack();
					//Loghelper::logPay(date('Y-m-d H:i:s',time()), 'YD_pay_error_'.$person_code);
					return false;
				}
			}
			//做标记，说明此订单已经支付过。
			$mark_res = ChannelOperate::where('proposal_num',$union_order_code)->select('pay_status')->first();
			if($mark_res['pay_status']==0||$mark_res['pay_status']==''||$mark_res['pay_status']=='0'){
				$update_res = ChannelOperate::where('proposal_num', $union_order_code)
				->update(
					['pay_status' => '300']
				);
				dump($update_res);
			}
			dump($union_order_code);
			echo '支付完成';die;
		}
	}
}