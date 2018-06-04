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
    'request_url'=>'http://127.0.0.1:8080/proposal/warranty/insure',
    'test_person_code'=>'620103199012171917',
	'server_host'=>'https://api-yunda.inschos.com',
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
    'file_url'=>'http://122.14.202.233:9100/api_file/',
    //产品ID对应邮箱地址
    'product_id_email'=>[
        '1'=>'ydgfkfb@163.com',
        '2'=>'taomy@inschos.com',
    ],
];