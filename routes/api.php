<?php

//use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix' => '/test', 'namespace'=>'FrontendControllers'],function (){
//    Route::get('test', 'TestController@index'); //唯家测试 DSB
    Route::get('quote', 'WkTestController@quote');    //算费
    Route::get('buy_ins', 'WkTestController@buyIns');   //投保
    Route::get('check_ins', 'WkTestController@checkIns');   //核保
    Route::get('pay_ins', 'WkTestController@payIns');   //支付
    Route::get('issue', 'WkTestController@issue');   //出单
});

Route::group(['prefix'=>'sms','namespace'=>'FrontendControllers'],function() {
    Route::post('email','SmsController@sendEmail'); //发送邮件
});

//车险测试路由 todo delete
Route::group(['prefix'=>'car_ins'],function(){
    Route::get('car_info', 'TestCarInsController@carInfo');    //车辆信息
    Route::get('clause_info', 'TestCarInsController@clauseInfo');    //条款信息
    Route::get('provinces', 'TestCarInsController@provinces');    //身份查询
    Route::get('cities', 'TestCarInsController@cities');    //城市查询
    Route::get('next_ins_time', 'TestCarInsController@nextInsTime');    //下次投保日期
    Route::get('quote', 'TestCarInsController@quote');    //算费
    Route::get('buy_ins', 'TestCarInsController@buyIns');    //投保
    Route::get('buy_ins', 'TestCarInsController@buyIns');    //投保
    Route::get('insurers', 'TestCarInsController@insurers');    //投保
});
//TODO  测试前后端分离
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
    $api->group(['namespace' => 'App\Api\Controllers','middleware' => ['account.change']], function ($api) {
        $api->post('user/login', 'AuthController@authenticate');  //登录授权
        $api->post('user/register', 'AuthController@register');
        $api->group(['middleware' => 'jwt.auth'], function ($api) {
            $api->post('tests', 'TestsController@index');
            //路径为 /api/tests
            //get post 请求 都可以
            //header头中加入 Authorization Bearer your_token  测试成功

            //请求方式：
            //http://localhost:8000/api/tests?token=xxxxxx  (从登陆或注册那里获取,目前只能用get)
            $api->get('tests/{id}', 'TestsController@show');
            $api->get('user/me', 'AuthController@AuthenticatedUser'); //根据
            $api->get('refresh', 'AuthController@refreshToken'); //刷新token
        });
    });
});
//TODO 2018-03-28韵达快递保  新流程
Route::group(['prefix' => 'channels', 'namespace'=>'ChannelsApiControllers'],function (){
    Route::group(['prefix' => 'yunda', 'namespace'=>'Yunda'],function () {
        //投保流程
          Route::any('ins_info', 'IndexController@insInfo');//投保详情页面
          Route::any('do_insured/{person_code}', 'IndexController@doInsured');//投保操作
          Route::any('ins_clause', 'IndexController@insClause');//产品条款页面
          Route::any('ins_error/{error_type}', 'IndexController@insError');//错误提示页面
        //银行卡操作
        Route::any('bank_index', 'BankController@bankIndex');//银行卡列表页面
        Route::any('bank_info/{bank_id}', 'BankController@bankInfo');//银行卡详情页面
        Route::any('bank_bind', 'BankController@bankBind');//添加银行卡页面
        Route::any('do_bank_bind', 'BankController@doBankBind');//添加银行卡操作
        Route::any('bank_del', 'BankController@bankDel');//删除银行卡操作
        //银行卡免密设置
        Route::any('insure_authorize', 'BankController@bankAuthorize');//免密授权页面
        Route::any('insure_authorize_info', 'BankController@bankAuthorizeInfo');//免密授权详情页面
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
    });
});


