<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/6/13
 * Time: 14:41
 */

namespace app\osapi\model\common;


use app\osapi\model\BaseModel;
use app\osapi\model\com\ComForum;
use app\osapi\model\com\ComPost;
use app\osapi\model\com\ComThread;
use app\osapi\model\user\UserModel;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;
use app\admin\model\system\SystemConfig;

class Support extends BaseModel
{


    /**
     * 判断是否点赞了
     * @param $model
     * @param $row
     * @param int $uid 不传代表当前登录用户
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function isSupport($model,$row,$uid=0)
    {
        $uid==0&&$uid=get_uid();
        $map=[
            'model'=>$model,
            'row'=>$row,
            'uid'=>$uid,
            'status'=>1
        ];
        $count=self::where($map)->count();
        return $count?true:false;
    }

    /**
     * 执行点赞
     * @param $model
     * @param $row
     * @param int $uid
     * @return bool|int|string
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function doSupport($model,$row,$uid=0)
    {
        if($uid==0){
            self::setErrorInfo('非法操作！');
            return false;
        }
        $checkModel=self::_checkModel($model);
        $author_uid=ComPost::where('id',$row)->value('author_uid');
        if(!$checkModel){
            self::setErrorInfo('非法操作！');
            return false;
        }
        $data=[
            'model'=>$model,
            'row'=>$row,
            'uid'=>$uid,
            'create_time'=>time(),
            'status'=>1
        ];
        self::startTrans();
        try{
            $res=self::add($data);
            switch ($model){
                case 'thread':
                    ComThread::where('post_id',$row)->setInc('support_count');
                    $to_uid=ComThread::where('post_id',$row)->value('author_uid');
                    if($author_uid!=$uid){
                        $set=MessageTemplate::getMessageSet(1);
                        if($set['status']==1){
                            $nickname=UserModel::where('uid',$uid)->value('nickname');
                            $template=str_replace('{用户昵称}', $nickname, $set['template']);
                            $message_id=Message::sendMessage($to_uid,$uid,$template,3,$set['title'],3,'',$model,$row);
                            MessageRead::createMessageRead($to_uid,$message_id,$set['popup'],3);
                        }
                    }
                    $type=8;
                    $action_title='点赞主题帖子';
                    break;
                case 'forum':
                    ComForum::where('id',$row)->setInc('support_count');
                    $type=10;
                    $action_title='点赞版块';
                    break;
                case 'ucard':
                    UserModel::where('uid',$row)->setInc('support_count');
                    if($row!=$uid){
                        $set=MessageTemplate::getMessageSet(2);
                        if($set['status']==1){
                            $nickname=UserModel::where('uid',$uid)->value('nickname');
                            $template=str_replace('{用户昵称}', $nickname, $set['template']);
                            $message_id=Message::sendMessage($row,$uid,$template,3,$set['title'],3,'',$model,$row);
                            MessageRead::createMessageRead($row,$message_id,$set['popup'],3);
                        }
                    }
                    $type=9;
                    $action_title='点赞用户小名片';
                    break;
                case 'reply':
                    UserModel::where('uid',$uid)->setInc('support_count');
                    ComPost::where('id',$row)->setInc('support_count');
                    $post=ComPost::where('id',$row)->find();
                    $tid=ComThread::where('id',$post['tid'])->value('post_id');
                    if($author_uid!=$uid){
                        $set=MessageTemplate::getMessageSet(1);
                        if($set['status']==1){
                            $nickname=UserModel::where('uid',$uid)->value('nickname');
                            $template=str_replace('{用户昵称}', $nickname, $set['template']);
                            $message_id=Message::sendMessage($post['author_uid'],$uid,$template,3,$set['title'],3,'',$model,$tid);
                            MessageRead::createMessageRead($post['author_uid'],$message_id,$set['popup'],3);
                        }
                    }
                    $type=14;
                    $action_title='点赞评论';
                    break;
                default:
            }
            action_log($uid,$type,$action_title,'support',$res);
            census('support',1);
            self::commitTrans();
            return $res;
        }catch (\Exception $e){
            self::rollbackTrans();
            self::setErrorInfo('点赞失败！：'.self::getErrorInfo().$e->getMessage());
            return false;
        }
    }

    /**
     * 取消点赞
     * @param $model
     * @param $row
     * @param int $uid
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function doDelSupport($model,$row,$uid=0)
    {
        if($uid==0){
            self::setErrorInfo('非法操作！');
            return false;
        }
        $checkModel=self::_checkModel($model);
        if(!$checkModel){
            self::setErrorInfo('非法操作！');
            return false;
        }
        $map=$map_up=[
            'model'=>$model,
            'row'=>$row,
            'status'=>1
        ];
        $map_up['uid']=$uid;
        $support_info=self::where($map_up)->find();
        self::startTrans();
        try{
            $res=self::update(['status'=>0],$map_up);
            if(!$res){
                exception('点赞记录不存在或未改动！');
            }
            switch ($model){
                case 'thread':
                    ComThread::where('post_id',$row)->setDec('support_count');
                    $to_uid=ComThread::where('post_id',$row)->value('author_uid');
                    Message::delMessage($to_uid,$uid,3,3,$model,$row);
                    $type=11;
                    $action_title='取消点赞主题帖子';
                    break;
                case 'forum':
                    ComForum::where('id',$row)->setDec('support_count');
                    $type=13;
                    $action_title='取消点赞版块';
                    break;
                case 'ucard':
                    UserModel::where('uid',$row)->setDec('support_count');
                    Message::delMessage($row,$uid,3,3,$model,$row);
                    $type=12;
                    $action_title='取消点赞用户小名片';
                    break;
                case 'reply':
                    UserModel::where('uid',$uid)->setDec('support_count');
                    ComPost::where('id',$row)->setDec('support_count');
                    $to_uid=ComPost::where('id',$row)->value('author_uid');
                    Message::delMessage($to_uid,$uid,3,3,$model,$row);
                    $type=15;
                    $action_title='取消点赞评论';
                    break;
                default:
            }
            action_log($uid,$type,$action_title,'support',$support_info['id']);
            self::commitTrans();
            return true;
        }catch (\Exception $e){
            self::rollbackTrans();
            self::setErrorInfo('取消点赞失败！：'.self::getErrorInfo().$e->getMessage());
            return false;
        }
    }

    /**
     * 判断点赞对象是否合法
     * @param $model
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _checkModel($model)
    {
        return in_array($model,[
            'thread',
            'forum',
            'ucard',
            'reply'
        ]);
    }

    // 增加任务积分
    public static function addrenwuscore($guanzhu,$count=0,$uid=0)
    {

        $user_id = $uid==0 ? get_uid() : $uid ;

        return self::firstAction($guanzhu,$user_id,$count);

    }

    //减去任务积分
    public static function subrenwuscore($guanzhu,$count)
    {

        if($count   == $guanzhu['require']){//  减去积分
            return  self::subtrue($guanzhu,$count) ;
        }
        return true;

    }
    //减去任务积分
    public static function subtrue($guanzhu,$count)
    {
        $user_id = get_uid() ;
        $jifenzhonglei = db('system_rule')->where('status',1)->select();
        $res = true;
        if(!empty($jifenzhonglei)){
            $log = [] ;
            foreach ($jifenzhonglei as $item) {
                //$res = $res && UserModel::where('uid',$user_id)->where($item['flag'],'>=',intval($guanzhu[$item['flag']]))->setDec($item['flag'],intval($guanzhu[$item['flag']])) ;
                $temp =  UserModel::where('uid',$user_id)->where($item['flag'],'>=',intval($guanzhu[$item['flag']]))->setDec($item['flag'],intval($guanzhu[$item['flag']])) ;
                $temp = $temp>=0 ? true : false ;
                $res = $res && $temp;
                if(intval($guanzhu[$item['flag']])>0){
                    $log[$item['flag']] = intval($guanzhu[$item['flag']]) ;
                }

            }
            if(!empty($log)){
                self::jiafenlog($user_id,$guanzhu['name'],$log,0,'任务') ;
            }

        }
        return $res;

    }

    //第一次任务加分、完成任务加分
    public static function firstAction($guanzhu,$user_id,$count=0)
    {
        //获取开启的积分种类，从行为取出对应的积分种类加分，记录对应的积分种类
        $jifenzhonglei = db('system_rule')->where('status',1)->select();

        $res = true;
        if(!empty($jifenzhonglei)){
            $log = [] ;
            foreach ($jifenzhonglei as $item) {
                if($item['flag']=='exp'){
                    $old_exp=UserModel::where('uid',$user_id)->value('exp');
                    $now_exp=$old_exp+intval($guanzhu[$item['flag']]);
                    $next_exp=db('system_user_grade')->where('experience','>',$old_exp)->where('is_del',0)->order('experience asc')->column('experience');
                    if($next_exp){
                        if($now_exp>$next_exp[0]){
                            $set=MessageTemplate::getMessageSet(39);
                            if($set['status']==1){
                                $message_id=Message::sendMessage($user_id,0,$set['template'],1,$set['title'],1,'','qiandao');
                                $read_id=MessageRead::createMessageRead($user_id,$message_id,$set['popup'],1);
                            }
                            if($set['sms']==1&&$set['status']==1){
                                $account=UserModel::where('uid',$user_id)->value('phone');
                                $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                                $template='【'.$config['cl_sms_sign'].'】'.$set['template'];
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
                $temp =  UserModel::where('uid',$user_id)->setInc($item['flag'],intval($guanzhu[$item['flag']])) ;
                $temp = $temp>=0 ? true : false ;
                $res = $res && $temp;
                if($guanzhu[$item['flag']]>0){
                    $log[$item['flag']] = $guanzhu[$item['flag']] ;
                }
            }
            if(!empty($log)){
                $log['zong'] = $count ;

                self::jiafenlog($user_id, $guanzhu['name'],$log,1,'任务') ;
            }

        }
        return $res;

    }




    // 增加行为积分
    public static function addjifen($guanzhu,$count,$user_id)
    {

        //获取开启的积分种类，从行为取出对应的积分种类加分，记录对应的积分种类
        $jifenzhonglei = db('system_rule')->where('status',1)->select();
        $res = true;
        if(!empty($jifenzhonglei)){
            $log = [] ;
            foreach ($jifenzhonglei as $item) {
                $tag = self::deal($item['flag']) ;
                //加分
                if($count * $guanzhu[$tag[0]] <= $guanzhu[$tag[1]]){
                    if($item['flag']=='exp'){
                        $old_exp=UserModel::where('uid',$user_id)->value('exp');
                        $now_exp=$old_exp+intval($guanzhu[$tag[0]]);
                        $next_exp=db('system_user_grade')->where('experience','>',$old_exp)->where('is_del',0)->order('experience asc')->column('experience');
                        if($next_exp){
                            if($now_exp>$next_exp[0]){
                                $set=MessageTemplate::getMessageSet(39);
                                if($set['status']==1){
                                    $message_id=Message::sendMessage($user_id,0,$set['template'],1,$set['title'],1,'','qiandao');
                                    $read_id=MessageRead::createMessageRead($user_id,$message_id,$set['popup'],1);
                                }
                                if($set['sms']==1&&$set['status']==1){
                                    $account=UserModel::where('uid',$user_id)->value('phone');
                                    $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                                    $template='【'.$config['cl_sms_sign'].'】'.$set['template'];
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
                    $temp =   UserModel::where('uid',$user_id)->setInc($item['flag'],intval($guanzhu[$tag[0]])) ;
                    $temp = $temp>=0 ? true : false ;
                    $res = $res && $temp;

                    if(intval($guanzhu[$tag[0]])>0){
                        $log[$item['flag']] = intval($guanzhu[$tag[0]]) ;
                    }
                }
            }
            //记录日志
            if(!empty($log)){
                $log['zong'] = $count ;
                self::jiafenlog($user_id,$guanzhu['actionname'],$log,1,'行为') ;
            }

        }
        return $res;
    }

    //减去行为积分 如果今天点赞次数 比今日要求的多很多，取消点赞，不减分，如果点赞次数取消后，点赞次数小于要求的点赞次数，减分
    public static function subjifen($guanzhu,$count,$user_id)
    {

        //获取开启的积分种类，从行为取出对应的积分种类加分，记录对应的积分种类
        $jifenzhonglei = db('system_rule')->where('status',1)->select();
        $res = true;
        if(!empty($jifenzhonglei)){
            $log = [] ;
            foreach ($jifenzhonglei as $item) {
                $tag = self::deal($item['flag']) ;
                //减分
                if($count * $guanzhu[$tag[0]] <= $guanzhu[$tag[1]]){

                    //$res = $res &&  UserModel::where('uid',$user_id)->where($item['flag'],'>=',intval($guanzhu[$tag[0]]))->setDec($item['flag'],intval($guanzhu[$tag[0]])) ;
                    $temp =   UserModel::where('uid',$user_id)->where($item['flag'],'>=',intval($guanzhu[$tag[0]]))->setDec($item['flag'],intval($guanzhu[$tag[0]])) ;
                    $temp = $temp>=0 ? true : false ;
                    $res = $res && $temp;
                    if(intval($guanzhu[$tag[0]])>0){
                        $log[$item['flag']] = intval($guanzhu[$tag[0]]) ;
                    }
                }
            }
            //记录日志
            if(!empty($log)){
                $log['zong'] = $count ;
                self::jiafenlog($user_id,$guanzhu['actionname'],$log,0,'行为') ;
            }

        }
        return $res;
    }

    //签到加积分
    public static function qiandaoaddjifen($guanzhu,$user_id)
    {
        $jifenzhonglei = db('system_rule')->where('status',1)->select();
        $res = true ;
        if(!empty($jifenzhonglei)){
            $log = [] ;
            foreach ($jifenzhonglei as $item) {
                $tag = self::deal($item['flag']) ;
                //加分
                if($item['flag']=='exp'){
                    $old_exp=UserModel::where('uid',$user_id)->value('exp');
                    $now_exp=$old_exp+intval($guanzhu[$tag[0]]);
                    $next_exp=db('system_user_grade')->where('experience','>',$old_exp)->where('is_del',0)->order('experience asc')->column('experience');
                    if($next_exp){
                        if($now_exp>$next_exp[0]){
                            $set=MessageTemplate::getMessageSet(39);
                            if($set['status']==1){
                                $message_id=Message::sendMessage($user_id,0,$set['template'],1,$set['title'],1,'','qiandao');
                                $read_id=MessageRead::createMessageRead($user_id,$message_id,$set['popup'],1);
                            }
                            if($set['sms']==1&&$set['status']==1){
                                $account=UserModel::where('uid',$user_id)->value('phone');
                                $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                                $template='【'.$config['cl_sms_sign'].'】'.$set['template'];
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
                //$res = $res && UserModel::where('uid',$user_id)->setInc($item['flag'],intval($guanzhu[$tag[0]])) ;
                $temp = UserModel::where('uid',$user_id)->setInc($item['flag'],intval($guanzhu[$tag[0]])) ;
                $temp = $temp>=0 ? true : false ;
                $res = $res && $temp;
                if(intval($guanzhu[$tag[0]])>0){
                    $log[$item['flag']] = intval($guanzhu[$tag[0]]) ;
                }
            }
            if(!empty($log)){
                self::jiafenlog($user_id,$guanzhu['name'],$log,1,'签到') ;
            }
        }
        return $res ;

    }

    //减去行为积分
    public static function delsubjifen($guanzhu,$user_id)
    {

        $jifenzhonglei = db('system_rule')->where('status',1)->select();
        $res = true;
        if(!empty($jifenzhonglei)){
            $log = [] ;
            foreach ($jifenzhonglei as $item) {
                $tag = self::deal($item['flag']) ;
                //减分
                //$res = $res && UserModel::where('uid',$user_id)->where($item['flag'],'>=',intval($guanzhu[$tag[0]]))->setDec($item['flag'],intval($guanzhu[$tag[0]])) ;
                $temp = UserModel::where('uid',$user_id)->where($item['flag'],'>=',intval($guanzhu[$tag[0]]))->setDec($item['flag'],intval($guanzhu[$tag[0]])) ;
                $temp = $temp>=0 ? true : false ;
                $res = $res && $temp;
                if(intval($guanzhu[$tag[0]])>0){
                    $log[$item['flag']] = intval($guanzhu[$tag[0]]) ;
                }

            }
            //记录日志
            if(!empty($log)){
                self::jiafenlog($user_id,$guanzhu['actionname'],$log,0,'行为') ;
            }

        }
        return $res ;


    }

    //记录行为日志
    public static function jiafenlog($uid,$explain,$log,$type=1,$model)
    {
        $log['uid'] = $uid;
        $log['explain'] = $explain;
        $log['create_time'] = time();
        $log['type'] = $type;
        $log['model'] = $model;
        return  db('renwu_jiafen_log')->insert($log) ;

    }



    public static function deal($tag)
    {
        if(strpos($tag,'exp')!==false){
            return ['expone','expmax'] ;
        }
        if(strpos($tag,'fly')!==false){
            return ['flyone','flymax'] ;
        }

        if(strpos($tag,'gong')!==false){
            return ['gongone','gongmax'] ;
        }
        if(strpos($tag,'buy')!==false){
            return ['buyone','buymax'] ;
        }
        if(strpos($tag,'one')!==false){
            return ['firstone','firstmax'] ;
        }
        if(strpos($tag,'two')!==false){
            return ['twoone','twomax'] ;
        }
        if(strpos($tag,'three')!==false){
            return ['threeone','threemax'] ;
        }
        if(strpos($tag,'four')!==false){
            return ['fourone','fourmax'] ;
        }
        if(strpos($tag,'five')!==false){
            return ['fiveone','fivemax'] ;
        }

    }


}
























