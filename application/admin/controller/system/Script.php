<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use app\admin\model\system\Script as ScriptModel;

/**
 * 商品管理
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class Script extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->fetch();
    }

    public function log_list(){
        $where = Util::getMore([
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(ScriptModel::ScriptLogList($where));
    }

}
