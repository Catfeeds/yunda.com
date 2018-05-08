<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelJointLoginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		//渠道详情表
		Schema::create('channel_joint_login', function (Blueprint $table) {
			$table->increments('id')->comment('主键id');
			$table->string('phone',11)->comment('手机号');
			$table->string('login_start',20)->comment('联合登录时间(第一次)');
			$table->string('operate_time',20)->comment('当日时间，方便查询某一天的联合登录人数');
			$table->engine = 'InnoDB';
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::dropIfExists('channel_joint_login');
    }
}
