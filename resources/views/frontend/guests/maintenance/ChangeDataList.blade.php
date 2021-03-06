@extends('frontend.guests.layout.bases')
@section('content')
    <style>
        th{
            text-align: center;
        }
        td{
            text-align: center;
        }
    </style>
    <div id="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">
                        <ol class="breadcrumb">
                            <li><a href="{{ url('/backend') }}">主页</a></li>
                            <li ><span>售后管理</span></li>
                            <li ><span>保全管理</span></li>
                            <li class="active"><span>个险变更</span></li>

                        </ol>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="main-box clearfix" style="min-height: 1100px;">
                            <div class="tabs-wrapper tabs-no-header">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#">个险变更</a></li>
                                </ul>
                                <div class="col-lg-12">
                                    @include('backend.layout.alert_info')
                                    <div class="main-box clearfix">
                                        <header class="main-box-header clearfix">
                                            <h2 class="pull-left">个险变更</h2>
                                        </header>
                                        <div class="main-box-body clearfix">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                    <tr>
                                                        <th><span>联合订单编号</span></th>
                                                        <th><span>保全状态</span></th>
                                                        <th><span>发起时间</span></th>
                                                        <th><span>查看详情</span></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @if($count == 0)
                                                        <td colspan="8">暂无修改记录</td>
                                                    @else
                                                        @foreach($list as $value)
                                                            <tr>
                                                                <td>{{$value->union_order_id}}</td>
                                                                <td>
                                                                    @if($value->status=='200')
                                                                        发起成功，等待审核
                                                                    @else
                                                                        发起成功，变更成功
                                                                    @endif
                                                                </td>
                                                                <td>{{$value->created_at}}</td>
                                                                <td><a href="{{ url('/maintenance/change_data_detail/'.$value->union_order_id) }}" target="_blank">更多修改内容</a></td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
@stop

