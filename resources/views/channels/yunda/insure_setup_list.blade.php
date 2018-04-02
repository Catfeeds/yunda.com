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
		<div class="step4">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<ul class="list-wrapper">
						<li class="list-item">
							<a  href="{{config('view_url.channel_yunda_target_url')}}user_info" id="user_setup_target">
								<div class="item-img"><i class="iconfont icon-geren"></i></div>
								<div class="item-content">
									<p class="title">个人信息</p>
								</div>
								<i class="iconfont icon-jiantou"></i>
							</a>
						</li>
						<li class="list-item">
							<a  href="{{config('view_url.channel_yunda_target_url')}}insure_seting" id="insure_setup_target">
								<div class="item-img"><i class="iconfont icon-baodanyangben"></i></div>
								<div class="item-content">
									<p class="title">自动投保</p>
								</div>
								<i class="iconfont icon-jiantou"></i>
							</a>
						</li>
						<li class="list-item">
							<a  href="{{config('view_url.channel_yunda_target_url')}}bank_index" id="bank_setup_target">
								<div class="item-img">	<img src="{{config('view_url.channel_url')}}imges/icon_set.png" alt="" /></div>

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
		<!--投保成功弹出层-->
		<div class="popups-wrapper popups-msg">
			<div class="popups-bg"></div>
			<div class="popups popups-tips">
				<div class="popups-title"><i class="iconfont icon-guanbi"></i></div>
				<div class="popups-content color-positive">
					<i class="iconfont icon-chenggong"></i>
					<p class="tips">投保成功</p>
				</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_url')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_url')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_url')}}js/common.js"></script>
		<script>
            $('.head-right').on('tap',function(){
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('.head-left').on('tap',function(){
                Mask.loding();
                window.history.go(-1);
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