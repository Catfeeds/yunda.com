<?php

namespace App\Helper;

use Validator;

class ReturnHelper
{
	/**
	 * 封装返回数据
	 *
	 * @param $code
	 * @param $msg
	 * @param null $data
	 * @param null $page
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function packageData($code, $msg, $data = NULL, $page = NULL)
	{
		$i = 0;
		$message = [];
		foreach ($msg as $key=>$value){
			$message[$i]['digest'] = $key;
			$message[$i]['details'] = $value;
			$i++;
		}
		$return = [
			'code' => $code,
			'message' => $message,
		];
		if(!is_null($data)){
			$return['data'] = $data;
		}
		if(!is_null($page)){
			$return['page'] = $page;
		}
		return response()->json($return);
	}

	/**
	 * 封装简单验证判断错误返回
	 *
	 * @param $params
	 * @param $condition
	 * @param null $msg
	 * @return bool
	 */
	public function validatorReturn($params,$condition,$msg = NULL)
	{
		$validator = Validator::make($params,$condition,$msg);
		if ($validator->fails()) {
			$messages=json_decode($validator->errors(),true);
			foreach ($messages as $key=>$value){
				$result[$key] = $value[0];
			}
			return $result;
		}
		return true;
	}

	/**
	 * 返回错误信息
	 * @param $code
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function returnErrorMsg($code,$data=null)
	{
		$msg = [];
		if($code == 200){
			$msg['default'] = '请求成功';
			if(!is_null($data))
				return $this->packageData($code,$msg,$data);
		}else{
			$msg['default'] = '请求失败';
		}
		return $this->packageData($code,$msg);
	}
}
