@extends('backend_v2.layout.base')
@section('title')@parent 保单管理 @stop
@section('head-more')
	<link rel="stylesheet" href="{{asset('r_backend/v2/css/client_details.css')}}" />
@stop
@section('top_menu')
	<div class="nav-top-wrapper fl">
		<ul>
			<li class="active">
				<a href="{{url('/backend/policy/')}}" >个人保单</a>
			</li>
		</ul>
	</div>
@stop
@section('main')
	{{--$agent_res = [];//代理人--}}
	{{--$ditch_res = [];//渠道--}}
	{{--$product_res = [];//产品--}}
	{{--$cust_policy_res = CustWarrantyPolicy::where('warranty_uuid',$warranty_uuid)->get();--}}
	{{--$policy_res = [];//投保人--}}
	{{--$insured_res = [];//被保人--}}
	{{--$beneficiary_res = [];//受益人--}}
		<div class="main-wrapper policy">
			<div class="row">
				<ol class="breadcrumb col-lg-12">
				    <li><a href="#">保单管理</a><i class="iconfont icon-gengduo"></i></li>
				    <li><a href="#">个人保单</a><i class="iconfont icon-gengduo"></i></li>
				</ol>
			</div>
			<div class="row">
				<div class="col-md-5">
					<div class="policy-content">
						<div class="policy-wrapper scroll-pane">
							@if(!empty($product_res)&&!empty($warranty_res))
								<div class="policy-info">
									<h3 class="title">{{$product_res['product_name']}}</h3>
									<p>保单号：{{$warranty['warranty_code']}}</p>
								</div>
							@endif
							<div class="policy-list">
								@if(isset($company_res)&&!empty($company_res))
								<div><span class="name">公司名称</span><i>:</i>{{$company_res['name']}}</div>
								<div><span class="name">保险险种</span><i>:</i>{{$company_res['category']['name']}}</div>
								<div><span class="name vtop">条款</span><i class="vtop">:</i>
									@foreach($company_res['clauses'] as $v)
										<li><a href="{{env('TY_API_PRODUCT_SERVICE_URL').'/'.$v['file_url']}}">{{$v['name']}}</a></li>
									@endforeach
								</div>

								<div><span class="name vtop">责任保额</span><i class="vtop">:</i>
									@php
									$i = 1;
									@endphp
									<ul>
										@foreach($json['clauses'] as $ck => $cv)
											@foreach($cv['duties'] as $dk => $dv)
												<li>
													<span class="duty-name" >{{ $dv['name'] }}</span>
													{{ preg_match('/^\d{5,}$/', $dv['pivot']['coverage_jc']) ?  $dv['pivot']['coverage_jc'] / 10000 . '万元' : $dv['pivot']['coverage_jc'] }}
												</li>
												@php
												$i++;
												@endphp
											@endforeach
										@endforeach
									</ul>
								</div>

								{{--<div><span class="name">特别约定</span><i>:</i>保障期内在发生任何风险都不进行赔偿。</div>--}}
								<div><span class="name">受益人</span><i>:</i>{{isset($beneficiary_res['name']) ? $beneficiary_res['name'] : "法定受益人" }}</div>
								<div><span class="name">保 费</span><i>:</i>{{ceil($warranty_res['premium']/100)}}元</div>
								<div><span class="name">缴费方式</span><i>:</i>{{  $warranty_res->by_stages_way=='0年' ? '趸交' : '年缴' }}</div>
								<div><span class="name">缴费期限</span><i>:</i>{{isset($warranty_res->by_stages_way) ? $warranty_res->by_stages_way : "0年" }}</div>
								<div><span class="name">佣金</span><i>:</i>{{isset($warranty_res->brokerage) ? ceil($warranty_res->brokerage/100)."元" : "--"  }}</div>
								<div><span class="name">渠道</span><i>:</i>{{isset($ditch_res->name) ? "$ditch_res->name" : "" }}</div>
								<div><span class="name">渠道佣金</span><i>:</i>{{ceil($warranty_res['premium']/100*$product_res['base_ratio']/100)}}元</div>
								<div><span class="name">代理人</span><i>:</i>{{isset($agent_res->real_name) ? "$agent_res->real_name" : "--"}}
									{{--<a href="#" style="margin-left: 20px;">更换代理人</a>--}}
								</div>
								{{--<div><span class="name">代理人佣金</span><i>:</i>400元</div>--}}
								<div><span class="name">保单状态</span><i>:</i><span class="color-default">
										@if($warranty_res->status == 1)
											已支付
										@elseif($warranty_res->status == 2)
											未支付
										@elseif($warranty_res->status == 3)
											支付失败
										@elseif($warranty_res->status == 4)
											支付中
										@elseif($warranty_res->status == 6)
											核保错误
										@elseif($warranty_res->status == 7)
											取消支付
										@endif
									</span></div>
							</div>
							@endif
						</div>

					</div>
				</div>
				<div class="col-md-7">
					@if(isset($policy_res)&&!empty($policy_res))
					<div class="info-wrapper active">
						<i class="iconfont icon-zhankai"></i>
						<button class="btn btn-primary">投保人</button>
						<div class="info-img col-xs-2">
							<img src="{{ asset('/r_backend/v2/img/girl.png')}}" alt="" />
						</div>
						<div class="col-xs-10" style="width: 60%;">
							<p class="info-name">{{$policy_res['name']}}<span class="color-primary"><i class="iconfont icon-shiming"></i>已实名</span></p>
						</div>
						<div class="info-datails">
							<div class="col-xs-6 col-md-4">
								<p><span class="name">性 别</span><i>:</i></p>
								<p><span class="name">证件类型</span><i>:</i>身份证</p>
								<p><span class="name">渠道</span><i>:</i>{{isset($agent_res->name) ? "$agent_res->name" : "" }}</p>
								<p><span class="name">购买保障</span><i>:</i>20种</p>
								<p><span class="name">职业类别</span><i>:</i>公司企业一般行政人员</p>
							</div>
							<div class="col-xs-6 col-md-5">
								<p><span class="name">手机号码</span><i>:</i>{{$policy_res['phone']}}</p>
								<p><span class="name">证件号码</span><i>:</i>{{$policy_res['code']}}</p>
								<p><span class="name">邮箱</span><i>:</i>{{$policy_res['email']}}</p>
								<p><span class="name">保费总计</span><i>:</i>30000元</p>
							</div>
						</div>
					</div>
					@endif
					@if(isset($recognizees_res)&&!empty($recognizees_res))
						@foreach($recognizees_res as $value)
					<div class="info-wrapper">
						<i class="iconfont icon-zhankai"></i>
						<button class="btn btn-warning">被保人</button>
						<div class="info-img col-xs-2">
							<img src="{{ asset('/r_backend/v2/img/girl.png')}}" alt="" />
						</div>
						<div class="col-xs-10" style="width: 60%;">
							<p class="info-name">{{isset($value->name) ? $value->name : "" }}<span class="color-primary"><i class="iconfont icon-shiming"></i>已实名</span></p>
						</div>
						<div class="info-datails">
							<div class="col-xs-6 col-md-4">
								<p><span class="name">性 别</span><i>:</i></p>
								<p><span class="name">证件类型</span><i>:</i>身份证</p>
								<p><span class="name">渠道</span><i>:</i>{{isset($value->name) ?  isset($ditches->name) ? "$ditches->name" : "": "" }}</p>
								<p><span class="name">购买保障</span><i>:</i>20种</p>
								<p><span class="name">职业类别</span><i>:</i>公司企业一般行政人员</p>
							</div>
							<div class="col-xs-6 col-md-5">
								<p><span class="name">手机号码</span><i>:</i>{{isset($value->phone) ? $value->phone : "$policy->phone" }}</p>
								<p><span class="name">证件号码</span><i>:</i>{{isset($value->code) ? $value->phone : "$policy->code" }}</p>
								<p><span class="name">邮箱</span><i>:</i>{{isset($value->email) ? $value->phone : "$policy->email" }}</p>
								<p><span class="name">保费总计</span><i>:</i>30000元</p>
							</div>
						</div>
					</div>
							@endforeach
						@endif
						@if(isset($beneficiar_res)&&!empty($beneficiar_res))
					<div class="info-wrapper">
						<i class="iconfont icon-zhankai"></i>
						<button class="btn btn-info">受益人</button>
						<div class="info-img col-xs-2">
							<img src="{{ asset('/r_backend/v2/img/girl.png')}}" alt="" />
						</div>
						<div class="col-xs-10" style="width: 60%;">
							<p class="info-name">{{isset($beneficiar_res['name']) ? $beneficiar_res['name'] : "" }}<span class="color-primary"><i class="iconfont icon-shiming"></i>已实名</span></p>
						</div>
						<div class="info-datails">
							<div class="col-xs-6 col-md-4">
								<p><span class="name">性 别</span><i>:</i></p>
								<p><span class="name">证件类型</span><i>:</i>身份证</p>
								<p><span class="name">渠道</span><i>:</i>{{isset($ditch_res->name) ? "$ditch_res->name" : "" }}</p>
								<p><span class="name">购买保障</span><i>:</i>20种</p>
								<p><span class="name">职业类别</span><i>:</i>公司企业一般行政人员</p>
							</div>
							<div class="col-xs-6 col-md-5">
								<p><span class="name">手机号码</span><i>:</i>{{isset($beneficiar_res->phone) ? $beneficiar_res->phone : "无" }}</p>
								<p><span class="name">证件号码</span><i>:</i>{{isset($beneficiar_res->code) ? $beneficiar_res->code : "无" }}</p>
								<p><span class="name">邮箱</span><i>:</i>{{isset($beneficiar_res->email) ? $beneficiar_res->email : "无" }}</p>
								<p><span class="name">保费总计</span><i>:</i>{{$beneficiar_res->name}}元</p>
							</div>
						</div>
					</div>
						@endif
					<div class="record">操作记录</div>
					<div class="record-list">
						<ul>
							<li>2017-03-02 11:52:50<span>创建订单</span>创建订单成功</li>
							<li>2017-03-02 11:52:50<span>创建订单</span>创建订单成功</li>
							<li>2017-03-02 11:52:50<span>创建订单</span>创建订单成功</li>
							<li>2017-03-02 11:52:50<span>创建订单</span>创建订单成功</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<script src="{{asset('r_backend/v2/js/lib/jquery-1.11.3.min.js')}}"></script>
		<script src="{{asset('r_backend/v2/js/common_backend.js')}}"></script>
		<script>
			$(".duty-list").panel({iWheelStep:32});

			$('.icon-zhankai').click(function(){
				var _this = $(this);
				_this.parent().addClass('active').find('.info-datails').show();
				_this.parent().siblings().removeClass('active').find('.info-datails').hide();
			});
		</script>
@stop
