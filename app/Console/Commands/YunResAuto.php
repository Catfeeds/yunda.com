<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/05/04
 * Time: 12:03
 * 还原自动投保设置-24小时
 */
namespace App\Console\Commands;

use App\Models\ChannelInsureSeting;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class YunResAuto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yunda_reset_auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'yunda_reset_auto Command description';

    /**
     * Create a new command instance.
     * @return void
     * 初始化
     *
     */
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
        set_time_limit(0);//永不超时
    }


	/**
	 * 还原自动投保设置-24小时
	 */
    public function handle()
    {
    	//获取关闭自动投保的，auto_insure_status 默认为1开启，0关闭
		//自动投保开通/关闭时间，自动投保只能关闭24小时,算自然天，第二天凌晨零点开启关闭的
		ChannelInsureSeting::where('auto_insure_status','0')
			->update([
				'auto_insure_status'=>'1',
			]);
    }
}