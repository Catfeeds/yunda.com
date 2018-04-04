<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/3/28
 * Time: 10:12
 * 韵达快递保--投保设置
 */
namespace App\Http\Controllers\ChannelsApiControllers\Yunda;

use App\Models\Person;
use Illuminate\Http\Request;
use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Models\ChannelInsureSeting;

class SetingController
{

    protected $request;

    protected $log_helper;

    protected $person_code;

    /**
     * 初始化
     * @access public
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->log_helper = new LogHelper();
        $this->sign_help = new RsaSignHelp();
        $access_token = $this->request->header('access-token');
        $access_token_data = json_decode($this->sign_help->base64url_decode($access_token),true);
        $this->person_code = $access_token_data['person_code'];
    }

    /**
     * 设置列表页面
     * @access public
     * @return view
     *
     */
    public function insureSetupList(){
        $person_code = $this->person_code;
        $person_code = '410881199406056514';
        return view('channels.yunda.insure_setup_list',compact('person_code'));
    }

    /**
     * 产品设置页面
     * @access public
     * @return view
     *
     */
    public function insureSeting(){
        $person_code = $this->person_code;
        $person_code = '410881199406056514';
        return view('channels.yunda.insure_seting',compact('person_code'));
    }

    /**
     * 自动投保页面
     * @access public
     * @return view
     *
     */
    public function insureAuto(){
        $person_code = $this->person_code;
        $person_code = '410881199406056514';
        $auto_res = ChannelInsureSeting::where('cust_cod',$person_code)
            ->select('auto_insure_status','auto_insure_type','auto_insure_time')
            ->first();
        return view('channels.yunda.insure_auto',compact('auto_res','person_code'));
    }

    /**
     * 自动投保设置
     * @access public
     * @return view
     *
     */
    public function doInsureAuto(){
        $input = $this->request->all();
        $auto_insure_status = $input['auto_insure_status'];
        $auto_insure_type = $input['auto_insure_type'];
        $auto_insure_price = config('insure_price.yunda')[$auto_insure_type];
        $cust_res['papers_code'] = $input['person_code'];
        $repeat_res = ChannelInsureSeting::where('cust_cod',$input['person_code'])
            ->select('id')
            ->first();
        $user_res = Person::where('papers_code',$input['person_code'])->select('id')->first();
        if(empty($repeat_res)){
            ChannelInsureSeting::insert([
                'cust_id'=>$user_res['id'],
                'cust_code'=>$cust_res['papers_code'],
                'cust_type'=>'user',
                'authorize_status'=>'',
                'authorize_start'=>'',
                'auto_insure_status'=>$auto_insure_status,
                'auto_insure_type'=>$auto_insure_type,
                'auto_insure_price'=>$auto_insure_price,
                'auto_insure_time'=>time(),
            ]);
            return json_encode(['status'=>'200','msg'=>'自动投保设置成功']);
        }
        //todo  判断有没有$auto_insure_type，没有传参，只更新状态
        ChannelInsureSeting::where(  'cust_id',$user_res['id'])->update([
            'auto_insure_status'=>$auto_insure_status,
            'auto_insure_type'=>$auto_insure_type,
            'auto_insure_price'=>$auto_insure_price,
            'auto_insure_time'=>time(),
        ]);
        return json_encode(['status'=>'200','msg'=>'自动投保设置成功']);
    }

    /**
     * 个人中心
     * @access public
     * @return view
     *
     */
    public function userInfo(){
        $person_code = $this->person_code;
        $person_code = '410881199406056514';
        $user_res = Person::where('papers_code',$person_code)->select('name','phone','papers_code')->first();
        return view('channels.yunda.user_info',compact('user_res'));
    }

}