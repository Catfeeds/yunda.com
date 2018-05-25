<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>免密支付</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
	</head>
	<body id="process2">
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
				<span>免密支付</span>
			</div>
		</header>
		<div class="step7">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div class="popups">
						<div class="popups-content">
							<div class="top">
								<p style="margin-bottom: .16rem;">上工才投保,每天2元钱</p>
								<h2 class="title">开通免密支付协议</h2>
							</div>
							<div class="policy_list_wrapper">
								<div class="tab" style="color: #adadad">
									<span class="item">银行卡</span>
								</div>
								<div class="tab">
									<span class="item">姓名</span>
									<input type="text" name="person_name" value="{{$cust_name??''}}" />
								</div>
								<div class="tab">
									<span class="item">手机号</span>
									<input type="text" name="person_phone" value="{{$cust_phone}}" />
								</div>
								<div class="tab">
									<span class="item">卡号</span>
									<input type="text" name="bank_code" value="{{isset($bank['code'])?$bank['code']:''}}"/>
									<input type="hidden" name="person_code" value="{{$person_code}}"/>
								</div>
							</div>
						</div>
						<div class="popups-footer">
							<div class="label-wrapper">
								<label><input id="agree" checked type="checkbox"/>我已阅读并同意
									<a href="{{config('view_url.channel_yunda_target_url')}}bank_authorize_info?token={{$_GET['token']}}"
											style="color: #00A2FF;" id="insure_authorize_info">《转账授权书》</a></label>
							</div>
							<button id="confirm" type="button" class="btn">已阅读并开通</button>
							@if(isset($wechat_status)&&$wechat_status)
								<form action="{{$wechat_url}}" method="post" id="do_insure_sign">
								</form>
								<div class="label-wrapper">
									<div class="or">or</div>
									<div class="wechat">微信</div>
									<label><input id="agree" checked type="checkbox" />
										我已阅读并同意<a style="color: #00A2FF;">《免密授权书》</a>
									</label>
								</div>
								<button type="button" id="wechat_pay" class="btn btn-default" style="background: #1aad19;">开通微信免密支付</button>
							@endif
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
                history.go(-1);return false;
            });
            var token = "{{$_GET['token']}}";
            localStorage.setItem('token', token);
            var app = {
                init: function () {
                    var _this = this;
                    $('.icon-guanbi').click(function () {
                        location.href = "bmapp:homepage";return false;
                    });
                    $('input').bind('input propertychange', function () {
                        _this.isDisabled()
                    })
                    $('.popups-footer input').click(function () {
                        _this.isDisabled()
                    })
                    _this.isDisabled()
                },
                isDisabled: function () {
                    var $confirm = $('.btn-default');
                    var status = this.checkInput() || this.isAgree()
                    $confirm.prop('disabled', status)
                },
                checkInput: function () {
                    var status = false
                    var $inputs = $('.tab input')
                    $inputs.each(function (index) {
                        if (!$(this).val()) {
                            status = true
                        }
                        return false
                    })
                    return status
                },
                isAgree: function () {
                    if ($('.popups-footer input').prop('checked')) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
            app.init();
            $('#insure_authorize_info').on('tap', function () {
                Mask.loding();
            });
            $('#confirm').on('click', function () {
                var bank_code = $("input[name='bank_code']").val();
                var person_phone = $("input[name='person_phone']").val();
                var person_name = $("input[name='person_name']").val();
                var person_code = $("input[name='person_code']").val();
                if (bank_code.length == 0 || person_name.length == 0 || person_phone.length == 0) {
                    Mask.alert('姓名，手机号，银行卡不能为空', 3);
                    return false;
                }
                if(!isChn(person_name)){
                    Mask.alert('姓名必须是汉字', 3);
                    return false;
                }
                if(!isRealNum(person_phone)){
                    Mask.alert('手机号必须是数字', 3);
                    return false;
                }
                if(!isRealNum(bank_code)){
                    Mask.alert('银行卡必须是数字', 3);
                    return false;
                }
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{config('view_url.channel_yunda_target_url')}}do_insure_authorize",
                    type: "post",
                    data: {'person_name': person_name,'person_phone':person_phone, 'person_code': person_code, 'bank_code': bank_code},
                    dataType: "json",
                    success: function (data) {
                        Mask.alert(data.msg, 3);
                        $('#confirm').attr("style", "display:none;");
                    }
                });
            });
            $('#wechat_pay').on('tap', function () {
                Mask.loding();
                $('#do_insure_sign').submit();
            });
            function isRealNum(val){
                // isNaN()函数 把空串 空格 以及NUll 按照0来处理 所以先去除
                if(val === "" || val ==null){
                    return false;
                }
                if(!isNaN(val)){
                    return true;
                }else{
                    return false;
                }
            }
            function isChn(str) {
                if (!str.match( /^[\u4E00-\u9FA5]{1,}$/)) {
                    return false;
                } else {
                    return true;
                }
            }
		</script>
	</body>
</html>