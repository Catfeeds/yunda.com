<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustWarranty extends Model
{
    //新保单表
    protected $table = 'cust_warranty';

    //关联用户
    public function person()
    {
        return $this->hasOne('App\Models\Person','id','user_id');
    }
    //关联被保人
    public function warrantyPerson()
    {
        return $this->hasMany('App\Models\CustWarrantyPerson','warranty_uuid','warranty_uuid');
    }
}
