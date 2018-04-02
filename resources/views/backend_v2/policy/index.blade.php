@extends('backend_v2.layout.base')
@section('title')@parent 保单管理 @stop
@section('head-more')
	<link rel="stylesheet" href="{{asset('r_backend/v2/css/agent.css')}}" />
	<link rel="stylesheet" href="{{asset('r_backend/v2/css/download.css')}}" />
@stop
@section('top_menu')
	@if(Auth::guard('admin')->user()->email==config('manager_account.manager'))
	<div class="nav-top-wrapper fl">
		<ul>
			<li class="active">
				<a href="{{url('/backend/policy/')}}" >个人保单</a>
			</li>
			<li>
				<a href="{{url('/backend/policy/policy_company/')}}">企业保单</a>
			</li>
			<li>
				<a href="{{url('/backend/policy/policy_offline/')}}">线下保单</a>
			</li>
		</ul>
	</div>
	@endif
@stop
@section('main')
		<div id="product" class="main-wrapper">
			<div class="row">
				<div class="select-wrapper radius">
					<form role="form" class="form-inline radius" >
						{{--<form role="form" class="form-inline radius" action="/backend/order" method="post" id="form">--}}
						{{--{{ csrf_field() }}--}}
						<div class="form-group">
							<div class="select-item">
								<label for="name">保单状态:</label>
								<select class="form-control" id="search_status">
									<option selected value="-1">全部保单</option>
									<option value="1" @if(isset($status_id) && $status_id == 1) selected @endif>保障中</option>
									<option value="2" @if(isset($status_id) && $status_id == 2) selected @endif>失效</option>
									<option value="0" @if(isset($status_id) && $status_id == 0 && $status_id != "") selected @endif>待生效</option>
									<option value="3" @if(isset($status_id) && $status_id == 3) selected @endif>退保</option>
								</select>
							</div>
							<label><span class="btn-select  @if(!isset($_GET['date'])||isset($_GET['date'])&&$_GET['date']=='0') active @endif">全部<input hidden type="radio" name="date" value="0" onclick="selDay(0)"/></span></label>
							<label><span class="btn-select  @if(isset($_GET['date'])&&$_GET['date']=='1') active @endif">今日<input hidden type="radio" name="date" value="1" onclick="selDay(1)"/></span></label>
							<label><span class="btn-select @if(isset($_GET['date'])&&$_GET['date']=='-1') active @endif">昨日<input hidden type="radio" name="date" value="2" onclick="selDay(-1)" /></span></label>
							<label><span class="btn-select @if(isset($_GET['date'])&&$_GET['date']=='7') active @endif">最近7天<input hidden type="radio" name="date" value="3" onclick="selDay(7)" /></span></label>
							<label><span class="btn-select @if(isset($_GET['date'])&&$_GET['date']=='30') active @endif">最近30天<input hidden type="radio" name="date" value="4" onclick="selDay(30)" /></span></label>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<div class="select-item">
								<label for="name">保单来源:</label>
								<select class="form-control" id="search_type">
									<option selected value="-2">全部来源</option>
									<option value="1" @if(isset($deal_type) && $deal_type == 1) selected @endif>线下成交</option>
									<option value="0" @if(isset($deal_type) && $deal_type == 0 && $deal_type != "") selected @endif>线上成交</option>
								</select>
							</div>
							<li class="date-picker" style="display: inline-block;">
								<div class="input-group date form_date form_date_start">
									<input id="date_start" class="form-control" type="text" value="@if(isset($_GET['date_start'])&&!empty($_GET['date_start'])) {{$_GET['date_start']}}@elseif(!empty($start)){{$start}}@endif" placeholder="@if(isset($_GET['date_start'])&&!empty($_GET['date_start'])) {{$_GET['date_start']}} @elseif(!empty($start)){{$start}}@else年/月/日@endif">
									<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
								&nbsp;&nbsp;&nbsp;
								<i class="">至</i>
								<div class="input-group date form_date form_date_end">
									<input id="date_end" class="form-control" type="text" value="@if(isset($_GET['date_end'])&&!empty($_GET['date_end'])) {{$_GET['date_end']}}@elseif(!empty($end)){{$end}}@endif" placeholder="@if(isset($_GET['date_end'])&&!empty($_GET['date_end'])){{$_GET['date_end']}}@elseif(!empty($end)){{$end}}@else年/月/日@endif">
									<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</li>
							&nbsp;&nbsp;&nbsp;
							<button type="button" class="btn btn-primary" id="sel_dates" onclick="selD()">查询</button>
						</div>
						<button type="button" class="btn btn-default fr">{{$count??"0"}}&nbsp;<i class="color-negative">单</i></button>
					</form>
				</div>
			</div>
			<div class="row">
				<div class="ui-table table-single-line">
					<div class="ui-table-header radius">
						<span class="col-md-2">保单号</span>
						<span class="col-md-1">保单产生时间</span>
						<span class="col-md-1">保单产品</span>
						<span class="col-md-1">保单状态</span>
						<span class="col-md-1">客户姓名</span>
						<span class="col-md-1">联系方式</span>
						<span class="col-md-1">保费</span>
						<span class="col-md-1">佣金</span>
						<span class="col-md-1">代理人</span>
						<span class="col-md-1">保单来源</span>
						<span class="col-md-1 col-one">操作</span>
					</div>
					<div class="ui-table-body">
						<ul>
							@foreach($list as $value)
								@if(isset($value['warranty'])&&!empty($value['warranty'])
								&&isset($value['warranty_rule_product'])&&!empty($value['warranty_rule_product'])
								&&isset($value['warranty_rule_order'])&&!empty($value['warranty_rule_order']))
								<li class="ui-table-tr">
								<div class="col-md-2">{{$value['warranty']['warranty_code']}}</div>
								<div class="col-md-1">{{$value['warranty']['created_at']}}</div>
								<div class="col-md-1">{{$value['warranty_rule_product']['product_name']}}</div>
								<div class="col-md-1 color-default">
									@if($value->status == 1)
										保障中
									@elseif($value->status == 2)
										失效
									@elseif($value->status == 3)
										退保
									@elseif($value->status == 0)
										待生效
									@endif
								</div>
								<div class="col-md-1">
									{{$value['policy']['name']}}
								</div>
								<div class="col-md-1">
									{{$value['policy']['phone']}}
								</div>
								<div class="col-md-1">
									{{ceil($value['warranty_rule_order']['premium']/100)}}
								</div>
								<div class="col-md-1">
									{{ceil($value['warranty_rule_order']['premium']/100*$value['warranty_product']['base_ratio']/100)}}
								</div>
								<div class="col-md-1">
									@if(isset($value->agent_name)&&!empty($value->agent_name))
										{{$value->agent_name}}
									@elseif($value->deal_type == 0)
											--
									@endif
								</div>
								<div class="col-md-1">
									@if($value->deal_type == 1)
										线下成交
									@elseif($value->deal_type == 0)
										线上成交
									@endif
								</div>
								<div class="col-md-1 text-right">
									<a class="btn btn-primary" href="{{url('backend/policy/policy_details?id='.$value['warranty']['id'])}}">查看详情</a>
								</div>
								</li>
								@endif
							@endforeach
						</ul>
					</div>
				</div>
			</div>
			<div class="row text-center">
				@if(isset($_GET['status_id'])&&!isset($_GET['type']))
					{{ $list->appends(['status_id' => $_GET['status_id']])->links() }}
				@elseif(isset($_GET['status_id'])&&isset($_GET['type']))
					{{ $list->appends(['status_id' => $_GET['status_id'],'type'=>$_GET['type']])->links() }}
				@elseif(isset($_GET['type'])&&!isset($_GET['status_id']))
					{{ $list->appends(['type'=>$_GET['type']])->links() }}
				@elseif(isset($_GET['date']))
					{{ $list->appends(['date' =>$_GET['date']])->links() }}
				@elseif(isset($_GET['date_start'])&&isset($_GET['date_end']))
					{{ $list->appends(['date_start' => $_GET['date_start'],'date_end'=>$_GET['date_end']])->links() }}
				@else
					{{ $list->links() }}
				@endif
			</div>
		</div>
		<!--弹层-->
		<form>
		<div class="modal modal-label" id="downloadbd">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header notitle">
						<button type="button" class="" data-dismiss="modal" aria-label="Close"><i class="iconfont icon-cancel"></i></button>
					</div>
					<div class="modal-body">
						<span class="choose">已选：10份保单</span>
						<span class="title">保单内容</span>
						<form action="/backend/policy/export_policy_person" id="download" method="post">
						<ul class="group-wrapper">
							<li>
								<span class="name">保单号：</span><input name="warranty_code" checked type="checkbox" value="1">
							</li>
							<li>
								<span class="name">代理人：</span><input name="agent" checked type="checkbox" value="1">
							</li>
							<li>
								<span class="name">起保时间：</span><input name="start_time" checked type="checkbox" value="1">
							</li>
							<li>
								<span class="name">代理人佣金：</span><input name="brokerage" checked type="checkbox" value="1">
							</li>
							<li>
								<span class="name">截止时间：</span><input name="end_time" checked type="checkbox" value="1">
							</li>
							<li>
								<span class="name">保单来源：</span><input name="policy_type" checked type="checkbox" value="1">
							</li>
							<li>
								<span class="name">产品名称：</span><input name="product_name" checked type="checkbox" value="1">
							</li>
							<li>
								<span class="name">投保人联系电话：</span><input name="phone" checked type="checkbox" value="1">
							</li>
							<li>
								<span class="name">保费：</span><input name="premium" checked type="checkbox" value="1">
							</li>
							<li>
								<span class="name">订单时间：</span><input name="created_at" checked type="checkbox" value="1">
							</li>
							<li>
								<span class="name">保单状态时间：</span><input name="policy_status" checked type="checkbox" value="1">
							</li>
						</ul>
							<input type="hidden" name="_token" value="{{csrf_token()}}">
							<input type="hidden" name="get" value="{{json_encode($_GET)}}">
						</form>
					</div>
					<div class="modal-footer fr">
						<button class="btn btn-primary">确定下载</button>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" id="status_id" value="{{$status_id}}">
		<input type="hidden" id="deal_type" value="{{$deal_type}}">
		</form>

@stop
<script src="{{asset('r_backend/v2/js/lib/jquery-1.11.3.min.js')}}"></script>
<script>
	$(function(){
		$("#search_type").change(function(){
			var status_id = $("#status_id").val();
			var type = $("#search_type").val();
			var agent = $("#agent").val();
			window.location.href="/backend/policy?type="+type+"&status_id="+status_id+"&agent="+agent;
		})
		$("#agent").change(function(){
			var status_id = $("#status_id").val();
			var type = $("#search_type").val();
			var agent = $("#agent").val();
			window.location.href="/backend/policy?type="+type+"&status_id="+status_id+"&agent="+agent;
		})
		//确定下载
		$('.group-wrapper input').click(function(){
			var status = $(this).prop('checked');
			var val = status === true ? 1 : 0;
			$(this).val(val);
		})
		$('.btn-primary').click(function(){
			$('.group-wrapper input').each(function(){
				if(!this.checked){
					this.checked = true;
				}
			});
			$("#download").submit();
		});
	})
    var pag = "@if(isset($_GET['page'])){{$_GET['page']}}@endif";
    $(function(){
        Util.DatePickerRange({
            ele: ".date-picker",
            startDate: null,
            endDate: new Date()
        });
        changeTab('.btn-select');
        $("#search_status").change(function(){
            var id = $("#search_status").val();
//            if(pag){
//                window.location.href="/backend/policy?page="+pag+"&status_id="+id;
//            }else{
                window.location.href="/backend/policy?status_id="+id;
//            }
        });
    })
    function selDay(id) {
        if(pag){
            window.location.href="/backend/policy?page="+pag+"&date="+id;
        }else{
            window.location.href="/backend/policy?date="+id;
        }
    }
    function selD() {
        var start  = $('#date_start').val();
        var end  = $('#date_end').val();
        if(!start||!end){
            Mask.alert('请选择起始时间！');
        }else{
            if(pag){
                window.location.href="/backend/policy?page="+pag+"&date_start="+start+"&date_end="+end;
            }else{
                window.location.href="/backend/policy?date_start="+start+"&date_end="+end;
            }
        }
    }
</script>
