<?php
/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/3/29
 * Time: 17:12
 * 韵达快递保新流程--理赔管理
 */
namespace App\Http\Controllers\ChannelsApiControllers\Yunda;

use App\Mail\YundaEmail;
use App\Models\ClaimYundaInfo;
use Illuminate\Http\Request;
use App\Helper\LogHelper;
use App\Helper\RsaSignHelp;
use App\Models\Person;
use App\Models\ClaimYunda;
use Illuminate\Support\Facades\DB;
use Ixudra\Curl\Facades\Curl;
use Mail;

class ClaimController
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
     * 申请理赔-出险人员
     */
    public function claimUser(){
        $input = $this->request->all();
        if(empty($input['warranty_id']))  return json_encode(['status'=>'500','msg'=>'保单号不可为空！']);
        $warranty_id = $input['warranty_id'];
        return view('channels.yunda.claim_user',compact('warranty_id'));
    }

    /**
     * 申请理赔-出险类型
     */
    public function claimType(){
        $input = $this->request->all();
        return view('channels.yunda.claim_type',compact('input','access_token'));
    }

    /**
     * 申请理赔-出险信息
     */
    public function claimReason(){
        $input = $this->request->all();
        $data = json_decode($input['input'],true);
        $data['type'] = implode(',',$input['type']);
        return view('channels.yunda.claim_reason',compact('access_token','data'));
    }

    /**
     * 申请理赔-联系地址
     */
    public function claimContact(){
        $input = $this->request->all();
        $data = json_decode($input['input'] ,true);
        $data['ins_nature'] = 1; //默认 意外门诊
        $data['accident'] = 1;   //默认 交通事故
        $data['ins_time'] = $input['ins_time'];
        $data['ins_address'] = $input['ins_address'];
        $data['ins_desc'] = $input['ins_desc'];
        return view('channels.yunda.claim_contact',compact('access_token','data'));
    }

    /**
     * 申请理赔-数据入库
     */
    public function claimResult(){
        $input = $this->request->all();
        $json  = json_decode($input['input'], true);
        unset($input['input']);
        $data = array_merge($json, $input);
        $person_code = $this->person_code;

        $person_code = '340323199305094715';

        $user_res = Person::where('papers_code',$person_code)->select('id')->first();
        $claim_yunda = new ClaimYunda();
        $claim_yunda->user_id = $user_res['id'];    //所属用户id
        $claim_yunda->warranty_id = $data['warranty_id']; //保单号
        $claim_yunda->name = $data['name'];
        $claim_yunda->papers_code = $data['papers_code'];
        $claim_yunda->address = $data['address'];
        $claim_yunda->type = $data['type'];
        $claim_yunda->accident = $data['accident'];
        $claim_yunda->ins_nature = $data['ins_nature'];
        $claim_yunda->ins_time = $data['ins_time'];
        $claim_yunda->ins_address = $data['ins_address'];
        $claim_yunda->ins_desc = $data['ins_desc'];
        $claim_yunda->contact_name = $data['contact_name'];
        $claim_yunda->phone = $data['phone'];
        $claim_yunda->email = $data['email'];
        $claim_yunda->status = 2; //进度 1申请理赔 2提交资料 3等待审核 4审核通过 -1 审核失败
        $claim_yunda->save();
        $claim_id = $claim_yunda->id;
        return view('channels.yunda.claim_result',compact('claim_id'));
    }

    /**
     * 上传资料
     */
    public function claimMaterialUpload(){

        $input = $this->request->all();
        $result = DB::table('claim_yunda')
            ->join('cust_warranty','cust_warranty.id','=','claim_yunda.warranty_id')
            ->join('product','product.id','=','cust_warranty.product_id')
            ->where('claim_yunda.id',$input['claim_id'])
            ->select(
                'claim_yunda.*',
                'claim_yunda.type as claim_type',
                'claim_yunda.id as claim_id',
                'cust_warranty.*',
                'product.*')
            ->first();

        return view('channels.yunda.claim_material_upload',compact('result'));
    }

    /**
     * 发邮件 资料入库
     */
    public function claimSendEmail(){
        $input = $this->request->all();
        $data = [];
        $data = $input;
        $result = DB::table('claim_yunda')
            ->join('cust_warranty','cust_warranty.id','=','claim_yunda.warranty_id')
            ->join('product','product.id','=','cust_warranty.product_id')
            ->where('claim_yunda.id',$data['claim_id'])
            ->select('product.id')
            ->first();

        DB::beginTransaction();
        try{

            ClaimYunda::where('id', $data['claim_id'])->update(['status' => 3]);
            $claim_yunda_info = new ClaimYundaInfo();
            $claim_yunda_info->claim_id = $data['claim_id'] ?? '';
            $claim_yunda_info->proof = $data['proof'] ?? '';
            $claim_yunda_info->invoice = $data['invoice'] ?? '';
            $claim_yunda_info->expenses = $data['expenses'] ?? '';
            $claim_yunda_info->papers_code_img = $data['papers_code_img'] ?? '';
            $claim_yunda_info->account_info = $data['account_info'] ?? '';
            $claim_yunda_info->accident_proof = $data['accident_proof'] ?? '';
            $claim_yunda_info->proof_loss = $data['proof_loss'] ?? '';
            $claim_yunda_info->bruise_whole = $data['bruise_whole'] ?? '';
            $claim_yunda_info->bruise_face = $data['bruise_face'] ?? '';
            $claim_yunda_info->bruise_wound = $data['bruise_wound'] ?? '';
            $claim_yunda_info->maim_proof = $data['maim_proof'] ?? '';
            $claim_yunda_info->die_proof = $data['die_proof'] ?? '';
            $claim_yunda_info->beneficiary = $data['beneficiary'] ?? '';
            $claim_yunda_info->status = 0; //进度 0等待审核 1审核通过 -1 审核失败
            $claim_yunda_info->save();
            $data['claim_yunda_info_id'] =  $claim_yunda_info->id;
            DB::commit();

            $data['url'] = env('APP_URL').config('yunda.email_url').'/claim_email?claim_yunda_info_id='.$claim_yunda_info->id;

            Mail::to([config('yunda.product_id_email')[$result->id]])->send(new YundaEmail($data));
            
            return json_encode(['code'=>200,'msg'=>'邮件发送成功，等待审核！']);
        }catch (\Exception $e){
            DB::rollBack();
            $message = $e->getMessage();
            return json_encode(['code'=>500,'msg'=> $message]);
        }
    }

    /**
     * 显示审核页面
     */
    public function claimEmail(){
        $input = $this->request->all();
        $result = DB::table('claim_yunda')
            ->join('claim_yunda_info','claim_yunda_info.claim_id','=','claim_yunda.id')
            ->join('cust_warranty','cust_warranty.id','=','claim_yunda.warranty_id')
            ->join('product','product.id','=','cust_warranty.product_id')
            ->where('claim_yunda_info.id', $input['claim_yunda_info_id'])
            ->select(
                'claim_yunda.*',
                'claim_yunda.type as claim_type',
                'claim_yunda.status as claim_status',
                'claim_yunda.id as claim_id',
                'cust_warranty.*',
                'product.*',
                'claim_yunda_info.*',
                'claim_yunda_info.status as claim_yunda_info_status'
            )
            ->first();

        if(empty($result))  return view('error.404');

        $status = config('yunda');

        if($result->claim_status == 4) return view('channels.yunda.claim_email_success',compact('result', 'status'));
        if($result->claim_yunda_info_status != 0) return view('channels.yunda.claim_email_success',compact('result', 'status'));

        $list = [];

        $list['list'][]['fileKey'] = $result->proof;
        $list['list'][]['fileKey'] = $result->invoice;
        $list['list'][]['fileKey'] = $result->expenses;
        $list['list'][]['fileKey'] = $result->papers_code_img;
        $list['list'][]['fileKey'] = $result->accident_proof;
        $list['list'][]['fileKey'] = $result->account_info;
        $list['list'][]['fileKey'] = $result->proof_loss;
        $list['list'][]['fileKey'] = $result->bruise_whole;
        $list['list'][]['fileKey'] = $result->bruise_face;
        $list['list'][]['fileKey'] = $result->bruise_wound;
        $list['list'][]['fileKey'] = $result->maim_proof;
        $list['list'][]['fileKey'] = $result->die_proof;
        $list['list'][]['fileKey'] = $result->beneficiary;

        $response = Curl::to(config('yunda.file_url').'file/getBatchFileUrl')
            ->returnResponseObject()
            ->withData(json_encode($list))
            ->withTimeout(60)
            ->withHeader("Content-Type: application/json;charset=UTF-8")
            ->post();

        if($response->status != 200) return json_encode(['code'=>500,'msg'=>'文件服务连接失败！']);

        $content = json_decode($response->content, true);

        if($content['code'] == 200){
            $file_url  = [];
            foreach ($content['data'] as $val){
                $file_url[$val['fileKey']] = $val['url'];
            }

            return view('channels.yunda.claim_email',compact('result', 'status', 'file_url'));
        }else{
            return json_encode(['code'=>500,'msg'=>'文件上传失败！']);
        }
    }

    /**
     * 处理审核结果
     */
    public function claimAudit(){
        $input = $this->request->all();
        DB::beginTransaction();
        try{
            $claim_yunda_info = ClaimYundaInfo::where('id', $input['id'])->first();
            if($input['status'] == '1') ClaimYunda::where('id', $claim_yunda_info->claim_id)->update(['status' => 4]);
            if($input['status'] == '-1') ClaimYunda::where('id', $claim_yunda_info->claim_id)->update(['status' => -1]);
            $claim_yunda_info->remark = $input['remark'];
            $claim_yunda_info->status = $input['status'];
            $claim_yunda_info->save();
            DB::commit();

            $result = DB::table('claim_yunda')
                ->join('claim_yunda_info','claim_yunda_info.claim_id','=','claim_yunda.id')
                ->join('cust_warranty','cust_warranty.id','=','claim_yunda.warranty_id')
                ->join('product','product.id','=','cust_warranty.product_id')
                ->where('claim_yunda_info.id', $input['id'])
                ->select(
                    'claim_yunda.*',
                    'claim_yunda.type as claim_type',
                    'claim_yunda.status as claim_status',
                    'claim_yunda.id as claim_id',
                    'cust_warranty.*',
                    'product.*',
                    'claim_yunda_info.*',
                    'claim_yunda_info.status as claim_yunda_info_status'
                )
                ->first();
            $status = config('yunda');

            return view('channels.yunda.claim_email_success', compact('result', 'status'));
        }catch (\Exception $e){
            DB::rollBack();
            $message = $e->getMessage();
            return json_encode(['code'=>500,'msg'=>$message]);
        }
    }

    /**
     * 理赔列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function claimProgress(){
        $input = $this->request->all();
        $type = $input['type'] ?? '0';
        $person_code = $this->person_code;

        $person_code = '340323199305094715';

        $users = Person::where('papers_code',$person_code)->first();
        $where = [1,2,3];
        if($type != '0') $where = [-1,4];
        $list = DB::table('claim_yunda')
            ->join('cust_warranty','cust_warranty.id','=','claim_yunda.warranty_id')
            ->join('product','product.id','=','cust_warranty.product_id')
            ->where('claim_yunda.user_id',$users->id)
            ->whereIn('claim_yunda.status',$where)
            ->select('claim_yunda.*','claim_yunda.type as claim_type','claim_yunda.status as claim_status', 'claim_yunda.created_at as claim_created_at','claim_yunda.id as claim_id','cust_warranty.*','product.product_name')
            ->get();
        $status = config('yunda');
        return view('channels.yunda.claim_progress', compact('list','status', 'type'));
    }




    public function claimInfo(){
        $input = $this->request->all();
        $result = DB::table('claim_yunda')
            ->join('claim_yunda_info','claim_yunda_info.claim_id','=','claim_yunda.id')
            ->join('cust_warranty','cust_warranty.id','=','claim_yunda.warranty_id')
            ->join('product','product.id','=','cust_warranty.product_id')
            ->where('claim_yunda.id', $input['claim_id'])
            ->select(
                'claim_yunda.*',
                'claim_yunda.type as claim_type',
                'claim_yunda.status as claim_status',
                'claim_yunda.created_at as claim_created_at',
                'claim_yunda_info.created_at as claim_info_created_at',
                'claim_yunda_info.updated_at as claim_info_updated_at',
                'claim_yunda_info.remark',
                'claim_yunda.id as claim_id','cust_warranty.*',
                'product.product_name'
            )
            ->first();
        $status = config('yunda');

        return view('channels.yunda.claim_info', compact('result','status'));
    }

    /**
     *
     * @param $file
     * @return string
     * @throws \Exception
     */
    protected function uploadFile($file)
    {
        $types = array('jpg', 'jpeg', 'png', 'pdf');
        $extension = $file->getClientOriginalExtension();
        if(!in_array($extension, $types)){
            throw  new \Exception('文件类型错误');
        }
        $path = 'upload/claim/'.date('Y-m-d',time()).'/';
        $name = date("YmdHis") . rand(1000, 9999) . '.' . $extension;
        $file -> move($path, $name);
        return $path . $name;
    }

    /**
     * base64图片上传
     * @param $base64_img
     * @return array
     */
    public function baseUploadFile()
    {
        $input = $this->request->all();

        if(empty($input['name']) || empty($input['base64']) || empty($input['claim_id'])) return json_encode(['code'=>500,'msg'=>'缺少必要参数！']);

        $base64_img = trim($input['base64']);
        $data = [];
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $base64_img = explode(',',$base64_img);
            $data['base64'] = $base64_img[1];
            $data['fileKey'] = md5('Yunda_'.$input['name'].$input['claim_id']);
            $data['fileName'] = 'yunda.'.$result[2]; //接口只使用后缀

            $response = Curl::to(config('yunda.file_url').'file/upBase')
                ->returnResponseObject()
                ->withData(json_encode($data))
                ->withTimeout(60)
                ->withHeader("Content-Type: application/json;charset=UTF-8")
                ->post();

            if($response->status != 200) return json_encode(['code'=>500,'msg'=>'文件服务连接失败！']);

            $content = json_decode($response->content, true);

            if($content['code'] == 200){
                return json_encode(['code'=>200,'url_key'=>$data['fileKey']]);
            }else{
                logHelper::logSuccess($data,$content ,'image' ,'response');
                return json_encode(['code'=>500,'msg'=>'文件上传失败！']);
            }
        }else{
            //文件错误
            return json_encode(['code'=>500,'msg'=>'文件错误']);
        }
    }
}