<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Ixudra\Curl\Facades\Curl;
use App\Helper\LogHelper;

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
		//用户身份信息
		$input = $this->param;
		if (is_array($input)) {
			$input = json_encode($input, JSON_UNESCAPED_UNICODE);
		}
		LogHelper::logChannelInsureSuccess($input, 'YD_CallBack_Params');
		$requset_url = config('yunda.callbank_request_url');
		LogHelper::logChannelInsureSuccess($requset_url, 'YD_CallBack_url');
		$response = Curl::to($requset_url)
			->returnResponseObject()
			->withData($input)
			->withTimeout(60)
			->post();
		LogHelper::logChannelInsureSuccess($response, 'YD_CallBack_Result');

//		{
//			"code": "",
//    "remark": "推送成功！",
//    "data": "",
//    "result": true
//}

	}
}