<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>理赔审核</title>
    <link rel="stylesheet" href="{{config('view_url.channel_views')}}css/email.css" />
    <link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css" />
    <style type="text/css">
        .title{line-height: 1.4;}
        .result{padding: 20px;text-align: center;font-size: 30px;color: #00A2FF;}
        .result .iconfont{margin-right: 10px; font-size: 28px;vertical-align: middle;}
        .result span{font-weight: bold;}
    </style>
</head>
<body>
<div class="header">
    <div class="content">
        <img class="logo" src="{{config('view_url.channel_views')}}imges/yunda.png"/><h1 class="step">理赔审核</h1>
    </div>
</div>
<div class="content">
    <div class="section" style="padding: 10px;">
        <h2  class="title">英大非机动车驾驶员意外险</h2>
    </div>
</div>
<div class="content">
    <div class="section">
        <div class="result">
            <i class="iconfont icon-chenggong"></i><span>审核完成</span>
        </div>
        <ul class="info">
            <li><span class="name">产品名</span><span>{{$result->product_name}}</span></li>
            <li><span class="name">保单号</span><span>{{$result->warranty_code}}</span></li>
            <li><span class="name">保障时间</span><span>{{date('Y-m-d H:i:s', $result->start_time / 1000)}} &nbsp;~&nbsp; {{date('Y-m-d H:i:s', $result->end_time / 1000)}}</span></li>
            <li><span class="name">保费</span><span>{{$result->premium}}元</span></li>
            <li><span class="name">审核结果</span><span>{{$status['claim_status'][$result->claim_status]}}</span></li>
            <li><span class="name">备注信息</span><span>{{$result->remark}}</span></li>
        </ul>
    </div>
</div>
</body>
</html>
