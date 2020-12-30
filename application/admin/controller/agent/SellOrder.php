<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/27
 * Time: 15:51
 */

namespace app\admin\controller\agent;


use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService;
use app\shareapi\model\SellOrder as SellOrderModel;

class SellOrder extends AuthController
{
    /**
     * 分销订单页面
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function index()
    {
        $this->assign([
            'year'=> getMonth('y'),
        ]);
        return $this->fetch();
    }
    /**
     * 获取订单列表
     * return json
     */
    public function order_list(){
        $where=UtilService::getMore([
            ['page',1],
            ['limit',20],
            ['select_date',''],
            ['order_status',''],//订单状态（-1：已退款（申请的或者后台直接退款）；0：待发货（已支付,包括申请退款中）；1：待收货；2：已收货(订单完成,待评价,准备返利）；3：交易完成,已评价；4：待付款)
            ['back_status',''],//返利状态，已结算1；未结算2；已关闭0（订单失败，取消、退款等）
            ['keywords_type','order_id'],//关键词类型 order_id：订单号   user：用户昵称或uid    product：商品标题
            ['keywords',''],
            ['excel',0],
        ]);
        return JsonService::successlayui(SellOrderModel::getListPageAdmin($where));
    }

    /**
     * 分销商管理中心的推广订单
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function sell_order()
    {
        $where=UtilService::getMore([
            ['seller_uid',0],
        ]);
        $this->assign([
            'year'=> getMonth('y'),
            'seller_uid'=>$where['seller_uid'],
        ]);
        return $this->fetch();
    }

    /**
     * 获取订单列表
     * return json
     */
    public function sell_order_list(){
        $where=UtilService::getMore([
            ['page',1],
            ['limit',20],
            ['select_date',''],
            ['type','all'],//订单类型    all：全部、level1：一级、level2：二级
            ['keywords',''],
            ['seller_uid',0],
        ]);
        return JsonService::successlayui(SellOrderModel::getSellOrderListPageAdmin($where));
    }
}