<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>购买结果</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
	</head>

	<body id="process1">
		<header class="mui-bar mui-bar-nav">
			<div class="head-left">
				<div class="head-img">
					<a href="bmapp:homepage"><i class="iconfont icon-fanhui"></i></a>
				</div>
			</div>
			<div class="head-right">
				<a href="bmapp:homepage"><i class="iconfont icon-close"></i></a>
			</div>
			<div class="head-title">
				<span>购买结果</span>
			</div>
		</header>
		<div class="process1">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div class="info">
						<div class="header">
							<h1 class="title">快递保·意外险</h1>
						</div>
						<ul class="list">
							{{--<li>被保人姓名<span class="fr">{{$user_res['name']}}</span></li>--}}
							{{--<li>被保人证件号<span class="fr">{{$user_res['papers_code']}}</span></li>--}}
							<li>保障期限<span class="fr">1天</span></li>
							<li>保费<span class="fr">2元</span></li>
						</ul>
					</div>
					@if($ins_status == '200')
					{{--TODO  投保成功--}}
					<div class="date">
						<div class="text"><i class="iconfont icon-chenggong"></i>保障已生效</div>
						<ul class="list">
							@if(!empty($warranty_res))
								@if(isset($warranty_res['warranty_code'])&&strpos($warranty_res['warranty_code'],','))
									@foreach(explode(',',$warranty_res['warranty_code']) as $key=>$value)
							<li>@if($key==0)保单号@endif<span class="fr">{{$value}}</span></li>
									@endforeach
							<li>保障开始时间<span class="fr">{{date('Y-m-d',substr($warranty_res['start_time'],0,strlen($warranty_res['start_time'])-3)).' '.'上工时间'}}</span></li>
							<li>保障结束时间<span class="fr">{{date('Y-m-d',substr($warranty_res['end_time'],0,strlen($warranty_res['end_time'])-3)).' '.'23:59:59'}}</span></li>
								@endif
							@endif
						</ul>
					</div>
					<div class="btn-wrapper">
						<button class="btn" id="warranty_info">查看保单详情</button>
						<button class="btn" id="do_insure">返回我的保险</button>
					</div>
					@elseif($ins_status == '500')
					{{--TODO  投保失败--}}
					<div class="date">
						<div class="text color-wraning"><i class="iconfont icon-error"></i>保障未生效</div>
						<ul class="list">
							<li class="cause">
								<div>失败原因：<span class="fr" style="color: #606060;">{{$ins_msg}}</span></div>
								<div style="text-align: center;"><a href="{{$target_url}}">前往操作</a></div>
							</li>
						</ul>
					</div>
					<div class="btn-wrapper">
						<button class="btn" id="insure_seting">查看投保设置</button>
						<button class="btn" id="do_insure">返回我的保险</button>
					</div>
					@elseif($ins_status == '100')
					{{--TODO  投保中--}}
						<div class="date">
							<div class="text color-wraning"><i class="iconfont icon-chenggong"></i>投保中...</div>
							<ul class="list">
								<li class="cause">
									<div>原因说明：<span class="fr" style="color: #606060;">{{$ins_msg}}</span></div>
									<div style="text-align: center;"><a href="{{$target_url}}">前往操作</a></div>
								</li>
							</ul>
						</div>
						<div class="btn-wrapper">
							<button class="btn" id="insure_seting">查看投保设置</button>
							<button class="btn" id="do_insure">返回我的保险</button>
						</div>
					@endif
				</div>
			</div>
		</div>
		
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
            var token = localStorage.getItem('token');
            var get_token = "{{$_GET['token']}}";
            if(token==null||token==''){
                token = get_token;
			}
            var person_code = "{{$person_code}}";
            var warranty_code = "{{$warranty_res['warranty_code']??""}}";
            $('#warranty_info').on('click',function () {

                window.location.href = '{{config('view_url.channel_yunda_target_url')}}warranty_info/'+warranty_code+'?token='+token;
            });
            $('#insure_seting').on('click',function () {

                window.location.href = '{{config('view_url.channel_yunda_target_url')}}insure_setup_list?token='+token;
            });
            $('#do_insure').on('click',function () {

                window.location.href = '{{config('view_url.channel_yunda_target_url')}}ins_center?token='+token;
            });
            $('.head-right').on('tap',function () {
                location.href = "bmapp:homepage";return false;
            });
            $('.head-left').on('tap',function(){
                history.back(-1);return false;
            });
		</script>
	</body>

</html>