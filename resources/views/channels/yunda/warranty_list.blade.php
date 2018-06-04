<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>保单列表</title>
	<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css"/>
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css"/>
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css"/>
	<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
</head>

<body id="process13">
<header class="mui-bar mui-bar-nav">
	<div class="head-left">
		<div class="head-img">
			<i class="iconfont icon-fanhui"></i>
		</div>
	</div>
	<div class="head-title">
		<span>我的保单</span>
	</div>
</header>
<div class="mui-content" style="">
	<div class="mui-scroll-wrapper">
		<div class="mui-scroll">
			<div class="policy_list_wrapper">
				<ul class="tab">
					<li class="item @if(isset($_GET['status'])&&$_GET['status']=='2') active @endif" data-id="2">
						待支付（{{count($warranty_paying_res)}}）
					</li>
					<li class="item @if(!isset($_GET['status'])||isset($_GET['status'])&&$_GET['status']=='4') active @endif"
						data-id="4">保障中（{{count($warranty_ok_res)}}）
					</li>
					<li class="item @if(isset($_GET['status'])&&$_GET['status']=='6') active @endif" data-id="6">
						已失效（{{count($warranty_timeout_res)}}）
					</li>
				</ul>
				@if(count($warranty_res)==0)
					<ul class="content">
						<li class="title">暂无保单数据</li>
					</ul>
				@else
					@foreach($warranty_res as $value)
						<ul class="content" data-id="{{$value['id']}}">
							<li class="title">快递保.意外险<i class="iconfont icon-jiantou"></i></li>
							<li class="item">
								<span>保单号</span>
								<span>{{$value['pro_policy_no']??""}}</span>
							</li>
							<li class="item">
								<span>保单状态</span>
								@if(isset($warranty_status) &&!empty($warranty_status))
									@foreach($warranty_status as $key=>$v)
										@if($value['warranty_status'] == $key&&$v=='待支付')
											<span class="special">	{{$pay_status[$value['pay_status']]}}</span>
										@endif
										@if($value['warranty_status'] == $key&&$v!='待支付')
											<span class="special">	{{$v}}</span>
										@endif
									@endforeach
								@endif
								{{--@if($value['status']=='7')--}}
								{{--<span class="special">保障中</span>--}}
								{{--@elseif(isset($_GET['status'])&&$_GET['status']=='3'||$value['status']=='3')--}}
								{{--<span class="special">待支付</span>--}}
								{{--@elseif(isset($_GET['status'])&&$_GET['status']=='10'||$value['status']=='10')--}}
								{{--<span class="special">已失效</span>--}}
								{{--@endif--}}
							</li>
							<li class="item">
								<span>生效时间</span>
								<span>
													{{date('Y-m-d',substr($value['start_time'],0,strlen($value['start_time'])-3)).' '.'上工时间'}}
									到
									{{date('Y-m-d',substr($value['start_time'],0,strlen($value['end_time'])-3)).' '.'23:59:59'}}
												</span>
							</li>
						</ul>
						<br/>
					@endforeach
				@endif
			</div>
		</div>
	</div>
</div>
<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
<script src="{{config('view_url.channel_views')}}js/common.js"></script>
<script type="text/javascript" charset="utf-8">
    var token = localStorage.getItem('token');
    $('.tab .item').click(function () {
        var status = $(this).data('id');
        $(this).addClass('active').siblings().removeClass('active')
        location.href = '{{config('view_url.channel_yunda_target_url')}}warranty_list?status=' + status + '&token=' + token;

    })
    $('.content').click(function () {
        var warranty_id = $(this).data('id');
        if (warranty_id) {
            //Mask.loding();
            location.href = '{{config('view_url.channel_yunda_target_url')}}warranty_detail/' + warranty_id + '?token=' + token;
        }
    });
    $('.head-right').on('tap', function () {
        location.href = "bmapp:homepage";
        return false;
    });
    $('.head-left').on('tap',function(){
        //history.back(-1);
        window.location.href = "{{config('view_url.channel_yunda_target_url')}}ins_center?token=" + token;
        return false;
    });
</script>
</body>
</html>