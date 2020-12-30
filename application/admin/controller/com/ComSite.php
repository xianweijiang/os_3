<?php
namespace app\admin\controller\com;

use app\admin\controller\AuthController;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Request;
use app\admin\model\com\ComSite as SiteModel;


/**
 * 版块控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */

class ComSite extends AuthController
{

	public function index(){
	    $data=SiteModel::where('id',1)->find()->toArray();
        $this->assign('data', $data);
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
            'forum_name',
            'user_name',
            'new_on',
            'hot_on',
            'threshold',
        ],$request);
        SiteModel::edit($data,1);
        return Json::successful('修改成功!');
    }


}