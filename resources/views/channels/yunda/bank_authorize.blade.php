<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>开通免密支付</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
		<style>
			body{background: url(imges/bg.png);background-size: cover;}
		</style>
	</head>

	<body id="process2">
		<div class="popups-wrapper popups-agree" style="display: block;">
			<div class="popups-bg"></div>
			<div class="popups">
				<div class="popups-title"><i class="iconfont icon-guanbi"></i></div>
				<div class="popups-content">
					<div class="top">
						<h2 class="title">开通免密支付协议</h2>
						<p style="margin-bottom: .16rem;">为了保障您上工的安全，需要您开通免密支付协议。开通免密协议后，可以实现每日自动投保以下保险产品：</p>
						<p class="tips">韵达给大家提供的福利： </p>
						<p class="tips">市场上都买不来第三者责任</p>
						<img style="margin-top: .1rem;" src="{{config('view_url.channel_views')}}imges/pop.png" alt="" />
					</div>
				</div>
				<div class="popups-footer">
					<div style="margin: .3rem 0;">
						<label><input id="agree" type="checkbox" />我已阅读并同意<a href="{{config('view_url.channel_yunda_target_url')}}insure_authorize_info" style="color: #00A2FF;" id="insure_authorize_info">《转账授权书》</a></label>
					</div>
					<button disabled id="confirm" type="button" class="btn btn-default">已阅读并开通</button>
				</div>
			</div>
		</div>
		
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
			var person_code = "{{$person_code}}";
			var $agree = $('#agree'),$confirm = $('#confirm'),$guanbi = $('.icon-guanbi');
			$agree.click(function(){
				var status = $(this).prop('checked')
				$confirm.prop('disabled',!status)
			});
			$guanbi.click(function(){
				history.go(-1);
			});
            $('#insure_authorize_info').on('tap',function(){
                Mask.loding();
            });
            $('#confirm').on('click',function(){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{config('view_url.channel_yunda_target_url')}}do_insure_authorize",
                    type: "post",
                    data: {'person_code':person_code},
                    dataType: "json",
                    success: function (data) {
                        Mask.alert(data.msg,3);
                        $('#confirm').attr("style","display:none;");
                        window.location.reload();
                    }
                });
            });
		</script>
	</body>

</html>