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

class ComForumAdmin extends BaseModel
{
    /**
     * qhy
     * 获取版主列表
     */
    public static function getForumAdminList($fid)
    {
        $uid=get_uid();
        $is_follow_uids=db('user_follow')->where('uid',$uid)->where('status',1)->column('follow_uid');
        unset($v);
        $pid=ComForum::where('id',$fid)->value('pid');
        $adminTwo=self::where('fid',$pid)->where('status',1)->column('uid');
        $adminTwo=array_unique($adminTwo);
        $adminOne=self::where('fid',$fid)->where('uid','not in',$adminTwo)->where('status',1)->column('uid');
        foreach ($adminTwo as &$val) {
            $val=UserModel::getUserInfo($val);
            $val['is_follow']=in_array($val['uid'],$is_follow_uids)?true:false;
        }
        unset($val);
        $adminOne=array_unique($adminOne);
        foreach ($adminOne as &$v) {
            $v=UserModel::getUserInfo($v);
            $v['is_follow']=in_array($v['uid'],$is_follow_uids)?true:false;
        }
        unset($v);
        $adminOne=array_values($adminOne);
        $adminTwo=array_values($adminTwo);
        $list['adminOne']=$adminOne;
        $list['adminTwo']=$adminTwo;
        $list['adminThree']=db('bind_group_uid')->where(['uid'=>$uid,'g_id'=>2,'status'=>1,'end_time'=>[['eq',0],['gt',time()],'or']])->count();
        return $list;
    }

    /**
     * qhy
     * 获取我管理的版块
     */
    public static function getMyForumAdmin($uid)
    {
        $adminOne=self::where('uid',$uid)->where('status',1)->where('level',1)->column('fid');
        $adminTwo=self::where('uid',$uid)->where('status',1)->where('level',2)->column('fid');
        $fid=ComForum::where('pid','in',$adminTwo)->where('pid','>',0)->where('status',1)->column('id');
        $fids=array_merge($adminOne,$fid);
        $forumList=ComForum::where('id','in',$fids)->field('id,name,type,logo')->select()->toArray();
        foreach($forumList as &$item){
            $item['post_count']=ComThread::where('fid',$item['id'])->where('status',1)->count();
            $item['member_count']=ComForumMember::where('fid',$item['id'])->where('status',1)->count();
            $item['logo_src'] = get_root_path($item['logo']);
        }
        unset($item);
        $forumList=array_combine(array_column($forumList,'id'),$forumList);
        $listOne=array();
        foreach ($adminOne as $key=>&$v) {
            $listOne[$key]['forum']=$forumList[$v];
            $listOne[$key]['level']='版主';
        }
        unset($v);
        unset($key);
        $listTwo=array();
        foreach ($fid as $k=>&$val) {
            $listTwo[$k]['forum']=$forumList[$val];
            $listTwo[$k]['level']='超级版主';
        }
        unset($val);
        unset($k);
        $list=array_merge($listOne,$listTwo);
        return $list;
    }


}