<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/4/2
 * Time: 14:12
 * 保单管理新流程
 */
namespace App\Http\Controllers\BackendControllers;

use Illuminate\Support\Facades\DB;
use League\Flysystem\Exception;
use Illuminate\Http\Request;

use App\Models\Person;
use App\Models\CustWarranty;
use App\Models\CustWarrantyPolicy;

use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;

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
        $warranty_status = config('status_setup.warranty');//保单状态
        $status_id = isset($_GET['status'])?$_GET['status']:'0';//不传保单状态,默认查询所有
        $date = isset($_GET['date'])?$_GET['date']:'0';//不传，默认查询今天
        $date_start = isset($_GET['date_start'])?$_GET['date_start']:'';
        $date_end = isset($_GET['date_end'])?$_GET['date_end']:'';
        $from = isset($_GET['from'])?$_GET['from']:'0';//不传保单来源，默认查询所有
        $page = isset($_GET['page'])?$_GET['page']:'1';//分页默认为1
        if($status_id == '0' || $status_id == ""&&empty($date)&&empty($date_start)&&empty($date_end)){
            $warranty_res = CustWarranty::orderBy('created_at','desc')
                ->paginate(config('list_num.backend.agent'));
        }

        return view('backend_v2.warranty.warranty_list',compact('warranty_status'));

    }

    /**
     * 保单详情
     * @access public
     * @params $union_order_code  联合订单号
     * @return view
     *
     */
    public function warrantyInfo($union_order_code)
    {
        return view('backend_v2.warranty.warranty_info');
    }

    /**
     * 投保人信息
     * @access public
     *
     */
    public function warrantyPolicy($union_order_code)
    {

    }

    /**
     * 被保人信息
     * @access public
     */
    public function warrantyRecognizee($union_order_code)
    {

    }
}