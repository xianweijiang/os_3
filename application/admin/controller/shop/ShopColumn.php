<?php

namespace app\admin\controller\shop;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use app\admin\model\shop\ShopColumn as ColumnModel;
use think\Request;
use think\Url;

use app\admin\model\system\SystemAttachment;


/**
 * 商品管理
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class ShopColumn extends AuthController
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
    public function set_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['status',1],
        ]);
        return JsonService::successlayui(ColumnModel::SetList($where));
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
            'type',
            'sort',
            'name',
        ],$request);
        ColumnModel::editSet($data,$id);
        return Json::successful('成功');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $column = ColumnModel::getOne($id);
        if(!$column) return Json::fail('数据不存在!');
        $field = [
            Form::select('type','栏目属性', (string)$column['type'])->options([
                ['value'=>1,'label'=>'商品列表'],
            ])->required('栏目属性必选'),
            Form::number('sort','排序',$column['sort'])->col(8),
            Form::input('name','栏目名称',$column['name'])->col(Form::col(24)),
        ];
        $form = Form::make_post_form('编辑栏目',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function set_on($is_on='',$id=''){
        ($is_on=='' || $id=='') && JsonService::fail('缺少参数');
        $res=ColumnModel::where(['id'=>$id])->update(['status'=>(int)$is_on]);
        if($res){
            return JsonService::successful($is_on==1 ? '启用成功':'禁用成功');
        }else{
            return JsonService::fail($is_on==1 ? '启用失败':'禁用失败');
        }
    }

    public function set_column($field='',$id='',$value=''){
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(ColumnModel::where(['id'=>$id])->update([$field=>$value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }

}
