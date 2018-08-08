<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Ixudra\Curl\Facades\Curl;
use App\Helper\LogHelper;
use App\Models\Person;
use App\Models\CustWarranty;


class YunDaCallBack implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $param;

	/**
	 * Create a new command instance.
	 * @return void
	 * 初始化
	 *
	 */
	public function __construct($param)
	{
		$this->param = $param;
		set_time_limit(0);//永不超时
	}

	public function handle()
	{
		$input = $this->param;
		$person_res = Person::where('phone',$input['phone'])
			->where('papers_code',$input['papers_code'])
			->select('id')
			->first();
		$warranty_res = CustWarranty::where('user_id',$person_res['id'])
			->where('created_at','>=',strtotime(date('Y-m-d')).'000')//当天开始时间
			->where('created_at','<',strtotime(date('Y-m-d',strtotime('+1 day'))).'000')//当天结束时间
			->where('warranty_status','4')
			->select('warranty_code','start_time','end_time','pay_time','premium')
			->first();
		LogHelper::logCallBackYDSuccess($input, 'YD_CallBack_Request_Params');
		$params = [];
		$params['ordersId'] = $warranty_res['warranty_code'];//保单号
		$params['payTime'] = date('Y-m-d H:i:s',time());//保单支付时间
		$params['effectiveTime'] = date('Y-m-d H:i:s',$warranty_res['start_time']/1000).'-'.date('Y-m-d H:i:s',$warranty_res['end_time']/1000);//保单生效时间
		//订单类型,1天/3天/10天
		switch ($warranty_res['premium']){
			case '2':
				$params['type'] = '0';
				break;
			case '5':
				$params['type'] = '1';
				break;
			case '13':
				$params['type'] = '2';
				break;
		}
		$params['status'] = '1';//订单状态
		$params['ordersName'] = '人身意外综合保险';
		$params['companyName'] = '英大泰和财产保险有限公司';
		LogHelper::logCallBackYDSuccess($params, 'YD_CallBack_Params');
		$params = json_encode($params,JSON_UNESCAPED_UNICODE);
		$requset_url = config('yunda.callbank_request_url');
		LogHelper::logCallBackYDSuccess($requset_url, 'YD_CallBack_url');
		$response = Curl::to($requset_url)
			->returnResponseObject()
			->withData($params)
			->withTimeout(60)
			->post();
		LogHelper::logCallBackYDSuccess($response, 'YD_CallBack_Result');
		if($response->status!=200){
			LogHelper::logCallBackYDError($response->content, 'YD_CallBack_Result');
			//TODO 失败后用定时任务做轮询
		}
		return $response->content;
	}
}