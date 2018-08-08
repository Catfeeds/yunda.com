<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/8/8
 * Time: 10:12
 * 测试投保信息推送韵达
 */

namespace App\Http\Controllers\ChannelsApiControllers\Yunda;

use App\Jobs\YunDaCallBack;
use App\Jobs\YunDaCallBackInsure;
use App\Models\Person;
use App\Models\CustWarranty;
use App\Helper\LogHelper;
use Ixudra\Curl\Facades\Curl;


class CallBackYdController
{

	public function index(){
		$params = [];
		$params['date'] = strtotime(date('Ymd'));//当日时间
		$params['papers_code'] = '320826199007030556';//用户信息-身份证号
		$params['phone'] = '15195564007';//用户信息-手机号
		dispatch(new YunDaCallBack($params));
		return '推送成功';
//		$res = $this->handle($params);
//		return $res;
	}

	//从数据库里查出用户信息,然后返回(只返回当前用户当天投保成功的保单) TODO 韵达已经做了去重操作
	public function handle($input)
	{
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

	public function time(){
		$warranty_res = CustWarranty::where('warranty_status','4')
//			->where('created_at','>=',strtotime(date('Y-m-d')).'000')//当天开始时间
//			->where('created_at','<',strtotime(date('Y-m-d',strtotime('+1 day'))).'000')//当天结束时间
			->select('warranty_code','start_time','end_time','pay_time','premium')
			->get();
		if(empty($warranty_res)){
			return false;
		}
		foreach ($warranty_res as $value){
			//$this->handles($value);
			//dispatch(new YunDaCallBackInsure($value));
		}
		return count($warranty_res).'条数据推送成功';
	}

	public function handles($input)
	{
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