<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/4/17
 * Time: 11:08
 */

namespace App\Helper;

use DB;
use App\Models\Person;
use App\Models\CustWarranty;
use App\Models\CustWarrantyPerson;
use App\Models\ChannelOperate;

class AddOrderHelper
{


	/**
	 * 添加投保返回信息
	 * @access public
	 * @param $return_data|订单返回数据
	 * @param $prepare|预投保信息
	 * @param $policy_res|投保人信息
	 * @param $holder_res|被保人信息
	 * @return mixed
	 * 新版表结构,保单返回数据只需要添加cust_warranty、cust_warranty_person、channel_operate、user
	 */
	public function doAddOrder($return_data, $prepare, $policy_res,$holder_res)
	{
//		dump($return_data);
//		dump($prepare);
//		dump($policy_res);
//		dump($holder_res);
//		die;
		DB::beginTransaction();//开启事务
		try{
			$policy_check_res  = Person::where('papers_code',$policy_res['ty_toubaoren_id_number'])
				->select('id','cust_type')
				->first();
			if(empty($policy_check_res)){
				$user_policy_res = new Person();
				$user_policy_res->name = $policy_res['ty_toubaoren_name'];
				$user_policy_res->papers_type = $policy_res['ty_toubaoren_id_type'];
				$user_policy_res->papers_code = $policy_res['ty_toubaoren_id_number'];
				$user_policy_res->papers_start = '';
				$user_policy_res->papers_end = '';
				$user_policy_res->sex = $policy_res['ty_toubaoren_sex'];
				$user_policy_res->birthday = $policy_res['ty_toubaoren_birthday'];
				$user_policy_res->address = $policy_res['ty_toubaoren_provinces'].'-'.$policy_res['ty_toubaoren_city'].'-'.$policy_res['ty_toubaoren_county'];
				$user_policy_res->address_detail = $policy_res['channel_user_address'];
				$user_policy_res->phone = $policy_res['ty_toubaoren_phone'];
				$user_policy_res->email = $policy_res['ty_toubaoren_email'];
				$user_policy_res->postcode = '';
				$user_policy_res->cust_type = '1';//客户类型，1：普通用户，2：代理人
				$user_policy_res->authentication = '1';//认证状态，1：未认证，2：已认证
				$user_policy_res->up_url = '';
				$user_policy_res->down_url = '';
				$user_policy_res->person_url = '';
				$user_policy_res->head = '';
				$user_policy_res->company_id = '';
				$user_policy_res->del = '0';
				$user_policy_res->status = '1';
				$user_policy_res->created_at = time();
				$user_policy_res->save();
			}
			foreach($holder_res as $value){
				$holder_check_res = Person::where('papers_code',$value['ty_beibaoren_id_number'])
					->select('id','cust_type')
					->first();
				if(empty($holder_check_res)){
					$user_holder_res = new Person();
					$user_holder_res->name = $value['ty_beibaoren_name'];
					$user_holder_res->papers_type = $value['ty_beibaoren_id_type'];
					$user_holder_res->papers_code = $value['ty_beibaoren_id_number'];
					$user_holder_res->papers_start = '';
					$user_holder_res->papers_end = '';
					$user_holder_res->sex = $value['ty_beibaoren_sex'];
					$user_holder_res->birthday = $value['ty_beibaoren_birthday'];
					$user_holder_res->address = $policy_res['ty_beibaoren_provinces'].'-'.$policy_res['ty_beibaoren_city'].'-'.$policy_res['ty_beibaoren_county'];
					$user_holder_res->address_detail = $value['ty_beibaoren_address'];
					$user_holder_res->phone = $value['ty_beibaoren_phone'];
					$user_holder_res->email = $value['ty_beibaoren_email'];
					$user_holder_res->postcode = '';
					$user_holder_res->cust_type = '1';//客户类型，1：普通用户，2：代理人
					$user_holder_res->authentication = '1';//认证状态，1：未认证，2：已认证
					$user_holder_res->up_url = '';
					$user_holder_res->down_url = '';
					$user_holder_res->person_url = '';
					$user_holder_res->head = '';
					$user_holder_res->company_id = '';
					$user_holder_res->del = '0';
					$user_holder_res->status = '1';
					$user_holder_res->created_at = time();
					$user_holder_res->updated_at = time();
					$user_holder_res->save();
				}
			}
			$user_res = Person::where('papers_code',$policy_res['ty_toubaoren_id_number'])
				->select('id','cust_type')
				->first();
			$cust_warranty = new CustWarranty();
			$cust_warranty->warranty_uuid = $return_data['union_order_code'];//内部保单唯一标识  TODO  暂时先用union_order_code代替
			$cust_warranty->pro_policy_no = $return_data['union_order_code'];//投保单号
			$cust_warranty->warranty_code = '';//保单号
			$cust_warranty->company_id = '';//公司id,固定值
			$cust_warranty->user_id = $user_res['id'];//用户id
			$cust_warranty->user_type = $user_res['cust_type'];//用户类型
			$cust_warranty->agent_id = '';//代理人id
			$cust_warranty->ditch_id = '';//渠道id
			$cust_warranty->plan_id = '';//计划书id
			$cust_warranty->product_id = $prepare['private_p_code'];//产品id
			$cust_warranty->premium = $return_data['total_premium'];//价格
			$cust_warranty->start_time = '';//起保时间
			$cust_warranty->end_time = '';//保障结束时间
			$cust_warranty->ins_company_id = '';//保险公司id
			$cust_warranty->count = '1';//购买份数
			$cust_warranty->pay_time = '';//支付时间
			$cust_warranty->pay_way = '3';//支付方式1 银联 2 支付宝 3 微信 4现金
			$cust_warranty->by_stages_way = '';//分期方式
			$cust_warranty->is_settlement = '0';//佣金 0表示未结算，1表示已结算
			$cust_warranty->warranty_url = '';//电子保单下载地址
			$cust_warranty->warranty_from = '2';//保单来源 1 自购 2线上成交 3线下成交 4导入
			$cust_warranty->type = '1';//保单类型,1表示个人保单，2表示团险保单，3表示车险保单
			$cust_warranty->check_status = '3';//核保状态
			$cust_warranty->pay_status = '0';//支付状态
			$cust_warranty->warranty_status = '2';//保单状态
			$cust_warranty->created_at = time();//创建时间
			$cust_warranty->updated_at = time();//更新时间
			$cust_warranty->state = '1';//删除标识 0删除 1可用
			$cust_warranty->save();
			//投保人信息
			$cust_warranty_person = new CustWarrantyPerson();
			$cust_warranty_person->warranty_uuid = '';//内部保单唯一标识
			$cust_warranty_person->out_order_no = $return_data['union_order_code'];//被保人单号
			$cust_warranty_person->type = '1';//人员类型: 1投保人 2被保人 3受益人
			$cust_warranty_person->relation_name = '';//被保人 投保人的（关系）
			$cust_warranty_person->name = $policy_res['ty_toubaoren_name'];//姓名
			$cust_warranty_person->card_type = $policy_res['ty_toubaoren_id_type'];//证件类型（1为身份证，2为护照，3为军官证）
			$cust_warranty_person->card_code = $policy_res['ty_toubaoren_id_number'];//证件号
			$cust_warranty_person->phone = $policy_res['ty_toubaoren_phone'];//手机号
			$cust_warranty_person->occupation = '';//职业
			$cust_warranty_person->birthday = $policy_res['ty_toubaoren_birthday'];//生日
			$cust_warranty_person->sex = $policy_res['ty_toubaoren_sex'];//性别 1 男 2 女 '
			$cust_warranty_person->age = '';//年龄
			$cust_warranty_person->email = $policy_res['ty_toubaoren_email'];//邮箱
			$cust_warranty_person->nationality = '中国';//国籍
			$cust_warranty_person->annual_income = '';//年收入
			$cust_warranty_person->height = '';//身高
			$cust_warranty_person->weight = '';//体重
			$cust_warranty_person->area = $policy_res['ty_toubaoren_provinces'].'-'.$policy_res['ty_toubaoren_city'].'-'.$policy_res['ty_toubaoren_county'];//地区
			$cust_warranty_person->address = $policy_res['channel_user_address'];//详细地址
			$cust_warranty_person->start_time = '';//起保时间
			$cust_warranty_person->end_time = '';//保障结束时间
			$cust_warranty_person->created_at = time();//创建时间
			$cust_warranty_person->updated_at = time();//更新时间
			$cust_warranty_person->save();
			//被保人信息
			if(count($holder_res)>0){//多个被保人
				foreach($holder_res as $value){
					$cust_warranty_person = new CustWarrantyPerson();
					$cust_warranty_person->warranty_uuid = '';//内部保单唯一标识
					$cust_warranty_person->out_order_no = $return_data['union_order_code'];//被保人单号
					$cust_warranty_person->type = '2';//人员类型: 1投保人 2被保人 3受益人
					$cust_warranty_person->relation_name = '';//被保人 投保人的（关系）
					$cust_warranty_person->name = $value['ty_beibaoren_name'];//姓名
					$cust_warranty_person->card_type = $value['ty_beibaoren_id_type'];//证件类型（1为身份证，2为护照，3为军官证）
					$cust_warranty_person->card_code = $value['ty_beibaoren_id_number'];//证件号
					$cust_warranty_person->phone = $value['ty_beibaoren_phone'];//手机号
					$cust_warranty_person->occupation = '';//职业
					$cust_warranty_person->birthday = $value['ty_beibaoren_birthday'];//生日
					$cust_warranty_person->sex = $value['ty_beibaoren_sex'];//性别 1 男 2 女 '
					$cust_warranty_person->age = '';//年龄
					$cust_warranty_person->email = $value['ty_beibaoren_email'];//邮箱
					$cust_warranty_person->nationality = '中国';//国籍
					$cust_warranty_person->annual_income = '';//年收入
					$cust_warranty_person->height = '';//身高
					$cust_warranty_person->weight = '';//体重
					$cust_warranty_person->area = $value['ty_beibaoren_provinces'].'-'.$value['ty_beibaoren_city'].'-'.$value['ty_beibaoren_county'];//地区
					$cust_warranty_person->address = $value['channel_user_address'];//详细地址
					$cust_warranty_person->start_time = '';//起保时间
					$cust_warranty_person->end_time = '';//保障结束时间
					$cust_warranty_person->created_at = time();//创建时间
					$cust_warranty_person->updated_at = time();//更新时间
					$cust_warranty_person->save();
				}
			}
			//渠道操作表
			$ChannelOperate = new ChannelOperate();
			$ChannelOperate->channel_user_code = $policy_res['ty_toubaoren_id_number'];
			$ChannelOperate->order_id = $cust_warranty->id;
			$ChannelOperate->proposal_num = $return_data['union_order_code'];
			$ChannelOperate->prepare_status = '200';
			$ChannelOperate->operate_time = date('Y-m-d',time());
			$ChannelOperate->save();
			DB::commit();
			return true;
		}catch (\Exception $e)
		{
			DB::rollBack();
			LogHelper::logChannelError([$return_data, $prepare], $e->getMessage(), 'addOrder');
			return false;
		}
	}
}