<!DOCTYPE html>
<html>

	<head>
		<meta charset="utf-8">
		<title>自动投保设置</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
	</head>
	<body id="process11">
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
				<span>自动投保设置</span>
			</div>
		</header>
		<div class="step3">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div class="section section-first">
						<div class="section-content">
							<span class="title">自动投保</span>

							<i class="tip"  @if(!$auto_res['auto_insure_status']) style="visibility: visible;" @endif>
								开关将于{{date('Y-m-d H:i:s',$auto_res['auto_insure_time']+3600*24)}}自动开启
							</i>
								<div class="switch @if($auto_res['auto_insure_status']) active @endif">
									<div class="switch-handle"></div>
								</div>
						</div>
						<div class="tit">自动投保方式</div>
						<ul class="protect">
							<li class="item  @if(!$auto_res['auto_insure_type']||$auto_res['auto_insure_type']=='1') activeauto @endif" data-id="1">
								<span>2元/次</span>
								<span>保障期为当天</span>
								<input hidden type="radio"  value="1" />
							</li>
							<li class="item @if($auto_res['auto_insure_type']=='3') activeauto @endif" data-id="3">
								<span>5元/次</span>
								<span>保障期为3天</span>
								<input hidden type="radio"  value="2" />
							</li>
							<li class="item @if($auto_res['auto_insure_type']=='10') activeauto @endif" data-id="10">
								<span>13元/次</span>
								<span>保障期为10天</span>
								<input  hidden type="radio"  value="3" />
							</li>
						</ul>
						<span class="tips">注：每次上工时根据自动投保的保障期为您投保。多天保障期内不会重复投保。
						<p><b>"3天期保障"和"10天期保障"只针对银行卡免密支付生效。</b></p>
						</span>
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
					<p>关闭功能将无法购买保险，是否确认</p>
				</div>
				<div class="popups-footer">
					<button id="confirm" type="button" class="btn btn-default">确定</button>
					<button id="cancel" type="button" class="btn btn-default">我再想想</button>
				</div>
			</div>
		</div>
		<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
		<script src="{{config('view_url.channel_views')}}js/common.js"></script>
		<script>
            $('.head-right').on('tap',function(){
                Mask.loding();
                location.href="bmapp:homepage";
            });
            $('.head-left').on('tap',function(){
                Mask.loding();
                window.history.go(-1);
            });
			$(function() {
			    var cust_id = '1';
				var btn_switch = $('.switch');
                var item = $('.protect').find('.item.active');
				var step3 = {
					init: function() {
						var _this = this;
						_this.isAutoInsure();
						_this.choose();
					},
					isAutoInsure: function() {
						btn_switch.on('tap', function() {
                            var auto_insure_type =$('.protect').find('.item.activeauto').attr('data-id');
                            var person_code = "{{$person_code}}";
                            var auto_insure_status = "{{$auto_res['auto_insure_status']}}";
							var _that = $(this);
							_that.hasClass('active') == true ?
								Popups.open('.popups-ask') :
								btn_switch.addClass('active').find('input').val(1);
							console.log();
							if(auto_insure_status=='0'||auto_insure_status!='1'||auto_insure_status==0){
                                $.ajax({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    url: "{{config('view_url.channel_yunda_target_url')}}do_insure_auto",
                                    type: "post",
                                    data: {
                                        'person_code': person_code,
                                        'auto_insure_status': '1',
                                        'auto_insure_type': auto_insure_type
                                    },
                                    dataType: "json",
                                    success: function (data) {
                                        Mask.alert(data.msg, 3);
                                        setTimeout(function(){//两秒后跳转
                                            window.location.reload();
                                        },2000);
                                    }
                                });
							}
						});
						$('#confirm').click(function() {
							btn_switch.removeClass('active').find('input').val(0);
                            var auto_insure_type =$('.protect').find('.item.activeauto').attr('data-id');
                            var person_code = "{{$person_code}}";
                            var auto_insure_status = "{{$auto_res['auto_insure_status']}}";
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                url: "{{config('view_url.channel_yunda_target_url')}}do_insure_auto",
                                type: "post",
                                data: {'person_code':person_code,'auto_insure_status':'0','auto_insure_type':auto_insure_type},
                                dataType: "json",
                                success: function (data) {
                                    Mask.alert(data.msg,3);
                                    setTimeout(function(){//两秒后跳转
                                        window.location.reload();
                                    },2000);
                                }
                            });
							Popups.close('.popups-ask');
							$('.tip').css('visibility','visible')
						});
						$('#cancel').click(function() {
							Popups.close('.popups-ask');
						});
					},
					choose:function(){
						var item=$('.protect').find('.item');
						for(var i=0;i<item.length;i++){
							$(item[i]).click(function(){
								$(this).addClass("activeauto").siblings().removeClass("activeauto");
								$(this).find('input').prop('checked',true)
							})
						}
					}
				}
				step3.init();
			});
		</script>
	</body>
</html>