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

class Prohibit extends BaseModel
{
    /**
     * qhy
     * 解除禁言
     */
    public static function RelieveProhibit($id,$now_uid,$is_admin){
        $data['relieve_uid']=$now_uid;
        if($is_admin['admin_two']==1){
            $data['relieve_identity']=2;
        }elseif($is_admin['admin_three']==1){
            $data['relieve_identity']=4;
        }else{
            $data['relieve_identity']=3;
        }
        $data['status']=2;
        $res=self::where('id',$id)->update($data);
        return $res;
    }

    /**
     * qhy
     * 禁言列表
     */
    public static function prohibitList($fid,$page,$row)
    {
        $list=self::where('fid',$fid)->where('status',1)->where('end_time','>',time())->page($page,$row)->select()->toArray();
        //用户信息
        $forum_name=ComForum::where('id',$fid)->field('id,name,type')->find()->toArray();
        //禁言时间信息
        $times=array_column($list,'prohibit_time');
        $timeList=db('report_prohibit')->where('id','in',$times)->select();
        $timeList=array_combine(array_column($timeList,'id'),$timeList);
        foreach($list as &$value){
            $value['forum_name']=$forum_name;
            $value['userInfo']=UserModel::getUserInfo($value['uid']);
            $value['time']=$timeList[$value['prohibit_time']];
            switch($value['time']['time_type']){
                case 1:
                    $value['time']['time_type']='小时';
                    break;
                case 2:
                    $value['time']['time_type']='天';
                    break;
            }
            $value['create_time']=time_format($value['create_time']);
            $value['end_time']=time_format($value['end_time']);
        }
        unset($value);
        return $list;
    }

    /**
     * qhy
     * 全部禁言列表
     */
    public static function prohibitListAll($fids,$page,$row)
    {
        $list=db('prohibit')->where('fid','in',$fids)->where('end_time','>',time())->where('status',1)->page($page,$row)->select();
        //用户信息
        $forum_name=ComForum::where('id','in',$fids)->field('id,name,type')->select()->toArray();
        $forum_name=array_combine(array_column($forum_name,'id'),$forum_name);
        //禁言时间信息
        $times=array_column($list,'prohibit_time');
        $timeList=db('report_prohibit')->where('id','in',$times)->select();
        $timeList=array_combine(array_column($timeList,'id'),$timeList);
        foreach($list as &$value){
            $value['forum_name']=$forum_name[$value['fid']];
            $value['userInfo']=UserModel::getUserInfo($value['uid']);
            $value['time']=$timeList[$value['prohibit_time']];
            switch($value['time']['time_type']){
                case 1:
                    $value['time']['time_type']='小时';
                    break;
                case 2:
                    $value['time']['time_type']='天';
                    break;
            }
            $value['create_time']=time_format($value['create_time']);
            $value['end_time']=time_format($value['end_time']);
        }
        unset($value);
        return $list;
    }

    /**
     * 禁言
     */
    public static function doProhibit($is_admin,$now_uid,$prohibit_time,$prohibit_uid,$other_reason,$prohibit_reason,$fid){
        //操作人和操作人身份
        if($is_admin['admin_two']==1){
            $data['operation_identity']=2;
        }elseif($is_admin['admin_three']==1){
            $data['operation_identity']=4;
        }else{
            $data['operation_identity']=3;
        }
        $data['operation_uid']=$now_uid;
        $report_prohibit=db('report_prohibit')->where('id',$prohibit_time)->find();
        if($report_prohibit['time_type']==1){
            $prohibit_end_time=$report_prohibit['num']*60*60+time();
        }else{
            $prohibit_end_time=$report_prohibit['num']*60*60*24+time();
        }
        //和原有禁言对比，判断哪个生效
        $old_prohibit=db('prohibit')->where('status',1)->where('uid',$prohibit_uid)->find();
        if($old_prohibit){
            if($old_prohibit['end_time']>$prohibit_end_time){
                $data['status']=0;
            }else{
                db('prohibit')->where('id',$old_prohibit['id'])->update(['status'=>0]);
                $data['status']=1;
            }
        }else{
            $data['status']=1;
        }
        $data['other_reason']=$other_reason;
        $data['prohibit_time']=$prohibit_time;
        $data['prohibit_reason']=$prohibit_reason;
        $data['create_time']=time();
        $data['uid']=$prohibit_uid;
        $data['fid']=$fid;
        $data['end_time']=$prohibit_end_time;
        $res=db('prohibit')->insert($data);
        return $res;
    }

}