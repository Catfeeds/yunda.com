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
        '1'=>'审核通过'
    ],
    'request_url'=>'http://59.110.136.249:9200/proposal/warranty/insure',
    'test_person_code'=>'620103199012171917',
    'webapi_route'=>'/yunda/webapi/',
    'joint_status'=>[
        'yes'=>'01',
        'no'=>'02',
    ],
    'authorize_status'=>[
        'no'=>'01',
        'yes'=>'02',
    ],
    //邮件路径
    'email_url'=>'api/channels/yunda',
    //产品ID对应邮箱地址
    'product_id_email'=>[
        '1'=>'ydgfkfb@163.com',
        '2'=>'ydgfkfb@163.com',
    ],

];