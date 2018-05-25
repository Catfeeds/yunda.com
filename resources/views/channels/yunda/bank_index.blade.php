<!DOCTYPE html>
<html>

	<head>
		<meta charset="utf-8">
		<title>银行卡列表</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
	</head>

	<body id="process9">
				<header class="mui-bar mui-bar-nav">
					<div class="head-left">
						<div class="head-img">
							<i class="iconfont icon-fanhui"></i>
						</div>
					</div>
					<div class="head-title">
						<span>银行卡列表</span>
						<span id="add">添加</span>
					</div>
				</header>
				<div class="mui-content" style="">
					<div class="mui-scroll-wrapper">
						<div class="mui-scroll">
							<div class="policy_list_wrapper">
								@if(count($bank_res)=='0')
									<ul class="tablist">
										<li class="item">暂无银行卡信息，请添加银行卡信息</li>
									</ul>
								@else
									@foreach($bank_res as $value)
										<ul class="tablist" data-id="{{$value['id']}}">
											<li class="item">{{$value['bank']}}</li>
											<li class="item"><span>{{$value['bank_city']}}</span><i class="iconfont icon-jiantou"></i></li>
											<li class="item"><span>{{$value['bank_code']}}</span></li>
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
			<script type="text/javascript">
                var token = localStorage.getItem('token');
				var cust_id = '1';
                $('#add').click(function(){

                    location.href = '{{config('view_url.channel_yunda_target_url')}}bank_bind?token='+token;
                });
                $('#add2').click(function(){

                    location.href = '{{config('view_url.channel_yunda_target_url')}}bank_bind?token='+token;
                });
                $('.tablist').click(function(){
                    var bank_id = $(this).data('id');
                    if(bank_id){

                        location.href = '{{config('view_url.channel_yunda_target_url')}}bank_info/'+bank_id+'?token='+token;
					}
                });
                $('.head-right').on('tap',function () {
                    location.href = "bmapp:homepage";return false;
                });
                $('.head-img').on('tap',function(){
                    history.back(-1);return false;
                });
			</script>
	</body>
</html>