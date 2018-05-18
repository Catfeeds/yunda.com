<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>理赔审核</title>
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/email.css" />
		<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
	</head>
	<body>
	<style>
		.img-wrapper img{width:100%;height:100%;}
	</style>
	<form action="{{config('view_url.channel_yunda_target_url')}}claim_audit" method="post" id="claim_audit" enctype="multipart/form-data">
	<div class="header">
			<div class="content">
				<img class="logo" src="{{config('view_url.channel_views')}}imges/yunda.png"/><h1 class="step">理赔审核</h1>
			</div>
		</div>
		<div class="content">
			<div class="section">
				<h2  class="title">快递保.意外险</h2>
				<ul class="info">
					<li><span class="name">保单号</span><span>{{$result->warranty_code}}</span></li>
					<li><span class="name">保障时间</span><span>{{$result->start_time}}   {{$result->end_time}}</span></li>
					<li><span class="name">保费</span><span>{{$result->premium / 100}} 元</span></li>
				</ul>
			</div>
			<div class="section">
				<h2 class="title-level2">出险人基本信息</h2>
				<ul class="info">
					<li><span class="name">姓名</span><span>{{$result->name}}</span></li>
					<li><span class="name">证件类型</span><span>身份证</span></li>
					<li><span class="name">证件号</span><span>{{$result->papers_code}}</span></li>
					<li><span class="name">联系人</span><span>{{$result->contact_name}}</span></li>
					<li><span class="name">手机号</span><span>{{$result->phone}}</span></li>
					<li><span class="name">开户行</span><span>工商银行</span></li>
					<li><span class="name">开户地</span><span>北京市昌平区回龙观</span></li>
					<li><span class="name">银行账户</span><span>6212260200145135626</span></li>
				</ul>
			</div>
			<div class="section">
				<h2 class="title-level2">出险信息</h2>
				<ul class="info">
					<li><span class="name">事故性质</span><span>交通事故</span></li>
					<li><span class="name">出险类型</span><span>意外门诊</span></li>
					<li><span class="name">出险时间</span><span>{{$result->ins_time}}</span></li>
					<li><span class="name">出险地点</span><span>{{$result->ins_address}}</span></li>
					<li><span class="name">手机号</span><span>{{$result->phone}}</span></li>
					<li><span class="name">经过描述</span><span>{{$result->ins_desc}}</span></li>
				</ul>
			</div>
			<div class="section" style="padding-bottom: 0;">
				<h2 class="title-level2">资料</h2>
				<ul class="img-wrapper">
					<li>
						<div style="background-image: url(imges/banner.png);">
							<img class="img" src="{{$file_url[$result->proof]}}">
						</div>
						<p>病历、诊断证明、出院记录等 医疗资料</p>
					</li>
					<li>
						<div class="img" style="background-image: url(imges/banner.png);">
							<img src="{{$file_url[$result->invoice]}}">
						</div>
						<p>医疗发票</p>
					</li>
					<li>
						<div class="img" style="background-image: url(imges/banner.png);">
							<img src="{{$file_url[$result->expenses]}}">
						</div>
						<p>费用清单</p>
					</li>
					<li>
						<div class="img" style="background-image: url(imges/banner.png);">
							<img src="{{$file_url[$result->papers_code_img]}}">
						</div>
						<p>伤者身份证复印件</p>
					</li>
					<li>
						<div class="img" style="background-image: url(imges/banner.png);">
							<img src="{{$file_url[$result->account_info]}}">
						</div>
						<p>划款户名、帐号、开户行信息</p>
					</li>
					<li>
						<div class="img" style="background-image: url(imges/banner.png);">
							<img src="{{$file_url[$result->accident_proof]}}">
						</div>
						<p>交通事故责任认定书</p>
					</li>
					<li>
						<div class="img" style="background-image: url(imges/banner.png);">
							<img src="{{$file_url[$result->proof_loss]}}">
						</div>
						<p>财产损失证明材料</p>
					</li>
					<li>
						<div class="img" style="background-image: url(imges/banner.png);">
							<img src="{{$file_url[$result->bruise_whole]}}">
						</div>
						<p>伤者相片-全身照</p>
					</li>
					<li>
						<div class="img" style="background-image: url(imges/banner.png);">
							<img src="{{$file_url[$result->bruise_face]}}">
						</div>
						<p>伤者相片-面部照</p>
					</li>
					<li>
						<div class="img" style="background-image: url(imges/banner.png);">
							<img src="{{$file_url[$result->bruise_wound]}}">
						</div>
						<p>伤者相片-伤处照</p>
					</li>
					@if(!empty($result->maim_proof))
						<li>
							<div class="img" style="background-image: url(imges/banner.png);">
								<img src="{{$file_url[$result->maim_proof]}}">
							</div>
							<p>伤残鉴定报告</p>
						</li>

					@endif
					@if(!empty($result->beneficiary))
						<li>
							<div class="img" style="background-image: url(imges/banner.png);">
								<img src="{{$file_url[$result->die_proof]}}">
							</div>
							<p>死亡证明</p>
						</li>
						<li>
							<div class="img" style="background-image: url(imges/banner.png);">
								<img src="{{$file_url[$result->beneficiary]}}">
							</div>
							<p>受益人</p>
						</li>
					@endif
				</ul>
			</div>
			<div class="section" style="padding: 10px;">
				<textarea class="remark" name="remark" placeholder="备注信息(选填)"></textarea>
			</div>
			<input type="hidden" name="status" id="status" value="">
			<input type="hidden" name="id" value="{{$result->id}}">
			<div class="btn-wrapper">
				<button type="button" onclick="auditSubmit(-1);" class="btn btn-warning submit">未通过审核</button>
				<button type="button" onclick="auditSubmit(1);" class="btn btn-primary submit">通过审核</button>
			</div>
			<div class="pop">
				<div class="pop-bg"></div>
				<div class="pop-container">
					<div><img class="pop-img" src="" alt="" /></div>
				</div>
			</div>
		</div>
	</form>
	<script src="{{config('view_url.channel_views')}}js/lib/jquery-1.11.3.min.js"></script>
	<script src="{{config('view_url.channel_views')}}js/lib/mui.min.js"></script>
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

        function auditSubmit(status){
            $('#status').val(status);
            $('#claim_audit').submit();
		}
        $('.img-wrapper li').click(function() {
            $('.pop').show()
            var url = $(this).find('img')[0].currentSrc
            $('.pop-img').attr('src',url)
        })

        $('.pop-bg').click(function() {
            $('.pop').hide()
        })
	</script>

	</body>
</html>
