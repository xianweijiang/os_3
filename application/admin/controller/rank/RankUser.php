<?php

namespace app\admin\controller\rank;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Db;
use think\Request;
use think\Url;
use think\Cache;
use service\FormBuilder as Form;
use app\admin\model\rank\Rank as RankModel;
use app\admin\model\rank\RankUser as RankUserModel;

/**
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class RankUser extends AuthController
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
     * @return json
     */
    public function rank_user_list(){
        $where=Util::getMore([
            ['order',''],
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(RankUserModel::rankUserList($where));
    }

}
