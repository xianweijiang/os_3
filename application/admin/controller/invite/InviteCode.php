<?php

namespace app\admin\controller\invite;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use app\admin\model\invite\InviteCode as InviteCodeModel;
use think\Db;

/**
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class InviteCode extends AuthController
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

    /**
     * 栏目设置列表
     *
     * @return json
     */
    public function invite_code_list(){
        $where=Util::getMore([
            ['uid',''],
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(InviteCodeModel::codeList($where));
    }

}
