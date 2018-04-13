<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>理赔详情</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
		<style>
			body{background: #fff;}
			.list{padding: .2rem;line-height: 2;}
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
				<span>理赔详情</span>
			</div>
		</header>
		<div>
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div>
						<ul class="form-wrapper">
							<li style="font-weight: bold;">快递保.意外险</li>
							<li>理赔状态<input style="color: #267cfc;" @if($result->claim_status == 2)onclick="uploadInfo({{$result->claim_id}})"@endif  type="text" value="{{$status['claim_status'][$result->status]}}"/></li>
							<li>申请时间<input disabled type="text"  value="{{$result->claim_created_at}}"/></li>
						</ul>
						<div class="division"></div>
						<ul class="form-wrapper">
							<li style="font-weight: bold;">当前进度</li>
						</ul>
						<div>
							<ul class="process-wrapper">
								<li @if($result->claim_status >= 1 || $result->claim_status == -1)class="active"@endif><div class="icon"></div><div>申请理赔</div></li>
								<li @if($result->claim_status >= 2 || $result->claim_status == -1)class="active"@endif><div class="icon"></div><div>提交资料</div></li>
								<li @if($result->claim_status >= 3 || $result->claim_status == -1)class="active"@endif><div class="icon"></div><div>等待审核</div></li>
								@if($result->claim_status != -1)
									<li @if($result->claim_status == 4)class="active"@endif><div class="icon"></div><div>审核通过</div></li>
								@endif
								@if($result->claim_status == -1)
									<li class="active"><div class="icon"></div><div>审核驳回</div></li>
								@endif
							</ul>
							<ul class="list">
								<li>{{$result->claim_created_at}} <span class="fr">申请理赔</span></li>
								@if($result->claim_status >= 2 || $result->claim_status == -1)
									<li>{{$result->claim_info_created_at}} <span class="fr">提交资料</span></li>
								@endif
								@if($result->claim_status == 4 || $result->claim_status == -1)
									<li>{{$result->claim_info_updated_at}} <span class="fr">{{$status['claim_status'][$result->status]}}</span></li>
									<li>审核备注： <span class="fr">{{$result->remark}} </span></li>
								@endif
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
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('.head-img').on('tap',function(){
                Mask.loding();
                window.history.go(-1);
            });
            //上传资料
            function uploadInfo(id){
                location.href = '{{config('view_url.channel_yunda_target_url')}}claim_material_upload?claim_id='+id;
            }
		</script>
	</body>
</html>

