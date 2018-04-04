<?php
/**
 * 
 * Author: mingyang <7789246@qq.com>
 * Date: 2018-04-03
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ClaimYundaInfo extends Model{
    protected $table = "claim_yunda_info";

    //关联表
    public function claim_warranty()
    {
        return $this->hasOne('App\Models\Warranty','claim_id','id');
    }

}
