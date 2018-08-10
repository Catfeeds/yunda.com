<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/08/08
 * Time: 15:07
 * 韵达回传信息定时任务（时间没定）
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Helper\DoChannelsSignHelp;
use App\Helper\RsaSignHelp;
use App\Helper\LogHelper;
use App\Helper\AesEncrypt;
use Validator, DB, Image, Schema;
use Session, Cache;
use App\Jobs\YunDaCallBackInsure;
use App\Models\CustWarranty;
use App\Models\Person;
use Ixudra\Curl\Facades\Curl;




class YundaCallback extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'yundacallback';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'yundacallback Command description';

	/**
	 * Create a new command instance.
	 * @return void
	 * 初始化
	 *
	 */
	public function __construct(Request $request)
	{
		parent::__construct();
		$this->sign_help = new DoChannelsSignHelp();
		$this->signhelp = new RsaSignHelp();
		$this->request = $request;
		set_time_limit(0);//永不超时
	}


	/**
	 * 返回给韵达的对账信息
	 *
	 */
	public function handle()
	{
		set_time_limit(0);//永不超时
		$warranty_res = CustWarranty::where('warranty_status', '4')
			->where('created_at', '>=', strtotime(date('Y-m-d')) . '000')//当天开始时间
			->where('created_at', '<', strtotime(date('Y-m-d', strtotime('+1 day'))) . '000')//当天结束时间
			->select('warranty_code', 'start_time', 'end_time', 'pay_time', 'premium')
			->get();
		if (empty($warranty_res)) {
			return false;
		}
		foreach ($warranty_res as $value) {
			$this->doRequest($value);
		}
		LogHelper::logCallBackYDSuccess(count($warranty_res), 'YD_CallBack_Request_Params_count');
	}

	public function doRequest($input)
	{
		set_time_limit(0);//永不超时
		LogHelper::logCallBackYDSuccess($input, 'YD_CallBack_Request_Params');
		$params = [];
		$params['ordersId'] = $input['warranty_code'];//保单号
		$params['payTime'] = date('Y-m-d H:i:s',time());//保单支付时间
		$params['effectiveTime'] = date('Y-m-d H:i:s',$input['start_time']/1000).'-'.date('Y-m-d H:i:s',$input['end_time']/1000);//保单生效时间
		//订单类型,1天/3天/10天
		switch ($input['premium']){
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
		$params = json_encode($params);
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
		}
		return $response->content;
	}
}


