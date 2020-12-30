<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:21:23
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-01 20:34:37
 */

namespace app\admin\controller\certification;
use service\FormBuilder as Form;
use traits\CurdControllerTrait;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use think\Url;
use app\admin\model\certification\CertificationCondition as ConditionModel;
use app\admin\controller\AuthController;

/**
 * 认证条件控制器
 * Class Condition
 * @package app\admin\controller\certification
 */
class Condition extends AuthController
{
    use CurdControllerTrait;

    /**
     * 显示认证条件列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $params = Util::getMore([
            ['status',''],
            ['keyword',''],
        ],$this->request);
        $this->assign(ConditionModel::getAdminPage($params));
        $addurl = Url::build('create');
        $this->assign(compact('params','addurl'));
        return $this->fetch();
    }


    /**
     * 禁用启用
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function status(Request $request)
    {
        $id=osx_input('id',0,'intval');
        $data = Util::postMore([
            ['status',1],
            ],$request);
        $conditon=ConditionModel::get($id);
        if(!$conditon) return Json::fail('编辑的记录不存在!');
        $data['status']=$conditon['status']?0:1;
        ConditionModel::edit($data,$id);
        return Json::successful('操作成功!');
    }


}
