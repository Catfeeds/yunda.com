<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>设置列表</title>
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
				<span>快递保·意外险</span>
			</div>
		</header>
		<div class="step4">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<ul class="list-wrapper">
						{{--<li class="list-item">--}}
							{{--<a  href="{{config('view_url.channel_yunda_target_url')}}user_info?token={{$_GET['token']}}" id="user_setup_target">--}}
								{{--<div class="item-img"><i class="iconfont icon-geren"></i></div>--}}
								{{--<div class="item-content">--}}
									{{--<p class="title">个人信息</p>--}}
								{{--</div>--}}
								{{--<i class="iconfont icon-jiantou"></i>--}}
							{{--</a>--}}
						{{--</li>--}}
						<li class="list-item">
							<a  href="{{config('view_url.channel_yunda_target_url')}}insure_seting?token={{$_GET['token']}}" id="insure_setup_target">
								<div class="item-img"><i class="iconfont icon-baodanyangben"></i></div>
								<div class="item-content">
									<p class="title">自动投保</p>
								</div>
								<i class="iconfont icon-jiantou"></i>
							</a>
						</li>
						<li class="list-item">
							<a  href="{{config('view_url.channel_yunda_target_url')}}bank_index?token={{$_GET['token']}}" id="bank_setup_target">
								<div class="item-img">	<img src="{{config('view_url.channel_views')}}imges/icon_set.png" alt="" /></div>

								<div class="item-content">
									<p class="title">银行卡设置</p>
								</div>
								<i class="iconfont icon-jiantou"></i>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
            $('.head-right').on('tap',function(){
                Mask.loding();
                location.href = "bmapp:homepage";return false;
            });
            $('.head-left').on('tap',function(){
                Mask.loding();
                window.history.go(-1);return false;
            });
            $('#user_setup_target').on('tap',function(){
                Mask.loding();
            });
            $('#insure_setup_target').on('tap',function(){
                Mask.loding();
            });
            $('#bank_setup_target').on('tap',function(){
                Mask.loding();
            });
		</script>
	</body>
</html>