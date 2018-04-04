<?php
/**
 * 
 * Author: mingyang <7789246@qq.com>
 * Date: 2018-04-02
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ClaimYunda extends Model{
    protected $table = "claim_yunda";

    //关联表
    public function claim_warranty()
    {
        return $this->hasOne('App\Models\Warranty','id','warranty_id');
    }
//    //关联单据表
//    public function claim_url()
//    {
//        return $this->hasMany('App\Models\ClaimUrl','claim_id','id');
//    }
//    //关联状态
//    public function claim_status()
//    {
//        return $this->hasOne('App\Models\Status','id','status');
//    }
//
//    //关联理赔处理表
//    public function claim_record()
//    {
//        return $this->hasOne('App\Models\ClaimRecord','claim_id','id');
//    }
}
