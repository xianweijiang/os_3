<?php

namespace app\admin\controller\rank;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Db;
use think\Request;
use think\Cache;
use think\Url;
use service\FormBuilder as Form;
use app\admin\model\rank\Rank as RankModel;

/**
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class Rank extends AuthController
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
    public function rank_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(RankModel::rankList($where));
    }

    public function edit_rank($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $rank= RankModel::get($id);
        if(!$rank) return Json::fail('数据不存在!');
        $field = [
            Form::input('title_one','榜单名',$rank->getData('title_one')),
            Form::frameImageOne('image','背景图',Url::build('admin/widget.images/index',array('fodder'=>'image')),$rank->getData('image'))->icon('image')->width('100%')->height('500px'),
            Form::select('frequency','更新时间',(string)$rank->getData('frequency'))->setOptions(function(){
                $menus=[['value'=>1,'label'=>'1小时'],['value'=>24,'label'=>'24小时'],['value'=>72,'label'=>'72小时'],
                    ['value'=>168,'label'=>'7天'],['value'=>360,'label'=>'15天'],['value'=>720,'label'=>'30天']];
                return $menus;
            }),
            Form::number('sort','排序',$rank->getData('sort'))->col(8),
            Form::radio('status','是否显示',$rank->getData('status'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]]),
        ];
        $form = Form::make_post_form('编辑榜单',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function edit_rank_thread($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $rank= RankModel::get($id);
        if(!$rank) return Json::fail('数据不存在!');
        $field = [
            Form::input('title_one','一级榜单',$rank->getData('title_one')),
            Form::input('title_two','二级榜单',$rank->getData('title_two')),
            Form::frameImageOne('image','背景图',Url::build('admin/widget.images/index',array('fodder'=>'image')),$rank->getData('image'))->icon('image')->width('100%')->height('500px'),
            Form::number('sort','排序',$rank->getData('sort'))->col(8),
            Form::radio('status','是否显示',$rank->getData('status'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]]),
        ];
        $form = Form::make_post_form('编辑榜单',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function edit_rank_user($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $rank= RankModel::get($id);
        if(!$rank) return Json::fail('数据不存在!');
        $field = [
            Form::input('title_one','榜单名',$rank->getData('title_one')),
            Form::frameImageOne('image','背景图',Url::build('admin/widget.images/index',array('fodder'=>'image')),$rank->getData('image'))->icon('image')->width('100%')->height('500px'),
            Form::number('sort','排序',$rank->getData('sort'))->col(8),
            Form::radio('status','是否显示',$rank->getData('status'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]]),
        ];
        $form = Form::make_post_form('编辑榜单',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'title_one',
            'image',
            ['frequency',24],
            ['title_two',''],
            'sort',
            'status'
        ],$request);
        if(!$data['title_one']) return Json::fail('请输入榜单名称');
        RankModel::where('id',$id)->update($data);
        Cache::rm('rank_list');
        return Json::successful('成功');
    }

}
