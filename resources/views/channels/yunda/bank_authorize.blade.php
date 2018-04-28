<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>开通免密支付</title>
	<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.picker.all.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
</head>
<body id="process2">
<div class="popups-wrapper popups-agree" style="display: block;">
	<div class="popups-bg"></div>
	<div class="popups">
		<div class="popups-title"><i class="iconfont icon-guanbi"></i></div>
		<div class="popups-content">
			<div class="top">
				<h2 class="title">开通免密支付协议</h2>
				<p style="margin-bottom: .16rem;">为了保障您上工的安全，需要您开通免密支付协议。开通后，可以实现每日自动投保。</p>
			</div>
			<div class="policy_list_wrapper">
				<div class="tab" style="color: #adadad">
					<span class="item">银行卡</span>
				</div>
				<div class="tab">
					<span class="item">姓名</span>
					<input type="text" name="person_name" value="{{$cust_name??""}}" />
				</div>
				<div class="tab">
					<span class="item">手机号</span>
					<input type="text" name="person_phone" value="{{$cust_phone}}" />
				</div>
				<div class="tab">
					<span class="item">卡号</span>
					<input type="text" name="bank_code" value="{{isset($bank['code'])?$bank['code']:""}}"/>
					<input type="hidden" name="person_code" value="{{$person_code}}"/>
				</div>
			</div>
		</div>
		<div class="popups-footer">

			<div class="label-wrapper">
				<label><input id="agree" checked type="checkbox" />我已阅读并同意<a href="{{config('view_url.channel_yunda_target_url')}}insure_authorize_info?token={{$_GET['token']}}" style="color: #00A2FF;" id="insure_authorize_info">《转账授权书》</a></label>
			</div>
			@if(!$authorize_status)
			<button disabled id="confirm" type="button" class="btn btn-default">已阅读并开通</button>
			@endif
			@if($wechat_status)
				<form action="{{$wechat_url}}" method="post" id="do_insure_sign">
				</form>
			<div class="label-wrapper">
				<div class="or">or</div>
				<div class="wechat">微信</div>
				{{--<label><input id="agree" checked type="checkbox" />我已阅读并同意<a style="color: #00A2FF;">《免密授权书》</a></label>--}}
			</div>
			<button type="button" id="wechat_pay" class="btn btn-default" style="background: #1aad19;">开通微信免密支付</button>
			@endif
		</div>
	</div>
</div>
<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
<script src="{{config('view_url.channel_views')}}js/lib/mui.picker.all.js"></script>
<script src="{{config('view_url.channel_views')}}js/lib/area.js"></script>
<script src="{{config('view_url.channel_views')}}js/common.js"></script>
<script>
    var token = "{{$_GET['token']}}";
    localStorage.setItem('token', token);
    var app = {
        init: function() {
            var _this = this;
            _this.bankPicker()
            _this.areaPicker()
            $('.icon-guanbi').click(function(){
                location.href="bmapp:homepage";
            });
            $('input').bind('input propertychange', function() {
                _this.isDisabled()
            })
            $('.popups-footer input').click(function(){
                _this.isDisabled()
            })
            _this.isDisabled()
        },
        bankPicker: function() {
            var bankPicker = new mui.PopPicker();
            bankPicker.setData([{value: 'ywj',text: '工商银行'}, {value: 'aaa',text: '民生银行'}]);
            $('.choose-bank').click(function(){
                var _this = $(this)
                $('input').blur();
                bankPicker.show(function(items) {
                    _this.next().val(items[0].value)
                    _this.text(items[0].text).css({'color':'#303030'})
                    app.isDisabled()
                });
            })
        },
        areaPicker: function() {
            var cityPicker = new mui.PopPicker({layer: 3});
            $('.choose-area').on('tap',function(){
                var _this = $(this)
                $('input').blur();
                var _this = $(this);
                cityPicker.setData(changeCityData(areaData));
                cityPicker.show(function(items) {
                    _this.text(items[0].text+"-"+items[1].text+"-"+items[2].text).css({'color':'#303030'});
                    _this.next().val(items[0].value+"-"+items[1].value+"-"+items[2].value);
                    app.isDisabled()
                });
            })
        },
        isDisabled: function() {
            var $confirm = $('.btn-default');
            var status = this.checkInput() || this.isAgree()
            $confirm.prop('disabled',status)
        },
        checkInput: function() {
            var status = false
            var $inputs = $('.tab input')
            $inputs.each(function(index){
                if(!$(this).val()){
                    status = true
                    return false
                }
            })
            return status
        },
        isAgree: function() {
            if($('.popups-footer input').prop('checked')){
                return false;
            }else{
                return true;
            }
        }
    }
    app.init();
    $('#insure_authorize_info').on('tap',function(){
        Mask.loding();
    });
    $('#confirm').on('click',function(){
        var bank_code = $("input[name='bank_code']").val();
        var person_name = $("input[name='person_name']").val();
        var person_code = $("input[name='person_code']").val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{config('view_url.channel_yunda_target_url')}}do_insure_authorize",
            type: "post",
            data: {'person_name':person_name,'person_code':person_code,'bank_code':bank_code},
            dataType: "json",
            success: function (data) {
                Mask.alert(data.msg,3);
                $('#confirm').attr("style","display:none;");
            }
        });
    });
    $('#wechat_pay').on('tap',function(){
        Mask.loding();
		$('#do_insure_sign').submit();
    });
</script>
</body>
</html>