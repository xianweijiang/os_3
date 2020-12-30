<?php

namespace app\admin\controller\Sensitive;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use app\admin\model\sensitive\SensitiveLog as SensitiveLogModel;

/**
 * 商品管理
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class SensitiveLog extends AuthController
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

    public function log_list(){
        $where = Util::getMore([
            ['sensitive',''],
            ['page',1],
            ['limit',20],
            ['data',''],
        ]);
        return JsonService::successlayui(SensitiveLogModel::SensitiveLogList($where));
    }

}
