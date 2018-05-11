<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/5/8
 * Time: 18:14
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelJointLogin extends Model
{
	protected $table = "channel_joint_login";

	//人员基础信息
	public function person()
	{
		return $this->hasOne('App\Models\Person','phone', 'phone');
	}
}