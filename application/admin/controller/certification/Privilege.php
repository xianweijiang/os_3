<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:21:23
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-17 14:48:44
 */

namespace app\admin\controller\certification;
use service\FormBuilder as Form;
use traits\CurdControllerTrait;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use think\Url;
use app\admin\model\certification\CertificationPrivilege as PrivilegeModel;
use app\admin\model\certification\CertificationCatePrivilege as CatePrivilegeModel;
use app\admin\controller\AuthController;

/**
 * 认证特权控制器
 * Class Privilege
 * @package app\admin\controller\certification
 */
class Privilege extends AuthController
{
    use CurdControllerTrait;

    /**
     * 显示认证特权列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $params = Util::getMore([
            ['status',''],
            ['keyword',''],
        ],$this->request);
        $this->assign(PrivilegeModel::getAdminPage($params));
        $addurl = Url::build('create');
        $this->assign(compact('params','addurl'));
        return $this->fetch();
    }

    /**
     * 显示创建认证特权单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $field = [
            Form::input('name','特权名称')->required('特权名称必填'),
            Form::input('desc','描述')->required('描述必填'),
            Form::frameImageOne('icon','图标（256*256）',Url::build('admin/widget.images/index',array('fodder'=>'icon','big'=>1)))->icon('image'),
            Form::radio('status','状态',1)->options([['value'=>0,'label'=>'关闭'],['value'=>1,'label'=>'开启']]),
            Form::number('sort','排序',0)
        ];
        $form = Form::make_post_form('添加认证特权',$field,Url::build('save'),3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的认证特权
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'name',
            'desc',
            ['icon',''],
            ['status',1],
            ['sort',0],
            ['create_time',time()],
            ],$request);
        if(!$data['name']) return Json::fail('请输入名称');
        PrivilegeModel::set($data);
        return Json::successful('添加认证特权成功!');
    }

    /**
     * 显示编辑认证特权表单页.
     * @return \think\Response
     */
    public function edit()
    {
        $id=osx_input('id',0,'intval');
        $data = PrivilegeModel::get($id);
        if(!$data) return Json::fail('数据不存在!');
        $field = [
            Form::input('name','特权名称',$data['name']),
            Form::input('desc','描述',$data['desc']),
            Form::frameImageOne('icon','图标（256*256）',Url::build('admin/widget.images/index',array('fodder'=>'icon')),$data['icon'])->icon('image'),
            Form::radio('status','状态',$data['status'])->options([['value'=>0,'label'=>'关闭'],['value'=>1,'label'=>'开启']]),
            Form::number('sort','排序',$data['sort'])
        ];
        $form = Form::make_post_form('修改认证特权',$field,Url::build('update',array('id'=>$id)),3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存更新的认证特权
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update(Request $request)
    {
        $id=osx_input('post.id',0,'intval');
        $data = Util::postMore([
            'name',
            'desc',
            ['icon',''],
            ['status',1],
            ['sort',0],
            ['update_time',time()],
            ],$request);
        if(!$data['name']) return Json::fail('请输入名称');
        if(!PrivilegeModel::get($id)) return Json::fail('编辑的记录不存在!');
        PrivilegeModel::edit($data,$id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除指定认证特权
     * @return \think\Response
     */
    public function delete()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('参数错误，请重新打开');
        $data=PrivilegeModel::find($id);
        if ($data['built_in']) {
            return $this->failed('内置不能删除！');
        }
        $res = PrivilegeModel::delData($id);
        //删除关联表信息
        $res1 = CatePrivilegeModel::where('privilege_id',$id)->delete();
        if(!$res)
            return Json::fail(PrivilegeModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

}
