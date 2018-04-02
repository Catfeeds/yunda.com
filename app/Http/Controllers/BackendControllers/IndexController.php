<?php

namespace App\Http\Controllers\BackendControllers;


use App\Models\Warranty;
use Illuminate\Support\Facades\Auth;
use App\Helper\RsaSignHelp;
use Ixudra\Curl\Facades\Curl;
use App\Helper\Relevance;
use App\Models\User;
use App\Models\TaskDetail;
use App\Models\DitchAgent;
use App\Models\WarrantyRule;
use App\Models\Agent;
use App\Models\Product;
use App\Models\Ditch;
use DB, Validator, Image;
use App\Models\Order;
use Cache;
use App\Models\CompanyBrokerage;

class IndexController extends BaseController
{
    public function __construct()
    {
        $this->relevance  = new Relevance();
    }
    //首页 todo  缓存
    public function index()
    {
//        //查询缓存
//        if (Cache::has('index_data')) {
//            $index_data = Cache::get('index_data');
//            $today = $index_data['today'];
//            $yesterday = $index_data['yesterday'];
//            $t_customer = $index_data['t_customer'];
//            $y_customer = $index_data['y_customer'];
//            $t_agent = $index_data['t_agent'];
//            $y_agent = $index_data['y_agent'];
//            $t_brokerage = $index_data['t_brokerage'];
//            $y_brokerage = $index_data['y_brokerage'];
//            $time = $index_data['time'];
//            $twoTime = $index_data['twoTime'];
//            $timeAgent = $index_data['timeAgent'];
//            $task = $index_data['task'];
//            $finish = $index_data['finish'];
//            return view('backend_v2.index.index',compact(
//                'today',
//                'yesterday',
//                't_customer',
//                'y_customer',
//                't_agent',
//                'y_agent',
//                't_brokerage',
//                'y_brokerage',
//                'time',
//                'twoTime',
//                'timeAgent',
//                'task',
//                'finish'
//            ));
//        }
        //保费统计：今天的和昨天的保费
        //TODO 保单表需要加索引  warranty id premium created_at
        $today = [];
        $today_res = Warranty::where('created_at','>',date('Y-m-d',time()).' '.'00:00:00')
            ->where('created_at','<',date('Y-m-d',time()).' '.'23:59:59');
        $today['count'] = $today_res->count('id');
        $today['premium'] = $today_res->sum('premium');
        $yesterday = [];
        $yesterday_res = Warranty::where('created_at','>',date('Y-m-d',strtotime("-1 day")).' '.'00:00:00')
            ->where('created_at','<',date('Y-m-d',strtotime("-1 day")).' '.'23:59:59');
        $yesterday['count'] = $yesterday_res->count('id');
        $yesterday['premium'] = $yesterday_res->sum('premium');

        //保费的日期  todo  还没优化
        $time = Warranty::select('created_at')
                ->groupBy(DB::raw('DATE_FORMAT(created_at,"%Y")'))
                ->get();

        //订单表客户统计和代理人数量统计（今天和昨天）
        //TODO 订单表需要加索引  order id user_id agent_id created_at
        $order_t = Order::where('created_at','>',date('Y-m-d',time()).' '.'00:00:00')
            ->where('created_at','<',date('Y-m-d',time()).' '.'23:59:59');
        $t_customer = $order_t->groupBy('user_id')->count('id');
        $t_agent = $order_t->groupBy('agent_id')->count('id');
        $order_y = Order::where('created_at','>',date('Y-m-d',strtotime("-1 day")).' '.'00:00:00')
            ->where('created_at','<',date('Y-m-d',strtotime("-1 day")).' '.'23:59:59');
        $y_customer = $order_y->groupBy('user_id')->count('id');
        $y_agent = $order_y->groupBy('agent_id')->count('id');

        //佣金统计（今天的和昨天的）
        //TODO 佣金表需要加索引  CompanyBrokerage brokerage created_at
        $brokerage_t = CompanyBrokerage::where('created_at','>',date('Y-m-d',time()).' '.'00:00:00')
            ->where('created_at','<',date('Y-m-d',time()).' '.'23:59:59');
        $brokerage_y =CompanyBrokerage::where('created_at','>',date('Y-m-d',strtotime("-1 day")).' '.'00:00:00')
            ->where('created_at','<',date('Y-m-d',strtotime("-1 day")).' '.'23:59:59');
        $t_brokerage = $brokerage_t->sum('brokerage');
        $y_brokerage = $brokerage_t->sum('brokerage');

        //佣金的日期 todo  还没优化
        $twoTime = CompanyBrokerage::select('created_at')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y")'))
            ->get();

        // 代理人的日期 todo  还没优化
        $timeAgent = Agent::whereNotNull('created_at')
            ->select('created_at')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y")'))
            ->get();

        // 一年的总任务
        $task = TaskDetail::where('year', date("Y"))
            ->sum('money');
        // 一年完成任务
        $finish = Order::where('status','1')
            ->where('created_at','>',date('Y',time()).'-01-01 00:00:00')
            ->where('created_at','<',date('Y',time()).'-12-31 23:59:59')
            ->sum('premium');
        $finish = $finish/100;
//            dump($today);
//            dump($yesterday);
//            dump($t_customer);
//            dump($y_customer);
//            dump($t_agent);
//            dump($y_agent);
//            dump($t_brokerage);
//            dump($y_brokerage);
//            dump($time);
//            dump($twoTime);
//            dump($timeAgent);
//            dump($task);
//            dump($finish);
//        $index_data = [];
//        $index_data['today'] = $today;
//        $index_data['yesterday'] = $yesterday;
//        $index_data['t_customer'] = $t_customer;
//        $index_data['y_customer'] = $y_customer;
//        $index_data['t_agent'] = $t_agent;
//        $index_data['y_agent'] = $y_agent;
//        $index_data['t_brokerage'] = $t_brokerage;
//        $index_data['y_brokerage'] = $y_brokerage;
//        $index_data['time'] = $time;
//        $index_data['twoTime'] = $twoTime;
//        $index_data['timeAgent'] = $timeAgent;
//        $index_data['task'] = $task;
//        $index_data['finish'] = $finish;
//        //存缓存（有效期为12小时）
//        $expiresAt = \Carbon\Carbon::now()->addMinutes(60);
//        Cache::put('index_data',$index_data,$expiresAt);
        return view('backend_v2.index.index',compact(
            'today',
            'yesterday',
            't_customer',
            'y_customer',
            't_agent',
            'y_agent',
            't_brokerage',
            'y_brokerage',
            'time',
            'twoTime',
            'timeAgent',
            'task',
            'finish'
        ));
    }
    //保费统计
    public function statistics(){
        $year = isset($_GET['time']) ? $_GET['time'] : date('Y',time());
//        //查询缓存
//        if (Cache::has('statistics'.$year)) {
//            $arr = Cache::get('statistics'.$year);
//            return $arr;
//        }
        //保费统计图
        $warranty = Warranty::where('created_at','>',$year.'-01-01 00:00:00')
            ->where('created_at','<',$year.'-12-31 23:59:59')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%m")'))
            ->select(
                DB::raw('sum(premium) as premium,count(id) as count'),
                DB::raw('DATE_FORMAT(created_at, "%m") as month')
            )
            ->get()
            ->toArray();
        //保费
        $arr = array();
        for($i=0; $i<=11; $i++){
            foreach($warranty as $k => $v){
                if((int)$v['month'] - 1 == $i){
                    $arr['premium'][$i] = ($v['premium'] / 100);
                    $arr['count'][$i] = ($v['count']);
                    break;
                } else{
                    $arr['premium'][$i] = 0;
                    $arr['count'][$i] = 0;
                }
            }
        }
//        //存缓存（有效期为12小时）
//        $expiresAt = \Carbon\Carbon::now()->addMinutes(60);
//        Cache::put('statistics'.$year, $arr,$expiresAt);
        return $arr;
    }
    //任务额度
    public function indexTask(){
        $year = $yearly ?? date('Y',time());
//        //查询缓存
//        if (Cache::has('indexTask'.$year)) {
//            $arr = Cache::get('indexTask'.$year);
//            return $arr;
//        }
        $arr = array();
        //应该完成
        $task = TaskDetail::where('year', $year)
            ->groupBy('month')
            ->select(DB::raw('sum(money) as money'), 'month')
            ->get()
            ->toArray();
        for($i=0; $i<=11; $i++){
            foreach($task as $k => $v){
                if((int)$v['month'] - 1 == $i){
                    $arr['money'][$i] = round(($v['money'] / 100),2);
                    break;
                } else{
                    $arr['money'][$i] = 0;
                }
            }
        }
        //任务达成率
        $complete = Order::where('created_at','>',$year.'-01-01 00:00:00')
            ->where('created_at','<',$year.'-12-31 23:59:59')
            ->groupBy(DB::raw('DATE_FORMAT(pay_time,"%m")'))
            ->select(DB::raw('sum(premium) as money'), DB::raw('DATE_FORMAT(pay_time,"%m") as month'))
            ->get()
            ->toArray();
        if(!empty($arr)){
            for($i=0; $i<=11; $i++){
                foreach($complete as $k => $v){
                    if((int)$v['month'] - 1 == $i){
                        $arr['c_money'][$i] = round(($v['money'] / 100)/$arr['money'][$i]*100,2);
                        break;
                    } else{
                        $arr['c_money'][$i] = 0;
                    }
                }
            }
        }
//        //存缓存（有效期为12小时）
//        $expiresAt = \Carbon\Carbon::now()->addMinutes(60);
//        Cache::put('indexTask'.$year, $arr,$expiresAt);
        return $arr;
    }
    //人均保费
    public function average(){
        $year = isset($_GET['time']) ? $_GET['time'] : date('Y');
        //人均保费
        $warranty = Warranty::where('created_at','>',$year.'-01-01 00:00:00')
            ->where('created_at','<',$year.'-12-31 23:59:59')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%m")'))
            ->select(
                DB::raw('sum(premium) as premium,count(id) as count'),
                DB::raw('DATE_FORMAT(created_at, "%m") as month')
            )->get()->toArray();
        $arr = array();
        for($i=0; $i<=11; $i++){
            foreach($warranty as $k => $v){
                if((int)$v['month'] - 1 == $i){
                    $count[$i] = ($v['count']);
                    $arr['premium'][$i] = round((($v['premium'] / 100)/$count[$i]),2);
                    break;
                } else{
                    $arr['premium'][$i] = 0;
                }
            }
        }
        //客户数
        $users = WarrantyRule::whereNotNull('warranty_id')
            ->join('order', function($join){
                $join->on('warranty_rule.order_id','=','order.id');
            })
            ->GroupBy(DB::raw('DATE_FORMAT(com_order.created_at, "%m")'))
            ->groupBy('user_id')
            ->where('created_at','>',$year.'-01-01 00:00:00')
            ->where('created_at','<',$year.'-12-31 23:59:59')
            ->select(
                DB::raw('DATE_FORMAT(com_order.created_at, "%m") as month')
            )
            ->get()->toArray();
        $res=array();
        foreach ($users as $key => $value) {
            foreach ($value as $k => $v) {
                $res[]=$v;
            }
        }
        $avb = array();
        $qwe=array_count_values($res);
        $res=array_unique($res);
        $qwe=array_values($qwe);
        $res=array_values($res);
        for ($i=0; $i < count($res) ; $i++) {
            $avb[$i]['count']=$qwe[$i];
            $avb[$i]['month']=$res[$i];
        }
        for($i=0; $i<=11; $i++){
            foreach($avb as $k => $v){
                if((int)$v['month'] - 1 == $i){
                    $arr['count'][$i] = ($v['count']);
                    break;
                } else{
                    $arr['count'][$i] = 0;
                }
            }
        }
        return $arr;
    }
    //    代理人数
    public function agentCount(){
        $year = isset($_GET['time']) ? $_GET['time'] : date('Y');
        $agent = where('created_at','>',$year.'-01-01 00:00:00')
            ->where('created_at','<',$year.'-12-31 23:59:59')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%m")'))
            ->select(
               DB::raw('count(id) as count'),
               DB::raw('DATE_FORMAT(created_at, "%m") as month')
            )->get()->toArray();
        $arr =array();
        for($i=0; $i<=11; $i++){
            foreach($agent as $k => $v){
                if((int)$v['month'] - 1 == $i){
                    $arr['count'][$i] = $v['count'];
                    break;
                } else{
                    $arr['count'][$i] = 0;
                }
            }
        }
        $order = Order::where('created_at','>',$year.'-01-01 00:00:00')
            ->where('created_at','<',$year.'-12-31 23:59:59')
            ->whereNotIn('agent_id', [0])
            ->groupBy('agent_id')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%m")'))
            ->select(
                DB::raw('count(agent_id) as count'),
                DB::raw('DATE_FORMAT(created_at, "%m") as month')
            )->get()->toArray();
        $param = array();
        foreach ($order as $key => $value) {
            if(isset($param[$value['month']])){
                $param[$value['month']] += $value['count'];
            }else{
                $param[$value['month']] = 0;
                $param[$value['month']] += $value['count'];
            }
        }
        $rate = array();
        $sum = array();
       foreach($param as $k =>$v){
           $rate['month'] = $k;
           $rate['count'] = $v;
           $sum[] = $rate;
       }
    for($i=0; $i<=11; $i++){
        foreach($sum as $k => $v){
            if((int)$v['month'] - 1 == $i){
                if($arr['count'][$i] != 0){
                    $arr['o_count'][$i] = round(($v['count']/$arr['count'][$i])*100,2);
                    break;
                }
            } else{
                $arr['o_count'][$i] = 0;
            }
        }
    }
        return $arr;
    }
    //    佣金
    public function brokerage(){
        $year = isset($_GET['time']) ? $_GET['time'] : date('Y');
        //累计佣金、件均保费
        $brokerage = CompanyBrokerage::where('created_at','>',$year.'-01-01 00:00:00')
            ->where('created_at','<',$year.'-12-31 23:59:59')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%m")'))
            ->select(
                DB::raw('sum(brokerage) as brokerage,count(order_id) as count'),
                DB::raw('DATE_FORMAT(created_at, "%m") as month')
            )->get()->toArray();
        $arr =array();
        for($i=0; $i<=11; $i++){
            foreach($brokerage as $k => $v){
                if((int)$v['month'] - 1 == $i){
                    $arr['brokerage'][$i] = $v['brokerage']/100;
                    $arr['a_brokerage'][$i] = round(($arr['brokerage'][$i]/$v['count']),2);
                    break;
                } else{
                    $arr['a_brokerage'][$i] = 0;
                    $arr['brokerage'][$i] = 0;
                }
            }
        }
        //人均保费
        $people = CompanyBrokerage::where(DB::raw('DATE_FORMAT(com_company_brokerage.created_at, "%Y")'),$year)
            ->join('order', function($join){
                $join->on('company_brokerage.order_id','=','order.id')
                    ->whereNotIn('agent_id', [0]);
            })
            ->groupBy('agent_id')
            ->groupBy(DB::raw('DATE_FORMAT(com_company_brokerage.created_at, "%m")'))
            ->select(
                DB::raw('count(agent_id) as count'),
                DB::raw('DATE_FORMAT(com_company_brokerage.created_at, "%m") as month')
            )
            ->get()->toArray();
            for($i=0; $i<=11; $i++){
                foreach($people as $k => $v){
                    if((int)$v['month'] - 1 == $i){
                        $arr['people'][$i] = round(($arr['brokerage'][$i]/$v['count']),2);
                        break;
                    } else{
                        $arr['people'][$i] =  0;
                    }
                }
            }
        return $arr;
    }
//    排行榜
    public function ranking(){
        $year = $yearly ?? date('Y');
        $rate = Order::where('created_at','>',$year.'-01-01 00:00:00')
            ->where('created_at','<',$year.'-12-31 23:59:59')
            ->whereNotIn('ditch_id', [0])
            ->groupBy('ditch_id')
            ->select('ditch_id',DB::raw('count(ditch_id) as o_count'))
            ->orderBy('o_count','desc')
            ->limit(3)
            ->get()
            ->toArray();
        $ditch = array();
        foreach($rate as $val){
            $ditche = DitchAgent::where('ditch_id',$val['ditch_id'])
                ->leftjoin('ditches', function($join){
                    $join->on('ditch_agent.ditch_id','=','ditches.id');
                })
                ->groupBy("ditch_id")
                ->select('ditch_id','name',DB::raw('count(ditch_id) as count'))
                ->get()
                ->toArray();
            foreach($ditche as $key=>$value){
                $ditch[] = $value;
            }
        }
        $res = array();
        $name = array();
        $arr = array();
        foreach ($ditch as $key => $value) {
            foreach($rate as $k => $v)
            {
                if($value['ditch_id']==$v['ditch_id'])
                {
                    $name[] = $value['name'];
                    $res[] = floor(($v['o_count']/$value['count'])*100);
                }
            }
        }
        $arr['name'] = $name;
        $arr['count'] = $res;
        return $arr;
    }
    //  热力图（地图）
    public function customer(){
        $year = isset($_GET['time']) ? $_GET['time'] : date('Y');
            $order = Warranty::where('created_at','>',$year.'-01-01 00:00:00')
                ->where('created_at','<',$year.'-12-31 23:59:59')
                ->with(['warranty_rule','warranty_rule.order','warranty_rule.order.order_user'])
                ->get()->toArray();
            $address = array();
            foreach($order as $value){
                if(strstr($value['warranty_rule']['order']['order_user']['address'] , '省') == false){
                    $address[]= strstr($value['warranty_rule']['order']['order_user']['address'] , '市', TRUE);
                }
                $address[]= strstr($value['warranty_rule']['order']['order_user']['address'] , '省', TRUE);
            }
            $a = array_filter($address);
            $qwe=array_count_values($a);
            $arr = array();
            foreach($qwe as $key=>$value){
                $arr['name'] = $key;
                $arr['count'] = $value;
                $array[] = $arr;
            };
        return $array;
    }
    //  保费的饼图
    public function safe(){
        $year = isset($_GET['time']) ? $_GET['time'] : date('Y');
        $p_type = Warranty::where('created_at','>',$year.'-01-01 00:00:00')
            ->where('created_at','<',$year.'-12-31 23:59:59')
            ->with("warranty_rule","warranty_rule.product")
            ->get();
        $id = array();
        foreach($p_type as $key => $value){
            $category = $value['warranty_rule']['product']['product_category'];
            $array[$category][] = $value['warranty_rule']['product']['id'];
            $type = $this->type($category);
            $type['id'] = $value['warranty_rule']['product']['id'];
            $id['one'][$type['one']][] = $type['id'];
            $id['two'][$type['two']][] = $type['id'];
        }
        // 一级分类
        foreach($id['one'] as $i => $j){
            $one_premium = WarrantyRule::where(DB::raw('DATE_FORMAT(com_warranty_rule.created_at,"%Y")'), $year)
                ->whereIn('ty_product_id', $j)
                ->join('warranty', 'warranty.id', '=', 'warranty_rule.warranty_id')
                ->select(DB::raw('SUM(com_warranty.premium) as one_premium'))
                ->first()->toArray();
            $box['one_premium'] = round($one_premium['one_premium']/100,2);
            $box['name'] = $i;
            $one[] = $box;
        }
        //二级分类
        foreach($id['two'] as $i => $j){
                $two_premium = WarrantyRule::where(DB::raw('DATE_FORMAT(com_warranty_rule.created_at,"%Y")'), $year)
                    ->whereIn('ty_product_id', $j)
                    ->join('warranty', 'warranty.id', '=', 'warranty_rule.warranty_id')
                    ->select(DB::raw('SUM(com_warranty.premium) as two_premium'))
                    ->first()->toArray();
            $asd['two_premium'] = round($two_premium['two_premium']/100,2);
            $asd['name'] = $i;
            $two[] = $asd;
        }
        $arr = array();
        $arr['one'] = $one;
        $arr['two'] = $two;
        return $arr;
    }
    //佣金的饼图
    public function PieChart(){
        $year = isset($_GET['time']) ? $_GET['time'] : date('Y');
        $brokerage = CompanyBrokerage::where('created_at','>',$year.'-01-01 00:00:00')
            ->where('created_at','<',$year.'-12-31 23:59:59')
            ->with('product')
            ->get()
            ->toArray();
        $id = array();
        foreach($brokerage as $key => $value){
            $category = $value['product']['product_category'];
            $array[$category][] = $value['product']['id'];
            $type = $this->type($category);
            $type['id'] = $value['product']['id'];
            $id['one'][$type['one']][] = $type['id'];
            $id['two'][$type['two']][] = $type['id'];
        }
        //一级分类
        foreach($id['one'] as $i => $j){
            $one_brokerage = Product::where(DB::raw('DATE_FORMAT(com_company_brokerage.created_at,"%Y")'), $year)
                ->whereIn('product.ty_product_id', $j)
                ->join('company_brokerage', 'product.ty_product_id', '=', 'company_brokerage.ty_product_id')
                ->select(DB::raw('SUM(brokerage) as one_brokerage'))
                ->first()
                ->toArray();
            $box['one_brokerage'] = round($one_brokerage['one_brokerage']/100,2);
            $box['name'] = $i;
            $one[] = $box;
        }
        //二级分类
        foreach($id['two'] as $i => $j){
            $two_brokerage = Product::where('created_at','>',$year.'-01-01 00:00:00')
                ->where('created_at','<',$year.'-12-31 23:59:59')
                ->whereIn('product.ty_product_id', $j)
                ->join('company_brokerage', 'product.ty_product_id', '=', 'company_brokerage.ty_product_id')
                ->select(DB::raw('SUM(brokerage) as two_brokerage'))
                ->first()->toArray();
            $asd['two_brokerage'] = round($two_brokerage['two_brokerage']/100,2);
            $asd['name'] = $i;
            $two[] = $asd;
        }
        $arr = array();
        $arr['one'] = $one;
        $arr['two'] = $two;
        return $arr;

    }
    //获取分类
    public function type($str){
        $res = $this->productGetCategory(Product::where('ty_product_id','>=',0)->get());
        $arr = $res['insurance'];
        foreach ($arr as $key => $val) {
            $arr[$key]=str_replace("-","",$val);
        }
        foreach ($arr as $key => $value) {
            if($value == $str){
                $keyValue = $key;
            }
        }
        $keyValue=explode("-",$keyValue);
        $content['one'] = $arr[$keyValue[0]];
        $content['two'] = $arr[$keyValue[0]."-".$keyValue[1]];
        return $content;
    }
    //天眼后台获取分类
    //todo  缓存
    public function productGetCategory($res){
        //直到五级分类
        $categorys = isset($res[0]['json']) ? json_decode($res[0]['json'],true)['categorys'] : $res;
        $one  = [];
        $two  = [];
        $three  = [];
        $four  = [];
        $five  = [];
        foreach($categorys as $v){
            $name = str_repeat('-' , $v['sort']) . $v['name'];
            if(preg_match('/-/', $name)){
                if(preg_match('/--/', $name)){
                    if(preg_match('/---/', $name)){
                        if(preg_match('/----/', $name)){
                            $five[$v['path'].'-'.$v['id']] = $name;
                        }else{
                            $four[$v['path'].'-'.$v['id']] = $name;
                        }
                    }else{
                        $three[$v['path'].'-'.$v['id']] = $name;
                    }
                }else{
                    $two[$v['path'].'-'.$v['id']] = $name;
                }
            }else{
                $one[$v['path'].'-'.$v['id']] = $name;
            }
        }
        foreach ($one as $key=>$value){
            foreach ($two as $key_two=>$value_two){
                if(explode("-", $key)[0].explode("-", $key)[1].','==explode("-", $key_two)[0]){
                    if(explode("-", $key)[1]=='1'){
                        $company_category[explode("-", $key_two)[1]] = $value_two;
                    }
                    if(explode("-", $key)[1]=='2'){
                        $insurance_category[explode("-", $key_two)[1]] = $value_two;
                    }
                    if(explode("-", $key)[1]=='3'){
                        $clause_category[explode("-", $key_two)[1]] = $value_two;
                    }
                    if(explode("-", $key)[1]=='4'){
                        $duty_category[explode("-", $key_two)[1]] = $value_two;
                    }
                }
            }
        }
        foreach ($three as $key_three=>$value_three){
            foreach ($company_category as $k_c=>$v_c){
                if(explode(',',explode("-", $key_three)[0])[3]==$k_c){
                    $company_category[$k_c.'-'.explode("-", $key_three)[1]]=$value_three;
                }
            }
        }
        foreach ($three as $key_three=>$value_three){
            foreach ($insurance_category as $k_c=>$v_c){
                if(explode(',',explode("-", $key_three)[0])[3]==$k_c){
                    $insurance_category[$k_c.'-'.explode("-", $key_three)[1]]=$value_three;
                }
            }
        }
        foreach ($four as $k=>$v){
            foreach ($company_category as $k_c=>$v_c){

                if(count(explode('-',$k_c))=='2'){
                    if(explode(',',explode('-',$k)[0])[4]==explode('-',$k_c)[1]){
                        $company_category[$k_c.'-'.explode("-", $k)[1]]=$v;
                    }
                }
            }
        }
        foreach ($four as $k=>$v){
            foreach ($insurance_category as $k_c=>$v_c){
                if(count(explode('-',$k_c))=='2'){
                    if(explode(',',explode('-',$k)[0])[4]==explode('-',$k_c)[1]){
                        $insurance_category[$k_c.'-'.explode("-", $k)[1]]=$v;
                    }
                }
            }
        }
        $return_data = array(
            'company' => isset($company_category)? $company_category : [],
            'insurance' => isset($insurance_category)? $insurance_category:[],
            'clause' => isset($clause_category)? $clause_category:[],
            'duty' => isset($duty_category)? $duty_category:[],
        );
        //存缓存（有效期为12小时）
//        $expiresAt = \Carbon\Carbon::now()->addMinutes(24*60);
//        Cache::forever('ty_product_categorys', $return_data,$expiresAt);
        return $return_data;
    }
}