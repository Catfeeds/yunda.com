<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>添加银行卡</title>
	<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.picker.all.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
	<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
	<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
	<style>
		.agree-wrapper{
			margin: .2rem 0 .3rem;
			padding: .2rem;
			line-height: 1.8;
			background: #fff;
		}
		.icon-check{
			float: left;
			margin-top: 0;
			margin-right: .2rem;
		}
	</style>
</head>

<body id="process10">
<header class="mui-bar mui-bar-nav">
	<div class="head-left">
		<div class="head-img">
			<i class="iconfont icon-fanhui"></i>
		</div>
	</div>
	<div class="head-title">
		<span>银行卡信息</span>
	</div>
</header>
<div class="mui-content" style="">
	<div class="mui-scroll-wrapper">
		<div class="mui-scroll">
			<div class="policy_list_wrapper">
				<div class="tab">
					<span class="item">开户所在城市<b>(非必选)</b></span>
					<span class="choose choose-area">选择</span>
					<input hidden type="text" name="bank_city" />
				</div>
				<div class="tab">
					<span class="item">借记卡开户行<b>(非必选)</b></span>
					<span class="choose choose-bank">选择</span>
					<input hidden type="text" name="bank_name" />
				</div>
				<div class="tab banktab">
					<span class="item"><b>银行卡号<span style="color: red">*</span></b></span>
					<input type="text" name="bank_code" placeholder="请输入银行卡号"/>
				</div>
				<div class="tab">
					<span class="item"><b>手机号<span style="color: red">*</span></b></span>
					<input type="text" name="bank_phone" value="" placeholder="请输入手机号">
				</div>
				<div class="tab">
					<div class="phonestyle phoneyansghi"></div>
					<button id="btn-send" class="zbtn zbtn-positive">获取验证码</button>
				</div>
				<div class="tab">
					<span class="item"><b>验证码<span style="color: red">*</span></b></span>
					<input id="code" type="text" name="verify_code" placeholder="输入验证码">
				</div>
				<input hidden type="text" name="person_data" value="{{json_encode($token_data)}}"/>
				<div class="banknotice">
					<p><span style="color: red">*</span>银行卡开户人必须为本人，且保证卡里余额充足</p>
					<p><span style="color: red">*</span>请填写办理该银行卡时预留的手机号码</p>
					<p><span style="color: red">*</span>支持的银行：<br/>
						<span style="font-size:12px">建设银行 平安银行 广发银行 中国银行 光大银行 华夏银行 农业银行 中信银行 工商银行 北京农商银行</span>
					</p>
				</div>
				<div class="agree-wrapper">
					<label>我已阅读并同意<a href="{{config('view_url.channel_yunda_target_url')}}bank_authorize_info?token={{$_GET['token']}}" id="insure_authorize_info"> 《转账授权书》 </a><i class="icon-check active"></i><input hidden type="checkbox" value=""/></label>
				</div>
				<button id="save" class="btn">保存</button>
			</div>
		</div>
		<div class="popups-wrapper popups-ask">
			<div class="popups-bg"></div>
			<div class="popups">
				<div class="popups-title"><i class="iconfont icon-guanbi"></i></div>
				<div class="popups-content">
					<p class="wraning">您确定要删除吗?</p>
					<p>关闭功能将无法购买保险，是否确认</p>
				</div>
				<div class="popups-footer">
					<button id="confirm" type="button" class="btn btn-default">确定</button>
					<button id="cancel" type="button" class="btn btn-default">我再想想</button>
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
    var token = "{{$_GET['token']}}";
    $('.head-right').on('tap',function () {
        location.href = "bmapp:homepage";return false;
    });
    $('.head-left').on('tap',function(){
        window.location.href = "{{config('view_url.channel_yunda_target_url')}}bank_index?token=" + token;
        return false;
    });
    $('#insure_authorize_info').on('tap',function(){

    });

    $("#btn-send").click(function(){
        var bank_code = $("input[name='bank_code']").val();
        var bank_phone = $("input[name='bank_phone']").val();
        var person_data = $("input[name='person_data']").val();
        if (bank_code.length == 0) {
            Mask.alert('银行卡不能为空', 3);
            return false;
        }
        if(!isRealNum(bank_code)){
            Mask.alert('银行卡必须是数字', 3);
            return false;
        }
        if (bank_code.length < 16) {
            Mask.alert('银行卡格式不正确', 3);
            return false;
        }
        if (bank_phone.length == 0) {
            Mask.alert('手机号不能为空', 3);
            return false;
        }
        if(!isRealNum(bank_phone)){
            Mask.alert('手机号必须是数字', 3);
            return false;
        }
        if (bank_phone.length < 10) {
            Mask.alert('银行卡格式不正确', 3);
            return false;
        }
        timer(60,$(this));
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{config('view_url.channel_yunda_target_url')}}bank_verify",
            type: "post",
            data: {'bank_code':bank_code,'person_data':person_data,'bank_phone':bank_phone},
            dataType: "json",
            success: function (data) {
                if(data.status==200||data.status=="200"){
                    Mask.alert(data.content,3);
                }else{
                    Mask.alert(data.content,3);
                }
            }
        });
    });
    var app = {
        init: function() {
            var _this = this;
            _this.bankPicker()
            _this.areaPicker()
            $('.agree-wrapper input').click(function(){
                var status = $(this).prop('checked')
                $(this).prev().toggleClass('active')

            })
            if($('#btn-remove').length){
                _this.remove()
            }
        },
        remove: function() {
            $('#btn-remove').click(function(){
                $('.popups-ask').show()
            })
            $('#cancel').click(function(){
                $('.popups-ask').hide()
            })
        },
        bankPicker: function() {
            var bankPicker = new mui.PopPicker();
            bankPicker.setData([
                {"text":"建设银行","value":"1"},
                {"text":"平安银行","value":"2"},
                {"text":"广发银行","value":"3"},
                {"text":"中国银行","value":"4"},
                {"text":"光大银行","value":"5"},
                {"text":"华夏银行","value":"6"},
                {"text":"农业银行","value":"7"},
                {"text":"中信银行","value":"8"},
                {"text":"工商银行","value":"9"},
                {"text":"北京农商银行","value":"10"}
            ]);
            $('.choose-bank').click(function(){
                var _this = $(this)
                $('input').blur();
                bankPicker.show(function(items) {
                    _this.next().val(items[0].text)
                    _this.text(items[0].text).css({'color':'#303030'})
                   
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
                    _this.next().val(items[0].text+"-"+items[1].text+"-"+items[2].text);
                    
                });
            })
        },
    }
    app.init();
    $('#save').click(function(){
        var bank_name = $("input[name='bank_name']").val();
        var bank_city = $("input[name='bank_city']").val();
        var bank_code = $("input[name='bank_code']").val();
        var bank_phone = $("input[name='bank_phone']").val();
        var verify_code = $("input[name='verify_code']").val();
        var person_data = $("input[name='person_data']").val();
        if (bank_code.length == 0) {
            Mask.alert('银行卡不能为空', 3);
            return false;
        }
        if(!isRealNum(bank_code)){
            Mask.alert('银行卡必须是数字', 3);
            return false;
        }
        if (bank_code.length < 16) {
            Mask.alert('银行卡格式不正确', 3);
            return false;
        }
        if (bank_phone.length == 0) {
            Mask.alert('手机号不能为空', 3);
            return false;
        }
        if(!isRealNum(bank_phone)){
            Mask.alert('手机号必须是数字', 3);
            return false;
        }
        if (bank_phone.length < 10) {
            Mask.alert('银行卡格式不正确', 3);
            return false;
        }
        if (verify_code.length == 0) {
            Mask.alert('验证码不能为空', 3);
            return false;
        }
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{config('view_url.channel_yunda_target_url')}}do_bank_bind",
            type: "post",
            data: {'bank_name':bank_name,'bank_city':bank_city,'bank_code':bank_code,'person_data':person_data,'bank_phone':bank_phone,'verify_code':verify_code},
            dataType: "json",
            success: function (data) {
                Mask.alert(data.msg,3);
                if(data.status==200){
                    $('#save').attr('style',"display:none");
                    setTimeout(function(){//两秒后跳转
                        window.location.href = "{{config('view_url.channel_yunda_target_url')}}bank_index?token="+token;
                    },2000);
				}
            }
        });
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
</script>
</body>
</html>