<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>我的保险</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
	</head>
	<body>
		<header class="mui-bar mui-bar-nav">
			<div class="head-left">
				<div class="head-img">
					<a href="bmapp:homepage"><i class="iconfont icon-fanhui"></i></a>
				</div>
			</div>
			<div class="head-right">
				<a href="bmapp:homepage"><i class="iconfont icon-close"></i></a>
			</div>
			<div class="head-title">
				<span>快递保</span>
			</div>
		</header>
		<div class="step2">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<a style="display: block;" href="{{config('view_url.channel_yunda_target_url')}}ins_info?token={{$_GET['token']}}" id="insure_target">
						<div class="banner">
							<img src="{{config('view_url.channel_views')}}imges/banner_text.png" alt="" />
						</div>
					</a>
					@if(!$auto_insure_status)
					<!--自动购保功能关闭时渲染-->
						<a href="{{config('view_url.channel_yunda_target_url')}}insure_seting?token={{$_GET['token']}}" id="insure_set_target" class="status-wrapper">开启快递保免密支付,每日出行有保障>></a>
					@else
							@if(!$insured_status)
								<a href="{{config('view_url.channel_yunda_target_url')}}ins_info?token={{$_GET['token']}}" id="insure_no_target" class="status-wrapper">今日快递保未生效,点击前往购买>></a>
							@endif
					@endif
					<ul class="list-wrapper">
						<li class="list-item">
							<a href="{{config('view_url.channel_yunda_target_url')}}warranty_list?token={{$_GET['token']}}" id="warranty_target">
								<div class="item-img"><img src="{{config('view_url.channel_views')}}imges/-warranty.png" alt="" /></div>
								<div class="item-content">
									<p class="title">我的保单</p>
									<p class="text"><span>保单列表</span><span>查看保障</span><span>发起理赔</span></p>
								</div>
								<i class="iconfont icon-jiantou"></i>
							</a>
						</li>
						<li class="list-item">
							<a   href="{{config('view_url.channel_yunda_target_url')}}claim_progress?token={{$_GET['token']}}" id="claim_target">
								<div class="item-img"><img src="{{config('view_url.channel_views')}}imges/icon_lp.png" alt="" /></div>
								<div class="item-content">
									<p class="title">我的理赔</p>
									<p class="text"><span>理赔列表</span><span>查看进度</span></p>
								</div>
								<i class="iconfont icon-jiantou"></i>
							</a>
						</li>
						<li class="list-item">
							<a  href="{{config('view_url.channel_yunda_target_url')}}insure_setup_list?token={{$_GET['token']}}" id="seting_target">
								<div class="item-img"><img src="{{config('view_url.channel_views')}}imges/icon_set.png" alt="" /></div>
								<div class="item-content">
									<p class="title">设置</p>
									<p class="text"><span>投保设置</span><span>银行卡设置</span></p>
								</div>
								<i class="iconfont icon-jiantou"></i>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<!--投保成功弹出层-->
		<div class="popups-wrapper popups-msg">
			<div class="popups-bg"></div>
			<div class="popups popups-tips">
				<div class="popups-title"><i class="iconfont icon-guanbi"></i></div>
				<div class="popups-content color-positive">
					<i class="iconfont icon-chenggong"></i>
					<p class="tips">投保成功</p>
				</div>
			</div>
		</div>
		<!--弹窗-->
		<div class="popups-wrapper popups-ask">
			<div class="popups-bg"></div>
			<div class="popups">
				<div class="popups-title"><i class="iconfont icon-guanbi"></i></div>
				<div class="popups-content">
					<p class="shop">为实现自动购保，给您的生活和工作带来各种保障，请开通免密支付功能</p>
				</div>
				<div class="popups-footer">
					<button id="do_authorize" type="button" class="btn btn-default confirm">去开通</button>
				</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
            var token = "{{$_GET['token']}}";
            var authorize_status = "{{$auto_insure_status?true:false}}";
            localStorage.setItem('token', token);

            window.onload = function(){
                $('.loading-wrapper').remove();
            };
            $('.head-right').on('tap',function () {

                location.href = "bmapp:homepage";return false;
            });
            $('.head-left').on('tap',function(){

                location.href = "bmapp:homepage";return false;
            });
            $('#claim_target').on('tap',function(){

            });
            $('#warranty_target').on('tap',function(){

            });
			$('#seting_target').on('tap',function(){

			});
            $('#insure_target').on('tap',function(){

            });
            $('#insure_no_target').on('tap',function(){

            });
            $('#insure_set_target').on('tap',function(){

            });
            $('#do_authorize').on('tap',function(){

                window.location.href = "{{config('view_url.channel_yunda_target_url')}}insure_authorize?token="+token;
            });
            $(function(){
                var hide = $('.hide');
                var btn_agree = $('#agree');
                var step = {
                    init: function(){
                        var _this = this;
                        $('.btn-unfold').click(function(){
                            hide.toggleClass('hide');
                            if(hide.hasClass('hide')){
                                $(this).find('.iconfont').removeClass('icon-xiangshangjiantou1').addClass('icon-xiajiantou');
                            }else{
                                $(this).find('.iconfont').removeClass('icon-xiajiantou').addClass('icon-xiangshangjiantou1');
                            }
                        });

                        $('#open').click(function(){
                            if($(this).hasClass('disabled')){
                                btn_agree.trigger('click');
                            }
                        });
                        _this.isAgree();
//						_this.getStatus();
						if(!authorize_status){
                            Popups.open('.popups-ask');
						}
                        $('#confirm').click(function(){
                            Popups.close('.popups-ask');
                        });
                        $('#cancel').click(function(){
                            Popups.close('.popups-ask');
                        });
                    },
                    isAgree: function(){
                        btn_agree.click(function(){
                            var _that = $(this);
                            _that.toggleClass('active');
                            var status = _that.hasClass('active') == true ? 1 : 0;
                            _that.prev().val(status);
                            $('#open').toggleClass('disabled');
//							$('#open').prop('disabled',!_that.hasClass('active'));
                        });
                    },
                    getStatus: function(){
                        var sign_status = "0";
                        if(!parseInt(sign_status)){
                            $(".mui-scroll-wrapper").css("cssText", "bottom:0!important;");
                        }
                    }
                }
                step.init();
            });
		</script>
	</body>
</html>