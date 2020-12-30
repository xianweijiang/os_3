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
use app\osapi\model\com\ComForum;
use app\osapi\model\user\UserModel;
use think\Cache;

class ComForumAdminApply extends BaseModel
{
    /**
     * qhy
     * 版主审核
     */
    public static function ApplyForumAdmin($id,$status,$reason){

        $data['status']=$status;
        $data['reject_reason']=$reason;
        $res=self::where('id',$id)->update($data);
        return $res;
    }

    /**
     * qhy
     * 版主申请列表
     */
    public static function ForumAdminApplyList($fid,$page,$row)
    {
        $list=self::where('fid',$fid)->where('status',1)->page($page,$row)->select()->toArray();
        $uids=array_column($list,'uid');
        $userList=UserModel::where('uid','in',$uids)->field('uid,nickname')->select()->toArray();
        $userList=array_combine(array_column($userList,'uid'),$userList);
        $forum=ComForum::where('id',$fid)->field('id,name,type')->find()->toArray();
        foreach($list as &$value){
            $value['forum_name']=$forum;
            $value['userInfo']=$userList[$value['uid']];
        }
        unset($value);
        return $list;
    }

    /**
     * qhy
     * 全部版主申请列表
     */
    public static function ForumAdminApplyListAll($fids,$page,$row)
    {
        $list=self::where('fid','in',$fids)->where('status',1)->page($page,$row)->select()->toArray();
        if($list){
            $uids=array_column($list,'uid');
            $userList=UserModel::where('uid','in',$uids)->field('uid,nickname')->select()->toArray();
            $userList=array_combine(array_column($userList,'uid'),$userList);
            $forum_name=ComForum::where('id','in',$fids)->field('id,name,type')->select()->toArray();
            $forum_name=array_combine(array_column($forum_name,'id'),$forum_name);
            foreach($list as &$value){
                $value['forum_name']=$forum_name[$value['fid']];
                $value['userInfo']=$userList[$value['uid']];
            }
            unset($value);
        }
        return $list;
    }

    /**
     * qhy
     * 版主申请
     */
    public static function forumAdminApply($reason,$fid,$uid){
        $data['content']=$reason;
        $data['uid']=$uid;
        $data['fid']=$fid;
        $data['create_time']=time();
        $data['status']=1;
        $data['level']=1;
        $res=self::insert($data);
        return $res;
    }

}