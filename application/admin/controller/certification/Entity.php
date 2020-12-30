<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:21:23
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-19 15:18:29
 */

namespace app\admin\controller\certification;
use service\FormBuilder as Form;
use traits\CurdControllerTrait;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use think\Url;
use service\JsonService;
use app\admin\model\certification\CertificationDatum as DatumModel;
use app\admin\model\certification\CertificationCate as CateModel;
use app\admin\model\certification\CertificationEntity as EntityModel;
use app\admin\controller\AuthController;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;
use app\admin\model\system\SystemConfig;

/**
 * 认证实体管理控制器
 * Class Entity
 * @package app\admin\controller\certification
 */
class Entity extends AuthController
{
    use CurdControllerTrait;

    /**
     * 显示认证实体列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $status=$this->request->param('status');
        $params = Util::getMore([
            ['create_time',''],
            ['create_time_between',''],
            ['cate_id',''],
            ['status',''],
            ['keyword',''],
            ['nickname',''],
        ],$this->request);
        //$this->assign(EntityModel::getAdminPage($params,0));

        $cates = CateModel::field('id as value,name')->select()->toArray();
        $this->assign([
            'year'=>getMonth('y'),
        ]);
        $this->assign(compact('params','status','cates'));
        return $this->fetch();
    }

    /**
     * 异步列表
     *
     * @return json
     */
    public function list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['select_date',''],
            ['create_time',''],
            ['create_time_between',''],
            ['cate_id',''],
            ['status',''],
            ['keyword',''],
            ['nickname',''],
        ]);
        return JsonService::successlayui(EntityModel::getAdminPage($where,1));
    }

    /**
     * 删除指定认证实体
     * @return \think\Response
     */
    public function delete()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('参数错误，请重新打开');
        $res = EntityModel::delData($id);
        if(!$res)
            return Json::fail(EntityModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }


    public function view(){
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $entity = EntityModel::get($id);
        if(!$entity) return Json::fail('数据不存在!');
        $datum_datas=unserialize($entity['datum_data']);
        $catedatums=$entity->cate->catedatums->toArray();
        $where=[];
        foreach ($catedatums as $key => $value) {
            $where[$key]=$value['datum_id'];
        }
        $datums=DatumModel::where('id','in',$where)->field('field,form_type,name')->select()->toArray();
        $field[] = Form::input('nickname','用户昵称',$entity['nickname'])->col(Form::col(24))->readonly(true);
        $field[] = Form::input('phone','电话',$entity['phone'])->col(Form::col(24))->readonly(true);
        foreach ($datums as $key => $value) {
            if (!isset($datum_datas[$value['field']])) {
                $datum_datas[$value['field']]="";
            }
            switch ($value['form_type']) {
                case 'text':
                    $field[] = Form::input($value['field'],$value['name'],$datum_datas[$value['field']])->col(Form::col(24))->readonly(true);
                    break;
                case 'file':
                    $field[] = Form::frameImageOne($value['field'],$value['name'],Url::build('admin/widget.images/index',array('fodder'=>'')), $datum_datas[$value['field']])->icon('image')->width('100%')->height('500px')->allowRemove(false);
                    break;
                default:
                    $field[] = Form::input($value['field'],$value['name'],$datum_datas[$value['field']])->col(Form::col(24))->readonly(true);
                    break;
            }
        }
        $field[] = Form::dateTime('create_time','创建时间',$entity['create_time'])->readonly(true);
        $form = Form::make_post_form('详情',$field, Url::build('view'),2);
        $form->hiddenSubmitBtn(true);
        $form->hiddenResetBtn(true);
        $this->assign(compact('form'));
        return $this->fetch('certification/entity/form-builder');
    }


    /**
     * qhy
     * 认证头衔编辑
     */
    public function rztx(){
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $entity = EntityModel::get($id);
        if(!$entity) return Json::fail('数据不存在!');
        $datum_datas=unserialize($entity['datum_data']);
        if(!empty($datum_datas['rztx'])){
            $field[] = Form::input('rztx','认证头衔',$datum_datas['rztx'])->col(Form::col(24));
        }else{
            $field[] = Form::input('rztx','认证头衔','')->col(Form::col(24));
        }
        $form = Form::make_post_form('编辑头衔',$field, Url::build('update_rztx',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('certification/entity/form-builder');
    }

    public function update_rztx(Request $request)
    {
        $id=osx_input('id',0,'intval');
        $data = Util::postMore([
            'rztx',
        ],$request);
        if(!$id) return $this->failed('数据不存在');
        $entity = EntityModel::get($id);
        if(!$entity) return Json::fail('数据不存在!');

        $datum_datas=unserialize($entity['datum_data']);
        $datum_datas['rztx']=$data['rztx'];
        $post['datum_data']=serialize($datum_datas);
        $res=EntityModel::where('id',$id)->update($post);
        if($res===false){
            return Json::fail('编辑失败');
        }else{
            return Json::successful('编辑成功');
        }
    }

    /**
     * 审核单页.
     * @return \think\Response
     */
    public function approve()
    {
        $id=osx_input('id',0,'intval');
        $entity = EntityModel::get($id);
        if(!$entity) return Json::fail('数据不存在!');
        $params = Util::getMore([
            ['status',''],
            ['reject_note',''],
        ],$this->request);
        switch ($params['status']) {
            case '1':
                $data['status']=$params['status'];
                $data['approve_time']=time();
                EntityModel::edit($data,$id);
                //扩展表
                $cate=CateModel::with(['cateprivileges.privilege'])->find($entity['cate_id']);
                $table_name=$cate['table_name'];
                $table_name=CateModel::getTableName($table_name,false);
                $model=db($table_name);
                $res = $model->where('entity_id',$entity['id'])->update($data);
                //更新认证图标
                $user['icon']= $cate['icon'];
                $user['cate_id']= $cate['id'];
                $res = db('user')->where('uid',$entity['uid'])->update($user);
                //是否更新红名
                $cateprivileges=$cate['cateprivileges'];
                foreach ($cateprivileges as $key => $value) {
                    if ($value['privilege']['name']=="点亮红名") {
                        $user['is_red']= 1;
                        $res = db('user')->where('uid',$entity['uid'])->update($user);
                    }
                }
                $set=MessageTemplate::getMessageSet(30);
                $name=CateModel::where('id',$entity['cate_id'])->value('name');
                $length=mb_strlen($name,'UTF-8');
                if($length>6){
                    $name=mb_substr($name,0,6,'UTF-8').'…';
                }
                $template=str_replace('{认证名称}', $name, $set['template']);
                if($set['status']==1){
                    $message_id=Message::sendMessage($entity['uid'],0,$template,1,$set['title'],1,'','certification_details',$entity['cate_id']);
                    $read_id=MessageRead::createMessageRead($entity['uid'],$message_id,$set['popup'],1);
                }
                if($set['sms']==1&&$set['status']==1){
                    $account=db('user')->where('uid',$entity['uid'])->value('phone');
                    $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                    $template='【'.$config['cl_sms_sign'].'】'.$template;
                    $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                    $sms=json_decode($sms,true);
                    if ($sms['code']==0) {
                        $read_data['is_sms']=1;
                        $read_data['sms_time']=time();
                        MessageRead::where('id',$read_id)->update($read_data);
                    }
                }
                return Json::successful('审核成功!');
                break;

            case '-1':
                if ($params['reject_note']) {
                    $data['status']=$params['status'];
                    $data['reject_time']=time();
                    $data['reject_note']=$params['reject_note'];
                    EntityModel::edit($data,$id);
                    //扩展表
                    $table_name=CateModel::find($entity['cate_id'])->table_name;
                    $table_name=CateModel::getTableName($table_name,false);
                    $model=db($table_name);
                    $res = $model->where('entity_id',$entity['id'])->update($data);
                    $set=MessageTemplate::getMessageSet(31);
                    $length=mb_strlen($data['reject_note'],'UTF-8');
                    if($length>7){
                        $data['reject_note']=mb_substr($data['reject_note'],0,7,'UTF-8').'…';
                    }
                    $template=str_replace('{驳回理由}', $data['reject_note'], $set['template']);
                    if($set['status']==1){
                        $message_id=Message::sendMessage($entity['uid'],0,$template,1,$set['title'],1,'','certification_details',$entity['cate_id']);
                        $read_id=MessageRead::createMessageRead($entity['uid'],$message_id,$set['popup'],1);
                    }
                    if($set['sms']==1&&$set['status']==1){
                        $account=db('user')->where('uid',$entity['uid'])->value('phone');
                        $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                        $template='【'.$config['cl_sms_sign'].'】'.$template;
                        $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                        $sms=json_decode($sms,true);
                        if ($sms['code']==0) {
                            $read_data['is_sms']=1;
                            $read_data['sms_time']=time();
                            MessageRead::where('id',$read_id)->update($read_data);
                        }
                    }
                    return Json::successful('驳回成功!');
                }else{
                    $field = [
                        Form::hidden('status',$params['status']),
                        Form::textarea('reject_note','驳回理由',$entity['reject_note'])
                    ];
                    $form = Form::make_post_form('审核认证',$field,Url::build('approve',array('id'=>$id)),2);
                    $this->assign(compact('form'));
                    return $this->fetch('public/form-builder');
                }
                break;

            case '-2':
                $data['status']=$params['status'];
                $data['approve_time']=time();
                EntityModel::edit($data,$id);
                //扩展表
                $table_name=CateModel::find($entity['cate_id'])->table_name;
                $table_name=CateModel::getTableName($table_name,false);
                $model=db($table_name);
                $res = $model->where('entity_id',$entity['id'])->update($data);

                //该用户是否有其他已经通过认证
                $where['uid']=$entity['uid'];
                $where['status']=1;
                $entity_list=EntityModel::where($where)->order('id DESC')->select();
                if (isset($entity_list[0])) {
                    $entity_other=$entity_list[0];
                    $cate=CateModel::with(['cateprivileges.privilege'])->find($entity_other['cate_id']);
                    if ($cate) {
                        //更新认证图标
                        $user['icon']= $cate['icon'];
                        $res = db('user')->where('uid',$entity['uid'])->update($user);
                        //是否更新红名
                        $cateprivileges=$cate['cateprivileges'];
                        foreach ($cateprivileges as $key => $value) {
                            if ($value['privilege']['name']=="点亮红名") {
                                $user['is_red']= 1;
                                $res = db('user')->where('uid',$entity['uid'])->update($user);
                            }
                        }
                    }
                }else{
                    //更新认证图标
                    $user['icon']= '';
                    //更新红名
                    $user['is_red']= 0;
                    $res = db('user')->where('uid',$entity['uid'])->update($user);
                }
                $set=MessageTemplate::getMessageSet(32);
                if($set['status']==1){
                    $message_id=Message::sendMessage($entity['uid'],0,$set['template'],1,$set['title'],1,'','certification_details',$entity['cate_id']);
                    $read_id=MessageRead::createMessageRead($entity['uid'],$message_id,$set['popup'],1);
                }
                if($set['sms']==1&&$set['status']==1){
                    $account=db('user')->where('uid',$entity['uid'])->value('phone');
                    $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                    $set['template']='【'.$config['cl_sms_sign'].'】'.$set['template'];
                    $sms=ChuanglanSmsApi::sendSMS($account,$set['template']); //发送短信
                    $sms=json_decode($sms,true);
                    if ($sms['code']==0) {
                        $read_data['is_sms']=1;
                        $read_data['sms_time']=time();
                        MessageRead::where('id',$read_id)->update($read_data);
                    }
                }
                //return $this->fetch('index');
                return Json::successful('取消认证成功!');
                break;
            default:
                # code...
                break;
        }

    }

}
