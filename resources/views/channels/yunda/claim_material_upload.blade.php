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
	<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
</head>
<body id="process8">
<div style="width:100%;height:100%;" id="defuTimes">
	<header class="mui-bar mui-bar-nav">
		<div class="head-left">
			<div class="head-img">
				<i class="iconfont icon-fanhui"></i>
			</div>
		</div>
		<div class="head-right">
			<i class="iconfont icon-close"></i>
		</div>
		<div class="head-title">
			<span>提交材料</span>
		</div>
	</header>
	@if(!empty($result))
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
					<li>保障期限<span class="fr">{{date('Y-m-d', $result->start_time/1000)}}  ~  {{date('Y-m-d',$result->end_time/1000)}}</span></li>
					{{--<li>保单号<span class="fr">{{$result->warranty_code}}</span></li>--}}
					<li>保单号<div class="fr" style="width:80%;word-wrap:break-word;line-height:2rem;">{{$result->warranty_code}}</div></li>
					<li>保费<span class="fr">{{$result->premium}}元</span></li>
				</ul>
			</div>
			<div class="main-content">
				<div class="formW formW1">
					<p class="text">诊断证明</p>
				</div>
				<div class="formW formW2">
					<img id="btn-front" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input id="fronf"  hidden onchange="upLoadImg(this);" accept="image/*" type="file"  accept=".gif,.jpg,.jpeg,.png">
					<input id="frontVal" name="proof" type="hidden">
				</div>
				<div class="formW formW1">
					<p class="text">医疗发票</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input id="invoice" hidden onchange="upLoadImg(this);" accept="image/*" type="file" accept=".gif,.jpg,.jpeg,.png">
					<input id="invoiceVal" name="invoice" type="hidden">
				</div>
				<div class="formW formW1">
					<p class="text">费用清单</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input id="expenses" hidden onchange="upLoadImg(this);" accept="image/*" type="file"  accept=".gif,.jpg,.jpeg,.png">
					<input id="expensesVal" name="expenses" type="hidden">
				</div>
				<div class="formW formW1">
					<p class="text">伤者身份证复印件</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="papers_code_img"  onchange="upLoadImg(this);" accept="image/*" type="file" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="papers_code_imgVal" name="papers_code_img" type="hidden">
				</div>
				<div class="formW formW1">
					<p class="text">划款户名、帐号、开户行信息</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="account_info" onchange="upLoadImg(this);" accept="image/*" type="file" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="account_infoVal" name="account_info" type="hidden">
				</div>
				<div class="formW formW1">
					<p class="text">交通事故责任认定书</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="accident_proof" onchange="upLoadImg(this);" accept="image/*" type="file" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="accident_proofVal" name="accident_proof" type="hidden">
				</div>
				<div class="formW formW1">
					<p class="text">财产损失证明材料</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="proof_loss"  onchange="upLoadImg(this);" accept="image/*" type="file" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="proof_lossVal" name="proof_loss" type="hidden">
				</div>

				<div class="formW formW1">
					<p class="text">伤者相片-全身照</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="bruise_whole" onchange="upLoadImg(this);" accept="image/*" type="file" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="bruise_wholeVal" name="bruise_whole" type="hidden">
				</div>
				<div class="formW formW1">
					<p class="text">伤者相片-面部照</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="bruise_face" onchange="upLoadImg(this);" accept="image/*" type="file" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="bruise_faceVal" name="bruise_face" type="hidden">
				</div>
				<div class="formW formW1">
					<p class="text">伤者相片-伤处照</p>
				</div>
				<div class="formW formW2">
					<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
					<input hidden id="bruise_wound" onchange="upLoadImg(this);" accept="image/*" type="file" accept=".gif,.jpg,.jpeg,.png">
					<input hidden id="bruise_woundVal" name="bruise_wound" type="hidden">
				</div>

				@if(strpos('-'.$result->claim_type,'2'))
					<div class="formW formW1">
						<p class="text">伤残证明</p>
					</div>
					<div class="formW formW2">
						<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
						<input hidden id="maim_proof" onchange="upLoadImg(this);" accept="image/*" type="file" accept=".gif,.jpg,.jpeg,.png">
						<input hidden id="maim_proofVal" name="maim_proof" type="hidden">
					</div>
				@endif

				@if(strpos('-'.$result->claim_type,'3'))
					<div class="formW formW1">
						<p class="text">死亡证明</p>
					</div>
					<div class="formW formW2">
						<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
						<input hidden id="die_proof" onchange="upLoadImg(this);" accept="image/*" type="file" accept=".gif,.jpg,.jpeg,.png">
						<input hidden id="die_proofVal" name="die_proof" type="hidden">
					</div>
					<div class="formW formW1">
						<p class="text">受益人</p>
					</div>
					<div class="formW formW2">
						<img id="btn-contrary" src="{{config('view_url.channel_views')}}imges/add.png" alt="" />
						<input hidden id="beneficiary"  onchange="upLoadImg(this);" accept="image/*" type="file" accept=".gif,.jpg,.jpeg,.png">
						<input hidden id="beneficiaryVal" name="beneficiary" type="hidden">
					</div>
				@endif
			</div>
		</div>
		<button id="next" class="btn-next" disabled>确认提交</button>
	</form>
	@endif
</div>
<script type="text/javascript" src="{{config('view_url.channel_views')}}js/jquery-1.10.2.min.js"></script>
<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
<script type="text/javascript">
    $('body').on('click','.formW2 img',function(){
        $(this).parent().find('input').eq(0).click();
    });
    // 上传照片
    var upLoadImg = function(e){
        var _this = $(e).parent();
        var $c = _this.find('input[type=file]')[0];
        var file = $c.files[0],reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onload = function(e){
            var event = this;
            var $targetEle = _this.find('input:hidden').eq(1);

            var img_base64 = event.result;
            img_base64 =img_base64.replace(/^(data:\s*image\/(\w+);base64,)/,'');
            var file_name = 'Yunda-claim-'+"{{$result->claim_id??""}}"+"-"+$targetEle.attr('name');
            var url = "{{config('yunda.file_url')}}file/upBase";


            //使用新线程
            var worker = new Worker("{{config('view_url.channel_views')}}js/worker_upload.js");

            var data = {'base64':img_base64,'file_name':file_name,'url':url};

            worker.postMessage(data);

            worker.onmessage = function(evt){
                console.log();
                if(evt.data.code == 200){
                    _this.find('img').attr('src',e.target.result).css({'width':'11rem','height':'7rem'});
                    $targetEle.val(e.target.result);
                    $targetEle.val(file_name);
                    $('#next').prop('disabled',false);
                }else{
                    console.log(JSON.stringify(evt.data));
                    alert('文件上传失败,请重新上传');
                }
                worker.terminate();
            }
        };
    };

    $('.head-right').on('tap',function () {
        location.href = "bmapp:homepage";return false;
    });
    $('.head-left').on('tap',function(){
        history.back(-1);return false;
    });
</script>
</body>
</html>
