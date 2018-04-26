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
						<img src="{{config('view_url.channel_views')}}imges/banner_text.png" alt="" />
					</div>
					<div class="section section-welfare">
						<p class="title">快递保·意外险</p>
						<p style="color: #ff6c30;">韵达给大家提供的福利：<span style="color: #000000;">市场上都买不来的第三者责任</span></p>
					</div>
					<div class="section section-rights">
						<h3 class="name">保障权益</h3>
						<ul>
							<li><span class="ellipsis">非机动车驾驶员意外险</span></li>
							<li><span class="ellipsis">意外伤残、身故</span><span class="fr">20万</span></li>
							<li><span class="ellipsis">附加意外伤害险</span><span class="fr">1万</span></li>
						</ul>
						<ul>
							<li><span class="ellipsis">第三者责任险</span></li>
							<li><span class="ellipsis">第三方人身伤害（死亡、伤残、医疗）</span><span class="fr">5万</span></li>
							<li><span class="ellipsis">第三方财产损失</span><span class="fr">1万</span></li>
						</ul>
					</div>
					
					<div class="section section-notice">
						<div class="section section-more">
							<p><b>承保公司为"泰康在线财产保险"和"英大泰和财产保险"，了解更多保险详情请查看</b></p >
							<p>泰康在线财险相关资料：</p >
							<p>
								<a href="{{config('view_url.channel_yunda_target_url')}}insure_yd_clause" class="ins_clause">《保险条款》</a>
								<a href="{{config('view_url.channel_yunda_target_url')}}insure_yd_notice" class="ins_clause">《投保须知》</a>
							</p >
						<p>泰康在线财险相关资料：</p >
						<p>
							<a href="{{config('view_url.channel_yunda_target_url')}}insure_tk_clause" class="ins_clause">《保险条款》</a>
							<a href="{{config('view_url.channel_yunda_target_url')}}insure_tk_notice" class="ins_clause">《投保须知》</a>
						</p >
						</div>
				</div>
			</div>
		</div>
			<div class="buttons-wrapper">
				<button type="button" class="btn btn-right" id="do_insured">立即投保</button>
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
            $('.ins_clause').on('tap',function(){
                Mask.loding();
            });
		</script>
	</body>
</html>