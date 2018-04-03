<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustWarranty extends Model
{
    //新保单表
    protected $table = 'cust_warranty';

    //关联用户
    public function warrantyPerson()
    {
        return $this->hasOne('App\Models\Person','id_code','user_id');
    }
    //关联被保人
    public function warrantyPolicy()
    {
        return $this->hasMany('App\Models\CustWarrantyPolicy','private_code','private_code');
    }
}
