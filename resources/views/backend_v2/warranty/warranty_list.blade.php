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
					<a href="{{url('/backend/warranty/list')}}" >个人保单</a>
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
					<div class="form-group">
						<div class="select-item">
							<label for="name">保单状态:</label>
							<select class="form-control" id="search_status">
								<option selected value="-1">全部保单</option>
								@if(isset($warranty_status)&&!empty($warranty_status))
									@foreach($warranty_status as $key=>$value)
										<option value="{{$key}}">{{$value}}</option>
									@endforeach
								@endif
							</select>
						</div>
						<label><span class="btn-select active">全部</span></label>
						<label><span class="btn-select">今日</span></label>
						<label><span class="btn-select">昨日</span></label>
						<label><span class="btn-select">最近7天</span></label>
						<label><span class="btn-select">最近30天</span></label>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<li class="date-picker" style="display: inline-block;">
							<div class="input-group date form_date form_date_start">
								<input id="date_start" class="form-control" type="text" value="" placeholder="年/月/日">
								<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
							</div>
							&nbsp;&nbsp;&nbsp;
							<i class="">至</i>
							<div class="input-group date form_date form_date_end">
								<input id="date_end" class="form-control" type="text" value="" placeholder="年/月/日">
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
					<span class="col-md-1">保单产品</span>
					<span class="col-md-1">保单状态</span>
					<span class="col-md-1">客户姓名</span>
					<span class="col-md-1">身份证号</span>
					<span class="col-md-1">联系方式</span>
					<span class="col-md-1">保费</span>
					<span class="col-md-1">佣金</span>
					<span class="col-md-1">保单来源</span>
					<span class="col-md-1">保单产生时间</span>
					<span class="col-md-1 col-one">操作</span>
				</div>
				<div class="ui-table-body">
					<ul>
						@if(isset($list)&&!empty($list))
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
							@endif
					</ul>
				</div>
			</div>
		</div>
		<div class="row text-center">
			@if(isset($list)&&!empty($list))
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
			@endif
		</div>
	</div>
@stop
<script src="{{asset('r_backend/v2/js/lib/jquery-1.11.3.min.js')}}"></script>
<script>
    var pag = "@if(isset($_GET['page'])){{$_GET['page']}}@endif";
    $(function(){
        Util.DatePickerRange({
            ele: ".date-picker",
            startDate: null,
            endDate: new Date()
        });
        changeTab('.btn-select');
    });
    function selD() {
        var start  = $('#date_start').val();
        var end  = $('#date_end').val();
        if(!start||!end){
            Mask.alert('请选择起始时间！');
        }else{

        }
    }
</script>
