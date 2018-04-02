<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>申请理赔</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
		<style>
			.btn-next{display: block;margin: .4rem auto;width: 90%;color: #744c22;background: #f6d85f;}
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
				<span>申请理赔</span>
			</div>
		</header>
		<div>
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div>
						<ul class="process-wrapper">
							<li class="active"><div class="icon"></div><div>出险人员</div></li>
							<li><div class="icon"></div><div>出险类型</div></li>
							<li><div class="icon"></div><div>出险信息</div></li>
							<li><div class="icon"></div><div>联系方式</div></li>
						</ul>
						<ul class="form-wrapper">
							<li style="font-weight: bold;">出险人员</li>
							<li>姓名<input type="text" value="" placeholder="请输入"/></li>
							<li>证件号码<input type="text"  value="" placeholder="请输入"/></li>
							<li>当前住址<input type="text"  value="" placeholder="请输入"/></li>
						</ul>
						<button id="next" disabled class="btn btn-next">下一步</button>
					</div>
				</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
			var $inputs = $('input'),$next = $('#next');
			$inputs.bind('input propertychange', function() {  
			  $inputs.each(function(index){
			  	if(!$(this).val()){
			  		$next.prop('disabled',true)
			  		return false
			  	}
			  	if(index == $inputs.length-1){
			  		$next.prop('disabled',false)
			  	}
			  })
			});
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