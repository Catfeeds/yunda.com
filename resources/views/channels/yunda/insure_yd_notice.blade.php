<!DOCTYPE html>
<html>

	<head>
		<meta charset="utf-8">
		<title>快递保</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<style type="text/css">
			.header{
				height: 1.25rem;
				background-color: #fff;
				padding-left: .3rem;
				padding-top: .3rem;
			}
			.header-bottom{
				margin-top: .15rem;
			}
			.detil-name{
				color: #666;
				font-size: .28rem;
			}
			.detil-name-color{
				color: #333;
				font-size: .28rem;
				margin-left: .15rem;
			}
			.mui-scroll{
				padding: .2rem;
				font-size: 14px;
				text-align: justify;
			}
			p{
				line-height: 1.6;
			}
			.mui-scroll-wrapper{
				top: 1.5rem;
				bottom: 0!important;
			}
			.title{
				height: .6rem;
				line-height: .6rem;
				font-size: .28rem;
				text-align: center;
				background: #f7f7f7;
			}
			.default-list{
				line-height: 1.4;
			}
			.default-list li{
				margin-bottom: .2rem;
			}
		</style>
	</head>

	<body>
		<div id="offCanvasWrapper" class="mui-off-canvas-wrap mui-slide-in mui-draggable">
			<div class="mui-inner-wrap">
				<header class="mui-bar mui-bar-nav">
					<div class="head-img">
						<img src="{{config('view_url.channel_views')}}imges/back.png"/>
					</div>
					<div class="head-right">
						<i class="iconfont icon-close"></i>
					</div>
					<div class="head-title">
						<span>购买须知</span>
					</div>
				</header>
				
				<div class="mui-content">
					<div class="mui-scroll-wrapper">
						<div class="mui-scroll">
							<ul class="default-list">
							   <li>1、本保单起保时间为当天xxx(以保单生效时间为准)，止保时间为当天23:59:59。</li>
							   <li>2. 本保单仅承保被保险人驾驶公司指定的非机动车从货物分拣开始至送达或取件结束期间发生的意外损失,具体保障责任时间以上述两个时间段为准。</li>
							   <li>3.本产品被保险人年龄为18周岁至60周岁，不符合此年龄的保单自始无效。</li>
							   <li>4.本保单出险后被保险人需提供交警开具的《交通事故责任认定书》或公安部门出具的报/立案证明。</li>
							   <li>5、本保单第三者责任险每次事故免赔额为500元或损失金额的10%，两者以高者为准。</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
            $('.head-right').on('tap',function () {
                location.href = "bmapp:homepage";return false;
            });
            $('.head-img').on('tap',function(){
                history.back(-1);return false;
            });
		</script>
	</body>

</html>