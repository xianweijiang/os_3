<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/30
 * Time: 14:52
 */

namespace app\osapi\model\com;


use app\osapi\model\BaseModel;
use app\osapi\model\user\UserModel;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\model\com\MessageUserPopup;
use app\admin\model\com\ComThread;


class Message extends BaseModel
{

    /**
     * 获取通知消息
     */
    public static function getNotice($uid,$page,$row){
        $map=[
            'to_uid'=>$uid,
            'type_id'=>1,
        ];
        $notice=self::where($map)->page($page,$row)->order('create_time desc')->select()->toArray();
        return $notice;
    }

    public static function getUserNotice($uid,$page,$row){
        $map=[
            'to_uid'=>array('in',array(0,$uid)),
            'type_id'=>1,
            'from_type'=>1,
        ];
        $notice=self::where($map)->page($page,$row)->where('send_time','<',time())->order('create_time desc')->select()->toArray();
        foreach($notice as &$value){
            if($value['route']=='thread'){
                $value['thread_id']=ComThread::where('post_id',$value['link_id'])->value('id');
            }
        }
        unset($value);
        $data['is_read']=1;
        $data['read_time']=time();
        MessageRead::where('type',1)->where('uid',$uid)->where('is_read',0)->update($data);
        return $notice;
    }

    /**
     * 获取评论消息
     */
    public static function getCommentMessage($uid,$page,$row){
        $map=[
            'to_uid'=>$uid,
            'type_id'=>2,
        ];
        $notice=self::where($map)->page($page,$row)->order('create_time desc')->select()->toArray();
        foreach ($notice as &$val){
            $map=[
                'id'=>$val['link_id'],
            ];
            $val['post']=db('com_post')->field('id,fid,tid,author_uid,title,content,image')->where($map)->find();
            $val['forum']=db('com_forum')->field('id,pid,name,type')->where('id',$val['post']['fid'])->find();
            $val['user']=UserModel::getUserInfo($val['from_uid']);
        }
        unset($val);
        $data['is_read']=1;
        $data['read_time']=time();
        MessageRead::where('type',2)->where('uid',$uid)->where('is_read',0)->update($data);
        return $notice;
    }

    /**
     * 获取被赞消息
     */
    public static function getSupportMessage($uid,$page,$row){
        $map=[
            'to_uid'=>$uid,
            'type_id'=>3,
        ];
        $notice=self::where($map)->page($page,$row)->order('create_time desc')->select()->toArray();
        foreach ($notice as &$val){
            $map=[
                'id'=>$val['link_id'],
            ];
            $val['post']=db('com_post')->field('id,fid,tid,author_uid,title,content,image')->where($map)->find();
            $val['forum']=db('com_forum')->field('id,pid,name,type')->where('id',$val['post']['fid'])->find();
            $val['user']=UserModel::getUserInfo($val['from_uid']);
        }
        unset($val);
        $data['is_read']=1;
        $data['read_time']=time();
        MessageRead::where('type',3)->where('uid',$uid)->where('is_read',0)->update($data);
        return $notice;
    }

    /**
     * 获取互动消息
     */
    public static function getInteractionMessage($uid){
        $map=[
            'to_uid'=>$uid,
            'type_id'=>4,
        ];
        $notice=self::where($map)->order('create_time desc')->select()->toArray();
        return $notice;
    }

    /**
     * 获取用户最新的一条消息
     */
    public static function getUserOne($uid){
        $message=self::where('to_uid',$uid)->order('send_time desc')->find();
        if($message['route']=='reply'||$message['route']=='thread'){
            $message['thread_id']=ComThread::where('post_id',$message['link_id'])->value('id');
        }
        $message['is_read']=MessageRead::getUserRead($uid,$message['id']);
        return $message;
    }

    /**
     * 获取用户未读信息条数
     */
    public static function getMessageCount(){
        $uid=get_uid();
        $map=[
            'to_uid'=>$uid,
            'is_read'=>0,
        ];
        $count=self::where($map)->count();
        return $count;
    }

    /**
     * 发送消息
     */
    static public function sendMessage($to_uid,$uid = 0,$content,$type = 1,$title,$from_type = 1,$image = '',$route= '',$link_id = ''){
        $data['to_uid']=$to_uid;
        $data['from_uid']=$uid;
        $data['content']=$content;
        $data['type_id']=$type;
        $data['title']=$title;
        $data['from_type']=$from_type;
        $data['image']=$image;
        $data['route']=$route;
        $data['link_id']=$link_id;
        $data['create_time']=time();
        $data['send_time']=time();
        $res=self::add($data);
        if($res){
            return $res;
        }else{
            return false;
        }
    }

    /**
     * 删除消息
     */
    static public function delMessage($to_uid,$uid = 0,$type = 1,$from_type = 1,$route= '',$link_id = ''){
        $data['to_uid']=$to_uid;
        $data['from_uid']=$uid;
        $data['type_id']=$type;
        $data['from_type']=$from_type;
        $data['route']=$route;
        $data['link_id']=$link_id;
        $res=self::where($data)->delete();
        if($res){
            return true;
        }else{
            return false;
        }
    }

}