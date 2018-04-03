<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>保单详情</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
	</head>

	<body id="process14">
				<header class="mui-bar mui-bar-nav">
					<div class="head-img">
						<img src="{{config('view_url.channel_views')}}imges/back.png" />
					</div>
					<div class="head-title">
						<span>我的保单</span>
					</div>
				</header>
				<div class="policy_details">
				<div class="mui-content" style="">
					<div class="mui-scroll-wrapper">
						<div class="mui-scroll">
							<div class="policy_wrapper ">
								<div class="item policy">
									<div class="title">
										<span class="info">英大非机动车驾驶员意外险</span>
										<img src="{{config('view_url.channel_views')}}imges/logo.png" />
									</div>
									<div class="line">
										<span>保单号</span>
										<span>{{$warranty_res['warranty_code']}}</span>
									</div>
									<div class="line">
										<span>状态</span>
										@if($warranty_res['status']=='7')
											<span class="special">保障中</span>
										@elseif($warranty_res['status']=='3')
											<span class="special">待支付</span>
										@elseif($warranty_res['status']=='10')
											<span class="special">已失效</span>
										@endif
									</div>
								</div>
								<div class="item">
									<div class="title">
										<span class="info">基本信息</span>
									</div>
									<div class="line">
										<span>保障时间</span>
										<span>{{$warranty_res['start_time']}}-{{$warranty_res['end_time']}}</span>
									</div>
									<div class="line">
										<span>份数</span>
										<span>1份</span>
									</div>
									<div class="line">
										<span>保费</span>
										<span>2元</span>
									</div>
								</div>
								<div class="item">
									<div class="title">
										<span class="info">保障权益</span>
									</div>
									<ul class="ul-list">
										<span class="tit">非机动车驾驶员意外险</span>
										<li class="ul-item">
											<span>非机动车驾驶员意外险</span>
											<span>20万</span>
										</li>
										<li class="ul-item">
											<span>附加意外伤害险</span>
											<span>1万</span>
										</li>
									</ul>
									<ul class="ul-list">
										<span class="tit">第三者责任险</span>
										<li class="ul-item">
											<span>第三方人身伤害（死亡、伤残、医疗）</span>
											<span>5万</span>
										</li>
										<li class="ul-item">
											<span>第三方财产损失</span>
											<span>1万</span>
										</li>
									</ul>
								</div>

								<div class="item">
									<div class="title">
										<span class="info">被保人信息</span>
									</div>
									<div class="line">
										<span>姓名</span>
										<span>{{$user_res['name']}}</span>
									</div>
									<div class="line">
										<span>证件类型</span>
										<span>身份证</span>
									</div>
									<div class="line">
										<span>证件号码</span>
										<span>{{$user_res['id_code']}}</span>
									</div>
									<div class="line">
										<span>出生日期</span>
										<span>{{strlen($user_res['id_code'])==15 ? ('19' . substr($user_res['id_code'], 6, 6)) : substr($user_res['id_code'], 6, 8)}}</span>
									</div>
									<div class="line">
										<span>性别</span>
										<span>{{substr($user_res['id_code'], (strlen($user_res['id_code'])==15 ? -2 : -1), 1) % 2 ? '女' : '男'}}</span>
									</div>
									<div class="line">
										<span>手机号码</span>
										<span>{{$user_res['phone']}}</span>
									</div>
								</div>
								<div class="item">
									<div class="title">
										<span class="info">投保人信息</span>
									</div>
									<div class="line">
										<span>姓名</span>
										<span>{{$user_res['name']}}</span>
									</div>
									<div class="line">
										<span>证件类型</span>
										<span>身份证</span>
									</div>
									<div class="line">
										<span>证件号码</span>
										<span>{{$user_res['id_code']}}</span>
									</div>
									<div class="line">
										<span>与被保人关系</span>
										<span>本人</span>
									</div>
									<div class="line">
										<span>手机号码</span>
										<span>{{$user_res['phone']}}</span>
									</div>
								</div>
								<div class="btn">
									<button id="claim_target">申请理赔</button>
								</div>
							</div>
						</div>
					</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script type="text/javascript" charset="utf-8">
            $('.head-right').on('tap',function () {
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('.head-img').on('tap',function(){
                Mask.loding();
                window.history.go(-1);
            });
            $('#claim_target').on('click',function () {
                Mask.loding();
                window.location.href = "{{config('view_url.channel_yunda_target_url')}}claim_index";
            });
		</script>
	</body>

</html>