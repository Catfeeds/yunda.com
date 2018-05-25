<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>银行卡信息</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.picker.all.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
	</head>

	<body id="process10">
		<header class="mui-bar mui-bar-nav">
			<div class="head-img">
				<img src="{{config('view_url.channel_views')}}imges/back.png" />
			</div>
			<div class="head-title">
				<span>银行卡信息</span>
				@if($bank_del_status)
				<span id="del">删除</span>
				@endif
			</div>
		</header>
		<div class="mui-content" style="">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div class="policy_list_wrapper">
						<div class="tab">
							<span class="item tip">银行卡信息</span>
						</div>
						<div class="tab">
							<span class="item">开户所在城市</span>
							<span class="choose choose-area" style="color: #303030;">{{$bank_res['bank_city']}}</span>
						</div>
						<div class="tab">
							<span class="item">借记卡开户行</span>
							<span class="choose choose-bank">{{$bank_res['bank']}}</span>
						</div>
						<div class="tab">
							<span class="item">银行卡号</span>
							<input type="text" name="bank_code" value="{{$bank_res['bank_code']}}" readonly/>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.picker.all.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/area.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
            var token = localStorage.getItem('token');
            $('.head-right').on('tap',function () {
                location.href = "bmapp:homepage";return false;
            });
            $('.head-img').on('tap',function(){
                window.history.go(-1);return false;
            });
            $('#del').click(function(){
                var bank_code = $("input[name='bank_code']").val();
                var cust_id ="{{$cust_id}}";
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{config('view_url.channel_yunda_target_url')}}bank_del",
                    type: "post",
                    data: {'cust_id':cust_id,'bank_code':bank_code},
                    dataType: "json",
                    success: function (data) {
                        Mask.alert(data.msg,3);
                        setTimeout(function(){//两秒后跳转
							window.location.href = "{{config('view_url.channel_yunda_target_url')}}bank_index?token="+token;
                        },2000);
                    }
                });
            });
		</script>
	</body>
</html>