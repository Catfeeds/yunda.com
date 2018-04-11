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
		<style>
			.tab{margin-bottom: .2rem;height: 1rem;line-height: 1rem;background: #fff;text-align: center;}
			.tab .tab-item{display: inline-block;width: 40%;}
			.tab .tab-item.active{color: #ff8a00;}
			.icon-jiantou{float: right;}
			.form-wrapper{margin-bottom: .2rem;}
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
				<span>理赔进度</span>
			</div>
		</header>
		<div>
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div>
						<div class="tab"><span class="tab-item active">进行中</span><span class="tab-item">已完结</span></div>
						<ul class="form-wrapper">
							<li style="font-weight: bold;">英大非机动车意外保险<i class="iconfont icon-jiantou"></i></li>
							<li>理赔状态<input style="color: #267cfc;" disabled type="text" value="资料上传"/></li>
							<li>申请时间<input disabled type="text" value="2018-03-21"/></li>
						</ul>
						<ul class="form-wrapper">
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
		<script>
            $('.head-right').on('tap',function () {
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('.head-img').on('tap',function(){
                Mask.loding();
                window.history.go(-1);
            });
            $('.form-wrapper').on('tap',function(){
                Mask.loding();
                window.location.href = "{{config('view_url.channel_yunda_target_url')}}claim_info";
            });
		</script>
	</body>

</html>