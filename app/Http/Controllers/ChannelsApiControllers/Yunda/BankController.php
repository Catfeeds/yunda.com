<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/3/29
 * Time: 14:12
 * 韵达快递保--银行卡管理
 */
namespace App\Http\Controllers\ChannelsApiControllers\Yunda;

use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\ChannelInsureSeting;
use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Models\Person;

class BankController
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
     * 银行卡管理页面
     * @access public
     * @return view
     *
     */
    public function bankIndex(){
        $person_code = $this->person_code;
        $person_code = config('yunda.test_person_code');
        $user_res = Person::where('papers_code',$person_code)->select('id','name','papers_type','papers_code','phone','address')->first();
        $cust_id = $user_res['id'];
        $bank_res = Bank::where('cust_id',$cust_id)
            ->where('bank_del','0')
            ->select('id','bank','bank_code','bank_city','phone')
            ->get()->toArray();
        return view('channels.yunda.bank_index',compact('bank_res'));
    }

    /**
     * 银行卡添加页面
     * @access public
     * @return view
     *
     */
    public function bankBind(){
        $person_code = $this->person_code;
        $person_code = config('yunda.test_person_code');
        return view('channels.yunda.bank_bind');
    }

    /**
     * 银行卡添加操作
     * @access public
     * @return json
     *
     */
    public function doBankBind(){
        $input = $this->request->all();
        $cust_id = $input['cust_id'];
        $bank = $input['bank_name'];
        $bank_cod = $input['bank_code'];
        $bank_city = $input['bank_city'];
        $bank_repeat = Bank::where('cust_id',$cust_id)
            ->where('bank',$bank)
            ->where('bank_code',$bank_cod)
            ->select('id','bank_del')
            ->first();
        if(!empty($bank_repeat)){
            if($bank_repeat['bank_del']=='1'){//已删除
                $update_res = Bank::where('bank_code',$bank_cod)->update([
                    'bank_del'=>'0',
                ]);
                if($update_res){
                    return json_encode(['status'=>'200','msg'=>'银行卡添加成功']);
                }else{
                    return json_encode(['status'=>'500','msg'=>'银行卡添加失败']);
                }
            }
            return json_encode(['status'=>'500','msg'=>'银行卡已存在，请更换银行卡！']);
        }
        $insert_res = Bank::insert([
            'cust_id'=>$cust_id,
            'cust_type'=>'1',
            'bank'=>$bank,
            'bank_code'=>$bank_cod,
            'bank_city'=>$bank_city,
            'phone'=>'',
        ]);
        if($insert_res){
            return json_encode(['status'=>'200','msg'=>'银行卡添加成功']);
        }else{
            return json_encode(['status'=>'500','msg'=>'银行卡添加失败']);
        }
    }

    /**
     * 银行卡查看详情
     * @access public
     * @params cust_id
     * @params bank_id
     * @return view
     * 用户不能删除的银行卡类型：1.从韵达传递过来的数据中获取的银行卡信息 2.银行卡列表中还剩最后一张银行卡时
     * 当满足这两种情况时，不显示删除按钮
     * bank_type  add：用户添加，own：韵达数据本身
     *
     */
    public function bankInfo($bank_id){
        $bank_res = Bank::where('id',$bank_id)
            ->select('cust_id','bank','bank_code','bank_city','bank_type','phone')
            ->first();
        $cust_id = $bank_res['cust_id'];
        $bank_num = Bank::where('cust_id',$cust_id)
            ->select('bank_code')
            ->get();
        $bank_del_status = true;//删除按钮显示状态，默认显示
        if(count($bank_num)<=1){//只剩最后一张银行卡
            $bank_del_status = false;
        }
        if($bank_res['bank_type']=='own'){//从韵达传递过来的数据中获取的银行卡信息
            $bank_del_status = false;
        }
        return view('channels.yunda.bank_info',compact('bank_res','bank_del_status'));
    }

    /**
     * 银行卡删除操作
     * @access public
     * @params cust_id
     * @params bank_id
     * @return json
     * 用户不能删除的银行卡类型：1.从韵达传递过来的数据中获取的银行卡信息 2.银行卡列表中还剩最后一张银行卡时
     *
     */
    public function bankDel(){
        $input = $this->request->all();
        $cust_id = $input['cust_id'];
        $bank_cod = $input['bank_code'];
        $bank_num = Bank::where('cust_id',$cust_id)
            ->where('bank_del','0')
            ->select('bank_code')
            ->get();
        $bank_res =  Bank::where('cust_id',$cust_id)
            ->where('bank_code',$bank_cod)
            ->select('bank','bank_code','bank_city','bank_type','phone')
            ->first()->toArray();
        if(count($bank_num)<=1){//只剩最后一张银行卡
            return json_encode(['status'=>'500','msg'=>'最后一张银行卡，不能删除']);
        }
        if($bank_res['bank_type']=='own'){//从韵达传递过来的数据中获取的银行卡信息
            return json_encode(['status'=>'500','msg'=>'系统银行卡数据，不能删除']);
        }
        $del_res = Bank::where('cust_id',$cust_id)
            ->where('bank_code',$bank_cod)
            ->update(['bank_del'=>'1']);//bank_del 默认为0，已删除为1
        if($del_res){
            return json_encode(['status'=>'200','msg'=>'银行卡删除成功']);
        }else{
            return json_encode(['status'=>'500','msg'=>'银行卡删除失败']);
        }
    }

    /**
     * 免密授权详情页面
     * @access public
     * @return view
     *
     */
    public function bankAuthorize(){
        $person_code = $this->person_code;
        $person_code = config('yunda.test_person_code');
        $user_res = Person::where('papers_code',$person_code)->select('id')->first();
        $cust_id = $user_res['id'];
        //签约页面上会显示签约人的相关信息
        return view('channels.yunda.bank_authorize',compact('cust_id','person_code'));
    }

    /**
     * 免密授权详情页面
     * @access public
     * @return view
     *
     */
    public function bankAuthorizeInfo(){
        $input = $this->request->all();
        $person_code = $this->person_code;
        $person_code = config('yunda.test_person_code');
        $user_res = Person::where('papers_code',$person_code)->select('id')->first();
        $cust_id = $user_res['id'];
        $insured_name = $input['insured_name']??"王石磊";
        $insured_code = $input['insured_code']??"410881199406053515";
        $insured_phone = $input['insured_phone']??"15701681524";
        $bank_name = $input['bank_name']??"工商银行";
        $bank_code = $input['bank_code']??"6815464654654654654654";
        $repeat_res = ChannelInsureSeting::where('cust_id',$cust_id)
            ->select('id')->first();
        $authorize_status = true;//授权按钮显示状态
        if(!empty($repeat_res)){
            $authorize_status = false;
        }
        //签约页面上会显示签约人的相关信息
        return view('channels.yunda.bank_authorize_info',compact('insured_name','insured_code','insured_phone','bank_code','bank_name','authorize_status','cust_id'));
    }

    /**
     * 免密授权设置
     * @access public
     * @return view
     *
     */
    public function doBankAuthorize(){
        $input = $this->request->all();
        $person_code = $input['person_code'];
        $user_res = Person::where('papers_code',$person_code)->select('id','name','papers_type','papers_code','phone','address')->first();
        $cust_id = $user_res['id'];
        $repeat_res = ChannelInsureSeting::where('cust_id',$cust_id)
            ->select('id')->first();
        if(empty($repeat_res)){
            ChannelInsureSeting::insert([
                'cust_id'=>$cust_id,
                'cust_cod'=>$person_code,
                'cust_type'=>'',
                'authorize_status'=>'1',
                'authorize_start'=>time(),
            ]);
        }else{
            ChannelInsureSeting::where('cust_cod',$person_code)->update([
                'authorize_status'=>'1',
                'authorize_start'=>time(),
            ]);
        }
        return json_encode(['status'=>'200','msg'=>'开通免密支付成功']);
    }
}