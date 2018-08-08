<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/08/08
 * Time: 15:07
 * 韵达回传信息定时任务（时间没定）
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Helper\DoChannelsSignHelp;
use App\Helper\RsaSignHelp;
use App\Helper\AesEncrypt;
use Validator, DB, Image, Schema;
use Session, Cache;
use App\Jobs\YunDaCallBackInsure;
use App\Models\CustWarranty;


class YundaCallback extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'yundacallback';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'yundacallback Command description';

	/**
	 * Create a new command instance.
	 * @return void
	 * 初始化
	 *
	 */
	public function __construct(Request $request)
	{
		parent::__construct();
		$this->sign_help = new DoChannelsSignHelp();
		$this->signhelp = new RsaSignHelp();
		$this->request = $request;
	}


	/**
	 * 返回给韵达的对账信息
	 *
	 */
	public function handle()
	{

		set_time_limit(0);//永不超时
		$warranty_res = CustWarranty::where('warranty_status', '4')
			->where('created_at', '>=', strtotime(date('Y-m-d')) . '000')//当天开始时间
			->where('created_at', '<', strtotime(date('Y-m-d', strtotime('+1 day'))) . '000')//当天结束时间
			->select('warranty_code', 'start_time', 'end_time', 'pay_time', 'premium')
			->get();
		if (empty($warranty_res)) {
			return false;
		}
		foreach ($warranty_res as $value) {
			dispatch(new YunDaCallBackInsure($value));
		}
	}
}


