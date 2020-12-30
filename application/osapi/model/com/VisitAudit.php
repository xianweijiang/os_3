<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/2/5
 * Time: 10:23
 */
namespace app\osapi\model\com;

use app\admin\model\system\SystemConfig;
use app\osapi\lib\ChuanglanSmsApi;
use app\osapi\model\com\ComForum;
use app\osapi\model\user\UserModel;
use app\osapi\model\com\Message;
use think\Cache;
use traits\ModelTrait;
use basic\ModelBasic;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use think\Db;

class VisitAudit extends ModelBasic
{
    use ModelTrait;
    protected $name='com_forum_member';

    /**
     * qhy
     * 成员审核
     */
    public static function ForumAdminMemberApply($ids,$status,$now_uid,$reason,$is_move){
        $data['status']=$status;
        $data['audit_uid']=$now_uid;
        $data['audit_time']=time();
        $data['reject_resaon']=$reason;
        $res=self::where('id','in',$ids)->update($data);
        if($res!==false){
            if($is_move==1){
                $temp=51;
            }elseif($status==1){
                $temp=49;
            }else{
                $temp=50;
            }
            $map['id']=['in',$ids];
            self::send_message($map,$temp,$reason);
            return true;
        }else{
            return false;
        }

    }

    /**
     * qhy
     * 成员管理列表
     */
    public static function ForumAdminMember($fid,$status,$page,$row){
        $admin_uid=ComForumAdmin::where('status',1)->column('uid');
        $list=self::where('fid',$fid)->where('status',$status)->where('uid','not in',$admin_uid)->page($page,$row)->order('create_time desc')->select()->toArray();
        $forum=ComForum::where('id',$fid)->field('id,name,type')->find()->toArray();
        foreach($list as $k=>&$value){
            $value['forum_name']=$forum;
            $value['user'] = UserModel::getUserInfo($value['uid']);  //获取用户信息
            //如果用户不存在，则过滤该条记录
            if($value['user']===null){
                unset($list[$k]);
            }
        }
        unset($k);
        unset($value);
        $list=array_values($list);
        $data['list']=$list;
        $data['count']=self::where('fid',$fid)->where('status',$status)->count();
        return $data;
    }

    /**
     * qhy
     * 成员管理列表
     */
    public static function ForumAdminMemberAll($fids,$status,$page,$row){
        $admin_uid=ComForumAdmin::where('status',1)->column('uid');
        $list=self::where('fid','in',$fids)->where('status',$status)->where('uid','not in',$admin_uid)->page($page,$row)->order('create_time desc')->select()->toArray();
        $forum_name=ComForum::where('id','in',$fids)->field('id,name,type')->select()->toArray();
        $forum_name=array_combine(array_column($forum_name,'id'),$forum_name);
        foreach($list as $k=>&$value){
            $value['forum_name']=$forum_name[$value['fid']];
            $value['user'] = UserModel::getUserInfo($value['uid']);  //获取用户信息
            //如果用户不存在，则过滤该条记录
            if($value['user']===null){
                unset($list[$k]);
            }
        }
        unset($value);
        unset($k);
        $list=array_values($list);
        $data['list']=$list;
        $data['count']=self::where('fid','in',$fids)->where('status',$status)->count();
        return $data;
    }

    /**
     * 发送消息
     * @param $map
     * @param $message_type
     * @param string $reason
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.8
     */
    public static function send_message($map,$message_type,$reason=''){
        $audit=self::where($map)->select()->toArray();
        foreach ($audit as $v){
            //发送举报人消息
            $forum_name=db('com_forum')->where(['id'=>$v['fid']])->value('name');
            $set=MessageTemplate::getMessageSet($message_type);
            //发送消息
            $template=str_replace('{版块名称}', $forum_name, $set['template']);;
            $template=str_replace('{删除原因}', $reason, $template);
            $template=str_replace('{移除原因}', $reason, $template);

            $now_uid=get_uid();
            if($set['status']==1){
                $message_id=Message::sendMessage($v['uid'],$now_uid,$template,1,$set['title'],1,'','','');
                $read_id=MessageRead::createMessageRead($v['uid'],$message_id,$set['popup'],1);
            }
            if($set['sms']==1&&$set['status']==1){
                $account=UserModel::where('uid',$v['uid'])->value('phone');
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
    }

}