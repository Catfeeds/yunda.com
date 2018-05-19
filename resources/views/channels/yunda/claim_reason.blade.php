<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>申请理赔</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.picker.all.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
		<style>
			.btn-select{float: right;margin-right: .26rem;color: #00A2FF;}
			.btn-next{display: block;margin: .4rem auto;width: 90%;color: #744c22;background: #f6d85f;}
			textarea{height:100%;padding:.1rem 0;font-size: .28rem;border: none;line-height: 1.3;}
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
				<span>申请理赔</span>
			</div>
		</header>
		<div>
			<form action="{{config('view_url.channel_yunda_target_url')}}claim_contact?token={{$_GET['token']}}" method="post" id="claim_contact">
				<input type="hidden" name="input" value="{{json_encode($data)}}">
				<div class="mui-scroll-wrapper">
					<div class="mui-scroll">
						<div>
							<ul class="process-wrapper">
								<li class="active"><div class="icon"></div><div>出险人员</div></li>
								<li class="active"><div class="icon"></div><div>出险类型</div></li>
								<li class="active"><div class="icon"></div><div>出险信息</div></li>
								<li><div class="icon"></div><div>联系方式</div></li>
							</ul>
							<ul class="form-wrapper">
								<li style="font-weight: bold;">出险信息</li>
								<li>事故性质<input type="text" name="accident" value="交通事故" disabled="disabled"/></li>
								<li>出险性质<input type="text" name="ins_nature" value="意外门诊" disabled="disabled"/></li>
								<li>出险时间<span class="btn-select">选择</span><input hidden name="ins_time" type="text" value=""/></li>
								<li>出险地址<input type="text" name="ins_address" value="" placeholder="请输入"/></li>
							</ul>
							<ul class="form-wrapper">
								<li style="font-weight: bold;">出险经过描述</li>
								<li style="height: 2rem;"><textarea placeholder="请填写事故的原因和现状" name="ins_desc"></textarea></li>
							</ul>
							<button disabled id="next" class="btn btn-next">下一步</button>
						</div>
					</div>
				</div>
			</form>
		</div>
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.picker.all.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
			var $next = $('#next'),$inputs = $('.form-wrapper input');
			var app = {
				init: function() {
					var _this = this;
					$inputs.bind('input propertychange', function() {
						_this.isDisabled()
					});
					$('textarea').bind('input propertychange', function() {  
						_this.isDisabled()
					});
					_this.dtPicker()
				},
				dtPicker: function() {
					var _this = this;
					var dtPicker = new mui.DtPicker({type: 'date',endDate: new Date()});
					$('.btn-select').click(function(){
						var $this = $(this);
						$('input').blur();
						dtPicker.show(function (rs) {
							$this.text(rs.text).css({'color': '#606060'})
							$this.next().val(rs.text)
							_this.isDisabled()
						})
					})
				},
				isDisabled: function() {
					var isDisabled = this.checkInput() || this.checkTextarea()
			  	$next.prop('disabled',isDisabled)
				},
				checkInput: function() {
					var isDisabled = false
					$inputs.each(function(index){
						if(!$(this).val()){
				  		isDisabled = true
				  		return
				  	}
				  })
					return isDisabled
				},
				checkTextarea: function() {
					var isDisabled = false
					if(!$('textarea').val()){
			  		isDisabled = true
			  	}
					return isDisabled
				}
			}
			app.init();
            $('.head-right').on('tap',function () {
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('.head-img').on('tap',function(){
                Mask.loding();
                window.history.go(-1);
            });
		</script>
	</body>

</html>