<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/24
 * Time: 17:11
 */

namespace app\commonapi\model;

use app\osapi\model\common\Support;
use basic\ModelBasic;

class Gong extends ModelBasic
{

    //自定义任务完成加分
    public static function finishzidingyi($jifenflag)
    {
        $guanzhu = db('system_renwu')->where('jifenflag',$jifenflag)->find();
        if($guanzhu['status'] == 1){
            $uid=get_uid();

            Support::addrenwuscore($guanzhu,1) ;


        }
    }


    //完成任务加积分(点赞、评论、收藏、发帖)
    public  static function finishtask($jifenflag,$tasktablename,$uidflag)
    {

        $guanzhu = db('system_renwu')->where('jifenflag',$jifenflag)->find();
        if($guanzhu['status'] == 1){
            $uid=get_uid();
            if($jifenflag == 'dianzan'){
                $followcount = db($tasktablename)->whereTime('create_time',date('Y-m-d'))->where($uidflag,$uid)->where('status',1)->count();
            }elseif($jifenflag == 'pinglun'){
                $followcount = db($tasktablename)->where($uidflag,$uid)->where('is_thread',0)->count();
            }else{
                $followcount = db($tasktablename)->whereTime('create_time',date('Y-m-d'))->where($uidflag,$uid)->count();
            }


            if($followcount == $guanzhu['require'] && $followcount >=1 ){//小于等于设定值时，加积分
                //增加积分
                Support::addrenwuscore($guanzhu,$followcount) ;

            }
        }
    }
    // 任务未完成减积分(点赞)
    public static function canceltask($jifenflag,$tasktablename,$uidflag)
    {
        $uid=get_uid();
        $guanzhu = db('system_renwu')->where('jifenflag',$jifenflag)->find();
        if($jifenflag == 'dianzan'){
            $followcount = db($tasktablename)->whereTime('create_time',date('Y-m-d'))->where($uidflag,$uid)->where('status',1)->count();
        }else{
            $followcount = db($tasktablename)->whereTime('create_time',date('Y-m-d'))->where($uidflag,$uid)->count();
        }


        $followcount = empty($followcount) ? 0 : $followcount ;
        Support::subrenwuscore($guanzhu,$followcount) ;
    }
    //第一次行为(点赞、评论、收藏、发帖)
    public static function firstaction($actiontablename,$actionflag,$uidflag)
    {
        $uid=get_uid();

        if($actionflag =='shoucipinglun'){  //首次评论
            $count = db($actiontablename)->where($uidflag,$uid)->where('is_thread',0)->count();
        }else{
            $count = db($actiontablename)->where($uidflag,$uid)->count();
        }

        if($count ==1 ){
            $guanzhu = db('system_renwu')->where('jifenflag',$actionflag)->find();
            if($guanzhu['status'] == 1){
                Support::firstAction($guanzhu,$uid,$count) ;
            }

        }

    }
    //完成行为加积分(点赞、评论、收藏、发帖)
    public static function actionadd($actionflag,$actiontable,$uidflag,$uid=0)
    {
        $uid=empty($uid) ? get_uid() : $uid;
        $guanzhu = db('system_rule_action')->where('actionflag',$actionflag)->find();

        if($actionflag == 'dianzan' || $actionflag == 'jiarubankuai'){
            $count = db($actiontable)->whereTime('create_time',date('Y-m-d'))->where($uidflag,$uid)->where('status',1)->count();
        }elseif($actionflag == 'zhuce'){
            $count =1 ;
        }elseif($actionflag == 'pinglun'){
            $count = db($actiontable)->where($uidflag,$uid)->where('is_thread',0)->whereTime('create_time',date('Y-m-d'))->count();
        }else{
            $count = db($actiontable)->whereTime('create_time',date('Y-m-d'))->where($uidflag,$uid)->count();
        }



        Support::addjifen($guanzhu,$count,$uid) ;

    }
    //取消行为减积分(取消点赞)
    public static function actionsub($actionflag,$actiontable,$uidflag)
    {

        $uid=get_uid();
        $guanzhu = db('system_rule_action')->where('actionflag',$actionflag)->find();
        if($actionflag == 'dianzan' || $actionflag == 'jiarubankuai'){
            $count = db($actiontable)->whereTime('create_time',date('Y-m-d'))->where($uidflag,$uid)->where('status',1)->count();
        }else{
            $count = db($actiontable)->whereTime('create_time',date('Y-m-d'))->where($uidflag,$uid)->count();
        }

        if($actionflag == 'shoucangtiezi'){
            $actionflag = 'shoucang' ;
        }
        $renwu = db('system_renwu')->where('jifenflag',$actionflag)->find();
        if($actionflag == 'jiarubankuai'){
            Support::subjifen($guanzhu,$count,$uid) ;
            return true;
        }
        if($count >= 1 && $count <= $renwu['require']){

            Support::subjifen($guanzhu,$count,$uid) ;
        }
    }
    //登录加分
    public static function actionsystem($actionflag,$uid)
    {
        $guanzhu = db('system_rule_action')->where('actionflag',$actionflag)->find();
        self::actionadd() ;
    }



    /**
     * //绑定手机号、更换头像、完善资料
     * @param $jifenflag
     * @param $status  1手机号，2头像，3个人信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *time Times
     *author 181299251@qq.com
     */
    public static function bindfirst($jifenflag,$status)
    {
        $uid=get_uid();
        $wan = db('user_wanshan')->where('uid',$uid)->where('status',$status)->find();

        // 如果没有填写过个人信息，加分
        if(empty($wan)){
            db('user_wanshan')->insert([
                'uid' => $uid,
                'status' => $status
            ]);
            $guanzhu = db('system_renwu')->where('jifenflag',$jifenflag)->find();
            Support::addrenwuscore($guanzhu) ;
        }


    }

    //帖子被删除、
    public static function delaction($actionflag,$uid)
    {

        $guanzhu = db('system_rule_action')->where('actionflag',$actionflag)->find();

        Support::delsubjifen($guanzhu,$uid) ;

    }


    /**
     * 系统周任务：每周的下单总数、下单总金额 ，完成下单数加分，完成购物总金额 加分
     * @param $orderId
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @return boolean
     *time 2019/10/20 10:38
     *author 181299251@qq.com
     */
    public static function weekOrderTask($orderId){


        $order = db('store_order')->where('order_id',$orderId)->find();

        $seven = $order['pay_time'] + 86400 * 7  ;
        $task = db('week_order_task')->whereTime('start','<=',$order['pay_time'])->whereTime('end','>=',$order['pay_time'])->where('uid',$order['uid'])->find() ;

        $res=true;
        if(empty($task)){
            $data = [
                'uid' => $order['uid'],
                'start' => $order['pay_time'],
                'end' => $seven,
                'total' => $order['total_price'],
                'nums' => 1,
            ] ;
            $taskId = db('week_order_task')->insertGetId($data) ;
            return self::orderAddFen(1,$order['total_price'],$order['uid'],1,$taskId) ;
        }else{
            $res=$res && db('week_order_task')->where('id',$task['id'])->setInc('nums',1) ;
            $res=$res && db('week_order_task')->where('id',$task['id'])->setInc('total',$order['total_price']) ;

            $nums = $task['nums'] + 1;
            $total = $task['total'] + $order['total_price'];

            return self::orderAddFen($nums,$total,$order['uid'],$task['status'],$task['id']) ;
        }

        return $res;
    }

    /**
     * @param $nums
     * @param $total
     * @param $uid
     * @param $status
     * @param $taskId
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     *time 2019/11/3 8:24
     *author 181299251@qq.com
     */
    public static function orderAddFen($nums,$total,$uid,$status,$taskId)
    {
        //下单数
        $res = true ;
        $guanzhu = db('system_renwu')->where('jifenflag','xiadanshu')->find();
        if($guanzhu['status'] == 1){
            if($nums == $guanzhu['require'] && $nums >=1 ){//满购物次数加积分
                //增加积分
                $res=$res && Support::addrenwuscore($guanzhu,$nums,$uid) ;
            }
        }
        //购物金额
        $guanzhu = db('system_renwu')->where('jifenflag','gouwujine')->find();
        if($guanzhu['status'] == 1){
            if($total >= $guanzhu['require'] && $status == 1 ){//大于等于设定值时，加积分
                //增加积分
                $res=$res && Support::addrenwuscore($guanzhu,$total,$uid) ;
                $res = $res && db('week_order_task')->where('id',$taskId)->update(['status'=>2]);

            }
        }
        return $res;


    }

}




