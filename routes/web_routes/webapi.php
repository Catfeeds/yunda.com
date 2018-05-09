<?php
//TODO 2018-04-08韵达快递保  新接口,新路由
Route::group(['prefix' => 'webapi', 'namespace'=>'ChannelsApiControllers\Yunda'],function (){
	Route::get('/', function() { return 'Hello webapi'; });
	//对外接口
	Route::any('joint_login', 'IntersController@jointLogin');//联合登录
	Route::any('authorization_query', 'IntersController@authorizationQuery');//授权查询
	Route::any('do_wechat_pay', 'IntersController@doWechatpay');//微信支付接口
	Route::any('do_wechat_pre', 'PrepareController@doWechatPrepare');//测试预投保接口
	//投保流程
	Route::any('ins_info', 'IndexController@insInfo');//投保详情页面
	Route::any('ins_center', 'IndexController@insureCenter');//我的保险页面
	Route::any('do_insured', 'IndexController@doInsured');//投保操作
	Route::any('ins_error/{error_type}', 'IndexController@insError');//错误提示页面
	//静态页面
	Route::any('insure_tk_clause', 'IndexController@insTkClause');//产品条款页面-泰康
	Route::any('insure_yd_clause', 'IndexController@insYdClause');//产品条款页面-英大
	Route::any('insure_tk_notice', 'IndexController@insTkNotice');//产品须知页面-泰康
	Route::any('insure_yd_notice', 'IndexController@insYdNotice');//产品须知页面-英大
	//银行卡操作
	Route::any('bank_index', 'BankController@bankIndex');//银行卡列表页面
	Route::any('bank_info/{bank_id}', 'BankController@bankInfo');//银行卡详情页面
	Route::any('bank_bind', 'BankController@bankBind');//添加银行卡页面
	Route::any('do_bank_bind', 'BankController@doBankBind');//添加银行卡操作
	Route::any('bank_del', 'BankController@bankDel');//删除银行卡操作
	//银行卡免密设置
	Route::any('insure_authorize', 'BankController@bankAuthorize');//免密授权页面
	Route::any('bank_authorize_info', 'BankController@bankAuthorizeInfo');//免密授权详情页面
	Route::any('insure_authorize_info', 'BankController@insureAuthorizeInfo');//免密授权详情页面
	Route::any('do_insure_authorize', 'BankController@doBankAuthorize');//免密授权页面
	//保单管理
	Route::any('warranty_list', 'WarrantyController@warrantyList');//保单列表
	Route::any('warranty_detail/{warranty_id}', 'WarrantyController@warrantyDetail');//保单详情
	//投保设置
	Route::any('insure_setup_list', 'SetingController@insureSetupList');//设置列表页面
	Route::any('insure_seting', 'SetingController@insureSeting');//产品设置页面
	Route::any('insure_auto', 'SetingController@insureAuto');//自动投保页面
	Route::any('do_insure_auto', 'SetingController@doInsureAuto');//自动投保操作
	Route::any('user_info', 'SetingController@userInfo');//用户信息
	//申请理赔
	Route::any('claim_contact', 'ClaimController@claimContact');//申请理赔
	Route::any('claim_email', 'ClaimController@claimEmail');
	Route::any('claim_info', 'ClaimController@claimInfo');
	Route::any('claim_material_upload', 'ClaimController@claimMaterialUpload');
	Route::any('claim_progress', 'ClaimController@claimProgress');
	Route::any('claim_reason', 'ClaimController@claimReason');
	Route::any('claim_result', 'ClaimController@claimResult');
	Route::any('claim_type', 'ClaimController@claimType');
	Route::any('claim_user', 'ClaimController@claimUser');
	Route::any('claim_send_email', 'ClaimController@claimSendEmail');
	Route::any('claim_audit', 'ClaimController@claimAudit');
	Route::any('base_upload_file', 'ClaimController@baseUploadFile');
});



