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

use App\Models\Person;
use App\Models\CustWarranty;
use App\Models\CustWarrantyPolicy;
use Illuminate\Http\Request;
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


    public function __construct(\Illuminate\Http\Request $request)
    {
        parent::__construct($request);
    }

    public function getWarranty(){
    }
    public function getWarrantyDetail($union_order_code){
    }
    public function getPolicyMessage($uuid,$policy_id){
    }
    public function getRecognizeeMessage($uuid,$order_id){
    }
}