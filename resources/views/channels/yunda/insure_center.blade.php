<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>我的保险</title>
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
					<a href="bmapp:homepage"><img src="{{config('view_url.channel_url')}}imges/back.png"></a>
				</div>
			</div>
			<div class="head-right">
				<a href="bmapp:homepage"><i class="iconfont icon-close"></i></a>
			</div>
			<div class="head-title">
				<span>快递保</span>
			</div>
		</header>
		<div class="step2">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<a style="display: block;" href="{{config('view_url.channel_yunda_target_url')}}ins_info" id="insure_target">
						<div class="banner">
							<img src="{{config('view_url.channel_views')}}imges/banner_3.png" alt="" />
						</div>
					</a>
					@if(!$auto_insure_status)
					<!--自动购保功能关闭时渲染-->
						<a href="{{config('view_url.channel_yunda_target_url')}}insure_seting" id="insure_set_target" class="status-wrapper">自动购保功能关闭中，去开启   >></a>
					@else
							@if(!$insured_status)
								<a href="{{config('view_url.channel_yunda_target_url')}}ins_info" id="insure_no_target" class="status-wrapper">保障未生效，点击查看详情  >></a>
							@endif
					@endif
					<ul class="list-wrapper">
						<li class="list-item">
							<a href="{{config('view_url.channel_yunda_target_url')}}warranty_list" id="warranty_target">
								<div class="item-img"><img src="{{config('view_url.channel_url')}}imges/-warranty.png" alt="" /></div>
								<div class="item-content">
									<p class="title">我的保单</p>
									<p class="text"><span>保单列表</span><span>查看保障</span></p>
								</div>
								<i class="iconfont icon-jiantou"></i>
							</a>
						</li>
						<li class="list-item">
							<a   href="{{config('view_url.channel_yunda_target_url')}}claim_index" id="claim_target">
								<div class="item-img"><img src="{{config('view_url.channel_url')}}imges/icon_lp.png" alt="" /></div>
								<div class="item-content">
									<p class="title">我要理赔</p>
									<p class="text"><span>人身保险</span><span>财产保险</span></p>
								</div>
								<i class="iconfont icon-jiantou"></i>
							</a>
						</li>
						<li class="list-item">
							<a  href="{{config('view_url.channel_yunda_target_url')}}insure_setup_list" id="seting_target">
								<div class="item-img"><img src="{{config('view_url.channel_url')}}imges/icon_set.png" alt="" /></div>
								<div class="item-content">
									<p class="title">设置</p>
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
            Mask.loding();
            window.onload = function(){
                $('.loading-wrapper').remove();
            };
            $('.head-right').on('tap',function () {
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('.head-left').on('tap',function(){
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('#claim_target').on('tap',function(){
                Mask.loding();
            });
            $('#warranty_target').on('tap',function(){
                Mask.loding();
            });
			$('#seting_target').on('tap',function(){
				Mask.loding();
			});
            $('#insure_target').on('tap',function(){
                Mask.loding();
            });
            $('#insure_no_target').on('tap',function(){
                Mask.loding();
            });
            $('#insure_set_target').on('tap',function(){
                Mask.loding();
            });
		</script>
	</body>
</html>