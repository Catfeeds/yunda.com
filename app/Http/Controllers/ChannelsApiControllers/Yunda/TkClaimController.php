<?php

namespace App\Http\Controllers\ChannelsApiControllers\Yunda;


use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Validator, DB, Image, Schema;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Session,Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\UserChannel;
use App\Models\UserChannels;
use App\Models\User;
use App\Models\UserContact;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderParameter;
use App\Models\WarrantyPolicy;
use App\Models\WarrantyRecognizee;
use App\Models\WarrantyRule;
use App\Models\OrderBrokerage;
use App\Models\Product;
use App\Models\ApiInfo;
use App\Models\Bank;
use App\Models\CompanyBrokerage;
use App\Models\OrderPrepareParameter;
use App\Models\ChannelClaimApply;
use App\Models\ChannelInsureInfo;
use App\Models\ChannelContract;
use App\Models\ChannelInsureSeting;
use App\Models\Channel;
use App\Models\ChannelOperate;
use App\Models\ChannelPrepareInfo;
use App\Models\Warranty;
use App\Helper\DoChannelsSignHelp;
use App\Helper\RsaSignHelp;
use App\Helper\AesEncrypt;
use App\Helper\LogHelper;
use App\Helper\Issue;
use App\Helper\UploadFileHelper;
use App\Helper\IdentityCardHelp;
use App\Models\CustWarranty;
use App\Models\CustWarrantyPerson;
use App\Jobs\YunDaPay;

class TkClaimController
{
    //初始化
    public function __construct(Request $request)
    {
        $this->sign_help = new DoChannelsSignHelp();
        $this->signhelp = new RsaSignHelp();
        $this->log = new LogHelper();
        $this->request = $request;
        set_time_limit(0);//永不超时
    }
    //理赔选择
    public function toClaim(){
        $access_token = $this->request->header('access-token');
		$access_token_data = json_decode($this->sign_help->base64url_decode($access_token),true);
        $person_code = $access_token_data['person_code'];
        $time = date('Y-m-d',time()-3600*48);
        //最近三天内可理赔的保单详情
        $channel_operate_res = ChannelOperate::where('channel_user_code',$person_code)
            ->where('operate_time','>=',$time)
            ->where('issue_status','200')
            ->with('warranty')
            ->get();
        $warranty_ids = [];
        foreach($channel_operate_res as $value){
            if(!empty($value->warranty->warranty_id)){
                $warranty_ids[] = $value->warranty->warranty_id;
            }
        }
		$warranty_ids = [];
        $warranty_res = Warranty::whereIn('id',$warranty_ids)->get();
        return view('frontend.channels.to_claim')
            ->with('res',$warranty_res)
            ->with('person_code',$person_code);
    }
	//理赔列表
	public function getClaim(){
		return view('frontend.channels.insure_claim');
	}
    //自助理赔服务须知
    public function claimNotice($warranty_code){
        return view('frontend.channels.claim_notice')->with('warranty_code',$warranty_code);
    }
    //理赔操作指引
    public function claimApplyGuide(){
        return view('frontend.channels.claim_guide');
    }
    //理赔指引详情
    public function claimApplyGuideIndex(){
        return view('frontend.channels.claim_guide_index');
    }
    //理赔适用范围
    public function claimApplyRange(){
        return view('frontend.channels.claim_apply_range');
    }
    //理赔应备材料
    public function claimApplyInfo(){
        return view('frontend.channels.claim_apply_info');
    }
    //理赔申请书接受方式
    public function claimApplyWay(){
        return view('frontend.channels.claim_apply_way');
    }
    //理赔第一步：填写出险人信息 todo 报案
    public function claimStep1($warranty_code){
        $data = $this->getClaimCommon($warranty_code);
        $member = $this->getInsurantInfo($data);
        $address = $this->getAreaInfo($data);
        $user_info = $this->getMemberInfo($data);
//                dump($address);
//                dump($member);
//                dump($user_info);
//                die;
        if(json_decode($address,true)){
            $address  = json_decode($address,true);
        }else{
            return back()->with('status','获取初始化地区信息出错！');
        }
        if(json_decode($member,true)){
            $member  = json_decode($member,true);
        }else{
            return back()->with('status','获取投保人信息出错！');
        }
        if(json_decode($user_info,true)){
            $user_info  = json_decode($user_info,true);
        }else{
            return back()->with('status','获取会员信息出错！');
        }
//                dump($address);
//                dump($member);
//                dump($user_info);
//                die;
        if(empty($address)&&empty($member)&&empty($user_info)){
            // LogHelper::logChannelError($member, 'YD_TK_get_init_member');
            // LogHelper::logChannelError($address, 'YD_TK_get_init_area');
            // LogHelper::logChannelError($address, 'YD_TK_get_init_user_info');
            $result =  json_encode(['status'=>'501','content'=>'初始化出错'],JSON_UNESCAPED_UNICODE);
            return $result;
        }
        ChannelOperate::where('proposal_num',$data['union_order_code'])->update(['init_status'=>'200','init_content'=>json_encode($member)]);
        return view('frontend.channels.claim_step1')
            ->with('area',$address)
            ->with('member',$member)
            ->with('user_info',$user_info)
            ->with('warranty_code',$warranty_code);
    }
    //处理第一步：报案处理
    public function doClaimStep1(){
        $input = $this->request->all();
        $data = $this->getClaimCommon($input['warranty_code']);
        $datas = $this->signhelp->tySign($input);
        $response = Curl::to(env('TY_API_SERVICE_URL') .'/claim/save_case_info')
            ->returnResponseObject()
            ->withData($datas)
            ->withTimeout(60)
            ->post();
        LogHelper::logChannelError($response, 'YD_TK_sms');
//        print_r($response);die;
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_claim_step1');
            $respose =  json_encode(['status'=>'501','content'=>'出险人信息提交失败'],JSON_UNESCAPED_UNICODE);
            print_r($respose);
            return back()->with('status','出险人信息提交失败');
        }
        ChannelClaimApply::insert([
            'union_order_code'=>$data['union_order_code'],
            'channel_user_code'=>$data['channel_user_code'],
            'warranty_code'=>$input['warranty_code'],
            'user_report_info'=>json_encode($input),
            'user_report_status'=>'200',
            'user_report_content'=>$response->content,
            'claim_start_time'=>date('Y-m-d',time()),
            'claim_start_status'=>'200'
        ]);
        return redirect('/channelsapi/claim_step2/'.$input['warranty_code'])->with('status','用户信息提交成功');
    }
    //理赔第二步：填写收款人账户信息
    public function claimStep2($warranty_code){
        $data = $this->getClaimCommon($warranty_code);
        $res = $this->getCliamSaveInfo($data);
        return view('frontend.channels.claim_step2')
            ->with('res',$res)
            ->with('data',$data)
            ->with('warranty_code',$warranty_code);
    }
    //理赔第三步：上传身份证件信息
    public function claimStep3(){
        $input = $this->request->all();
        $data = $this->getClaimCommon($input['warranty_code']);
        if(isset($input['bank_info_file'])){
            if(is_string($input['bank_info_file'])){
                $image_path =$input['bank_info_file'];
            }else{
                $path = 'upload/channel/claim_post/' . date("Ymd") .'/';
                $image_path = UploadFileHelper::uploadImage($input['bank_info_file'], $path);//理赔上传图片路径（存数据库）
            }
        }
        ChannelClaimApply::where(  'warranty_code',$data['ins_policy_code'])
            -> where( 'union_order_code',$data['union_order_code'])
            ->update(['bank_files'=>json_encode($image_path)]);
        return view('frontend.channels.claim_step3')
            ->with('data',$data)
            ->with('bank_info',$input['bank_info']);
    }
    //理赔第四步：上传理赔资料
    public function claimStep4(){
        set_time_limit(0);//永不超时
        $input = $this->request->all();
        $data = $this->getClaimCommon($input['warranty_code']);
        $claim_id = $this->getCliamId($data);
        $claim_save_info = $this->getCliamSaveInfo($data);
        $data['claim_id'] = $claim_id;
        $data['sign'] = $claim_save_info['claim_sign']??$claim_save_info['sign'];
        $claim_flug = $claim_save_info['bank_flag'];
        if($claim_flug=='TKC'){//人伤
            $doc_res = $this->claimGetTKCDocType($data);
            $doc_res = is_array(json_decode($doc_res,true)) ? json_decode($doc_res,true) : $doc_res;
            $doc_desc_res = [];
            foreach($doc_res as $value){
                $doc_desc_res[] = $value['desc'];
            }
            $doc_res = $doc_desc_res;
        }elseif($claim_flug=='TKA'){//财产
            $doc_res = $this->claimGetTKAUploadDesc($data);
            $doc_res = is_array(explode('、',$doc_res)) ? explode('、',$doc_res) :$doc_res;
        }
        $image_paths = [];
        if(isset($input['cid_file'])){
            foreach ($input['cid_file'] as $key=>$value){
                if(is_string($value)){
                    $image_path = $value;
                }else{
                    $path = 'upload/channel/claim_post/' . date("Ymd") .'/';
                    $image_path = UploadFileHelper::uploadImage($value, $path);//理赔上传图片路径（存数据库）
                }
                $image_paths[$key] = $image_path;
            }
        }
        ChannelClaimApply::where('warranty_code',$data['ins_policy_code'])
            ->update([
                'cid_files'=>json_encode($image_paths),
            ]);
        $cid_info = [];
        $cid_info['cid1'] = $input['cid1'];
        $cid_info['cid2'] = $input['cid2'];
        return view('frontend.channels.claim_step4')
            ->with('data',$data)
            ->with('doc_res',$doc_res)
            ->with('cid_info',$cid_info)
            ->with('bank_info',$input['bank_info']);
    }
    //处理第四步，理赔材料保存接口
    public function doClaimStep4(){
        set_time_limit(0);//永不超时
        $input = $this->request->all();
        $data = $this->getClaimCommon($input['warranty_code']);//获取通用信息
        $claim_id = $this->getCliamId($data);//获取报案号
        $claim_save_info = $this->getCliamSaveInfo($data);//获取报案返回信息
        $member_info = $this->getMemberInfo($data);//获取会员信息
        $data['claim_id'] = $claim_id;
        $data['sign'] = $claim_save_info['claim_sign']??$claim_save_info['sign'];
        if(json_decode($member_info,true)){
            $member_info  = json_decode($member_info,true);
        }else{
            return back()->with('status','获取信息出错！');
        }
        $claim_apply_info = isset($input['claim_apply_info'])?$input['claim_apply_info']:[];
        $start_time_info = isset($input['start_time_info'])?$input['start_time_info']:[];
        $compensation_agreement_info = isset($input['compensation_agreement_info'])?$input['compensation_agreement_info']:[];
        $sick_record_info = isset($input['sick_record_info'])?$input['sick_record_info']:[];
        $invoice_info = isset($input['invoice_info'])?$input['invoice_info']:[];
        $traffic_police_info = isset($input['traffic_police_info'])?$input['traffic_police_info']:[];
        $accident_scene_info = isset($input['accident_scene_info'])?$input['accident_scene_info']:[];
        $delegation_info = isset($input['delegation_info'])?$input['delegation_info']:[];
        $app_screenshot_info = isset($input['app_screenshot_info'])?$input['app_screenshot_info']:[];
        $end_time_info = isset($input['end_time_info'])?$input['end_time_info']:[];
        $cid1_info = [json_decode($input['cid_file'],true)['cid1']];
        $cid2_info = [json_decode($input['cid_file'],true)['cid2']];
        $bank_info = [$input['bank_info']];
        //上传图片参数
        $data['function_code'] = 'addBase64';
        $data['claim_id'] = $claim_id;
        $data['coop_id'] = $member_info['member_id'];
        $data['sign'] =  $claim_save_info['sign'];
        $data['claim_flag'] = $claim_save_info['bank_flag'];
        //调用上传方法
        $cid1_res = $this->getImgUpload($cid1_info,$data,'cid1');//证件正面
        $cid2_res = $this->getImgUpload($cid2_info,$data,'cid2');//证件反面
        $bank_res = $this->getImgUpload($bank_info,$data,'bank');//银行卡信息
        $image_upload_path = [];
        $image_upload_code = [];
        $image_upload_base64 = [];
        if(!empty($claim_apply_info)){
            $image_upload_code['claim_apply'] = $this->getImgUpload($claim_apply_info,$data,'claim_apply');//理赔申请书
            $image_upload_path['claim_apply'] = $this->getImgPath($input,'claim_apply');//理赔申请书
            $image_upload_base64['claim_apply'] = $claim_apply_info;
        }
        if(!empty($start_time_info)){
            $image_upload_code['start_time'] = $this->getImgUpload($start_time_info,$data,'start_time');//分拣开始
            $image_upload_path['start_time'] = $this->getImgPath($input,'start_time');//分拣开始
            $image_upload_base64['start_time'] = $start_time_info;
        }
        if(!empty($compensation_agreement_info)){
            $image_upload_code['compensation_agreement'] = $this->getImgUpload($compensation_agreement_info,$data,'compensation_agreement');//赔偿协议
            $image_upload_path['compensation_agreement'] = $this->getImgPath($input,'compensation_agreement');//赔偿协议
            $image_upload_base64['compensation_agreement'] = $compensation_agreement_info;
        }
        if(!empty($sick_record_info)){
            $image_upload_code['sick_record'] = $this->getImgUpload($sick_record_info,$data,'sick_record');//病例信息
            $image_upload_path['sick_record'] = $this->getImgPath($input,'sick_record');//病例
            $image_upload_base64['sick_record'] = $sick_record_info;
        }
        if(!empty($invoice_info)){
            $image_upload_code['invoice'] = $this->getImgUpload($invoice_info,$data,'invoice');//发票信息
            $image_upload_path['invoice'] = $this->getImgPath($input,'invoice');//发票
            $image_upload_base64['invoice'] = $invoice_info;
        }
        if(!empty($traffic_police_info)){
            $image_upload_code['traffic_police'] = $this->getImgUpload($traffic_police_info,$data,'traffic_police');//交警事故认定书
            $image_upload_path['traffic_police'] = $this->getImgPath($input,'traffic_police');//交警
            $image_upload_base64['traffic_police'] = $traffic_police_info;
        }
        if(!empty($accident_scene_info)){
            $image_upload_code['accident_scene'] = $this->getImgUpload($accident_scene_info,$data,'accident_scene');//事故现场
            $image_upload_path['accident_scene'] = $this->getImgPath($input,'accident_scene');//事故现场
            $image_upload_base64['accident_scene'] = $accident_scene_info;
        }
        if(!empty($delegation_info)){
            $image_upload_code['delegation'] = $this->getImgUpload($delegation_info,$data,'delegation');//保险理赔授权委托书
            $image_upload_path['delegation'] = $this->getImgPath($input,'delegation');//保险理赔授权委托书
            $image_upload_base64['delegation'] = $delegation_info;
        }
        if(!empty($app_screenshot_info)){
            $image_upload_code['app_screenshot'] = $this->getImgUpload($app_screenshot_info,$data,'app_screenshot');//APP截图
            $image_upload_path['app_screenshot'] = $this->getImgPath($input,'app_screenshot'); //APP屏幕截图
            $image_upload_base64['app_screenshot'] = $app_screenshot_info;
        }
        if(!empty($end_time_info)){
            $image_upload_code['end_time'] = $this->getImgUpload($end_time_info,$data,'end_time');//分拣结束
            $image_upload_path['end_time'] = $this->getImgPath($input,'end_time');//分拣结束
            $image_upload_base64['end_time'] = $end_time_info;
        }
        $img_upload_res = [];
        $img_upload_res['path'] = $image_upload_path;
        $img_upload_res['code'] = $image_upload_code;
        $img_upload_res['base64'] = $image_upload_base64;
//                dump($this->getImgUpload($cid1_info,$data,'cid1'));
//                dump($input);
//                dump($claim_save_info);
//                dump($data);
//                dump($member_info);
//                dump($cid1_res);
//                dump($cid2_res);
//                dump($bank_res);
//                dump($image_upload_path);
//                dump($image_upload_code);
//                dump($img_upload_res);
//                die;
        $claim_files_res = ChannelClaimApply::where('warranty_code',$data['ins_policy_code'])
            ->where('union_order_code',$data['union_order_code'])
            ->first();
        if(empty($claim_files_res)){
            ChannelClaimApply::insert([
                'union_order_code'=>$data['union_order_code'],
                'warranty_code'=>$data['warranty_code'],
                'claim_materials'=>json_encode($img_upload_res),
            ]);
        }
        ChannelClaimApply::where('warranty_code',$data['ins_policy_code'])
            ->where('union_order_code',$data['union_order_code'])->update([
                'claim_materials'=>json_encode($img_upload_res),
            ]);
        ChannelOperate::where('proposal_num',$data['union_order_code'])->update([
            'claim_status'=>'100',//理赔材料提交成功
        ]);
        return redirect('/channelsapi/claim_submit/'.$data['ins_policy_code'])->with('status','理赔信息提交成功！');
    }
    //获取图片上传路径
    public function getImgPath($res,$param){
        $image_paths = [];
        if(isset($res[$param])){
            foreach ($res[$param] as $key=>$value){
                if(is_string($value)){
                    $image_path     = $value;
                }else{
                    $path = 'upload/channel/'.$param.'/' . date("Ymd") .'/';
                    $image_path = UploadFileHelper::uploadImage($value, $path);//理赔上传图片路径（存数据库）
                }
                $image_paths[$key] = $image_path;
            }
        }
        return $image_paths;
    }
    //调用图片上传接口
    public function getImgUpload($res,$data,$name){
        if(empty($res)&&count($res)==0){
            return back()->with('status','理赔资料提交失败');
        }
        $return_code = [];
        switch ($name){
            case 'cid1':
                $data['img_type'] = 'cid1';
                break;
            case 'cid2':
                $data['img_type'] = 'cid2';
                break;
            case 'bank':
                $data['img_type'] = 'bank';
                break;
            case 'claim_apply':
                $data['img_type'] = 'claimapply';
                break;
            case 'invoice':
                $data['img_type'] = 'invoice';
                break;
            case 'sick_record':
                $data['img_type'] = 'sickrecord';
                break;
            case 'diagnosis':
                $data['img_type'] = 'diagnosis';
                break;
            case 'benecid':
                $data['img_type'] = 'benecid';
                break;
            case 'beneficiary':
                $data['img_type'] = 'beneficiary';
                break;
            case 'ECM':
                $data['img_type'] = 'ECM';
                break;
            case 'supply':
                $data['img_type'] = 'supply';
                break;
            case 'death':
                $data['img_type'] = 'death';
                break;
            case 'pathology':
                $data['img_type'] = 'pathology';
                break;
            case 'benecid':
                $data['img_type'] = 'benecid';
                break;
            case 'start_time':
                $data['img_type'] = 'other';
                break;
            case 'compensation_agreement':
                $data['img_type'] = 'other';
                break;
            case 'traffic_police':
                $data['img_type'] = 'other';
                break;
            case 'delegation':
                $data['img_type'] = 'other';
                break;
            case 'app_screenshot':
                $data['img_type'] = 'other';
                break;
            case 'end_time':
                $data['img_type'] = 'other';
                break;
        }
        foreach ($res as $key=>$value){
            $data['img_id'] = $value;
            $datas = $this->signhelp->tySign($data);
            $response = Curl::to(env('TY_API_SERVICE_URL') .'/claim/handle_docs')
                ->returnResponseObject()
                ->withData($datas)
                ->withTimeout(60)
                ->post();
//            print_r($response);die;
            if($response->status != 200){
                $content = $response->content;
                LogHelper::logChannelError($content, 'YD_TK_claim_upload_metarial');
                $respose =  json_encode(['status'=>'501','content'=>'理赔资料提交失败'],JSON_UNESCAPED_UNICODE);
                print_r($respose);
                return back()->with('status','理赔资料提交失败');
            }
            $return_code[] = $response->content;
        }
        return $return_code;
    }
    //获取短信验证码
    public function getSmsCode(){
        $input = $this->request->all();
        $data = [];
        $data['tka_mobile'] = $input['tka_mobile'];
        $data['mobile_sign'] = $input['mobile_sign'];
        $data['ty_product_id'] = '15';
        $data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
        $data = $this->signhelp->tySign($data);
        //发送请求
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/claim/get_verify_code')
            ->returnResponseObject()
            ->withData($data)
            ->withTimeout(60)
            ->post();
        //        print_r($response);die;
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_sms_send');
            $result =  json_encode(['status'=>'501','content'=>$response->content],JSON_UNESCAPED_UNICODE);
            return $result;
        }
        //验证码存缓存
        $expiresAt = \Carbon\Carbon::now()->addMinutes(3);
        \Cache::put("reg_code_".$input['tka_mobile'], $response->content, $expiresAt);
        $result =  json_encode(['status'=>'200','content'=>'获取验证码成功'],JSON_UNESCAPED_UNICODE);
        return $result;
    }
    //验证手机验证码
    protected function checkPhoneCode($phone, $phone_code)
    {
        if(!Cache::get("reg_code_".$phone))
            return ['status'=>'error', 'message'=>'验证码不存在，请重新发送'];
        if(Cache::get("reg_code_".$phone) != $phone_code)
            return ['status'=>'error', 'message'=>'验证码错误'];
        Cache::forget("reg_code_".$phone);
        return ['status'=> 'success', 'message'=>'验证码正确'];
    }
    //邮件发送接口(理赔申请书下载)
    public function getEmailSend(){
        $input = $this->request->all();
        $claim_id = getCliamId($input);
        $data = [];
        $data['person_code'] = config('yunda.test_person_code');
        $data['emaildress'] = $input['email'];
        $data['ismodify'] = 'Y';
        $data['claim_id'] = $claim_id;
        $data['sign'] = 'sign';
        $data['function_code'] = 'claimsend';
        $data['ty_product_id'] = '15';
        $data['private_p_code'] = 'VGstMTEyMkEwMUcwMQ';
        $data = $this->signhelp->tySign($data);
        //发送请求
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/claim/get_email_send')
            ->returnResponseObject()
            ->withData($data)
            ->withTimeout(60)
            ->post();
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_email_send');
            $respose =  json_encode(['status'=>'501','content'=>'邮件发送出错'],JSON_UNESCAPED_UNICODE);
            return $respose;
        }
        $respose =  json_encode(['status'=>'200','content'=>'邮件发送成功，请前往邮箱查看邮件'],JSON_UNESCAPED_UNICODE);
        return $respose;

        //        ismodify		字符	Y	固定值：Y
        //emaildress	发送地址	字符	Y	826884878@qq.com前端校验邮箱格式，否则不予发送
        //claim_id	报案号	字符	Y	保存接口返回的claim_id
        //sign	验签	字符	Y	保存接口返回的sign
        //function_code	功能编码	字符	Y	固定值：claimsend
    }
    //理赔进度/历史列表
    public function claimRecords($person_code){
        $access_token = $this->request->header('access-token');
        $access_token_data = json_decode($this->sign_help->base64url_decode($access_token),true);
        $person_id_code = $access_token_data['person_code'] ? $access_token_data['person_code'] : $person_code;
        $channel_operate_res = ChannelOperate::where('channel_user_code',$person_id_code)
            ->where('issue_status','200')
            ->where('claim_status','<>',' ')//理赔中200，理赔结束100
            ->with(['warranty','warranty.warranty','warranty.warranty_product','warranty.warranty_rule_order.warranty_recognizee'])
            ->get();
        $channel_operate_end_res = ChannelOperate::where('channel_user_code',$person_id_code)
            ->where('issue_status','100')
            ->where('claim_status','<>',' ')//理赔中200，理赔结束100
            ->with(['warranty','warranty.warranty','warranty.warranty_product','warranty.warranty_rule_order.warranty_recognizee'])
            ->get();
        return view('frontend.channels.claim_records')
            ->with('res',$channel_operate_res)
            ->with('res_end',$channel_operate_end_res);
    }
    //理赔详情页
    public function claimInfo($warranty_code){
        $data = $this->getClaimCommon($warranty_code);
        $claim_id = $this->getCliamId($data);
        $claim_save_info = $this->getCliamSaveInfo($data);//获取报案返回信息
        $data['claim_id'] = $claim_id;
        $data['bank_flag'] = $claim_save_info['bank_flag'];
        $user_info = $this->getMemberInfo($data);
        $progress_info = $this->getCliamProgress($data);
        if(json_decode($user_info,true)){
            $user_info  = json_decode($user_info,true);
        }else{
            return back()->with('status','获取会员信息出错！');
        };
        if(json_decode($progress_info,true)){
            $progress_info  = json_decode($progress_info,true);
        }else{
            return back()->with('status','获取理赔进度信息出错！');
        };
        foreach ($progress_info as $value){
            if($claim_save_info['bank_flag'].$claim_id==$value['claim_id']){
                $sign = $value['sign'];
                $claim_flag = $value['claimFlag'];
            }
        }
        $data['sign'] = $sign;
        $data['member_id'] = $user_info['member_id'];
        $data['claim_id'] = $claim_flag.$claim_id;
        $data = $this->signhelp->tySign($data);
        //发送请求
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/claim/get_detail')
            ->returnResponseObject()
            ->withData($data)
            ->withTimeout(60)
            ->post();
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_Claim_Info_error');
            return back()->with('status','获取理赔详情出错');
        }
        LogHelper::logChannelError($response->content, 'YD_TK_Claim_Info');
        $apply_res = ChannelClaimApply::where('warranty_code',$warranty_code)
            ->where('claim_start_status','200')
            ->with('warrantyRule','warrantyRule.warranty_product','warrantyRule.warranty_rule_order.warranty_recognizee')
            ->first();
        $warranty_res = Warranty::where('warranty_code',$warranty_code)->first();
        return view('frontend.channels.claim_info')
            ->with('res',$response->content)
            ->with('apply_res',$apply_res)
            ->with('warranty_res',$warranty_res)
            ->with('warranty_code',$warranty_code);
    }
    //理赔资料提交
    public function claimSubmit($warranty_code){
        $data = $this->getClaimCommon($warranty_code);//获取通用信息
        $claim_save_info = $this->getCliamSaveInfo($data);//获取报案返回信息
        $member_info = $this->getMemberInfo($data);//获取会员信息
        $claim_apply_res = ChannelClaimApply::where('warranty_code',$warranty_code)
            ->where('union_order_code',$data['union_order_code'])
            ->where('channel_user_code',$data['channel_user_code'])
            ->first();
        if(empty($claim_apply_res)){
            return back()->with('status','获取理赔上传资料出错！');
        }
        $cid_res = $claim_apply_res['cid_files'];
        $bank_res = $claim_apply_res['bank_files'];
        $claim_materials_path = json_decode($claim_apply_res['claim_materials'],true)['path'];
        $claim_materials_code = json_decode($claim_apply_res['claim_materials'],true)['code'];
        //        foreach ($claim_materials_path as $key=>$value){
        //            foreach ($claim_materials_code as $k=>$v){
        //
        //            }
        //        }
        return view('frontend.channels.claim_submit')
            ->with('claim_save_info',$claim_save_info)
            ->with('member_info',$member_info)
            ->with('cid_res',$cid_res)
            ->with('bank_res',$bank_res)
            ->with('claim_materials_path',$claim_materials_path)
            ->with('claim_materials_code',$claim_materials_code)
            ->with('warranty_code',$warranty_code);
    }
    //处理理赔材料删除
    public function doClaimDel(){
        $input = $this->request->all();
        $data = $this->getClaimCommon($input['code']);//获取通用信息
        $claim_save_info = $this->getCliamSaveInfo($data);//获取报案返回信息
        $member_info = $this->getMemberInfo($data);//获取会员信息
        if(json_decode($member_info,true)){
            $member_info  = json_decode($member_info,true);
        }else{
            return back()->with('status','获取信息出错！');
        };

        $claim_apply_res = ChannelClaimApply::where('warranty_code',$input['code'])
            ->where('union_order_code',$data['union_order_code'])
            ->where('channel_user_code',$data['channel_user_code'])
            ->first();
        $key = explode('-',$input['key'])[0];
        $k = explode('-',$input['key'])[1];
        $claim_materials_path = json_decode($claim_apply_res['claim_materials'],true)['path'];
        $claim_materials_code = json_decode($claim_apply_res['claim_materials'],true)['code'];
        $claim_materials_base64 = json_decode($claim_apply_res['claim_materials'],true)['base64'];
        $img_code = $claim_materials_code[$key][$k];
        $data['function_code'] = 'del';
        $data['claim_id'] = $claim_save_info['claim_id'];
        $data['coop_id'] = $member_info['member_id'];
        $data['sign'] =  $claim_save_info['sign'];
        $data['claim_flag'] = $claim_save_info['bank_flag'];
        $data['img_id'] = $img_code;
        $datas = $this->signhelp->tySign($data);
        $response = Curl::to(env('TY_API_SERVICE_URL') .'/claim/handle_docs')
            ->returnResponseObject()
            ->withData($datas)
            ->withTimeout(60)
            ->post();
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_claim_del_metarial');
            $respose =  json_encode(['status'=>'501','content'=>'理赔资料删除失败'],JSON_UNESCAPED_UNICODE);
            return $respose;
        }
        unset($claim_materials_code[$key][$k]);
        unset($claim_materials_path[$key][$k]);
        unset($claim_materials_base64[$key][$k]);
        $img_upload_res = [];
        $img_upload_res['path'] = $claim_materials_path;
        $img_upload_res['code'] = $claim_materials_code;
        $img_upload_res['base64'] = $claim_materials_base64;
        ChannelClaimApply::where('warranty_code',$data['ins_policy_code'])
            ->where('union_order_code',$data['union_order_code'])->update([
                'claim_materials'=>json_encode($img_upload_res),
            ]);
        $respose =  json_encode(['status'=>'200','content'=>'理赔资料删除成功'],JSON_UNESCAPED_UNICODE);
        return $respose;
    }
    //处理理赔材料提交
    public function doClaimSubmit(){
        $input = $this->request->all();
        $data = $this->getClaimCommon($input['warranty_code']);//获取通用信息
        $claim_save_info = $this->getCliamSaveInfo($data);//获取报案返回信息
        $member_info = $this->getMemberInfo($data);//获取会员信息
        if(json_decode($member_info,true)){
            $member_info  = json_decode($member_info,true);
        }else{
            return back()->with('status','获取信息出错！');
        }
        $data['claim_id'] = $claim_save_info['claim_id'];
        $data['coop_id'] = $member_info['member_id'];
        $data['sign'] =  $claim_save_info['sign'];
        $data['claim_flag'] = $claim_save_info['bank_flag'];
        $datas = $this->signhelp->tySign($data);
        $response = Curl::to(env('TY_API_SERVICE_URL') .'/claim/submit')
            ->returnResponseObject()
            ->withData($datas)
            ->withTimeout(60)
            ->post();
//        print_r($response);die;
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_claim_submit_metarial');
            $respose =  json_encode(['status'=>'501','content'=>'理赔资料提交失败'],JSON_UNESCAPED_UNICODE);
            print_r($respose);
            return back()->with('status','理赔资料提交失败');
        }
        ChannelOperate::where('proposal_num',$data['union_order_code'])->update([
            'claim_status'=>'200',//理赔材料提交成功
        ]);
        return redirect('/channelsapi/claim_submit/'.$input['warranty_code'])->with('status','理赔资料提交成功');
    }
    //理赔材料补充
    public function claimAddMaterial($warranty_code){
        return view('frontend.channels.claim_add_material')->with('warranty_code',$warranty_code);
    }
    //处理理赔材料补充
    public function doClaimAddMaterial(){
        $input = $this->request->all();
        $data = $this->getClaimCommon($input['warranty_code']);//获取通用信息
        $claim_save_info = $this->getCliamSaveInfo($data);//获取报案返回信息
        $member_info = $this->getMemberInfo($data);//获取会员信息
        if(json_decode($member_info,true)){
            $member_info  = json_decode($member_info,true);
        }else{
            return back()->with('status','获取信息出错！');
        }
        $data['claim_id'] = $claim_save_info['claim_id'];
        $data['coop_id'] = $member_info['member_id'];
        $data['sign'] =  $claim_save_info['sign'];

        $data = $this->signhelp->tySign($data);
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/claim/get_detail')
            ->returnResponseObject()
            ->withData($data)
            ->withTimeout(60)
            ->post();

        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_Claim_Info_error');
            return back()->with('status','获取理赔详情出错');
        }
        $data['claim_flag'] = $claim_save_info['bank_flag'];
        $data['function_code'] = 'sendQuestionInfo2ECM';
        $data['channel'] = 'YDAPP';
        $data['seq_no'] = $response->content['quesNo'];//申请号
        $data['questionid'] = $response->content['seq_no'];//问题件号
        $data['question_desc'] = $response->content['quesDesc'];//补充资料内容
        $datas = $this->signhelp->tySign($data);
        //发送请求
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/claim/submit_append')
            ->returnResponseObject()
            ->withData($datas)
            ->withTimeout(60)
            ->post();
//        print_r($response);die;
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_claim_append_metarial');
            $respose =  json_encode(['status'=>'501','content'=>'理赔资料提交失败'],JSON_UNESCAPED_UNICODE);
            print_r($respose);
            return back()->with('status','理赔资料提交失败');
        }
        $image_paths = [];
        if(isset($input['add_material_info'])){
            foreach ($input['add_material_info'] as $key=>$value){
                if(is_string($value)){
                    $image_path = $value;
                }else{
                    $path = 'upload/channel/claim_add_material/' . date("Ymd") .'/';
                    $image_path = UploadFileHelper::uploadImage($value, $path);//理赔上传图片路径（存数据库）
                }
                $image_paths[$key] = $image_path;
            }
        }
        $claim_files_res = ChannelClaimApply::where('warranty_code',$data['ins_policy_code'])
            ->where('union_order_code',$data['union_order_code'])
            ->first();
        ChannelClaimApply::where('warranty_code',$data['ins_policy_code'])
            ->where('union_order_code',$data['union_order_code'])->update([
                'add_push_files'=>json_encode($image_paths),
                'claim_add_status'=>'200',//已上传补充资料
            ]);
        return redirect('/channelsapi/to_claim')->with('status','补充资料上传成功！');
    }
    //获取请求接口的公共参数  todo 有改动
    public function getClaimCommon($warranty_code){
        //获取保单号，联合订单号，产品信息，被保人信息等
        $warranty_res = Warranty::where('warranty_code',$warranty_code)
            ->with([
                'warranty_rule.warranty_rule_order.warranty_recognizee',
                'warranty_rule.warranty_product',
                'warranty_rule.warranty_rule_order',
                'warranty_rule.policy',
            ])->first();
        $data = [];
        if(!empty($warranty_res->warranty_rule)
            &&!empty($warranty_res->warranty_rule->policy)
            &&!empty($warranty_res->warranty_rule->warranty_rule_order->warranty_recognizee)){
            $data['ins_policy_code'] = $warranty_code;
            $data['private_p_code'] = $warranty_res->warranty_rule->private_p_code ??  'VGstMTEyMkEwMUcwMQ';
            $data['union_order_code'] = $warranty_res->warranty_rule->union_order_code;
            $data['ty_product_id'] = $warranty_res->warranty_rule->warranty_product->ty_product_id ??  '15';
            $data['policy_user_code'] =$warranty_res->warranty_rule->policy->code;
            $data['channel_user_code'] =$warranty_res->warranty_rule->policy->code;
            $data['recognizee_user_code'] =$warranty_res->warranty_rule->warranty_rule_order->warranty_recognizee[0]->code;

        }
        return $data;
    }
    //获取会员绑定信息查询接口
    public function getMemberInfo($res){
        $data = [];
        $data['ins_policy_code'] = $res['ins_policy_code'];
        $data['union_order_code'] = $res['union_order_code'] ?? '';
        $data['ty_product_id'] = $res['ty_product_id'] ?? '15';
        $data['private_p_code'] = $res['private_p_code']?? '';
        $data = $this->signhelp->tySign($data);
        //发送请求
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/claim/get_member_info')
            ->returnResponseObject()
            ->withData($data)
            ->withTimeout(60)
            ->post();
//        print_r($response);die;
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_Claim_Info');
            return back()->with('status','获取会员信息出错');
        }
        $return_data =  is_array($response->content)? json_encode($response->content) :$response->content;
        return $return_data;
    }
    //获取地区初始化信息
    public function getAreaInfo($res){
        $data = [];
        $data['ins_policy_code'] = $res['ins_policy_code'];
        $data['union_order_code'] = $res['union_order_code'];
        $data['private_p_code'] = $res['private_p_code'];
        $data['ty_product_id'] = $res['ty_product_id'];
        $data = $this->signhelp->tySign($data);
        //发送请求
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/claim/get_area')
            ->returnResponseObject()
            ->withData($data)
            ->withTimeout(60)
            ->post();
        //        print_r($response);die;
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_Get_Area');
            return back()->with('status','获取地区初始化信息失败');
        }
        $return_data =  is_array($response->content)? json_encode($response->content) :$response->content;
        return $return_data;
    }
    //获取投保人信息
    public function getInsurantInfo($res){
        $data = [];
        $data['ins_policy_code'] = $res['ins_policy_code'];
        $data['union_order_code'] = $res['union_order_code'];
        $data['private_p_code'] = $res['private_p_code'];
        $data['ty_product_id'] = $res['ty_product_id'];
        $member_info = $this->getMemberInfo($res);
//        dd($member_info);
        if(json_decode($member_info,true)){
            $member_info  = json_decode($member_info,true);
        }else{
            return back()->with('status','获取会员信息出错！');
        }
        //        dump($member_info);
        $data['cidnumber_decrypt'] = $member_info['cidnumber_decrypt'];
        $data['cidtype'] = $member_info['cidtype'];
//                dump($data);
        $data = $this->signhelp->tySign($data);
        //发送请求
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/claim/get_insurant_info')
            ->returnResponseObject()
            ->withData($data)
            ->withTimeout(60)
            ->post();
//        print_r($response);die;
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_get_insurant_info');
            return back()->with('status','获取投保人信息失败');
        }
        $return_data =  is_array($response->content)? json_encode($response->content) :$response->content;
        return $return_data;
    }
    //获取投保进度
    public function getCliamProgress($res){
        $data = [];
        $data['ins_policy_code'] = $res['ins_policy_code'];
        $data['union_order_code'] = $res['union_order_code'];
        $data['ty_product_id'] = $res['ty_product_id'];
        $data['private_p_code'] = $res['private_p_code'];
        $data['claim_id'] = $res['bank_flag'].$res['claim_id'];
        $user_info = $this->getMemberInfo($data);
        if(json_decode($user_info,true)){
            $user_info  = json_decode($user_info,true);
        }else{
            return back()->with('status','获取信息出错！');
        };
        $data['coop_id'] = $user_info['member_id'];
        $data['member_id'] = $user_info['member_id'];
        $data['sign'] = $user_info['member_sign'];
//        dump($data);
        $data = $this->signhelp->tySign($data);
        //发送请求
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/claim/get_progress')
            ->returnResponseObject()
            ->withData($data)
            ->withTimeout(60)
            ->post();
//        print_r($response);die;
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_Claim_Progress');
            return back()->with('status',$content);
        }
        $return_data =  is_array($response->content)? json_encode($response->content) :$response->content;
        return $return_data;
    }
    //获取理赔报案号
    public function getCliamId($res){
        $claim_apply_res =  ChannelClaimApply::where('warranty_code',$res['ins_policy_code'])
            ->first();
        if(empty($claim_apply_res)){
            return back()->with('status','获取报案号出错');
        }
        $return_data =  isset(json_decode($claim_apply_res['user_report_content'],true)['claim_id'])?json_decode($claim_apply_res['user_report_content'],true)['claim_id']:[];
        return $return_data;
    }
    //获取理赔报案返回信息
    public function getCliamSaveInfo($res){
        $claim_apply_res =  ChannelClaimApply::where('warranty_code',$res['ins_policy_code'])
            ->first();
        if(empty($claim_apply_res)){
            return back()->with('status','获取理赔报案返回信息出错');
        }
        $return_data =  isset($claim_apply_res['user_report_content']) ? json_decode($claim_apply_res['user_report_content'],true):[];
        return $return_data;
    }
    //获取人伤险理赔资料上传类型
    public function claimGetTKCDocType($res){
        $data = [];
        $data['ins_policy_code'] = $res['ins_policy_code'] ?? "";;
        $data['union_order_code'] = $res['union_order_code'] ?? "";
        $data['ty_product_id'] = $res['ty_product_id'] ?? "15";;
        $data['claim_id'] = $res['claim_id'] ?? "";;
        $data['sign'] = $res['sign'] ?? "";;
        $data = $this->signhelp->tySign($data);
        //发送请求
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/claim/get_tkc_doc_type')
            ->returnResponseObject()
            ->withData($data)
            ->withTimeout(60)
            ->post();
//        print_r($response);die;
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_Claim_tkc_doc_type');
            return back()->with('status','获取信息出错');
        }
        $return_data = $response->content;
        return $return_data;
    }
    //获取财产险上传资料描述
    public function claimGetTKAUploadDesc($res){
        $data = [];
        $data['ins_policy_code'] = $res['ins_policy_code'];
        $data['union_order_code'] = $res['union_order_code'];
        $data['ty_product_id'] = $res['ty_product_id'];
        $data['private_p_code'] = $res['private_p_code'];
        $data = $this->signhelp->tySign($data);
        //发送请求
        $response = Curl::to(env('TY_API_SERVICE_URL') . '/claim/get_tka_upload_desc')
            ->returnResponseObject()
            ->withData($data)
            ->withTimeout(60)
            ->post();
        if($response->status != 200){
            $content = $response->content;
            LogHelper::logChannelError($content, 'YD_TK_Claim_tka_doc_type');
            return back()->with('status','获取信息出错');
        }
        $return_data = $response->content;
        return $return_data;
    }
	//对象转化数组
	public function object2array($object) {
		if (is_object($object)) {
			foreach ($object as $key => $value) {
				$array[$key] = $value;
			}
		}
		else {
			$array = $object;
		}
		return $array;
	}
}

