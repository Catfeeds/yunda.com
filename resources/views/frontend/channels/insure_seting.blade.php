<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>保障详情</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_url')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_url')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_url')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_url')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_url')}}css/step.css" />
		<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
	</head>

	<body>
		<header class="mui-bar mui-bar-nav">
			<div class="head-left">
				<div class="head-img">
					<img src="{{config('view_url.channel_url')}}imges/back.png">
				</div>
			</div>
			<div class="head-right">
				<i class="iconfont icon-close"></i>
			</div>
			<div class="head-title">
				<span>快递保</span>
			</div>
		</header>
		<div class="step3">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div class="section section-first">
						<div class="section-content">
							<h3 class="title">上工自动投保</h3>
							<div class='switch @if($insure_status<>"1") active @endif'>
								<input  id="insure_status" name="insure_status" type="hidden">
								<div class="switch-handle"></div>
							</div>
						</div>
						<ul>
							<li>开通免密支付协议后，每天上工后自动扣除当天的保费。</li>
							<li>如果当日未上工则不扣费。</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		
		<div class="popups-wrapper popups-ask">
			<div class="popups-bg"></div>
			<div class="popups">
				<div class="popups-title"><i class="iconfont icon-guanbi"></i></div>
				<div class="popups-content">
					<p class="wraning">您确定要取消吗?</p>
					<p>取消后上工期间将无法自动投保,如果发生意外就没有保障了哟。</p>
				</div>
				<div class="popups-footer">
					<button id="confirm" type="button" class="btn btn-default">确定</button>
					<button id="cancel" type="button" class="btn btn-default">取消</button>
				</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_url')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_url')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_url')}}js/common.js"></script>
		<script>
            $('.head-right').on('tap',function () {

                location.href="bmapp:homepage";
            });
            $('.head-left').on('tap',function(){

                window.location.go(-1);
            });
			$(function(){
				var btn_switch = $('.switch');
                var person_code = "{{$person_code}}";
				var step3 = {
					init: function(){
						var _this = this;
						_this.isAutoInsure();
					},
					isAutoInsure: function(){
						btn_switch.on('tap',function(){
							var _that = $(this);
							_that.hasClass('active') == true ? Popups.open('.popups-ask') : btn_switch.addClass('active').find('input').val(1);
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                url: "/channelsapi/do_insure_seting",
                                type: "post",
                                data: {'person_code':person_code,'insure_status':'0'},
                                dataType: "json",
                                success: function (data) {
                                    console.log(data);
                                }
                            });

						});
						$('#confirm').click(function(){
							btn_switch.removeClass('active').find('input').val(0);
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                url: "/channelsapi/do_insure_seting",
                                type: "post",
                                data: {'person_code':person_code,'insure_status':'1'},
                                dataType: "json",
                                success: function (data) {
                                    console.log(data);
                                }
                            });
							Popups.close('.popups-ask');
						});
						$('#cancel').click(function(){
							Popups.close('.popups-ask');
						});
					}
				}
				step3.init();
			});
            $('#insure_info').on('tap',function(){

            });
            $('#clause_target').on('tap',function(){

            });
            $('#notice_target').on('tap',function(){

            });
		</script>
	</body>

</html>