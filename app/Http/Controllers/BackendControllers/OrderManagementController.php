<?php
/**
 * Created by PhpStorm.
 * User: dell01
 * Date: 2017/5/2
 * Time: 18:12
 */
namespace App\Http\Controllers\BackendControllers;

use App\Http\Controllers\BackendControllers;
use App\Models\Clause;
use App\Models\CodeType;
use App\Models\Company;
use App\Models\Order;
use App\Models\CompanyBrokerage;
use App\Models\OrderBrokerage;
use App\Models\Ditch;
use App\Models\Product;
use App\Models\User;
use App\Models\WarrantyBeneficiary;
use App\Models\Warranty;
use App\Models\WarrantyPolicy;
use App\Models\WarrantyRecognizee;
use App\Models\WarrantyRelation;
use App\Models\WarrantyRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use League\Flysystem\Exception;
use Request;
use Illuminate\Support\Facades\DB;

class OrderManagementController extends BaseController{

    //个人订单
    //个人订单
    public function index()
    {
        $input = $this->request->all();
        $status_id = $input['status_id'] ?? "";//订单状态
        $date = $input['date'] ?? "";//订单日期
        $date_start = $input['date_start'] ?? "";//订单开始时间
        $date_end =$input['date_end'] ?? "";//订单结束时间
        if(empty($status_id)&&empty($date)&&empty($date_start)&&empty($date_end)){
            $list = Order::with(['product_res','product','order_agent','warranty_rule.policy'])
                ->orderBy('created_at','desc')
                ->paginate(config('list_num.backend.agent'));
        }else{
            if(!empty($status_id)){
                $list = Order::with(['product_res','product','order_agent','warranty_rule.policy'])
                    ->where('status',$status_id)
                    ->orderBy('created_at','desc')
                    ->paginate(config('list_num.backend.agent'));
            }
            if(!empty($date)){
                switch ($date){
                    case '1':
                        $list = Order::with(['product_res','product','order_agent','warranty_rule.policy'])
                            ->where('created_at','>',date('Y-m-d',time()).' 00:00:00')
                            ->orderBy('created_at','desc')
                            ->paginate(config('list_num.backend.agent'));
                        $start = date('Y-m-d',time());
                        $end = date('Y-m-d',time());
                        break;
                    case '-1':
                        $list = Order::with(['product_res','product','order_agent','warranty_rule.policy'])
                            ->where('created_at','>',date('Y-m-d',time()-24*3600*2).' 00:00:00')
                            ->where('created_at','<',date('Y-m-d',time()).' 00:00:00')
                            ->orderBy('created_at','desc')
                            ->paginate(config('list_num.backend.agent'));
                        $start = date('Y-m-d',time()-24*3600);
                        $end = date('Y-m-d',time());
                        break;
                    case '7':
                        $list = Order::with(['product_res','product','order_agent','warranty_rule.policy'])
                            ->where('created_at','>',date('Y-m-d',time()-24*3600*7).' 00:00:00')
                            ->orderBy('created_at','desc')
                            ->paginate(config('list_num.backend.agent'));
                        $start = date('Y-m-d',time()-24*3600*7);
                        $end = date('Y-m-d',time());
                        break;
                    case '30':
                        $list = Order::with(['product_res','product','order_agent','warranty_rule.policy'])
                            ->where('created_at','>',date('Y-m-d',time()-24*3600*30).' 00:00:00')
                            ->orderBy('created_at','desc')
                            ->paginate(config('list_num.backend.agent'));
                        $start = date('Y-m-d',time()-24*3600*30);
                        $end = date('Y-m-d',time());
                        break;
                }
            }
            if(!empty($date_start)&&!empty($date_end)){

                $list = Order::with(['product_res','product','order_agent','warranty_rule.policy'])
                    ->where('created_at','>',$date_start.' 00:00:00')
                    ->where('created_at','<',$date_end.' 23:59:59')
                    ->orderBy('created_at','desc')
                    ->paginate(config('list_num.backend.agent'));
            }

        }
        $count = $list->total();
        $start = $start??"";
        $end = $end??"";
        return view('backend_v2.order.index',compact(['count','list','status_id','start','end']));
    }
    //个险详情
    public function personal_details()
    {
        $id = $_GET['id'];

        $product = Order::where('order.id',$id)
            ->with('product_res','product')
            ->first();
        if(empty($product)){
            return back()->withErrors('获取信息失败');
        }
        if(empty($product->product)) {
            if(empty($product->product_res)) {
                $item = [];
            }else{
                $item = json_decode($product->product_res->json, true);
            }
        }else{
            $item = json_decode($product->product->json, true);
        }
        $brokerage = CompanyBrokerage::where('order_id',$id)->select('brokerage')->first();//佣金
        $ditches = Order::where('order.id',$id)
            ->join('ditches','order.ditch_id','ditches.id')
            ->select('name')->first(); //渠道
        //        渠道佣金
        $order_brokerage = OrderBrokerage::where('order_id',$id)->select("user_earnings")->first();
        $order = Order::where('order.id',$id)->leftjoin('agents','order.agent_id','agents.id')
            ->leftjoin('users as user','agents.user_id','user.id')->select('real_name')->first(); //代理人
        //        受益人
        $warranty_beneficiary = WarrantyRule::where('order_id', $id)->with("beneficiary")->first();
        //        被保人
        $warranty_recognizee = WarrantyRecognizee::where('order_id', $id)->first();
        //投保人
        $warranty_policy = WarrantyRule::where('order_id', $id)->with('policy')->first();
        return view('backend_v2.order.order_details',compact('product','item','brokerage','ditches','order_brokerage','order','warranty_beneficiary','warranty_recognizee','warranty_policy'));
    }
    //企业订单
    public function enterprise(){
        $status_id = $this->request->id ?? "";
        $select = [
            'user.real_name as name',
            'users.real_name',
            'users.phone',
            'product_name',
            'premium',
            'order.status',
            'order_code',
            'order.id',
            'order.created_at'
        ];
        $c_order = Order::join('product','order.ty_product_id','product.ty_product_id');
        $c_order->join('users','order.user_id','users.id');
        $c_order->leftjoin('agents','order.agent_id','agents.id');
        $c_order->leftjoin('users as user','agents.user_id','user.id');
        $c_order->where('users.type','company');
        $status_id && $c_order->where('order.status',$status_id);
        $c_order->where('order.ty_product_id','>=',0);
        $c_order->select($select);
        $list = $c_order->paginate(config('list_num.backend.agent'));

        return view('backend_v2.order.order_company',compact(['list','status_id']));
    }
    //团险详情
    public function order_company_details()
    {
        $id= $_GET['id'];
        $product = Order::where('order.id',$id)
            ->join('product','order.ty_product_id','product.ty_product_id')
            ->first();
        $item = json_decode($product->json, true);

        $brokerage = CompanyBrokerage::where('order_id',$id)->select('brokerage')->first();//佣金


        $ditches = Order::where('order.id',$id)
            ->join('ditches','order.ditch_id','ditches.id')
            ->select('name')->first(); //渠道

        //        渠道佣金
        $order_brokerage = OrderBrokerage::where('order_id',$id)->select("user_earnings")->first();

        $order = Order::where('order.id',$id)->leftjoin('agents','order.agent_id','agents.id')
            ->leftjoin('users as user','agents.user_id','user.id')->select('real_name')->first(); //代理人

        //        受益人
        $warranty_beneficiary = WarrantyRule::where('order_id', $id)->with("beneficiary")->first();

        //被保人
        $warranty_recognizee = WarrantyRecognizee::where('order_id', $id)->paginate(config('list_num.backend.ditches'));


        return view('backend_v2.order.order_company_details',compact(['product','item','brokerage','ditches','order_brokerage','order','warranty_beneficiary','warranty_recognizee']));
    }



}

