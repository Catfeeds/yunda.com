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
use App\Jobs\DemoTest;
use App\Jobs\YunDaIssue;


class YunDaPayInsure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $param;

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
     *
     * 异步队列，投保操作
     *
     */
    public function handle()
    {
        //用户身份信息
        $input = $this->param;
        $input = '{"channel_code":"YD","insured_name":"王磊","insured_code":"4108811994060565141234","insured_phone":"15701681527","insured_email":"wangs@inschos.com","insured_province":"北京市","insured_city":"北京市","insured_county":"东城区","insured_address":"夕照寺中街19号","bank_name":"工商银行","bank_code":"6222022002006651860 ","bank_phone":"15701681527","bank_address":"北京市东城区广渠门内广渠路支行"}';
        $requset_url = config('yunda.request_url');
        LogHelper::logChannelSuccess($requset_url, 'YD_request_url');
        $response = Curl::to($requset_url)
            ->returnResponseObject()
            ->withData($input)
            ->withTimeout(60)
            ->post();
        LogHelper::logChannelSuccess($response, 'YD_insure_result');
    }
}