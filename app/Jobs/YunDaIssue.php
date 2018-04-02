<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Models\ChannelPrepareInfo;
use App\Models\Warranty;
use Illuminate\Http\Request;
use App\Helper\DoChannelsSignHelp;
use App\Helper\RsaSignHelp;
use App\Helper\AesEncrypt;
use Ixudra\Curl\Facades\Curl;
use Validator, DB, Image, Schema;
use App\Models\Channel;
use App\Models\ChannelOperate;
use App\Models\UserChannel;
use App\Models\UserChannels;
use App\Models\User;
use App\Models\UserContact;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Session,Cache;
use App\Models\Order;
use App\Models\OrderParameter;
use App\Models\WarrantyPolicy;
use App\Models\WarrantyRecognizee;
use App\Models\WarrantyRule;
use App\Models\OrderBrokerage;
use App\Helper\LogHelper;
use App\Models\Product;
use App\Models\ApiInfo;
use App\Models\Bank;
use App\Models\UserBank;
use App\Models\Competition;
use App\Models\CompanyBrokerage;
use App\Models\OrderPrepareParameter;
use App\Models\ChannelClaimApply;
use App\Models\ChannelInsureInfo;
use App\Helper\Issue;
use App\Helper\UploadFileHelper;
use App\Helper\IdentityCardHelp;
use App\Models\ChannelContract;
use Illuminate\Console\Command;


class YunDaIssue implements ShouldQueue
{
    protected $param;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;



    /**
     * Create a new command instance.
     * @return void
     * 初始化
     *
     */
    public function __construct($param)
    {
        $this->sign_help = new DoChannelsSignHelp();
        $this->signhelp = new RsaSignHelp();
        $this->param = $param;
        set_time_limit(0);//永不超时
    }


    /**
     * 微信代扣支付
     * 定时任务，跑支付
     */
    public function handle()
    {
        set_time_limit(0);//永不超时
        //已支付订单信息
        $union_order_code = $this->param;
        $warranty_rule = WarrantyRule::where('union_order_code', $union_order_code)->first();
        if (count($warranty_rule) == '0') {
            LogHelper::logError(date('Y-m-d H:i:s', time()), 'YD_issue_fail_' . $union_order_code);
            die;
        }
        //出单操作
        $i = new Issue();
        $result = $i->issue($warranty_rule);
        if (!$result) {
            $respose = json_encode(['status' => '503', 'content' => '出单失败'], JSON_UNESCAPED_UNICODE);
            LogHelper::logError($respose, 'YD_issue_fail_' . $union_order_code);
        }
        ChannelOperate::where('proposal_num', $union_order_code)
            ->update(['issue_status' => '200']);
        $respose = json_encode(['status' => '200', 'content' => '出单完成'], JSON_UNESCAPED_UNICODE);
        LogHelper::logSuccess($respose, 'YD_issue_success_' . $union_order_code);
        LogHelper::logSuccess(date('Y-m-d H:i:s', time()), 'YD_issue_end');
    }
}