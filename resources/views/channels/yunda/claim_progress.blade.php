<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>理赔进度</title>
	<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
	<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
</head>
<body id="process13">
<header class="mui-bar mui-bar-nav">
	<div class="head-img">
		<img src="{{config('view_url.channel_views')}}imges/back.png" />
	</div>
	<div class="head-title">
		<span>理赔列表</span>
	</div>
</header>
<div class="mui-content" style="">
	<div class="mui-scroll-wrapper">
		<div class="mui-scroll">
			<div class="policy_list_wrapper">
				<ul class="tab">
					<li class="item @if(isset($_GET['status'])&&$_GET['status']=='3') active @endif" data-id="3">待理赔</li>
					<li class="item @if(!isset($_GET['status'])||isset($_GET['status'])&&$_GET['status']=='7') active @endif" data-id="7">理赔中</li>
					<li class="item @if(isset($_GET['status'])&&$_GET['status']=='10') active @endif" data-id="10">理赔结束</li>
				</ul>
				<ul class="form-wrapper" data-id="1">
					<li style="font-weight: bold;">英大非机动车意外保险<i class="iconfont icon-jiantou"></i></li>
					<li>理赔状态<input style="color: #267cfc;" disabled type="text" value="资料上传"/></li>
					<li>申请时间<input disabled type="text" value="2018-03-21"/></li>
				</ul>
				<ul class="form-wrapper" data-id="2">
					<li style="font-weight: bold;">英大非机动车意外保险<i class="iconfont icon-jiantou"></i></li>
					<li>理赔状态<input style="color: #267cfc;" disabled type="text" value="资料上传"/></li>
					<li>申请时间<input disabled type="text" value="2018-03-21"/></li>
				</ul>
			</div>
		</div>
	</div>
</div>
<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
<script src="{{config('view_url.channel_views')}}js/common.js"></script>
<script type="text/javascript" charset="utf-8">
    $('.head-right').on('tap',function () {
        Mask.loding();
        location.href="bmapp:homepage";
    });
    $('.head-img').on('tap',function(){
        Mask.loding();
        window.history.go(-1);
    });
    $('.tab .item').click(function(){
        var status = $(this).data('id');
        $(this).addClass('active').siblings().removeClass('active')
        location.href = '{{config('view_url.channel_yunda_target_url')}}claim_progress?status='+status;

    });
    $('.form-wrapper').on('tap',function(){
        Mask.loding();
        window.location.href = "{{config('view_url.channel_yunda_target_url')}}claim_info";
    });
</script>
</body>
</html>
