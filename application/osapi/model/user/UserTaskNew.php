<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/6/4
 * Time: 13:41
 */

namespace app\osapi\model\user;


use app\osapi\model\BaseModel;

class UserTaskNew extends BaseModel
{
    /**
     * 用户签到新手任务
     * @param $uid
     * @return bool
     * @author qhu qhy@ourstu.com
     * @date 2019/3/19 11:20
     */
    public function newCheck($uid){
        $task_new=$this->where('uid',$uid)->where('action','check')->find();//新手任务
        if(!$task_new){
            $score=db('user_info')->where('uid',$uid)->setInc('score1', 20);
            if(!$score){
                $this->apiError('新手签到积分增加失败');
            }
            $data_new['action']='check';
            $data_new['uid']=$uid;
            $data_new['value']=20;
            $data_new['create_time']=time();
            $first=db('task_new')->insert($data_new);
            if(!$first){
                $this->apiError('新手任务存入数据库失败');
            }
        }else{
            return true;
        }
    }

    /**
     * 用户加入版块新手任务
     * @param $uid
     * @return bool
     * @author qhu qhy@ourstu.com
     * @date 2019/3/19 13:20
     */
    public static function newAddToForum($uid){
        $task_new=self::where('uid',$uid)->where('action','add_to_forum')->find();//新手任务
        if(!$task_new){
            /**
             * todo
             * 新手任务增加用户积分
             * 等积分体系完成
             */
            $score=1;
            //$score=db('user_info')->where('uid',$uid)->setInc('score1', 20);
            if(!$score){
                self::setErrorInfo('新手加入版块积分增加失败');
                return false;
            }
            $data_new['action']='add_to_forum';
            $data_new['uid']=$uid;
            $data_new['value']=20;
            $data_new['create_time']=time();
            $first=self::add($data_new);
            if(!$first){
                self::setErrorInfo('新手任务存入数据库失败');
                return false;
            }
        }else{
            return true;
        }
    }

    /**
     * 用户发主题帖新手任务
     * @param $uid
     * @return bool
     * @author qhu qhy@ourstu.com
     * @date 2019/3/19 13:40
     */
    public static function newSendThread($uid){
        $task_new=self::where('uid',$uid)->where('action','add_thread')->find();//新手任务
        if(!$task_new){
            /**
             * todo
             * 新手任务增加用户积分
             * 等积分体系完成
             */
            $score=1;
            //$score=db('user_info')->where('uid',$uid)->setInc('score1', 20);
            if(!$score){
                self::setErrorInfo('新手发主题帖积分增加失败');
                return false;
            }
            $data_new['action']='add_thread';
            $data_new['uid']=$uid;
            $data_new['value']=20;
            $data_new['create_time']=time();
            $first=self::add($data_new);
            if(!$first){
                self::setErrorInfo('新手任务存入数据库失败');
                return false;
            }
        }else{
            return true;
        }
    }

    /**
     * 用户回帖新手任务
     * @param $uid
     * @return bool
     * @author qhu qhy@ourstu.com
     * @date 2019/3/19 14:45
     */
    public static function newPost($uid){
        $task_new=self::where('uid',$uid)->where('action','post_comment')->find();//新手任务
        if(!$task_new){
            /**
             * todo
             * 新手任务增加用户积分
             * 等积分体系完成
             */
            $score=1;
            //$score=db('user_info')->where('uid',$uid)->setInc('score1', 20);
            if(!$score){
                self::setErrorInfo('新手回帖积分增加失败');
                return false;
            }
            $data_new['action']='post_comment';
            $data_new['uid']=$uid;
            $data_new['value']=20;
            $data_new['create_time']=time();
            $first=self::add($data_new);
            if(!$first){
                self::setErrorInfo('新手任务存入数据库失败');
                return false;
            }
        }else{
            return true;
        }
    }
}