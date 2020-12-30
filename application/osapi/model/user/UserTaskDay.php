<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/6/5
 * Time: 14:10
 */

namespace app\osapi\model\user;


use app\osapi\model\BaseModel;

class UserTaskDay extends BaseModel
{
    /**
     * 用户签到日常任务
     * @param $uid
     * @return bool
     * @author qhu qhy@ourstu.com
     * @date 2019/3/19 10:48
     */
    public function dayCheck($uid){
        $task_day=$this->where('uid',$uid)->where('action','check')->find();//日常任务
        if(!$task_day){//如果是第一次
            $score=db('user_info')->where('uid',$uid)->setInc('score1', 20);
            if(!$score){
                $this->apiError('日常签到积分增加失败');
            }
            $data_day['action']='check';
            $data_day['uid']=$uid;
            $data_day['value']=20;
            $data_day['create_time']=time();
            $data_day['update_time']=time();
            $day=db('task_day')->insert($data_day);
            if(!$day){
                $this->apiError('日常任务存入数据库失败');
            }
        }else{
            $score=db('user_info')->where('uid',$uid)->setInc('score1', 20);
            if(!$score){
                $this->apiError('日常签到积分增加失败');
            }
            $data['update_time']=time();
            $this->isUpdate(true)->save($data,['id' => $uid]);
        }
    }

    /**
     * 用户发主题帖日常任务
     * @param $uid
     * @return bool
     * @author qhu qhy@ourstu.com
     * @date 2019/3/19 13:48
     */
    public static function daySendThread($uid){
        $task_day=self::where('uid',$uid)->where('action','add_thread')->find();//日常任务
        if(!$task_day){//如果是第一次
            $score=1;
            //$score=db('user_info')->where('uid',$uid)->setInc('score1', 20);
            if(!$score){
                self::setErrorInfo('日常发主题帖积分增加失败');
                return false;
            }
            $data_day['action']='add_thread';
            $data_day['uid']=$uid;
            $data_day['value']=20;
            $data_day['create_time']=time();
            $data_day['update_time']=time();
            $day=self::add($data_day);
            if(!$day){
                self::setErrorInfo('日常任务存入数据库失败');
                return false;
            }
        }else{
            if(date('Y-m-d',$task_day['update_time']) == date('Y-m-d',time())){
                return true;
            }else{
                $score=1;
                //$score=db('user_info')->where('uid',$uid)->setInc('score1', 20);
                if(!$score){
                    self::setErrorInfo('日常发主题帖积分增加失败');
                    return false;
                }
                $res=self::update(['update_time'=>time()],['id' => $task_day['id']]);
                if(!$res){
                    self::setErrorInfo('日常任务存入数据库失败');
                    return false;
                }
            }
        }
    }

    /**
     * 用户回帖日常任务
     * @param $uid
     * @return bool
     * @author qhu qhy@ourstu.com
     * @date 2019/3/19 14:32
     */
    public static function dayPost($uid){
        $task_day=self::where('uid',$uid)->where('action','post_comment')->find();//日常任务
        if(!$task_day){//如果是第一次
            $score=1;
            //$score=db('user_info')->where('uid',$uid)->setInc('score1', 20);
            if(!$score){
                self::setErrorInfo('日常回复积分增加失败');
                return false;
            }
            $data_day['action']='post_comment';
            $data_day['uid']=$uid;
            $data_day['value']=20;
            $data_day['create_time']=time();
            $data_day['update_time']=time();
            $day=self::add($data_day);
            if(!$day){
                self::setErrorInfo('日常任务存入数据库失败');
                return false;
            }
        }else{
            if(date('Y-m-d',$task_day['update_time']) == date('Y-m-d',time())){
                return true;
            }else{
                $score=1;
                //$score=db('user_info')->where('uid',$uid)->setInc('score1', 20);
                if(!$score){
                    self::setErrorInfo('日常发主题帖积分增加失败');
                    return false;
                }
                $res=self::update(['update_time'=>time()],['id' => $task_day['id']]);
                if(!$res){
                    self::setErrorInfo('日常任务存入数据库失败');
                    return false;
                }
            }
        }
    }


}