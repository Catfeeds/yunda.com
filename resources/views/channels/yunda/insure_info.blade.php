<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>保障详情</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
		<style>
			.section{
				padding: 0;
				margin-bottom: .2rem;
				border-top: 1px solid #ddd;
			}
			.padding{
				padding: 0 .2rem;
			}
			.section .name{
				padding: 0 .2rem;
				height: 1rem;
				line-height: 1rem;
				font-size: .28rem;
				color: #303030;
				border-bottom: 1px solid #ddd;
			}
			.section-welfare{
				padding: .2rem;
				color: #303030;
				position: relative;
			}
			.section-welfare .title{
				font-size: .34rem;font-weight: bold;
			}
			.section-welfare .logo{
				position: absolute;
				top: 50%;
				right: .2rem;
				transform: translateY(-50%);
				width: auto;
				height: 1rem;
			}
			.section-rights ul{
				margin: 0 .2rem;
				padding: .2rem 0;
				border-bottom: 1px solid #ddd;
			}
			.section-rights ul:last-child{border-bottom: none;}
			.section-rights ul li:first-child{
				color: #303030;
			}
			.section-notice ul{
				padding: .2rem .2rem 0;
				line-height: 1.4;
			}
			.section-notice li{margin-bottom: .2rem;}
			.section-more{
				padding: .2rem;
				color: #303030;
			}
			.section-more a{color: #00A2FF;}
			.buttons-wrapper .btn-right{width: 100%;}
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
				<span>保险详情</span>
			</div>
		</header>
		<div class="step1">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div class="banner">
						<img src="{{config('view_url.channel_views')}}imges/banner_3.png" alt="" />
					</div>
					<div class="section section-welfare">
						<img class="logo" src="{{config('view_url.channel_views')}}imges/logo.png" alt="" />
						<p class="title">英大非机动车驾驶员意外险</p>
						<p style="color: #ff6c30;">韵达给大家提供的福利：</p>
						<p>市场上都买不来的第三者责任</p>
					</div>
					<div class="section section-rights">
						<h3 class="name">保障权益</h3>
						<ul>
							<li><span class="ellipsis">非机动车驾驶员意外险</span></li>
							<li><span class="ellipsis">非机动车驾驶员意外险</span><span class="fr">1万</span></li>
							<li><span class="ellipsis">附加意外伤害险</span><span class="fr">5万</span></li>
						</ul>
						<ul>
							<li><span class="ellipsis">第三者责任险</span></li>
							<li><span class="ellipsis">第三方人身伤害（死亡、伤残、医疗）</span><span class="fr">1万</span></li>
							<li><span class="ellipsis">第三方财产损失</span><span class="fr">5万</span></li>
						</ul>
					</div>
					
					<div class="section section-notice">
						<h3 class="name">购买须知</h3>
						<ul>
							<li>1、本保单起保时间为当天xxx(以保单生效时间为准)，止保时间为当天23:59:59。</li>
							<li>2. 本保单仅承保被保险人驾驶公司指定的非机动车从货物分拣开始至送达或取件结束期间发生的意外损失,具体保障责任时间以上述两个时间段为准。</li>
							<li>3.本产品被保险人年龄为18周岁至60周岁，不符合此年龄的保单自始无效。</li>
							<li>4.本保单出险后被保险人需提供交警开具的《交通事故责任认定书》或公安部门出具的报/立案证明。</li>
							<li>5、本保单第三者责任险每次事故免赔额为500元或损失金额的10%，两者以高者为准。</li>
						</ul>
					</div>
					<div class="section section-more">
						<p>更多详情请查看</p >
						<p>泰康在线财险相关资料：</p >
						<p><a href="">《保险条款》</a><a href="">《投保须知》</a></p >
					<p>泰康在线财险相关资料：</p >
					<p><a href="">《保险条款》</a><a href="">《投保须知》</a></p >
					</div>
					{{--<div class="section section-more">--}}
						{{--<p>更多详情，请查看--}}
							{{--<a href="{{config('view_url.channel_yunda_target_url')}}ins_clause" id="ins_clause">《保险条款》</a>--}}
							{{--<a href="{{config('view_url.channel_views')}}">《投保须知》</a>--}}
							{{--<a href="{{config('view_url.channel_views')}}">《投保责任书》</a>--}}
							{{--的全部内容。--}}
						{{--</p>--}}
					{{--</div>--}}
				</div>
			</div>
			<div class="buttons-wrapper">
				<button type="button" class="btn btn-right" id="do_insured">立即投保</button>
			</div>
		</div>
		
		<!--投保失败弹出层-->
		<div class="popups-wrapper popups-msg">
			<div class="popups-bg"></div>
			<div class="popups popups-tips">
				<div class="popups-title"><i class="iconfont icon-guanbi"></i></div>
				<div class="popups-content color-wraning">
					<i class="iconfont icon-error"></i>
					<p class="tips">投保失败</p>
				</div>
			</div>
		</div>
		
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
			$('#do_insured').on('click',function () {
			    var person_code = "{{$person_code}}";
                Mask.loding();
				window.location.href = "{{config('view_url.channel_yunda_target_url')}}do_insured/"+person_code;
            });
            $('.head-right').on('tap',function () {
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('.head-left').on('tap',function(){
                Mask.loding();
                window.history.go(-1);
            });
            $('#ins_clause').on('tap',function(){
                Mask.loding();
            });
		</script>
	</body>

</html>