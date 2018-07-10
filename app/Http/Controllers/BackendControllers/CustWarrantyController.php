<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/4/2
 * Time: 14:12
 * 保单管理新流程
 */
namespace App\Http\Controllers\BackendControllers;

use App\Http\Controllers\BackendControllers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Exception;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Excel;
use Log;

use App\Models\Agent;
use App\Models\Clause;
use App\Models\CodeType;
use App\Models\Company;
use App\Models\Ditch;
use App\Models\DitchAgent;
use App\Models\Product;
use App\Models\Person;
use App\Models\CustWarranty;
use App\Models\CustWarrantyPerson;

use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;

use App\Services\ReadExcel;
use App\Services\UploadImage;






class CustWarrantyController extends BaseController{

    protected $request;

    protected $log_helper;

    /**
     * 初始化
     * @access public
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->log_helper = new LogHelper();
        $this->sign_help = new RsaSignHelp();
    }

    /**
     * 保单列表
     * @access public
     * @return view
     * 查询条件：
     * 1.保单状态(status)：0全部保单，1待核保，2核保失败，3未支付-核保成功，4支付中,5支付失败,6支付成功，7保障中,8待生效,9待续保，10已失效，11已退保
     * 2.保单时间(date)：0今天，1昨天，7最近七天，30最近一个月，自定义时间段(date_start,date_end)
     * 3.保单来源(from)：0全部来源，1线上成交，2线下成交
     * 4.分页(page)
     *
     */
    public function WarrantyList()
    {
        $status_id = isset($_GET['status_id'])?$_GET['status_id']:'-1';//不传保单状态,默认查询所有
        $date = isset($_GET['date'])?$_GET['date']:'0';//不传，默认查询今天
        $date_start = isset($_GET['date_start'])?$_GET['date_start']:'';
        $date_end = isset($_GET['date_end'])?$_GET['date_end']:'';
        $page = isset($_GET['page'])?$_GET['page']:'1';//分页默认为1
        if($status_id == '-1' || $status_id == ""&&empty($date)&&empty($date_start)&&empty($date_end)){
            $warranty_res = CustWarranty::where('state','1')
                ->with('person')
                ->orderBy('created_at','desc')
                ->paginate(config('list_num.backend.agent'));
        }else{
            $warranty_res = CustWarranty::where('warranty_status',$status_id)->where('state','1')
                 ->with('person')
                ->orderBy('created_at','desc')
                ->paginate(config('list_num.backend.agent'));
        }
        if(!empty($date)){
            switch ($date){
                case '0':
                    $warranty_res = CustWarranty::where('state','1')
                        ->with('person')
                        ->orderBy('created_at','desc')
                        ->paginate(config('list_num.backend.agent'));
                    break;
                case '1':
                    $warranty_res = CustWarranty::where('state','1')
                        ->with('person')
                        ->orderBy('created_at','desc')
                        ->paginate(config('list_num.backend.agent'));
                    break;
                case '-1':
                    $warranty_res = CustWarranty::where('state','1')
                        ->where('created_at','>',date('Ymd',strtotime(date('Y-m-d',time()-24*3600*2).'00:00:00')))
                        ->where('created_at','<',date('Ymd',strtotime(date('Y-m-d',time()).'00:00:00')))
                        ->with('person')
                        ->orderBy('created_at','desc')
                        ->paginate(config('list_num.backend.agent'));
                    break;
                case '7':
                    $warranty_res = CustWarranty::where('state','1')
                        ->where('created_at','>',date('Ymd',strtotime(date('Y-m-d',time()-24*3600*7).'00:00:00')))
                        ->with('person')
                        ->orderBy('created_at','desc')
                        ->paginate(config('list_num.backend.agent'));
                    break;
                case '30':
                    $warranty_res = CustWarranty::where('state','1')
                        ->where('created_at','>',date('Ymd',strtotime(date('Y-m-d',time()-24*3600*30).'00:00:00')))
                        ->with('person')
                        ->orderBy('created_at','desc')
                        ->paginate(config('list_num.backend.agent'));
                    break;
            }
            if(!empty($date_start)&&!empty($date_end)){
                $warranty_res = CustWarranty::where('state','1')
                    ->where('created_at','>',date('Ymd',strtotime($date_start.'00:00:00')))
                    ->where('created_at','<',date('Ymd',strtotime($date_end.'23:59:59')))
                    ->with('person')
                    ->orderBy('created_at','desc')
                    ->paginate(config('list_num.backend.agent'));
            }
        }
        $list = $warranty_res;
        $count = $warranty_res->total();
		$warranty_status = config('status_setup.warranty_status');//保单状态
		$pay_status = config('status_setup.pay_status');//支付状态
		$check_status = config('status_setup.check_status');//核保状态
        return view('backend_v2.warranty.warranty_list',compact('pay_status','check_status','warranty_status','list','count','status_id','date','date_start','date_end'));

    }

    /**
     * 保单详情
     * @access public
     * @params $warranty_uuid  保单唯一标识
     * @return view
     *
     */
    public function warrantyInfo($warranty_uuid)
    {
        $warranty_res = CustWarranty::where('warranty_uuid',$warranty_uuid)
            ->with('warrantyPerson')
            ->first();
        $agent_id = $warranty_res['agent_id'];
        $ditch_id = $warranty_res['ditch_id'];
        $product_id= $warranty_res['product_id'];
        $company_id= '';
        $agent_res = [];//代理人
        $ditch_res = [];//渠道
        $product_res = [];//产品
        $company_res = [];//保险公司
        $cust_policy_res = CustWarrantyPerson::where('warranty_uuid',$warranty_uuid)->get();
        $policy_res = [];//投保人
        $insured_res = [];//被保人
        $beneficiary_res = [];//受益人
        return view('backend_v2.warranty.warranty_info',compact('warranty_res','agent_res','ditch_res','product_res','insured_res','policy_res','beneficiary_res','company_res'));
    }
}