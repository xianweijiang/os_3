<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/6/10
 * Time: 14:40
 */

namespace app\osapi\model\user;


use app\osapi\model\BaseModel;

class UserFriend extends BaseModel
{
    /**
     * 重置好友关系
     * @param $follow_uid
     * @param $type 1:执行关注时触发；0：执行取消关注时触发
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function remarkFriend($follow_uid,$type=1)
    {
        $uid=get_uid();
        if($uid==$follow_uid||$uid*$follow_uid==0){
            exception('非法操作！');
            return false;
        }
        $map=[
            'uid'=>$uid,
            'friend_uid'=>$follow_uid,
        ];
        $map1=[
            'uid'=>$follow_uid,
            'friend_uid'=>$uid,
        ];
        if($type==0){//取消关注，肯定不是好友关系了
            self::where($map)->setField('status',0);
            self::where($map1)->setField('status',0);
        }else{
            $is_follow=UserFollow::isFollow($follow_uid,$uid);//我刚关注的人是否关注了我
            if($is_follow){//添加好友关系
                self::where($map)->delete();
                self::where($map1)->delete();

                $map['create_time']=$map1['create_time']=time();
                $map['status']=$map1['status']=1;
                self::add($map);
                self::add($map1);
            }
        }
        return true;
    }

    /**
     * 判断是否是朋友关系
     * @param $uid
     * @param $friend_uid
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function isFriend($uid,$friend_uid)
    {
        $map=[
            'uid'=>$uid,
            'friend_uid'=>$friend_uid,
            'status'=>1
        ];
        $res=self::where($map)->count();
        if($res){
            return true;
        }else{
            return false;
        }
    }
}