<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/6/3
 * Time: 10:42
 */

namespace app\osapi\model\com;


use app\osapi\model\BaseModel;

class ComAnnounce extends BaseModel
{
    /**
     * 获取一条用户未读过的最新（1个月内）公告
     * @param $uid
     * @return array|bool|false|\PDOStatement|string|\think\Model
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getOneAnnounce($uid)
    {
        if(!$uid){
            return false;
        }
        $start=strtotime('-1 month');
        $end=time();
        $tids=ComAnnounceUser::where('uid ',$uid)->whereBetween('create_time',[$start,$end])->column('tid');
        $oneAnnounce=self::where('start_time','<',time())->where('end_time','>',time())->whereNotIn('tid',$tids)->where('status',1)->order('sort desc,create_time desc')->find();
        return $oneAnnounce;
    }

    /**
     * 获取最新公告
     * @return array|false|\PDOStatement|string|\think\Model
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getLastAnnounce()
    {
        $data=self::where('start_time','<',time())->where('end_time','>',time())->where('status',1)->order('sort desc,create_time desc')->find();
        return $data;
    }
}