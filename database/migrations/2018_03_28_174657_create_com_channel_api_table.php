<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComChannelApiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        //todo 用户银行卡表  同 inschos_cloud  bank
        Schema::create('bank', function (Blueprint $table) {
            $table->increments('id')->comment('主键id,自增');
            $table->integer('cust_id',10)->comment('user_id,与用户表关联');
            $table->string('cust_type',20)->comment('用户类型');
            $table->string('bank')->comment('开户银行');
            $table->string('bank_cod',30)->comment('银行卡号');
            $table->string('bank_city',30)->comment('开户行地址');
            $table->integer('bank_type')->comment('银行卡类型');
            $table->string('phone',11)->comment('预留手机号');
            $table->integer('bank_del')->comment('删除');
            $table->engine = 'InnoDB';
        });
        //渠道用户投保信息与设置
        //代扣授权针对用户，不是针对银行卡，授权信息可以和保障天数存在一张表里
        Schema::create('channel_insure_seting', function (Blueprint $table) {
            $table->increments('id')->comment('主键id,自增');
            $table->integer('cust_id')->comment('user_id,与用户表关联');
            $table->string('cust_type',20)->comment('用户类型');
            $table->string('cust_cod')->comment('身份证号');
            $table->integer('authorize_status')->comment('授权状态：默认为0关闭,1开启');//默认为0关闭,1开启
            $table->string('authorize_start')->comment('授权时间');
            $table->string('authorize_bank')->comment('授权银行卡号');
            $table->integer('auto_insure_status')->comment('自动投保状态：默认为1开启，0关闭');//默认为1开启，0关闭
            $table->integer('auto_insure_type')->comment('自动投保,购保类型，有不同天数的保险（韵达）');
            $table->integer('auto_insure_price')->comment('自动投保,购保价格，有不同天数的保险价格不同，1天2元，3天5元，10天13元（韵达）');
            $table->string('auto_insure_time')->comment('自动投保开通/关闭时间，自动投保只能关闭24小时');//自动投保只能关闭24小时
            //每次联合登录，可以先查询这张表。如果没有数据，说明首次访问，进行授权签约操作。
            //如果有数据，判断保障是否过期，没有过期，显示在保中；保障过期，触发投保操作，更新数据
            $table->string('warranty_code')->comment('保单号');
            $table->string('insure_days')->comment('保障天数');
            $table->string('insure_start')->comment('起保时间');
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
        Schema::dropIfExists('bank');
        Schema::dropIfExists('channel_insure_seting');
    }
}
