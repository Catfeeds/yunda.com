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
					<input type="text" name="bank_code"/>
				</div>
				<input hidden type="text" name="person_data" value="{{json_encode($token_data)}}"/>
				<div class="agree-wrapper">
					<label>我已阅读并同意<a href="{{config('view_url.channel_yunda_target_url')}}bank_authorize_info?token={{$_GET['token']}}" id="insure_authorize_info"> 《转账授权书》 </a><i class="icon-check"></i><input hidden type="checkbox" value=""/></label>
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
    $('.head-right').on('tap',function () {
        location.href = "bmapp:homepage";return false;
    });
    $('.head-img').on('tap',function(){
        history.back(-1);return false;
    });
    $('#insure_authorize_info').on('tap',function(){

    });
    var app = {
        init: function() {
            var _this = this;
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
            bankPicker.setData([{text:"\u4e2d\u56fd\u5de5\u5546\u94f6\u884c ",value:"1"},{text:"\u4e2d\u56fd\u519c\u4e1a\u94f6\u884c ",value:"2"},{text:"\u4e2d\u56fd\u5efa\u8bbe\u94f6\u884c",value:"3"},{text:"\u4e2d\u56fd\u94f6\u884c",value:"4"},{text:"\u4ea4\u901a\u94f6\u884c ",value:"5"},{text:"\u62db\u5546\u94f6\u884c",value:"6"},{text:"\u4e2d\u4fe1\u5b9e\u4e1a\u94f6\u884c",value:"7"},{text:"\u4e0a\u6d77\u6d66\u4e1c\u53d1\u5c55\u94f6\u884c",value:"8"},{text:"\u6c11\u751f\u94f6\u884c",value:"9"},{text:"\u5149\u5927\u94f6\u884c",value:"10"},{text:"\u5e7f\u4e1c\u53d1\u5c55\u94f6\u884c",value:"11"},{text:"\u5174\u4e1a\u94f6\u884c",value:"12"},{text:"\u534e\u590f\u94f6\u884c",value:"13"},{text:"\u4e0a\u6d77\u94f6\u884c",value:"14"},{text:"\u5317\u4eac\u94f6\u884c",value:"15"},{text:"\u6df1\u5733\u53d1\u5c55\u94f6\u884c",value:"16"},{text:"\u6df1\u5733\u5e02\u5546\u4e1a\u94f6\u884c",value:"17"},{text:"\u5929\u6d25\u5e02\u5546\u4e1a\u94f6\u884c",value:"18"},{text:"\u5e7f\u5dde\u5e02\u5546\u4e1a\u94f6\u884c",value:"19"},{text:"\u676d\u5dde\u5e02\u5546\u4e1a\u94f6\u884c",value:"20"},{text:"\u5357\u4eac\u5e02\u5546\u4e1a\u94f6\u884c",value:"21"},{text:"\u4e1c\u839e\u5e02\u5546\u4e1a\u94f6\u884c",value:"22"},{text:"\u5b81\u6ce2\u5e02\u5546\u4e1a\u94f6\u884c",value:"23"},{text:"\u65e0\u9521\u5e02\u5546\u4e1a\u94f6\u884c",value:"24"},{text:"\u6052\u4e30\u94f6\u884c",value:"25"},{text:"\u6b66\u6c49\u5e02\u5546\u4e1a\u94f6\u884c",value:"26"},{text:"\u957f\u6c99\u5e02\u5546\u4e1a\u94f6\u884c",value:"27"},{text:"\u5927\u8fde\u5e02\u5546\u4e1a\u94f6\u884c",value:"28"},{text:"\u897f\u5b89\u5e02\u5546\u4e1a\u94f6\u884c",value:"29"},{text:"\u91cd\u5e86\u5e02\u5546\u4e1a\u94f6\u884c",value:"30"},{text:"\u6d4e\u5357\u5e02\u5546\u4e1a\u94f6\u884c",value:"31"},{text:"\u6210\u90fd\u5e02\u5546\u4e1a\u94f6\u884c",value:"32"},{text:"\u8d35\u9633\u5e02\u5546\u4e1a\u94f6\u884c",value:"33"},{text:"\u77f3\u5bb6\u5e84\u5e02\u5546\u4e1a\u94f6\u884c",value:"34"},{text:"\u6606\u660e\u5e02\u5546\u4e1a\u94f6\u884c",value:"35"},{text:"\u70df\u53f0\u5e02\u5546\u4e1a\u94f6\u884c",value:"36"},{text:"\u54c8\u5c14\u6ee8\u5e02\u5546\u4e1a\u94f6\u884c",value:"37"},{text:"\u90d1\u5dde\u5e02\u5546\u4e1a\u94f6\u884c",value:"38"},{text:"\u4e4c\u9c81\u6728\u9f50\u5e02\u5546\u4e1a\u94f6\u884c",value:"39"},{text:"\u9752\u5c9b\u5e02\u5546\u4e1a\u94f6\u884c",value:"40"},{text:"\u6e29\u5dde\u5e02\u5546\u4e1a\u94f6\u884c",value:"41"},{text:"\u5408\u80a5\u5e02\u5546\u4e1a\u94f6\u884c",value:"42"},{text:"\u6dc4\u535a\u5e02\u5546\u4e1a\u94f6\u884c",value:"43"},{text:"\u82cf\u5dde\u5e02\u5546\u4e1a\u94f6\u884c ",value:"44"},{text:"\u592a\u539f\u5e02\u5546\u4e1a\u94f6\u884c",value:"45"},{text:"\u7ecd\u5174\u5e02\u5546\u4e1a\u94f6\u884c",value:"46"},{text:"\u53f0\u5dde\u5e02\u5546\u4e1a\u94f6\u884c",value:"47"},{text:"\u6d59\u5546\u94f6\u884c",value:"48"},{text:"\u4e34\u6c82\u5e02\u5546\u4e1a\u94f6\u884c",value:"49"},{text:"\u978d\u5c71\u5e02\u5546\u4e1a\u94f6\u884c",value:"50"},{text:"\u6f4d\u574a\u5e02\u5546\u4e1a\u94f6\u884c",value:"51"}]);
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
        var person_data = $("input[name='person_data']").val();

        if (bank_code.length == 0) {
            Mask.alert('银行卡不能为空', 3);
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
            url: "{{config('view_url.channel_yunda_target_url')}}do_bank_bind",
            type: "post",
            data: {'bank_name':bank_name,'bank_city':bank_city,'bank_code':bank_code,'person_data':person_data},
            dataType: "json",
            success: function (data) {
                Mask.alert(data.msg,3);
                $('#save').attr('style',"display:none");
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