<?php
/**
 *
 * Author: mingyang <7789246@qq.com>
 * Date: 2018-04-04
 */

return [
    'claim_status'=>[
        '-1'=>'审核驳回',
        '1'=>'申请理赔',
        '2'=>'提交资料',
        '3'=>'等待审核',
        '4'=>'审核通过'
    ],
    'claim_info_status'=>[
        '-1'=>'审核驳回',
        '0'=>'未审核',
        '1'=>'审核通过',
    ],

		'request_url'=>'http://10.10.10.18:8080/proposal/warranty/insure',
		'bank_verify_url'=>'http://10.10.10.18:8080/trading/proposal/auth/apply',
		'check_bank_verify_url'=>'http://10.10.10.18:8080/trading/proposal/auth/confirm',
		'test_request_url'=>'https://api-yunda.inschos.com/webapi/joint_login?bank_code=6217001210078544622 &bank_address=11&channel_code=YD&insured_province=320000&insured_county=320982&insured_code=342225199504065369&insured_phone=15856218334&insured_city=320900&insured_name=曹桥桥&insured_address=11&bank_phone=111&bank_name=11&insured_email=111&channel_order_code=1111',
    'test_person_code'=>'620103199012171917',
	'server_host'=>'https://api-yunda.inschos.com',
//	'server_host'=>'http://yunda.com',
//	'server_host'=>'',
    'webapi_route'=>'/webapi/',
    'prepare_route'=>'/channelsapi/',
    'joint_status'=>[
        'yes'=>'01',
        'no'=>'02',
    ],
    'authorize_status'=>[
        'no'=>'01',
        'yes'=>'02',
    ],
    //邮件路径
    'email_url'=>'/webapi',
//    'file_url'=>'http://122.14.202.233:9100/api_file/',
    'file_url'=>'/api/file/web/',
    //产品ID对应邮箱地址
    'product_id_email'=>[
        '1'=>'ydgfkfb@163.com',
        '2'=>'taomy@inschos.com',
    ],
];