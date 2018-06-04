<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelInsureSeting extends Model{

    protected $table = "channel_insure_seting";

	public function bank()
	{
		return $this->hasOne('App\Models\Bank','bank_code','authorize_bank');
	}
}
