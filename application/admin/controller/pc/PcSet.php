<?php

namespace app\admin\controller\pc;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Db;
use think\Request;
use think\Cache;
use think\Url;
use service\FormBuilder as Form;
use app\admin\model\pc\PcSet as PcSetModel;

/**
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class PcSet extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $info=PcSetModel::where('id',1)->find()->toArray();
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request)
    {
        $data = Util::postMore([
            'is_jump',
            'jump_type',
            ['frame_url',''],
            ['pc_url',''],
            ['image',''],
        ],$request);
        PcSetModel::where('id',1)->update($data);
        return Json::successful('成功');
    }

}
