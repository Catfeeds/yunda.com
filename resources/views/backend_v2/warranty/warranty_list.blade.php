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
					<a href="{{url('/backend/warranty/list/')}}" >个人保单</a>
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
								@if(isset($warranty_status) &&!empty($warranty_status))
									@foreach($warranty_status as $key=>$value)
										<option value="{{$key}}" @if(isset($status_id) && $status_id == $key) selected @endif>{{$value}}</option>
									@endforeach
								@endif
							</select>
						</div>
						<label><span class="btn-select  @if(!isset($_GET['date'])||isset($_GET['date'])&&$_GET['date']=='0') active @endif">全部<input hidden type="radio" name="date" value="0" onclick="selDay(0)"/></span></label>
						<label><span class="btn-select  @if(isset($_GET['date'])&&$_GET['date']=='1') active @endif">今日<input hidden type="radio" name="date" value="1" onclick="selDay(1)"/></span></label>
						<label><span class="btn-select @if(isset($_GET['date'])&&$_GET['date']=='-1') active @endif">昨日<input hidden type="radio" name="date" value="2" onclick="selDay(-1)" /></span></label>
						<label><span class="btn-select @if(isset($_GET['date'])&&$_GET['date']=='7') active @endif">最近7天<input hidden type="radio" name="date" value="3" onclick="selDay(7)" /></span></label>
						<label><span class="btn-select @if(isset($_GET['date'])&&$_GET['date']=='30') active @endif">最近30天<input hidden type="radio" name="date" value="4" onclick="selDay(30)" /></span></label>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
					<span class="col-md-3">保单号</span>
					<span class="col-md-1">保单状态</span>
					<span class="col-md-2">保单产品</span>
					{{--<span class="col-md-1">客户姓名</span>--}}
					{{--<span class="col-md-1">身份证号</span>--}}
					<span class="col-md-1">联系方式</span>
					<span class="col-md-1">保费</span>
					<span class="col-md-1">佣金</span>
					<span class="col-md-2">保单更新时间</span>
					<span class="col-md-1 col-one">操作</span>
				</div>
				<div class="ui-table-body">
					<ul>
						@if(isset($list)&&!empty($list))
							@foreach($list as $value)
								<li class="ui-table-tr">
									<div class="col-md-3">{{empty($value['warranty_code'])?$value['warranty_uuid']:$value['warranty_code']}}</div>
									<div class="col-md-1">
											@if(isset($warranty_status) &&!empty($warranty_status))
												@foreach($warranty_status as $key=>$v)
													@if($value['warranty_status'] == $key&&$v=='待支付')
														{{$pay_status[$value['pay_status']]}}
													@endif
													@if($value['warranty_status'] == $key&&$v!='待支付')
															{{$v}}
													@endif
												@endforeach
											@endif
									</div>
									<div class="col-md-2">快递保.意外险</div>
									{{--@if(isset($value['warrantyPerson'])&&!empty($value['warrantyPerson']))--}}
										{{--@foreach($value['warrantyPerson'] as $va)--}}
											{{--@if($va['type'] == '1')--}}
												{{--<div class="col-md-1">{{$va['name']}}</div>--}}
												{{--<div class="col-md-1">{{$va['card_code']}}</div>--}}
												{{--<div class="col-md-1">{{$va['phone']}}</div>--}}
											{{--@endif--}}
										{{--@endforeach--}}

									@if(isset($value['person'])&&!empty($value['person']))
{{--										<div class="col-md-1">{{$value['person']['name']}}</div>--}}
{{--										<div class="col-md-1">{{$value['person']['papers_code']}}</div>--}}
										<div class="col-md-1">{{$value['person']['phone']}}</div>
									@endif
									<div class="col-md-1">2</div>
									<div class="col-md-1">1</div>
									<div class="col-md-2">
									</div>
									<div class="col-md-1 text-right">
										<a class="btn btn-primary" href="{{url('backend/warranty/info/'.$value['warranty_uuid'])}}">查看详情</a>
									</div>
								</li>
							@endforeach
						@endif
					</ul>
				</div>
			</div>
		</div>
		{{--分页--}}
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
@stop
<script src="{{asset('r_backend/v2/js/lib/jquery-1.11.3.min.js')}}"></script>
<script>
    $(function(){
        $("#search_type").change(function(){
            var status_id = $("#status_id").val();
            var type = $("#search_type").val();
            var agent = $("#agent").val();
            window.location.href="/backend/warranty/list?type="+type+"&status_id="+status_id+"&agent="+agent;
        })
        $("#agent").change(function(){
            var status_id = $("#status_id").val();
            var type = $("#search_type").val();
            var agent = $("#agent").val();
            window.location.href="/backend/warranty/list?type="+type+"&status_id="+status_id+"&agent="+agent;
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
            window.location.href="/backend/warranty/list?status_id="+id;
        });
    })
    function selDay(id) {
        if(pag){
            window.location.href="/backend/warranty/list?page="+pag+"&date="+id;
        }else{
            window.location.href="/backend/warranty/list?date="+id;
        }
    }
    function selD() {
        var start  = $('#date_start').val();
        var end  = $('#date_end').val();
        if(!start||!end){
            Mask.alert('请选择起始时间！');
        }else{
            if(pag){
                window.location.href="/backend/warranty/list?page="+pag+"&date_start="+start+"&date_end="+end;
            }else{
                window.location.href="/backend/warranty/list?date_start="+start+"&date_end="+end;
            }
        }
    }
</script>
