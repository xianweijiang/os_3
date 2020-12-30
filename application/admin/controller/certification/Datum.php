<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:21:23
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-17 14:52:34
 */

namespace app\admin\controller\certification;
use service\FormBuilder as Form;
use traits\CurdControllerTrait;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use think\Url;
use service\JsonService;
use app\admin\model\certification\CertificationType as typeModel;
use app\admin\model\certification\CertificationDatum as DatumModel;
use app\admin\model\certification\CertificationCateDatum as CateDatumModel;
use app\admin\controller\AuthController;
use think\Db;

/**
 * 资料项管理控制器
 * Class Datum
 * @package app\admin\controller\certification
 */
class Datum extends AuthController
{
    use CurdControllerTrait;

    /**
     * 显示资料项列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $params = Util::getMore([
            ['status',''],
            ['keyword',''],
        ],$this->request);
        //$this->assign(DatumModel::getAdminPage($params,0));
        $addurl = Url::build('create');
        $this->assign(compact('params','addurl'));
        return $this->fetch();
    }

    /**
     * 异步资料项列表
     *
     * @return json
     */
    public function list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['status',''],
            ['keyword',''],
        ]);
        return JsonService::successlayui(DatumModel::getAdminPage($where,1));
    }

    /**
     * 显示创建资料项单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $cid=osx_input('cid',0,'intval');
        $FiledTypeList=getCertificationDatumFiledTypeList();
        $field = [
            Form::input('name','名称')->required('名称必填'),
            Form::input('field','标识')->required('标识（字段名）必填')->placeholder('标识必须唯一'),
            Form::input('input_tips','备注说明'),
            Form::select('type_id','认证类型',$cid)->setOptions(function(){
                $list = typeModel::all()->toArray();
                $types=array();
                foreach ($list as $type){
                    $types[] = ['value'=>$type['id'],'label'=>$type['name']];
                }
                return $types;
            })->filterable(1)->multiple(1),
            Form::select('form_type','字段类型 样式')->options($FiledTypeList),
            Form::textarea('setting','参数')->placeholder('针对多选框、下拉选项等样式的选择设置，一行为一个选择'),
            Form::radio('status','状态',1)->options([['value'=>0,'label'=>'关闭'],['value'=>1,'label'=>'开启']]),
            Form::number('sort','排序',0)
        ];
        $form = Form::make_post_form('添加资料项',$field,Url::build('save'),3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资料项
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'name',
            'field',
            ['input_tips',''],
            ['type_id',[]],
            'form_type',
            'setting',
            ['status',1],
            ['sort',0],
            ['create_time',time()],
            ],$request);
        if(!$data['name']) return Json::fail('请输入名称');
        //校验
        $fields = Db::getTableInfo('osx_certification_entity', 'fields');
        foreach ($fields as $key => $value) {
            if ($data['field']==$value) {
                return Json::fail('系统不允许该字段名，请更换！');
            }
        }
        DatumModel::set($data);
        return Json::successful('添加资料项成功!');
    }

    /**
     * 显示编辑资料项表单页.
     * @return \think\Response
     */
    public function edit()
    {
        $id=osx_input('id',0,'intval');
        $data = DatumModel::get($id);
        if(!$data) return Json::fail('数据不存在!');
        $FiledTypeList=getCertificationDatumFiledTypeList();
        $field = [
            Form::input('name','名称',$data['name']),
            Form::input('field','标识',$data['field'])->readonly(true),
            Form::input('input_tips','备注说明',$data['input_tips']),
            Form::select('type_id','认证类型',explode(',',$data->type_id))->setOptions(function (){
                $list = typeModel::all()->toArray();
                $types=array();
                foreach ($list as $type){
                    $types[] = ['value'=>$type['id'],'label'=>$type['name']];
                }
                return $types;
            })->filterable(1)->multiple(1),
            Form::select('form_type','字段类型 样式',$data->form_type)->options($FiledTypeList),
            Form::textarea('setting','参数',$data['setting'])->placeholder('针对多选框、下拉选项等样式的选择设置(多选建议不要超过8个选项)，一行为一个选择'),
            Form::radio('status','状态',$data['status'])->options([['value'=>0,'label'=>'关闭'],['value'=>1,'label'=>'开启']]),
            Form::number('sort','排序',$data['sort']),
        ];
        $form = Form::make_post_form('修改资料项',$field,Url::build('update',array('id'=>$id)),3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存更新的资料项
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update(Request $request)
    {
        $id=osx_input('id',0,'intval');
        $data = Util::postMore([
            'name',
            'field',
            ['input_tips',''],
            ['type_id',[]],
            'form_type',
            'setting',
            ['status',1],
            ['sort',0],
            ['update_time',time()],
            ],$request);
        if(!$data['name']) return Json::fail('请输入名称');
        //校验
        $fields = Db::getTableInfo('osx_certification_entity', 'fields');
        foreach ($fields as $key => $value) {
            if ($data['field']==$value) {
                return Json::fail('系统不允许该字段名，请更换！');
            }
        }
        if(!DatumModel::get($id)) return Json::fail('编辑的记录不存在!');
        $res=DatumModel::editDataAddCheckType($data,$id);
        if($res){
            return Json::successful('修改成功!');
        }else{
            return Json::fail('修改失败!');
        }

    }

    /**
     * 删除指定资料项
     * @return \think\Response
     */
    public function delete()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('参数错误，请重新打开');
        $data=DatumModel::find($id);
        if ($data['built_in']) {
            return $this->failed('内置不能删除！');
        }
        $res = DatumModel::delData($id);
        //删除关联表信息
        $res1 = CateDatumModel::where('datum_id',$id)->delete();
        if(!$res)
            return Json::fail(DatumModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }
    /**
     * 状态切换
     *
     * @return json
     */
    public function set_status(){
        $id=osx_input('id',0,'intval');
        $status=osx_input('status','');
        ($status=='' || $id=='') && JsonService::fail('缺少参数');
        $res=DatumModel::where(['id'=>$id])->update(['status'=>(int)$status]);
        if($res){
            return JsonService::successful($status==1 ? '启用成功':'关闭成功');
        }else{
            return JsonService::fail($status==1 ? '启用失败':'关闭失败');
        }
    }

}
