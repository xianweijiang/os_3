<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/29
 * Time: 13:51
 */

namespace app\admin\controller\share;


use app\admin\controller\AuthController;
use app\shareapi\model\InviteShare;
use service\FormBuilder;
use service\JsonService;
use service\UtilService;
use think\Request;
use think\Url;

class Index extends AuthController
{

    /**
     * 分销海报首页
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function hai_bao()
    {
        $where=UtilService::getMore([
            ['status',2],
        ]);
        $this->assign('status',$where['status']);
        return $this->fetch();
    }

    /**
     * 异步查找海报列表
     *
     * @return json
     */
    public function hai_bao_list(){
        $where=UtilService::getMore([
            ['page',1],
            ['limit',20],
            ['status',2],
        ]);
        return JsonService::successlayui(InviteShare::haiBaoList($where));
    }

    /**
     * 新增海报
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function createOne()
    {
        $field = [
            FormBuilder::input('title','海报名称')->col(FormBuilder::col(24))->required('海报名称必填'),
            FormBuilder::frameImageOne('url','海报图片(562*1000px)',Url::build('admin/widget.images/index',array('fodder'=>'url')))->icon('image')->width('100%')->height('400px'),
            FormBuilder::number('sort','排序'),
            FormBuilder::radio('status','是否显示',1)->options([
                ['value'=>1,'label'=>'显示'],
                ['value'=>0,'label'=>'隐藏'],
            ]),
        ];
        $form = FormBuilder::make_post_form('创建海报',$field,Url::build('saveOne'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 新增海报保存
     * @param Request $request
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function saveOne(Request $request)
    {
        $data = UtilService::postMore([
            'title',
            'url',
            'sort',
            'status',
        ],$request);
        InviteShare::set($data);
        return JsonService::successful('添加成功');
    }

    /**
     * 编辑海报
     * @param $id
     * @return mixed|void
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function edit_one($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $invite_share = InviteShare::get($id);
        if(!$invite_share) return JsonService::fail('数据不存在!');

        $field = [
            FormBuilder::input('title','海报名称',$invite_share->getData('title'))->col(FormBuilder::col(24))->required('海报名必填'),
            FormBuilder::frameImageOne('url','海报图片(562*1000px)',Url::build('admin/widget.images/index',array('fodder'=>'url')),$invite_share->getData('url'))->icon('image')->width('100%')->height('400px'),
            FormBuilder::number('sort','排序',$invite_share->getData('sort')),
            FormBuilder::radio('status','是否启用',$invite_share->getData('status'))->options([
                ['value'=>1,'label'=>'显示'],
                ['value'=>0,'label'=>'隐藏'],
            ]),
        ];
        $form = FormBuilder::make_post_form('编辑版块',$field,Url::build('update_one',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 编辑保存
     * @param Request $request
     * @param $id
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function update_one(Request $request, $id)
    {
        $data = UtilService::postMore([
            'title',
            'url',
            'sort',
            'status',
        ],$request);
        InviteShare::edit($data,$id,'id');
        return JsonService::successful('编辑成功');
    }

    /**
     * 删除海报
     * @param $id
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function delete_one($id)
    {
        InviteShare::edit(['status'=>-1],$id,'id');
        return JsonService::successful('删除成功');
    }

    /**
     * 启用禁用海报
     * @param $id
     * @param $status
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function change_status_one($id,$status=1)
    {
        InviteShare::edit(['status'=>$status],$id,'id');
        return JsonService::successful('设置成功');
    }
}