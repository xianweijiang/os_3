<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/22
 * Time: 13:30
 */

namespace app\shareapi\controller;


use app\admin\model\system\SystemConfig;
use app\shareapi\model\Sell;
use app\shareapi\model\SellOrder;
use basic\ControllerBasic;

class Seller extends ControllerBasic
{
    /**
     * 我的团队
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function myGroup()
    {
        $uid=$this->_needLogin();
        $seller_info=Sell::get(['uid'=>$uid]);
        if(!$seller_info||$seller_info['status']!=1){
            return $this->apiError('请先成为分销商！');
        }
        $level_one_list=db('invite_level')->where('father1',$uid)->field('uid,child_num,create_time,order_num')->limit(0,500)->order('create_time desc')->select();
        $level_two_list=db('invite_level')->where('father2',$uid)->field('uid,father1,create_time,order_num')->limit(0,500)->order('create_time desc')->select();
        $group_user_num=count($level_one_list)+count($level_two_list);

        $level_one_user_info=db('user')->where('uid','in',array_column($level_one_list,'uid'))->field('uid,nickname,avatar')->select();
        foreach($level_one_user_info as &$value){
            if($value['avatar']){
                $value['avatar']=get_root_path($value['avatar']);
                $value['avatar_64']=thumb_path($value['avatar'],64,64);
                $value['avatar_128']=thumb_path($value['avatar'],128,128);
                $value['avatar_256']=thumb_path($value['avatar'],256,256);
            }
        }
        unset($value);
        $level_one_user_info=array_combine(array_column($level_one_user_info,'uid'),$level_one_user_info);
        $level_two_user_info=db('user')->where('uid','in',array_column($level_two_list,'uid'))->field('uid,nickname,avatar')->select();
        foreach($level_two_user_info as &$value){
            if($value['avatar']){
                $value['avatar']=get_root_path($value['avatar']);
                $value['avatar_64']=thumb_path($value['avatar'],64,64);
                $value['avatar_128']=thumb_path($value['avatar'],128,128);
                $value['avatar_256']=thumb_path($value['avatar'],256,256);
            }
        }
        unset($value);
        $level_two_user_info=array_combine(array_column($level_two_user_info,'uid'),$level_two_user_info);
        foreach ($level_one_list as &$val){
            $val['user_info']=$level_one_user_info[$val['uid']];
            $val['create_time_show']=time_format($val['create_time'],'Y-m-d');
        }
        unset($val);

        foreach ($level_two_list as &$val){
            $val['user_info']=$level_two_user_info[$val['uid']];
            $val['inviter_user_info']=$level_one_user_info[$val['father1']];
            $val['create_time_show']=time_format($val['create_time'],'Y-m-d');
        }
        unset($val);

        $return_data=[
            'group_user_num'=>$group_user_num,//团队成员数，不计算自己
            'leval1_user_list'=>$level_one_list,//下一级用户信息列表 child_num：推广X人，order_num：订单数量，user_info：用户信息
            'level1_user_num'=>count($level_one_list),//下一级用户数
            'level2_user_list'=>$level_two_list,//下二级用户列表 order_num：订单数量，user_info：用户信息，inviter_user_info：推荐人信息
            'level2_user_num'=>count($level_two_list),//下二级用户数
        ];
        $this->apiSuccess($return_data);
    }

    /**
     * 我的收益
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function myIncome()
    {
        $uid=$this->_needLogin();
        $seller_info=Sell::get(['uid'=>$uid]);
        if(!$seller_info||$seller_info['status']!=1){
            $this->apiError('请先成为分销商！');
        }
        //本月收益预估
        $back1_month=SellOrder::where('father1',$uid)->where('back_status','neq',0)->whereTime('create_time','month')->sum('father1_back');
        $back2_month=SellOrder::where('father2',$uid)->where('back_status','neq',0)->whereTime('create_time','month')->sum('father2_back');
        $back_month=$back1_month+$back2_month;

        //上月收益预估
        $back1_last_month=SellOrder::where('father1',$uid)->where('back_status','neq',0)->whereTime('create_time','last month')->sum('father1_back');
        $back2_last_month=SellOrder::where('father2',$uid)->where('back_status','neq',0)->whereTime('create_time','last month')->sum('father2_back');
        $back_last_month=$back1_last_month+$back2_last_month;

        //上月结算收益
        $back1_last_arrive_month=SellOrder::where('father1',$uid)->where('back_status',1)->whereTime('end_time','last month')->sum('father1_back');
        $back2_last_arrive_month=SellOrder::where('father2',$uid)->where('back_status',1)->whereTime('end_time','last month')->sum('father2_back');
        $back_last_arrive_month=$back1_last_arrive_month+$back2_last_arrive_month;

        //今日收益预估
        $back1_day=SellOrder::where('father1',$uid)->where('back_status','neq',0)->whereTime('create_time','today')->sum('father1_back');
        $back2_day=SellOrder::where('father2',$uid)->where('back_status','neq',0)->whereTime('create_time','today')->sum('father2_back');
        $back_day=$back1_day+$back2_day;

        $back_day_order_num=SellOrder::where('father1|father2',$uid)->where('back_status','neq',0)->whereTime('create_time','today')->count();

        //昨日收益预估
        $back1_yesterday=SellOrder::where('father1',$uid)->where('back_status','neq',0)->whereTime('create_time','yesterday')->sum('father1_back');
        $back2_yesterday=SellOrder::where('father2',$uid)->where('back_status','neq',0)->whereTime('create_time','yesterday')->sum('father2_back');
        $back_yesterday=$back1_yesterday+$back2_yesterday;

        $back_yesterday_order_num=SellOrder::where('father1|father2',$uid)->where('back_status','neq',0)->whereTime('create_time','yesterday')->count();

        $return_data=[
            'total_income'=>$seller_info['total_income'],//累计收益
            'has_income'=>$seller_info['has_income'],//可提现收益
            'month_income'=>$back_month,//本月预估收益
            'last_month_income'=>$back_last_month,//上月预估收益
            'last_month_arrive_income'=>$back_last_arrive_month,//上月结算收益
            'day_income'=>$back_day,//今日预估收益
            'yesterday_income'=>$back_yesterday,//昨日预估收益
            'back_day_order_num'=>$back_day_order_num,//今日订单数
            'back_yesterday_order_num'=>$back_yesterday_order_num,//昨日订单数
        ];
        $this->apiSuccess($return_data);
    }

    /**
     * 收益说明获取
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function incomeStatement()
    {
        $agent_income_config=SystemConfig::getValue('agent_income_config');
        $this->apiSuccess(['agent_income_config'=>$agent_income_config]);
    }
}