<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/24
 * Time: 17:08
 */

namespace app\osapi\controller;

use app\admin\controller\setting\SystemConfig;
use app\admin\model\com\ForumPower;
use app\admin\model\com\VisitAudit as ForumAudit;
use app\osapi\lib\ChuanglanSmsApi;
use app\osapi\model\com\ComForum;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageRead;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\user\UserModel;

class VisitAudit extends Base
{
    /**
     * 版块审核请求
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.9
     */
    public function add_audit(){
        $fid=input('fid',0);
        $reason = input('reason','','text');
        $uid=get_uid();
        if(!$fid){
            $this->apiError('请选择申请的版块');
        }
        if(!$uid){
            $this->apiError('请先登录后,再申请');
        }
        if(ForumAudit::where(['uid'=>$uid,'fid'=>$fid,'status'=>2])->count()){
            $this->apiError('您的申请正在审核中,请勿重复提交');
        }
        $data['fid']=$fid;
        $data['reason']=$reason;
        $data['status']=2;
        $data['uid']=$uid;
        $data['create_time']=time();
        $res=ForumAudit::addDate($data);
        if($res){
            //发送短信到版主
            $pid=db('com_forum')->where('id',$fid)->value('pid');
            $map['status']=1;
            $map['fid']=['in',[$fid,$pid]];
            $user=db('com_forum_admin')->where($map)->group('uid')->column('uid');
            $user_name=db('user')->where(['uid'=>$uid])->value('nickname');
            $set=MessageTemplate::getMessageSet(54);
            $time=time_format($data['create_time']);
            foreach ($user as $v){
                $forum_name=db('com_forum')->where(['id'=>$fid])->value('name');
                //发送消息
                $template=str_replace('{版块名称}', $forum_name, $set['template']);;
                $template=str_replace('{用户}', $user_name, $template);;
                $template=str_replace('{时间}', $time, $template);;
                $now_uid=get_uid();
                if($set['status']==1){
                    $message_id=Message::sendMessage($v,$now_uid,$template,1,$set['title'],1,'','user_apply','');
                    $read_id=MessageRead::createMessageRead($v,$message_id,$set['popup'],1);
                }
                if($set['sms']==1&&$set['status']==1){
                    $account=UserModel::where('uid',$v)->value('phone');
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
            }
            $this->apiSuccess('申请已提交,正在审核中');
        }else{
            $this->apiError('申请提交失败');
        }
    }

    /**
     * 单个权限获取判断
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.9
     */
    public function get_fid_power(){
        $fid=input('fid',0);
        $uid=get_uid();
        $action=input('action','');
        if(!$fid){
            $this->apiError('请选择申请的版块');
        }
        $power=forum_power($action,$uid,$fid);
        if($power==-1&&$action=='audit'){
            $this->apiError('您暂无权限访问私密分区下的该版块');
        }
        $fid=ComForum::where('id',$fid)->field('logo,name')->find();
        $this->apiSuccess([$action=>$power,'fid'=>$fid],'获取权限成功');
    }

    /**
     * 获取全部权限
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.9
     */
    public function get_fid_power_all(){
        $fid=input('fid',0);
        $uid=get_uid();
        if(!$fid){
            $this->apiError('请选择申请的版块');
        }
        $power=ForumPower::get_forum_user_power($uid,$fid);
        $this->apiSuccess(['power'=>$power],'获取权限成功');
    }
}