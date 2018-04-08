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


class YunDaPay implements ShouldQueue
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
        $input = '{"operate_code":"","channel_code":"YD","courier_state":"","courier_start_time":"","p_code":"","is_insure":"","channel_back_url":"","channel_user_name":"王石磊","channel_user_code":"410881199406056514","channel_user_phone":"15701681524","channel_user_email":null,"channel_user_address":null,"channel_bank_code":null,"channel_bank_name":null,"channel_bank_address":null,"channel_bank_phone":null,"channel_provinces":null,"channel_city":null,"channel_county":null}';
        LogHelper::logChannelSuccess($input, 'YD_isnure_msg');
        $requset_url = 'http://59.110.136.249:9200/warranty/insure';
        $response = Curl::to($requset_url)
            ->returnResponseObject()
            ->withData($input)
            ->withTimeout(60)
            ->post();
        print_r($response);die;
    }
}