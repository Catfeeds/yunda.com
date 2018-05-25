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
	</head>

	<body id="process7">
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
		<div class="mui-scroll-wrapper">
			<div class="mui-scroll">
				<div class="section header">
					<p><i class="iconfont icon-chenggong"></i></p>
					@if($email)
						<p>等待审核</p>
						@else
						<p>申请成功</p>
					@endif
				</div>
				<div class="section">
					<h2 class="title">理赔流程指引</h2>
					<div class="content">
						<ul class="list-wrapper">
							<li>（1）根据理赔资料清单准备资料</li>
							<li>（2）递交资料</li>
							<li>（3）保险公司审核 </li>
							<li>（4）支付理赔款</li>
						</ul>
					</div>
				</div>
				<div class="section">
					<h2 class="title">理赔资料清单</h2>
					<div class="content">
						<h3 class="name">（一）普通案件保险金申请</h3>
						<ul class="list-wrapper">
							<li>1.理赔申请书</li>
							<li>2.病历、诊断证明、出院记录等医疗资料</li>
							<li>3.医疗发票</li>
							<li>4.费用清单（凭发票在医院打印）</li>
							<li>5.伤者身份证复印件</li>
							<li>6.划款户名、帐号、开户行信息</li>
							<li>7.交通事故责任认定书或公安部门出具的报/立案证明</li>
							<li>8.涉及与双方交通事故的三者方的财产损失证明材料、医疗资料及双方赔偿协议</li>
							<li></li>
						</ul>
					</div>
					<div class="content">
						<h3 class="name">（二）残疾保 险金申请</h3>
						<ul class="list-wrapper">
							<li>1.同样提交普通案件的索赔资料</li>
							<li>2.二级以上（含二级）或保险人认可的医疗机构或司法鉴定机构出具的伤残鉴定报告</li>
						</ul>
					</div>
					<div class="content">
						<h3 class="name">（三）身故保险金申请</h3>
						<ul class="list-wrapper">
							<li>1. 同样提交普通案件的索赔资料</li>
							<li>2.公安部门或医疗机构出具的被保险人死亡证明书，火化证，户籍注销证明复印件</li>
							<li>3.受益人资料：包括所有法定受益人与被保险人的亲属关系证明（身份证、户口本、结婚证等），赔款分配协议或所有受益人委托某一受益人领取赔款的协议或公证书</li>
						</ul>
					</div>
				</div>
				<div class="btn-wrapper">
					<button class="btn" id="home">返回首页</button>
					@if($email != true)
						<a href="{{config('view_url.channel_yunda_target_url')}}claim_material_upload?claim_id={{$claim_id}}&token={{$_GET['token']}}" class="btn">提交资料</a>
					@endif
				</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
            var token = localStorage.getItem('token');
            $('.head-right').on('tap',function () {
                location.href = "bmapp:homepage";return false;
            });
            $('.head-img').on('tap',function(){
                window.history.go(-1);return false;
            });

            $('#home').click(function(){
                location.href = '{{config('view_url.channel_yunda_target_url')}}ins_center?token='+token;
            });
		</script>
	</body>

</html>