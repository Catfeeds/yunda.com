<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>开通免密支付</title>
	<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.picker.all.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css"/>
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css"/>
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css"/>
</head>
<body id="process2">
<div class="popups">
	<div class="popups-content">
		<div class="top">
			<p style="margin-bottom: .16rem;">上工才投保,每天2元钱</p>
			<h2 class="title">开通银行卡转账授权</h2>
		</div>
		<div class="policy_list_wrapper">
			{{--<div class="tab" style="color: #adadad">--}}
				{{--<span class="item">银行卡</span>--}}
			{{--</div>--}}
			<div class="tab">
				<span class="item">姓名</span>
				<input type="text" name="person_name" value="{{$cust_name??''}}" placeholder="请输入"/>
			</div>
			<div class="tab">
				<span class="item">银行卡号</span>
				<input type="text" name="bank_code" value="{{isset($bank['code'])?$bank['code']:''}}" placeholder="请输入"/>
				<input type="hidden" name="person_code" value="{{$person_code}}"/>
			</div>
			<div class="tab">
				<span class="item">对应手机号</span>
				<input type="text" name="person_phone" value="{{$cust_phone}}" placeholder="请输入"/>
			</div>
		</div>
		<p>*支持的银行：建设银行 平安银行 广发银行 中国银行 光大银行 华夏银行 农业银行 中信银行 工商银行 北京农商银行</p>
		<p>*手机号请填写您银行卡绑定的手机号</p>
	</div>
	<div class="popups-footer">
		<div class="label-wrapper">
			<label><input id="agree" checked type="checkbox"/>我已阅读并同意<a
						href="{{config('view_url.channel_yunda_target_url')}}insure_authorize_info?token={{$_GET['token']}}"
						style="color: #00A2FF;" id="insure_authorize_info">《转账授权书》</a></label>
		</div>
		<button id="confirm" type="button" class="btn">已阅读并开通</button>
		@if(isset($wechat_status)&&$wechat_status)
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
<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
<script src="{{config('view_url.channel_views')}}js/lib/mui.picker.all.js"></script>
<script src="{{config('view_url.channel_views')}}js/lib/area.js"></script>
<script src="{{config('view_url.channel_views')}}js/common.js"></script>
<script>
    var token = "{{$_GET['token']}}";
    localStorage.setItem('token', token);
    var app = {
        init: function () {
            var _this = this;
            _this.bankPicker()
            _this.areaPicker()
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
        bankPicker: function () {
            var bankPicker = new mui.PopPicker();
            bankPicker.setData([{value: 'ywj', text: '工商银行'}, {value: 'aaa', text: '民生银行'}]);
            $('.choose-bank').click(function () {
                var _this = $(this)
                $('input').blur();
                bankPicker.show(function (items) {
                    _this.next().val(items[0].value)
                    _this.text(items[0].text).css({'color': '#303030'})
                    app.isDisabled()
                });
            })
        },
        areaPicker: function () {
            var cityPicker = new mui.PopPicker({layer: 3});
            $('.choose-area').on('tap', function () {
                var _this = $(this)
                $('input').blur();
                var _this = $(this);
                cityPicker.setData(changeCityData(areaData));
                cityPicker.show(function (items) {
                    _this.text(items[0].text + "-" + items[1].text + "-" + items[2].text).css({'color': '#303030'});
                    _this.next().val(items[0].value + "-" + items[1].value + "-" + items[2].value);
                    app.isDisabled()
                });
            })
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
                console.log($(this).val());
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

    });
    $('#confirm').on('click', function () {
        var bank_code = $("input[name='bank_code']").val();
        var person_phone = $("input[name='person_phone']").val();
        var person_name = $("input[name='person_name']").val();
        var person_code = $("input[name='person_code']").val();
         if(bank_code.length == 0||person_name.length == 0||person_code.length == 0){
            Mask.alert('姓名，手机号，银行卡不能为空', 3);
            return false;
		}
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
