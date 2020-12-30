<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/26
 * Time: 9:11
 */

namespace app\shareapi\controller;


use app\shareapi\model\SellOrder;
use basic\ControllerBasic;
use service\JsonService;
use service\UtilService;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;
use think\Cache;
use app\admin\model\system\SystemConfig;
class Order extends ControllerBasic
{
    public function index()
    {
        $uid=$this->_needLogin();
        $is_seller=is_seller($uid);
        if(!$is_seller){
            $this->apiError('请先成为分销商');
        }
        list($page,$row,$status,$keyword)=UtilService::getMore([
            ['page',1],
            ['row',10],
            ['status',0],
            ['keyword','']
        ],$this->request,true);
        $keyword=text($keyword);
        switch ($status){
            case 1:
                $map['order_status']=['neq',4];
                $map['back_status']=2;
                break;
            case 2:
                $map['back_status']=1;
                break;
            case 3:
                $map['back_status']=0;
                break;
            default:
        }
        if($keyword!=''){
            $map['goods_title|order_id']=['like','%'.$keyword.'%'];
        }
        $map['father1|father2']=$uid;
        list($order_list,$count)=SellOrder::getListPage($map,$page,$row,'create_time desc');
        foreach ($order_list as &$val){
            $val['back_to_my_money']=($val['father1']==$uid)?$val['father1_back']:$val['father2_back'];
        }
        unset($val);

        return JsonService::successlayui($count,$order_list,'success',200);
    }


}