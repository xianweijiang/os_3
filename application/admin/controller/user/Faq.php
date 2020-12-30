<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:21:23
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-03 12:30:41
 */

namespace app\admin\controller\user;
use service\FormBuilder as Form;
use traits\CurdControllerTrait;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use think\Url;
use app\admin\model\user\LoginFaq as FaqModel;
use app\admin\controller\AuthController;

/**
 * 常见问题控制器
 * Class Faq
 * @package app\admin\controller\certification
 */
class Faq extends AuthController
{
    use CurdControllerTrait;

    /**
     * 显示常见问题列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $params = Util::getMore([
            ['status',''],
            ['keyword',''],
        ],$this->request);
        $this->assign(FaqModel::getAdminPage($params));
        $addurl = Url::build('create');
        $this->assign(compact('params','addurl'));
        return $this->fetch();
    }


    /**
     * 显示创建常见问题单页.
     *
     * @return \think\Response
     */
    public function create($cid = 0)
    {
        $field = [
            Form::input('title','问题')->required('问题必填'),
            Form::textarea('desc','问题说明')->required('问题说明必填'),
            Form::radio('status','状态',1)->options([['value'=>0,'label'=>'关闭'],['value'=>1,'label'=>'开启']]),
            Form::number('sort','排序',0)
        ];
        $form = Form::make_post_form('添加常见问题',$field,Url::build('save'),3);
        $this->assign(compact('form'));
        //return $this->fetch('public/form-builder');
        return $this->fetch();
        
    }
    

    /**
     * 保存新建的常见问题
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'title',
            ['status',1],
            ['sort',0],
            ['create_time',time()],
            ],$request);
        $data['desc']=osx_input('post.desc','','html');
        if(!$data['title']) return Json::fail('请输入问题');
        FaqModel::set($data);
        return Json::successful('添加常见问题成功!');
    }

    /**
     * 显示编辑常见问题表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $data = FaqModel::get($id);
        if(!$data) return Json::fail('数据不存在!');
        $field = [
            Form::input('title','问题',$data['title']),
            Form::textarea('desc','问题描述',$data['desc']),
            Form::radio('status','状态',$data['status'])->options([['value'=>0,'label'=>'关闭'],['value'=>1,'label'=>'开启']]),
            Form::number('sort','排序',$data['sort'])
        ];
        $form = Form::make_post_form('修改常见问题',$field,Url::build('update',array('id'=>$id)),3);
        $this->assign(compact('form'));
        //return $this->fetch('public/form-builder');
        $this->assign('data',$data);
        return $this->fetch('create');
    }


    /**
     * 保存更新的常见问题
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'title',
            ['status',1],
            ['sort',0],
            ['update_time',time()],
            ],$request);

        $data['desc']=osx_input('post.desc','','html');

        if(!$data['title']) return Json::fail('请输入问题');
        if(!FaqModel::get($id)) return Json::fail('编辑的记录不存在!');
        FaqModel::edit($data,$id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除指定常见问题
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(!$id) return $this->failed('参数错误，请重新打开');
        $res = FaqModel::delData($id);
        if(!$res)
            return Json::fail(FaqModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

}
