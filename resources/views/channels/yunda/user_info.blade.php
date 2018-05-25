<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>客户信息</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
	</head>

	<body>
		<header class="mui-bar mui-bar-nav">
			<div class="head-left">
				<div class="head-img">
					<img src="{{config('view_url.channel_views')}}imges/back.png">
				</div>
			</div>
			<div class="head-right">
				<i class="iconfont icon-close"></i>
			</div>
			<div class="head-title">
				<span>快递保</span>
			</div>
		</header>
		<div class="step6">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<ul class="list-wrapper">
							<li class="list-item"><span class="name">姓名:</span><span>{{$user_res['name']}}</span></li>
							<li class="list-item"><span class="name">手机号:</span><span>{{$user_res['phone']}}</span></li>
							<li class="list-item"><span class="name">身份证号:</span><span>{{$user_res['papers_code']}}</span></li>
					</ul>
				</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
            $('.head-right').on('tap',function () {
                location.href = "bmapp:homepage";return false;
            });
            $('.head-left').on('tap',function(){
                window.history.go(-1);return false;
            });
		</script>
	</body>
</html>