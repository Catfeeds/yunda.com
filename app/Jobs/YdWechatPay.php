<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Ixudra\Curl\Facades\Curl;
use Validator, DB, Image, Schema;
use Session,Cache;
use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Models\ChannelOperate;
use App\Models\CustWarranty;
use App\Models\Person;

class YdWechatPay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     *
     * 投保参数
     * @var array
     */
    protected $param;

    /**
     * Create a new command instance.
     * @return void
     * 初始化
     *
     */
    public function __construct($param)
    {
        set_time_limit(0);//永不超时
        $this->param = $param;
    }

    /**
     *
     * 异步队列，投保操作
     *
     */
    public function handle()
    {
        set_time_limit(0);//永不超时
        $input = $this->param;
        $this->signhelp = new RsaSignHelp();
        $person_code  = $input['person_code'];
        $union_order_code = $input['union_order_code'];
        $data = [];
        $data['price'] = '2';
        $data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
        $data['quote_selected'] = '';
        $data['insurance_attributes'] = '';
        $data['union_order_code'] = $union_order_code;
        $data['pay_account'] = $input['openid'];
        $data['contract_id'] = $input['contract_id'];
        $data = $this->signhelp->tySign($data);
        //发送请求
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/ins_curl/wechat_pay_ins')
            ->returnResponseObject()
            ->withData($data)
            ->withTimeout(60)
            ->post();
        LogHelper::logPay($response, 'YD_pay_return_data_'.$union_order_code);
        if($response->status != 200){
            ChannelOperate::where('channel_user_code',$person_code)
                ->where('proposal_num',$union_order_code)
                ->update(['pay_status'=>'500','pay_content'=>$response->content]);
            LogHelper::logPay($person_code, 'YD_pay_fail_'.$union_order_code);
            return false;
        }
        LogHelper::logPay($person_code, 'YD_pay_ok_'.$union_order_code);
        DB::beginTransaction();//开启事务
        try{
            ChannelOperate::where('channel_user_code',$person_code)
                ->where('proposal_num',$union_order_code)
                ->update(['pay_status'=>'200']);
            $person_res = Person::where('papers_code',$person_code)->select('id')->first();
            CustWarranty::where('user_id',$person_res['id'])
                ->update([
                    'pay_status'=>'3',//支付成功
                    'warranty_status'=>'3',//待生效
                ]);
            DB::commit();
            LogHelper::logPay(date('Y-m-d H:i:s',time()), 'YD_pay_end_'.$person_code);
        }catch (\Exception $e){
            DB::rollBack();
            return false;
        }
    }
}