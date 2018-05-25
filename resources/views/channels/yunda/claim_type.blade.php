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
		<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
		<style>
			.btn-next{display: block;margin: .4rem auto;width: 90%;color: #744c22;background: #f6d85f;}
			.icon-check{float: right;margin-top: .24rem;width: .5rem;height: .5rem;background: url({{config('view_url.channel_views')}}imges/no_checked.png) no-repeat;background-size: 100% 100%;}
			.icon-check.active{background: url({{config('view_url.channel_views')}}imges/checked_on.png) no-repeat;background-size: 100% 100%;}
		</style>
	</head>

	<body>
		<header class="mui-bar mui-bar-nav">
			<div class="head-left">
				<div class="head-img">
					<i class="iconfont icon-fanhui"></i>
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
				<form action="{{config('view_url.channel_yunda_target_url')}}claim_reason?token={{$_GET['token']}}" method="post" id="claim_reason">
					<input type="hidden" name="input" value="{{json_encode($input)}}">
					<div class="mui-scroll">
						<div>
							<ul class="process-wrapper">
								<li class="active"><div class="icon"></div><div>出险人员</div></li>
								<li class="active"><div class="icon"></div><div>出险类型</div></li>
								<li><div class="icon"></div><div>出险信息</div></li>
								<li><div class="icon"></div><div>联系方式</div></li>
							</ul>
							<ul class="form-wrapper">
								<li style="font-weight: bold;">出险类型</li>
								<li><label>普通案件保险金申请<i class="icon-check"></i><input hidden name="type[]" type="checkbox" value="1"/></label></li>
								<li><label>残疾保险金申请<i class="icon-check"></i><input hidden name="type[]" type="checkbox"  value="2"/></label></li>
								<li><label>身故保险金申请<i class="icon-check"></i><input hidden name="type[]" type="checkbox"  value="3"/></label></li>
							</ul>
							<button id="next" disabled class="btn btn-next">下一步</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
			var $next = $('#next');
			$('.form-wrapper input').click(function(){
				var status = $(this).prop('checked')
				$(this).prev().toggleClass('active')
				if($('.icon-check.active').length){
					$next.prop('disabled',false)
				}else{
					$next.prop('disabled',true)
				}
			})
            $('.head-right').on('tap',function () {
                location.href = "bmapp:homepage";return false;
            });
            $('.head-img').on('tap',function(){
                history.back(-1);return false;
            });
		</script>
	</body>

</html>