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
			$table->bigInteger('login_start',20)->comment('联合登录时间(第一次)');
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
