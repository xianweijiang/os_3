<?php
namespace app\admin\controller\com;

use app\admin\controller\AuthController;
use app\admin\model\group\Power;
use app\admin\model\system\SystemConfig;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Cache;
use think\Request;
use app\admin\model\com\ComForumAdmin as ForumAdminModel;
use app\admin\model\com\ComForum as ForumModel;
use think\Url;
use app\admin\model\user\User as UserModel;
use app\admin\model\system\SystemAdmin;
use app\admin\model\com\ComForumAdminApply;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;

/**
 * 版块控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class ComForumAdmin extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $forum=ForumModel::getCatTierList();
        $this->assign('forum',$forum);
        return $this->fetch();
    }
    /**
     * @return json
     */
    public function admin_list(){
        $where = Util::getMore([
            ['uid',''],
            ['fid',''],
            ['level',''],
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(ForumAdminModel::AdminList($where));
    }

    public function set_admin()
    {
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(Request $request)
    {
        $post  = $request->post();
        $data = Util::postMore([
            'uid',
            'level',
            'fid'
        ],$request);
        if (!$data['uid']) return Json::fail('请输入用户');
        if (!$data['level']) return Json::fail('请输入权限');
        if (!$data['fid']) return Json::fail('请输入版块');
        $is_admin=ForumAdminModel::where('fid',$data['fid'])->where('status',1)->where('uid',$data['uid'])->count();
        if($is_admin>0){
            return JsonService::fail('该用户已添加，无法再添加');
        }
        //判断是否数量超过后台限制
        $count=ForumAdminModel::where('fid',$data['fid'])->where('status',1)->count();
        if($data['level']==1){
            $follow=db('com_forum_member')->where('status',1)->where('fid',$data['fid'])->where('uid',$data['uid'])->find();
            if(!$follow){
                return JsonService::fail('该用户没有关注该版块，无法添加');
            }
            $admin_num=SystemConfig::getValue('forum_admin_num');
            if($count>=$admin_num){
                return JsonService::fail('版主数量超出后台限制，无法再添加');
            }
        }else{
            $super_admin_num=SystemConfig::getValue('super_forum_admin_num');
            if($count>=$super_admin_num){
                return JsonService::fail('超级版主数量超出后台限制，无法再添加');
            }
        }
        $data['admin']=SystemAdmin::activeAdminIdOrFail();
        $data['create_time']=time();
        $data['status']=1;
        $res=ForumAdminModel::set($data);
        if($res){
            //添加版主绑定
            $g_id=$data['level']==1?4:3;
            Power::add_bind_group_uid($data['uid'],$g_id,0);
            return JsonService::successful('设置成功');
        }else{
            return JsonService::fail('设置失败');
        }
    }


    /**
     * 删除版主
     *
     * @return json
     */
    public function del(){
        $id=osx_input('id',0,'intval');
        if(!$id){
            JsonService::fail('缺少参数');
        }
        $res=ForumAdminModel::where('id',$id)->delete();
        if($res!==false){
            //删除版主绑定
            $admin=ForumAdminModel::where('id',$id)->find();
            if(!ForumAdminModel::where(['uid'=>$admin['uid'],'level'=>$admin['level']])->count()){
                $g_id=$admin['level']==1?4:3;
                Power::delete_bind_group_uid($admin['uid'],$g_id);
            }
            return JsonService::successful('删除成功');
        }else{
            return JsonService::fail('删除失败');
        }
    }

    /**
     * 禁用版主
     *
     * @return json
     */
    public function close(){
        $id=osx_input('id',0,'intval');
        if(!$id){
            JsonService::fail('缺少参数');
        }
        $data['status']=0;
        $res=ForumAdminModel::where('id',$id)->update($data);
        if($res!==false){
            //删除版主绑定
            $admin=ForumAdminModel::where('id',$id)->find();
            if(!ForumAdminModel::where(['uid'=>$admin['uid'],'level'=>$admin['level']])->count()){
                $g_id=$admin['level']==1?4:3;
                Power::delete_bind_group_uid($admin['uid'],$g_id);
            }
            return JsonService::successful('删除成功');
        }else{
            return JsonService::fail('删除失败');
        }
    }

    /**
     * 开启版主
     *
     * @return json
     */
    public function open(){
        $id=osx_input('id',0,'intval');
        if(!$id){
            JsonService::fail('缺少参数');
        }
        $info=ForumAdminModel::where('id',$id)->find();
        $data['status']=1;
        $res=ForumAdminModel::where('id',$id)->update($data);
        if($res!==false){
            //添加版主绑定
            $g_id=$info['level']==1?4:3;
            Power::add_bind_group_uid($info['uid'],$g_id,0);
            return JsonService::successful('删除成功');
        }else{
            return JsonService::fail('删除失败');
        }
    }

    public function find_users(){
        $nickname=osx_input('nickname','','text');
        $users=UserModel::where('nickname|uid|phone','like',"%$nickname%")->limit(10)->select()->toArray();
        $data=array();
        if($users){
            foreach ($users as $v){
                if($v){
                    $data[]=array('value'=>$v['uid'],'name'=>$v['nickname']);
                }
            }
        }
        return Json::successlayui(count($users),$data,'成功');
    }

    public function select_class(Request $request)
    {
        $post  = $request->post();
        $data = Util::postMore([
            'type',
        ],$request);
        if($data['type']==1){
            $select=ForumModel::where('status',1)->where('pid','>',0)->select()->toArray();
        }else{
            $select=ForumModel::where('status',1)->where('pid',0)->select()->toArray();
        }
        Json::successful($select);
    }

    public function apply()
    {
        return $this->fetch();
    }

    /**
     * @return json
     */
    public function admin_apply_list(){
        $where = Util::getMore([
            ['uid',''],
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(ComForumAdminApply::AdminApplyList($where));
    }

    /**
     * qhy
     * 审核通过
     */
    public function approved(){
        $id=osx_input('id',0,'intval');
        $info=ComForumAdminApply::where('id',$id)->find();
        $data['uid']=$info['uid'];
        $data['fid']=$info['fid'];
        $data['level']=$info['level'];
        $data['admin']=SystemAdmin::activeAdminIdOrFail();
        $data['create_time']=time();
        $data['status']=1;
        $res=ForumAdminModel::set($data);
        if($res){
            $fid=ComForumAdminApply::where('id',$id)->value('fid');
            ComForumAdminApply::where('id',$id)->update(['status'=>2]);
            $forum_name=db('com_forum')->where(['id'=>$fid])->value('name');
            $uid=ComForumAdminApply::where('id',$id)->value('uid');
            //添加版主绑定
            $g_id=$data['level']==1?4:3;
            Power::add_bind_group_uid($data['uid'],$g_id,0);
            //发送消息
            $set=MessageTemplate::getMessageSet(52);
            $template=str_replace('{版块名称}', $forum_name, $set['template']);
            if($set['status']==1){
                $message_id=Message::sendMessage($uid,0,$template,1,$set['title'],1);
                $read_id=MessageRead::createMessageRead($uid,$message_id,$set['popup'],1);
            }
            if($set['sms']==1&&$set['status']==1){
                $account=UserModel::where('uid',$uid)->value('phone');
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
            return JsonService::successful('审核成功');
        }else{
            return JsonService::fail('审核失败');
        }
    }

    /**
     * qhy
     * 审核驳回
     */
    public function reject(Request $request)
    {
		$id=osx_input('id',0,'intval');
        $data = Util::postMore([
            'reject_reason',
        ],$request);
        if($data['reject_reason']==''){
            return JsonService::fail('驳回理由不能为空');
        }
        $data['status']=0;
        $res= ComForumAdminApply::where('id',$id)->update($data);
        if($res!==false){
            $uid=ComForumAdminApply::where('id',$id)->value('uid');
            //发送消息
            $set=MessageTemplate::getMessageSet(53);
            $template=str_replace('{驳回原因}', $data['reject_reason'], $set['template']);
            if($set['status']==1){
                $message_id=Message::sendMessage($uid,0,$template,1,$set['title'],1);
                $read_id=MessageRead::createMessageRead($uid,$message_id,$set['popup'],1);
            }
            if($set['sms']==1&&$set['status']==1){
                $account=UserModel::where('uid',$uid)->value('phone');
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
            return JsonService::successful('驳回成功');
        }else{
            return JsonService::fail('驳回失败');
        }
    }

    public function reject_reason($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $field = [
            Form::input('reject_reason','驳回理由')->type('textarea'),
        ];
        $form = Form::make_post_form('驳回理由',$field,Url::build('reject',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

}
