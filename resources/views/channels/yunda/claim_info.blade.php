<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>理赔详情</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
		<style>
			body{background: #fff;}
			.list{padding: .2rem;line-height: 2;}
		</style>
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
				<span>理赔详情</span>
			</div>
		</header>
		<div>
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div>
						<ul class="form-wrapper">
							<li style="font-weight: bold;">英大非机动车意外保险</li>
							<li>理赔状态<input style="color: #267cfc;" disabled type="text" value="资料上传"/></li>
							<li>申请时间<input disabled type="text"  value="2018-03-21"/></li>
						</ul>
						<div class="division"></div>
						<ul class="form-wrapper">
							<li style="font-weight: bold;">当前进度</li>
						</ul>
						<div>
							<ul class="process-wrapper">
								<li class="active"><div class="icon"></div><div>出险人员</div></li>
								<li><div class="icon"></div><div>出险类型</div></li>
								<li><div class="icon"></div><div>出险信息</div></li>
								<li><div class="icon"></div><div>联系方式</div></li>
							</ul>
							<ul class="list">
								<li>2017-11-11 <span class="fr">申请完成</span></li>
								<li>2017-11-11 <span class="fr">申请完成</span></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
            $('.head-right').on('tap',function () {
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('.head-img').on('tap',function(){
                Mask.loding();
                window.history.go(-1);
            });
		</script>
	</body>
</html>

