<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>理赔进度</title>
	<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
	<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
	<style>
		.tab{margin-bottom: .2rem;height: 1rem;line-height: 1rem;background: #fff;text-align: center;}
		.tab .tab-item{display: inline-block;width: 40%;}
		.tab .tab-item.active{color: #ff8a00;}
		.icon-jiantou{float: right;}
		.form-wrapper{margin-bottom: .2rem;}
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
		<span>理赔进度</span>
	</div>
</header>
<div>
	<div class="mui-scroll-wrapper">
		<div class="mui-scroll">
			<div>
				<div class="tab">
					<span class="tab-item @if($type == 0) active @endif" id="underway">进行中</span><span class="tab-item @if($type == 1) active @endif" id="end">已完结</span>
				</div>
				@if(count($list)==0)
					<ul class="form-wrapper">
						<li class="title">暂无保单数据</li>
					</ul>
				@else
					@foreach($list as $value)
						<ul class="form-wrapper">
							<li style="font-weight: bold;" onclick="showId({{$value->claim_id}})">{{$value->product_name}}<i class="iconfont icon-jiantou"></i></li>
							<li>理赔状态<input @if($value->claim_status == 2)onclick="uploadInfo({{$value->claim_id}})"@endif  type="text" value="{{$status['claim_status'][$value->status]}}"/></li>
							<li>申请时间<input disabled type="text" value="{{$value->claim_created_at}}"/></li>
						</ul>
					@endforeach
				@endif
			</div>
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
        history.go(-1);return false;
    });
    $('#underway').click(function(){
        location.href = '{{config('view_url.channel_yunda_target_url')}}claim_progress?type=0&token='+token;
    });
    $('#end').click(function(){
        location.href = '{{config('view_url.channel_yunda_target_url')}}claim_progress?type=1&token='+token;
    });
    //显示详情
    function showId(id){
        location.href = '{{config('view_url.channel_yunda_target_url')}}claim_info?claim_id='+id+'&token='+token;
    }
    //上传资料
    function uploadInfo(id){
        location.href = '{{config('view_url.channel_yunda_target_url')}}claim_material_upload?claim_id='+id+'&token='+token;
    }





</script>
</body>

</html>