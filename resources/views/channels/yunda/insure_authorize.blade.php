<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>免密支付</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
	</head>
	<body id="process2">
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
				<span>免密支付</span>
			</div>
		</header>
		<div class="step7">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div class="popups">
						<div class="popups-content">
							<div class="top">
								<p style="margin-bottom: .16rem;">上工才投保,每天2元钱</p>
								<h2 class="title">开通免密支付协议</h2>
							</div>
							<div class="policy_list_wrapper">
								<div class="tab" style="color: #adadad">
									<span class="item">银行卡</span>
								</div>
								<div class="tab">
									<span class="item">姓名</span>
									<input type="text" name="person_name" value="">
								</div>
								<div class="tab">
									<span class="item">手机号</span>
									<input type="text" name="person_phone" value="">
								</div>
								<div class="tab">
									<span class="item">卡号</span>
									<input type="text" name="bank_code" value="">
									<input type="hidden" name="person_code" value="">
								</div>
							</div>
						</div>
						<div class="popups-footer">
							<div class="label-wrapper">
								<label><input id="agree" checked="" type="checkbox">我已阅读并同意<a href="/webapi/insure_authorize_info?token=" style="color: #00A2FF;" id="insure_authorize_info">《转账授权书》</a></label>
							</div>
						</div>
					</div>
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
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
	</body>

</html>