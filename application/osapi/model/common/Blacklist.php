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
use app\osapi\model\user\UserModel;


class Blacklist extends BaseModel
{

    /**
     * 黑名单列表
     * @author qhy
     */
    public static function getBlackList($uid)
    {
        $list=self::where('uid',$uid)->where('status',1)->order('create_time desc')->column('black_uid');
        $user=UserModel::where('uid','in',$list)->field('uid,nickname,avatar')->select()->toArray();
        $user=array_combine(array_column($user,'uid'),$user);
        foreach($list as &$value){
            $value=$user[$value];
        }
        return $list;
    }

    /**
     * 判断是否拉黑了
     * @author qhy
     */
    public static function isBlack($uid,$black_uid)
    {
        $count=self::where('uid',$uid)->where('black_uid',$black_uid)->where('status',1)->count();
        return $count;
    }

    /**
     * 执行拉黑
     * @author qhy
     */
    public static function doBlack($uid,$black_uid)
    {
        $id=self::where('uid',$uid)->where('black_uid',$black_uid)->value('id');
        if($id){
            $data['status']=1;
            $data['create_time']=time();
            $res=self::where('id',$id)->update($data);
        }else{
            $data['uid']=$uid;
            $data['black_uid']=$black_uid;
            $data['status']=1;
            $data['create_time']=time();
            $res=self::insert($data);
        }
        return $res;
    }

    /**
     * 取消拉黑
     * @author qhy
     */
    public static function doDelBlack($uid,$black_uid)
    {
        $id=self::where('uid',$uid)->where('black_uid',$black_uid)->value('id');
        $data['status']=0;
        $data['create_time']=time();
        $res=self::where('id',$id)->update($data);
        return $res;
    }

}
























