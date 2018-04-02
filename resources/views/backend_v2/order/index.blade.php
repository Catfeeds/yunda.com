@extends('backend_v2.layout.base')
@section('title')@parent 订单管理 @stop
@section('head-more')
    <link rel="stylesheet" href="{{asset('r_backend/v2/css/agent.css')}}" />
@stop
@section('top_menu')
    @if(Auth::guard('admin')->user()->email==config('manager_account.manager'))
        <div class="nav-top-wrapper fl">
            <ul>
                <li class="active">
                    <a href="{{url('/backend/order/')}}" >个人订单</a>
                </li>
                <li>
                    <a href="{{url('/backend/order/enterprise/')}}">企业订单</a>
                </li>
            </ul>
        </div>
    @endif
@stop
@section('main')
    <script src="{{asset('r_backend/v2/js/lib/jquery-1.11.3.min.js')}}"></script>
    <script src="{{asset('r_backend/v2/js/common_backend.js')}}"></script>
    <script src="{{asset('r_backend/v2/js/lib/bootstrap-datetimepicker.min.js')}}"></script>
    <script src="{{asset('r_backend/v2/js/lib/bootstrap-datetimepicker.zh-CN.js')}}"></script>
    <script src="{{asset('r_backend/v2/js/lib/bootstrap.min.js')}}"></script>
    <div id="product" class="main-wrapper">
        <div class="row">
            <div class="select-wrapper radius">
                <form role="form" class="form-inline radius" >
                {{--<form role="form" class="form-inline radius" action="/backend/order" method="post" id="form">--}}
                    {{--{{ csrf_field() }}--}}
                    <div class="form-group">
                        <div class="select-item">
                            <label for="name">订单状态:</label>
                            <select class="form-control" id="search_status">
                                <option value="0">全部订单</option>
                                <option value="1" @if(isset($status_id) && $status_id == 1) selected @endif>已支付</option>
                                <option value="2" @if(isset($status_id) && $status_id == 2) selected @endif>未支付</option>
                                <option value="3" @if(isset($status_id) && $status_id == 3) selected @endif>支付失败</option>
                                <option value="4" @if(isset($status_id) && $status_id == 4) selected @endif>支付中</option>
                                <option value="6" @if(isset($status_id) && $status_id == 6) selected @endif>核保错误</option>
                                <option value="7" @if(isset($status_id) && $status_id == 7) selected @endif>取消支付</option>
                            </select>
                        </div>
                             <label><span class="btn-select  @if(!isset($_GET['date'])||isset($_GET['date'])&&$_GET['date']=='0') active @endif">全部<input hidden type="radio" name="date" value="0" onclick="selDay(0)"/></span></label>
                            <label><span class="btn-select  @if(isset($_GET['date'])&&$_GET['date']=='1') active @endif">今日<input hidden type="radio" name="date" value="1" onclick="selDay(1)"/></span></label>
                            <label><span class="btn-select @if(isset($_GET['date'])&&$_GET['date']=='-1') active @endif">昨日<input hidden type="radio" name="date" value="2" onclick="selDay(-1)" /></span></label>
                            <label><span class="btn-select @if(isset($_GET['date'])&&$_GET['date']=='7') active @endif">最近7天<input hidden type="radio" name="date" value="3" onclick="selDay(7)" /></span></label>
                            <label><span class="btn-select @if(isset($_GET['date'])&&$_GET['date']=='30') active @endif">最近30天<input hidden type="radio" name="date" value="4" onclick="selDay(30)" /></span></label>
                        <li class="date-picker" style="display: inline-block;">
                            <div class="input-group date form_date form_date_start">
                                <input id="date_start" class="form-control" type="text" value="@if(isset($_GET['date_start'])&&!empty($_GET['date_start'])){{$_GET['date_start']}}@elseif(!empty($start)){{$start}}@endif" placeholder="@if(isset($_GET['date_start'])&&!empty($_GET['date_start'])){{$_GET['date_start']}}@elseif(!empty($start)){{$start}}@else 年/月/日@endif">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                            </div>
                            &nbsp;&nbsp;&nbsp;
                            <i class="">至</i>
                            <div class="input-group date form_date form_date_end">
                                <input id="date_end" class="form-control" type="text" value="@if(isset($_GET['date_end'])&&!empty($_GET['date_end'])){{$_GET['date_end']}}@elseif(!empty($end)){{$end}}@endif" placeholder="@if(isset($_GET['date_end'])&&!empty($_GET['date_end'])) {{$_GET['date_end']}}@elseif(!empty($end)){{$end}}@else 年/月/日@endif">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                            </div>
                        </li>
                        &nbsp;&nbsp;&nbsp;
                        <button type="button" class="btn btn-primary" id="sel_date">查询</button>
                    </div>
                    <button type="button" class="btn btn-default fr">{{$count}}&nbsp;<i class="color-negative">单</i></button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="ui-table table-single-line">
                <div class="ui-table-header radius">
                    <span class="col-md-2">订单号</span>
                    <span class="col-md-2">订单产品</span>
                    <span class="col-md-1">订单生成时间</span>
                    <span class="col-md-1">订单状态</span>
                    <span class="col-md-1">客户姓名</span>
                    <span class="col-md-1">联系方式</span>
                    <span class="col-md-1">保费</span>
                    <span class="col-md-1">代理人</span>
                    <span class="col-md-2 col-one">操作</span>
                </div>
                <div class="ui-table-body">
                    <ul>
                        @foreach($list as $value)
                            <li class="ui-table-tr">
                                <div class="col-md-2">{{$value->order_code}}</div>
                                <div class="col-md-2">
                                    @if(!empty($value->product->product_name))
                                        {{$value->product->product_name}}
                                    @else
                                        @if(!empty($value->product_res->product_name))
                                            {{$value->product_res->product_name}}
                                        @endif
                                    @endif
                                </div>
                                <div class="col-md-1">{{$value->created_at}}</div>
                                <div class="col-md-1 color-negative">
                                    @if($value->status == 1)
                                        已支付
                                    @elseif($value->status == 2)
                                        未支付
                                    @elseif($value->status == 3)
                                        支付失败
                                    @elseif($value->status == 4)
                                        支付中
                                    @elseif($value->status == 6)
                                        核保错误
                                    @elseif($value->status == 7)
                                        取消支付
                                    @endif
                                </div>
                                <div class="col-md-1">
                                    @if(!empty($value->real_name))
                                        {{$value->real_name}}
                                    @elseif(!empty($value->warranty_rule->policy))
                                        {{$value->warranty_rule->policy->name}}
                                    @endif
                                </div>
                                <div class="col-md-1">
                                    @if(!empty($value->phone))
                                        {{$value->phone}}
                                    @elseif(!empty($value->warranty_rule->policy))
                                        {{$value->warranty_rule->policy->phone}}
                                    @endif
                                </div>
                                <div class="col-md-1">{{ceil($value->premium/100)}}</div>
                                <div class="col-md-1">
                                    @if(!empty($value->order_agent->name))
                                        {{$value->order_agent->name}}
                                    @else
                                        --
                                    @endif
                                </div>
                                <div class="col-md-2 text-right">
                                    <a class="btn btn-primary" href="{{url('backend/order/personal_details?id='.$value->id)}}">查看详情</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="row text-center">
            @if(isset($_GET['status_id']))
                {{ $list->appends(['status_id' => $_GET['status_id']])->links() }}
            @elseif(isset($_GET['date']))
                {{ $list->appends(['date' =>$_GET['date']])->links() }}
            @elseif(isset($_GET['date_start'])&&isset($_GET['date_end']))
                {{ $list->appends(['date_start' => $_GET['date_start'],'date_end'=>$_GET['date_end']])->links() }}
            @else
                {{ $list->links() }}
            @endif
        </div>
    </div>
    <script>
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
                if(pag){
                    window.location.href="/backend/order?page="+pag+"&status_id="+id;
                }else{
                    window.location.href="/backend/order?status_id="+id;
                }

            });
        })
        function selDay(id) {
            if(pag){
                window.location.href="/backend/order?page="+pag+"&date="+id;
            }else{
                window.location.href="/backend/order?date="+id;
            }
        }
        $('#sel_date').on('click',function () {
            var start  = $('#date_start').val();
            var end  = $('#date_end').val();
            if(!start||!end){
                Mask.alert('请选择起始时间！');
            }else{
                if(pag){
                    window.location.href="/backend/order?page="+pag+"&date_start="+start+"&date_end="+end;
                }else{
                    window.location.href="/backend/order?date_start="+start+"&date_end="+end;
                }
            }
        });
    </script>
@stop