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
		<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
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
			<div class="head-img">
				<img src="{{config('view_url.channel_views')}}imges/back.png" />
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
							<span class="item">开户所在城市</span>
							<span class="choose choose-area">选择</span>
							<input hidden type="text" name="bank_city" />
						</div>
						<div class="tab">
							<span class="item">借记卡开户行</span>
							<span class="choose choose-bank">选择</span>
							<input hidden type="text" name="bank_name" />
						</div>
						<div class="tab">
							<span class="item">银行卡号</span>
							<input type="text" name="bank_code"/>
						</div>
							<input hidden type="text" name="cust_id" value="1"/>
						<div class="agree-wrapper">
							<label>我已阅读并同意<a href="{{config('view_url.channel_yunda_target_url')}}insure_authorize_info" id="insure_authorize_info"> 《转账授权书》 </a><i class="icon-check"></i><input hidden type="checkbox" value=""/></label>
						</div>
						<button disabled id="save" class="btn">保存</button>
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
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('.head-img').on('tap',function(){
                Mask.loding();
                window.history.go(-1);
            });
            $('#insure_authorize_info').on('tap',function(){
                Mask.loding();
            });
			var app = {
				init: function() {
					var _this = this;
					_this.bankPicker()
					_this.areaPicker()
					$('input').bind('input propertychange', function() {  
						_this.isDisabled()
					})
					$('.agree-wrapper input').click(function(){
						var status = $(this).prop('checked')
						$(this).prev().toggleClass('active')
						_this.isDisabled()
					})
					_this.isDisabled()
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
					bankPicker.setData([{value: 'ywj',text: '工商银行'}, {value: 'aaa',text: '民生银行'}]);
					$('.choose-bank').click(function(){
						var _this = $(this)
						$('input').blur();
						bankPicker.show(function(items) {
							_this.next().val(items[0].text)
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
							_this.next().val(items[0].text+"-"+items[1].text+"-"+items[2].text);
							app.isDisabled()
						});
					})
				},
				isDisabled: function() {
					var $save = $('#save')
					var status = this.checkInput() || this.isAgree()
					$save.prop('disabled',status)
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
					if($('.icon-check.active').length){
						return false;
					}else{
						return true;
					}
				}
			}
			app.init();
            $('#save').click(function(){
                var bank_name = $("input[name='bank_name']").val();
                var bank_city = $("input[name='bank_city']").val();
                var bank_code = $("input[name='bank_code']").val();
                var cust_id = $("input[name='cust_id']").val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{config('view_url.channel_yunda_target_url')}}do_bank_bind",
                    type: "post",
                    data: {'bank_name':bank_name,'bank_city':bank_city,'bank_code':bank_code,'cust_id':cust_id},
                    dataType: "json",
                    success: function (data) {
                        Mask.alert(data.msg,3);
                        $('#save').attr('style',"display:none");
                    }
                });
            });
		</script>
	</body>
</html>