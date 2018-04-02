<!DOCTYPE HTML>
<html>
<head>
<title>提交材料</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=5.0" charset="UTF-8">
<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
<link href="{{config('view_url.channel_views')}}css/service.css" rel="stylesheet"/>
<link href="{{config('view_url.channel_views')}}css/claim.css" rel="stylesheet"/>
<link href="{{config('view_url.channel_views')}}css/step.css" rel="stylesheet"/>
	<script src="{{config('view_url.channel_url')}}js/baidu.statistics.js"></script>
</head>
<body id="process8">  
<div style="width:100%;height:100%;" id="defuTimes">
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
				<span>提交材料</span>
			</div>
		</header>
    <div class="main">
    	<div class="info-wrapper">
				<div class="top">
					<h1 class="title">英大非机动车驾驶员意外险</h1>
					<img class="logo" src="{{config('view_url.channel_views')}}imges/logo.png" alt="">
				</div>
				<ul class="list">
					<li>被保人<span class="fr">王大力</span></li>
					<li>保障期限<span class="fr">1天</span></li>
					<li>保单号<span class="fr">41785452123654</span></li>
					<li>保费<span class="fr">2元</span></li>
				</ul>
			</div>
    	<div class="main-content">
		    <div class="formW formW1">
		    	<p class="text">上传有效的资料(1)</p>
		    </div>
		    <div class="formW formW2">
		    	<img id="btn-front" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
		    	<input id="front" hidden onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
					<input id="frontVal" hidden type="text"></input>
		    </div>
		    <div class="formW formW1">
		    	<p class="text">上传有效的资料(2)</p>
		    </div>
		    <div class="formW formW2">
		    	<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
		    	<input id="contrary" hidden onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
					<input id="contraryVal" hidden type="text"></input>
		    </div>
		    <div class="formW formW1">
		    	<p class="text">上传有效的资料(3)</p>
		    </div>
		    <div class="formW formW2">
		    	<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
		    	<input  hidden onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
					<input hidden type="text"></input>
		    </div>
		    <div class="formW formW1">
		    	<p class="text">上传有效的资料(4)</p>
		    </div>
		    <div class="formW formW2">
		    	<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
		    	<input hidden onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
					<input hidden type="text"></input>
		    </div>
    	</div>
   	</div>
    <button id="next" class="btn-next" disabled>确认修改</button>
</div>
<script type="text/javascript" src="{{config('view_url.channel_views')}}js/jquery-1.10.2.min.js"></script>
<script type="text/javascript">
   $('body').on('click','.formW2 img',function(){
    	$(this).parent().find('input').eq(0).click();
    })
    // 上传照片
  var num = 0;
	var upLoadImg = function(e){
		var _this = $(e).parent();
		var $c = _this.find('input[type=file]')[0];
		var file = $c.files[0],reader = new FileReader();
	    reader.readAsDataURL(file);
	    reader.onload = function(e){
	    	num++
	    	_this.find('img').attr('src',e.target.result).css({'width':'11rem','height':'7rem'});
	    	var $targetEle = _this.find('input:hidden').eq(1);
	    	$targetEle.val(e.target.result);
	    	if(num>0){
	    		$('#next').prop('disabled',false);
	    	}
		};
	};
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
