<?php

namespace app\admin\controller\invite;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use app\admin\model\invite\InviteLog as InviteLogModel;

/**
 * 商品管理
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class InviteLog extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->assign([
            'year'       => getMonth('y'),
        ]);
        return $this->fetch();
    }

    public function invite_log_list(){
        $where = Util::getMore([
            ['uid',''],
            ['father_uid',''],
            ['page',1],
            ['limit',20],
            ['data',''],
        ]);
        return JsonService::successlayui(InviteLogModel::InviteLogList($where));
    }

}
