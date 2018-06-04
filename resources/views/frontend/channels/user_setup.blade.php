<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>保障详情</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_url')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_url')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_url')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_url')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_url')}}css/step.css" />
		<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
	</head>

	<body>
		<header class="mui-bar mui-bar-nav">
			<div class="head-left">
				<div class="head-img">
					<img src="{{config('view_url.channel_url')}}imges/back.png">
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
						@if(isset($user_res)&&!empty($user_res))
							<li class="list-item"><span class="name">姓名:</span><span>{{$user_res['channel_user_name']}}</span></li>
							<li class="list-item"><span class="name">手机号:</span><span>{{$user_res['channel_user_phone']??$access_token_data['person_phone']}}</span></li>
							<li class="list-item"><span class="name">身份证号:</span><span>{{$user_res['channel_user_code']??$access_token_data['person_code']}}</span></li>
						@elseif(isset($access_token_data)&&!empty($access_token_data))
							<li class="list-item"><span class="name">手机号:</span><span>{{$user_res['channel_user_phone']??$access_token_data['person_phone']}}</span></li>
							<li class="list-item"><span class="name">身份证号:</span><span>{{$user_res['channel_user_code']??$access_token_data['person_code']}}</span></li>
						@else
							<li class="list-item"><span class="name">姓名:</span><span>张益达</span></li>
							<li class="list-item"><span class="name">手机号:</span><span>17600441991</span></li>
							<li class="list-item"><span class="name">身份证号:</span><span>120154198705242687</span></li>
						@endif
					</ul>
				</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_url')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_url')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_url')}}js/common.js"></script>
		<script>
            $('.head-right').on('tap',function () {

                location.href="bmapp:homepage";
            });
            $('.head-left').on('tap',function(){

                window.location.go(-1);
            });
		</script>
	</body>
</html>