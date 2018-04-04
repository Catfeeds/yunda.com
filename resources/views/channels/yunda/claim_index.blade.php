<!DOCTYPE HTML>
<html>
<head>
	<title>自助理赔申请</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=5.0" charset="UTF-8">
	<link href="{{config('view_url.channel_url')}}css/service.css" rel="stylesheet"/>
	<link href="{{config('view_url.channel_url')}}css/mobile-select-area.css" rel="stylesheet"/>
	<link href="{{config('view_url.channel_url')}}css/claim.css" rel="stylesheet"/>
	<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
</head>
<body style=" background-color:#f5f5f5;">
	<div class="header">
    	自助理赔申请
        <img src="{{config('view_url.channel_url')}}imges/arrow-left.png" class="arrow-left2" onclick="back();">
        <img src="{{config('view_url.channel_url')}}imges/home.png" class="home" onclick="close_windows();"	>
    </div>
	<div id='mySwipe' class='main swipe'>
	    <div class='swipe-wrap' style="display:-webkit-box; display: -moz-box; display: -ms-box;display: -o-box;display: box;">
	        <div style="float:left;"><img src="{{config('view_url.channel_url')}}imges/serviceIndexLunbo01.png" width="100%" alt=""></div>
	        <div style="float:left;"><img src="{{config('view_url.channel_url')}}imges/serviceIndexLunbo02.png" width="100%" alt=""></div>
	    </div>
	    <ul class='iosSliderButtons'>
	        <li class='ios_button selected'></li>
	        <li class='ios_button'></li>
	    </ul>
	</div>
	<!--新增内容start-->
	<div class="tips-wrapper"><i class="iconfont icon-cuowu"></i>请在事故发生72小时内报案，逾期不可申请理赔</div>
	<!--新增内容end-->
	<div id="select-date" class="list margin-t" >
		<div class="listL">
			<img src="{{config('view_url.channel_url')}}imges/serviceIndex02.png" class="listImg">
			<div class="listText">
				<P class="t-ora ulev2 fw">我要理赔</P>
				<P class="t-6 ulev-1 margin-t">理赔报案  提交材料</P>
			</div>
		</div>
		<div class="listR"><img src="{{config('view_url.channel_url')}}imges/serviceIndex08.png" class="serviceIndex08"></div>
	</div>
	<div class="list margin-t">
		<div class="listL">
			<img src="{{config('view_url.channel_url')}}imges/serviceIndex04.png" class="listImg">
			<div class="listText">
				<P class="t-ora ulev2 fw">理赔记录查询</P>
				<P class="t-6 ulev-1 margin-t">进度查询  历史查询</P>
			</div>
		</div>
		<div class="listR"><img src="{{config('view_url.channel_url')}}imges/serviceIndex08.png" class="serviceIndex08"></div>
	</div>
	@include('frontend.channels.insure_alert')
	<script type="text/javascript" src="{{config('view_url.channel_url')}}js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="{{config('view_url.channel_url')}}js/swipe.js"></script>
	<script type="text/javascript" src="{{config('view_url.channel_url')}}js/main.js"></script>
	<script type="text/javascript" src="{{config('view_url.channel_url')}}js/dialog.js"></script>
	<script  type="text/javascript">
		var person_code = "{{$person_code??'1'}}";
		$(function() {
			scrollindex(); //首页轮播图
		});
		$('#insureWin').click(function(){
			var warranty_code = $('#date').val();
			location.href = '/channelsapi/claim_notice/'+warranty_code;
		});
        $('#insureBack').click(function(){
            $('#claim-alertwin').hide();
        });
		$('#select-date').click(function(){
			//$('#claim-alertwin').show();
            //Mask.loding();
            location.href = '/api/channels/yunda/claim_user?warranty_id=1';

		})
		function scrollindex() {
			var elem = document.getElementById('mySwipe');
			window.mySwipe = Swipe(elem, {
				auto: 4000,
				speed: 1000,
				continuous: true,
				disableScroll: false,
				stopPropagation: false,
				callback: function(index, element) {
					index = index % 2;
					$('ul li').eq(index).addClass('selected').siblings().removeClass('selected')
				}
			});
		}
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
