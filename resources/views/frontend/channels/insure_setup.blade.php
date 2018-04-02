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
		<div class="step5">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<ul class="list-wrapper">
						<li class="list-item">
							<a  href="{{config('view_url.channel_target_url')}}insure_seting" id="insure_seting_target">
								<div class="item-img"><i class="iconfont icon-kuaidiyuanshangmen"></i></div>
								<div class="item-content">
									<p class="title">快递保</p>
								</div>
								<i class="iconfont icon-jiantou"></i>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_url')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_url')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_url')}}js/common.js"></script>
		<script>
            $('.head-right').on('tap',function () {
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('.head-left').on('tap',function(){
                Mask.loding();
                window.location.go(-1);
            });
            $('#insure_seting_target').on('tap',function(){
                Mask.loding();
            });
		</script>
	</body>

</html>