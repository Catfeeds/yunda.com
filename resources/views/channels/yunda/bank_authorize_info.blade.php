<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>协议扣款授权书</title>
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/mui.min.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/lib/iconfont.css">
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/common.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/index.css" />
		<link rel="stylesheet" href="{{config('view_url.channel_views')}}css/step.css" />
		<script src="{{config('view_url.channel_views')}}js/baidu.statistics.js"></script>
		<style>
			.mui-scroll-wrapper{
				bottom: 0!important;
			}
			.content{
				padding: .2rem;
				background: #fff;
				line-height: 1.4;
				font-size: .28rem;
			}
			.content p{
				margin-bottom: .2rem;
			}
			#process1 .list li{
				height: .6rem;
				line-height: .6rem;
			}
		</style>
	</head>
	<body id="process1">
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
				<span>协议扣款授权书</span>
			</div>
		</header>
		<div class="process1">
			<div class="mui-scroll-wrapper">
				<div class="mui-scroll">
					<div class="info">
						<ul class="list">
							<li>甲    方:<span class="fr">英大泰和财产保险股份有限公司</span></li>
							<li>乙    方:<span class="fr">{{$insured_name}}</span></li>
						</ul>
						<div class="header">
							<h1 class="title">授权人账户信息</h1>
						</div>
						<ul class="list">
							<li>持卡人姓名<span class="fr">{{$insured_name}}</span></li>
							<li>持卡人联系方式<span class="fr">{{$insured_phone}}</span></li>
							<li>持卡人身份证号<span class="fr">{{$insured_code}}</span></li>
							<li>银行名称<span class="fr">{{$bank_name}}</span></li>
							<li>银行卡号<span class="fr">{{$bank_code}}</span></li>
							<li>扣款金额<span class="fr">RMB 2元</span></li>
						</ul>
					</div>
					<div class="content">
						<p>此协议用于韵达快递员于“韵镖侠app”中投保甲方保险产品过程中的保费代扣业务。</p>
						<p>乙方保证以上“授权账户信息”中的身份及所提供的信息真实、有效、准确及合法，因乙方身份或所提供信息错误而引起的法律后果及损失，由乙方自行承担。乙方同意甲方通过易宝支付有限公司协议扣款支付平台，从乙方的银行账户中将款项划拨到甲方账户。</p>
						<p>乙方应当按照预付款的金额在上述银行卡内存入足够的款项，甲方工作人员根据乙方预付款金额办理相关扣款转账手续；由于挂失、账户冻结、金额不足等原因造成扣款失败而导致乙方损失的，由乙方自行承担。</p>
						<p>乙方变更付款授权账户时，须及时通知甲方，并按更换后的账户信息重新签署授权书。因乙方未及时办理变更手续而导致的结果，由乙方承担。</p>
						<p>授权人确认并同意：支付机构不对其根据本授权书所做的操作结果作任何承诺或保证。</p>
						<p>本授权书项下的资金代扣及转账操作相关的任何责任，与支付机构无关，因此产生的后果由授权人承担。</p>
						<p>本授权书为授权人对支付机构从其代扣账户中扣款或转账的授权证明，不作为收付现金的凭据。</p>
						<p>此协议自系统签订日起开始生效，默认为1年有效期。一年后，需重新签订此协议，才能继续生效。</p>
					</div>
				</div>
			</div>
		</div>
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
		</script>
	</body>
</html>