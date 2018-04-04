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
	<form action="{{config('view_url.channel_yunda_target_url')}}claim_send_email" method="post" id="claim_send_email" enctype="multipart/form-data">
		<input name="claim_id" type="hidden" value="{{$result->claim_id}}">
		<div class="main">
		<div class="info-wrapper">
				<div class="top">
					<h1 class="title">{{$result->product_name}}</h1>
					<img class="logo" src="{{config('view_url.channel_views')}}imges/logo.png" alt="">
				</div>
				<ul class="list">
					<li>被保人<span class="fr">{{$result->name}}</span></li>
					<li>保障期限<span class="fr">{{$result->start_time}}  -  {{$result->end_time}}</span></li>
					<li>保单号<span class="fr">{{$result->warranty_code}}</span></li>
					<li>保费<span class="fr">{{$result->premium / 100}}</span></li>
				</ul>
		</div>
    	<div class="main-content">
				<div class="formW formW1">
					<p class="text">诊断证明</p>
				</div>
				<div class="formW formW2">
					<img id="btn-front" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input id="front" name="proof" hidden onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
						<input id="frontVal" name="proof" hidden type="text"></input>
				</div>
				<div class="formW formW1">
					<p class="text">医疗发票</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input id="invoice" name="invoice" hidden onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
						<input id="invoiceVal" name="invoice" hidden type="text"></input>
				</div>
				<div class="formW formW1">
					<p class="text">费用清单</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input id="expenses" name="expenses" hidden onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
						<input id="expensesVal" name="expenses" hidden type="text"></input>
				</div>
				<div class="formW formW1">
					<p class="text">伤者身份证复印件</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="papers_code_img" name="papers_code_img" onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
						<input hidden id="papers_code_imgVal" name="papers_code_img" type="text"></input>
				</div>
				<div class="formW formW1">
					<p class="text">划款户名、帐号、开户行信息</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="account_info" name="account_info" onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="account_infoVal" name="account_info" type="text"></input>
				</div>
				<div class="formW formW1">
					<p class="text">交通事故责任认定书</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="accident_proof" name="accident_proof" onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="accident_proofVal" name="accident_proof" type="text"></input>
				</div>
				<div class="formW formW1">
					<p class="text">财产损失证明材料</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="proof_loss" name="proof_loss" onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="proof_lossVal" name="proof_loss" type="text"></input>
				</div>

				<div class="formW formW1">
					<p class="text">伤者相片-全身照</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="bruise_whole" name="bruise_whole" onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="bruise_wholeVal" name="bruise_whole" type="text"></input>
				</div>
				<div class="formW formW1">
					<p class="text">伤者相片-面部照</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="bruise_face" name="bruise_face" onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="bruise_faceVal" name="bruise_face" type="text"></input>
				</div>
				<div class="formW formW1">
					<p class="text">伤者相片-伤处照</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="bruise_wound" name="bruise_wound" onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="bruise_woundVal" name="bruise_wound" type="text"></input>
				</div>

				@if(strpos('-'.$result->claim_type,'2'))
					<div class="formW formW1">
						<p class="text">伤残证明</p>
					</div>
					<div class="formW formW2">
						<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
						<input hidden id="maim_proof" name="maim_proof" onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
						<input hidden id="maim_proofVal" name="maim_proof" type="text"></input>
					</div>
				@endif

				@if(strpos('-'.$result->claim_type,'3'))
					<div class="formW formW1">
						<p class="text">死亡证明</p>
					</div>
					<div class="formW formW2">
						<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
						<input hidden id="die_proof" name="die_proof" onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
						<input hidden id="die_proofVal" name="die_proof" type="text"></input>
					</div>
					<div class="formW formW1">
						<p class="text">受益人</p>
					</div>
					<div class="formW formW2">
						<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
						<input hidden id="beneficiary" name="beneficiary" onchange="upLoadImg(this);" accept="image/*" type="file" capture="camera" accept=".gif,.jpg,.jpeg,.png">
						<input hidden id="beneficiaryVal" name="beneficiary" type="text"></input>
					</div>
				@endif
    	</div>
   	</div>
    <button id="next" class="btn-next" disabled>确认修改</button>
	</form>
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
