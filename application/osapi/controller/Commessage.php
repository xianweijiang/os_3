<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/24
 * Time: 17:11
 */

namespace app\osapi\controller;


use app\osapi\model\com\MessageNews;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\model\com\MessageUserPopup;
use app\admin\model\com\ComThread;

class ComMessage extends Base
{

    /**
     * 运营消息
     */
    public function messageNew(){
        $page = input('page',1);
        $row = input('row', 10);
        $message_new=MessageNews::getMessageNews($page,$row);
        $this->apiSuccess($message_new);
    }

    /**
     * 通知消息
     */
    public function messageNotice(){
        $page=input('post.page/d',1);
        $row=input('post.row/d',10);
        $uid=get_uid();
        $notice = Message::getNotice($uid,$page,$row);
        $this->apiSuccess($notice);
    }

    /**
     * 通知消息
     */
    public function messageUserNotice(){
        $page=input('post.page/d',1);
        $row=input('post.row/d',10);
        $uid=get_uid();
        $notice = Message::getUserNotice($uid,$page,$row);
        $this->apiSuccess($notice);
    }

    /**
     * 评论消息
     */
    public function messageComment(){
        $page=input('post.page/d',1);
        $row=input('post.row/d',10);
        $uid=get_uid();
        $notice = Message::getCommentMessage($uid,$page,$row);
        $this->apiSuccess($notice);
    }

    /**
     * 被赞消息
     */
    public function messageSupport(){
        $page=input('post.page/d',1);
        $row=input('post.row/d',10);
        $uid=get_uid();
        $notice = Message::getSupportMessage($uid,$page,$row);
        $this->apiSuccess($notice);
    }

    /**
     * 互动消息
     */
    public function messageInteraction(){
        $uid=get_uid();
        $notice = Message::getInteractionMessage($uid);
        $this->apiSuccess($notice);
    }

    /**
     * 获取新消息
     */
    public function newMessage(){
        $count = Message::getMessageCount();
        $this->apiSuccess($count);
    }

    /**
     * 用户设置弹窗
     */
    public function user_set_popup(){
        $status=input('status','','intval');
        $uid=get_uid();
        $res = MessageUserPopup::setUserPopup($uid,$status);
        if($res===false){
            $this->apiError('设置失败');
        }else{
            $this->apiSuccess('设置成功');
        }
    }

    /**
     * 获取用户弹窗设置
     */
    public function get_user_popup(){
        $uid=get_uid();
        $res = MessageUserPopup::getUserPopup($uid);
        $this->apiSuccess($res);
    }

    /**
     * 消息首页
     */
    public function message_index(){
        $uid=$this->_needLogin();
        $res = Message::getUserOne($uid);
        $res['message_count']=MessageRead::getMessageCount($uid);
        $res['reply_count']=MessageRead::getReplyCount($uid);
        $res['support_count']=MessageRead::getSupportCount($uid);
        $this->apiSuccess($res);
    }

    /**
     * 单条消息设置为已读
     */
    public function setReadOne(){
        $message_id=input('message_id','','intval');
        $uid=get_uid();
        $data['is_read']=1;
        $data['read_time']=time();
        MessageRead::where('message_id',$message_id)->where('uid',$uid)->update($data);
        $this->apiSuccess('设置成功');
    }

    /**
     * 弹窗列表
     */
    public function popupList(){
        $uid=get_uid();
        $message_ids=MessageRead::where('is_popup',0)->where('uid',$uid)->order('create_time desc')->limit(10)->column('message_id');
        if($message_ids){
            $message=Message::where('id','in',$message_ids)->select()->toArray();
            foreach($message as &$value){
                if($value['route']=='reply'||$value['route']=='thread'){
                    $value['thread_id']=ComThread::where('post_id',$value['link_id'])->value('id');
                }
            }
            $data['is_popup']=1;
            $data['popup_time']=time();
            MessageRead::where('is_popup',0)->where('uid',$uid)->update($data);
            $this->apiSuccess($message);
        }else{
            $message='';
            $this->apiSuccess($message);
        }
    }

    public function sendPhone(){
        $cid=input('cid','','intval');
        $title='您有一条新消息';
        $content='有人点赞了您的帖子';
        $payload='';

    }

}