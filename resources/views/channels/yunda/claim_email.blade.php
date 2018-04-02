<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>理赔审核</title>
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/email.css" />
		<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
	</head>
	<body>
		<div class="header">
			<div class="content">
				<img class="logo" src="{{config('view_url.channel_views')}}imges/yunda.png"/><h1 class="step">理赔审核</h1>
			</div>
		</div>
		<div class="content">
			<div class="section">
				<h2  class="title">英大非机动车驾驶员意外险</h2>
				<ul class="info">
					<li><span class="name">保单号</span><span>1212121212121212ddddssss</span></li>
					<li><span class="name">保障时间</span><span>2018-04-24 9:00-208-04-25 23:59:59</span></li>
					<li><span class="name">保费</span><span>2元</span></li>
				</ul>
			</div>
			<div class="section">
				<h2 class="title-level2">出险人基本信息</h2>
				<ul class="info">
					<li><span class="name">姓名</span><span>1212121212121212ddddssss</span></li>
					<li><span class="name">证件类型</span><span>2018-04-24 9:00-208-04-25 23:59:59</span></li>
					<li><span class="name">证件号</span><span>2元</span></li>
					<li><span class="name">联系人</span><span>2元</span></li>
					<li><span class="name">手机号</span><span>2元</span></li>
					<li><span class="name">开户行</span><span>2元</span></li>
					<li><span class="name">开户地</span><span>2元</span></li>
					<li><span class="name">银行账户</span><span>2元</span></li>
				</ul>
			</div>
			<div class="section">
				<h2 class="title-level2">出险信息</h2>
				<ul class="info">
					<li><span class="name">事故性质</span><span>1212121212121212ddddssss</span></li>
					<li><span class="name">出险类型</span><span>2018-04-24 9:00-208-04-25 23:59:59</span></li>
					<li><span class="name">出险时间</span><span>2元</span></li>
					<li><span class="name">出险地点</span><span>2元</span></li>
					<li><span class="name">手机号</span><span>2元</span></li>
					<li><span class="name">经过描述</span><span>2元</span></li>
				</ul>
			</div>
			<div class="section" style="padding-bottom: 0;">
				<h2 class="title-level2">资料</h2>
				<ul class="img-wrapper">
					<li>
						<div class="img" style="background-image: url(imges/banner.png);"></div>
						<p>病历、诊断证明、出院记录等 医疗资料</p>
					</li>
				</ul>
			</div>
			<div class="section" style="padding: 10px;">
				<textarea class="remark" placeholder="备注信息(选填)"></textarea>
			</div>
			<div class="btn-wrapper">
				<button class="btn btn-warning">未通过审核</button>
				<button class="btn btn-primary">通过审核</button>
			</div>
		</div>
	</body>
</html>
