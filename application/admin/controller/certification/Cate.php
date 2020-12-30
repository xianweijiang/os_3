<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:21:23
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2020-01-09 09:21:48
 */

namespace app\admin\controller\certification;
use service\FormBuilder as Form;
use traits\CurdControllerTrait;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use think\Url;
use app\admin\model\certification\CertificationDatum as DatumModel;
use app\admin\model\certification\CertificationCateDatum as CateDatumModel;
use app\admin\model\certification\CertificationPrivilege as PrivilegeModel;
use app\admin\model\certification\CertificationCatePrivilege as CatePrivilegeModel;
use app\admin\model\certification\CertificationCondition as ConditionModel;
use app\admin\model\certification\CertificationCateCondition as CateConditionModel;
use app\admin\model\certification\CertificationType as TypeModel;
use app\admin\model\certification\CertificationCate as CateModel;
use app\admin\controller\AuthController;
use think\Validate;

/**
 * 认证类别控制器
 * Class Cate
 * @package app\admin\controller\certification
 */
class Cate extends AuthController
{
    use CurdControllerTrait;

    /**
     * 显示认证类别列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $params = Util::getMore([
            ['status',''],
            ['keyword',''],
        ],$this->request);
        $this->assign(CateModel::getAdminPage($params));
        $addurl = Url::build('create');
        $this->assign(compact('params','addurl'));
        return $this->fetch();
    }

    /**
     * 显示创建认证类别单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $cid=osx_input('cid',0,'intval');
        $field = [
            Form::input('name','认证名称')->required('认证名称必填'),
            Form::input('table_name','对应英文表名')->required('英文，唯一')->placeholder('只能最多二十个英文字母'),
            Form::input('desc','描述')->required('描述必填'),
            Form::select('type_id','认证类型',$cid)->setOptions(function(){
                $list = typeModel::all()->toArray();
                $types=array();
                foreach ($list as $type){
                    $types[] = ['value'=>$type['id'],'label'=>$type['name']];
                }
                return $types;
            })->filterable(1),
            Form::frameImageOne('icon','认证图标（60*60,用户认证后头像处显示）',Url::build('admin/widget.images/index',array('fodder'=>'icon','big'=>1)))->icon('image'),
            Form::frameImageOne('image','类别图标（256*256，用于认证类别选择页面）',Url::build('admin/widget.images/index',array('fodder'=>'image','big'=>1)))->icon('image'),
            Form::radio('status','状态',1)->options([['value'=>0,'label'=>'关闭'],['value'=>1,'label'=>'开启']]),
            Form::number('sort','排序',0)
        ];
        $form = Form::make_post_form('添加认证类别',$field,Url::build('save'),3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的认证类别
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'name',
            'table_name',
            'desc',
            'type_id',
            'image',
            'icon',
            ['status',1],
            ['sort',0],
            ['create_time',time()],
            ],$request);
        if(!$data['name']) return Json::fail('请输入认证名称');
        if(!$data['table_name']) return Json::fail('请输入英文表名');
        //
        $rule = [
            'table_name'  => 'require|alphalowercase',
        ];
        $msg = [
            'table_name.require' => '请输入英文表名',
            'table_name.alphalowercase'   => '请输入小写英文表名',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check($data)) {
            return Json::fail($validate->getError());
        }
        if(mb_strlen($data['table_name'],'utf-8')>20){
            return  Json::fail('对应英文表最多二十个英文字母');
        }
        if(CateModel::where('table_name',$data['table_name'])->count()) return Json::fail('该表名已存在');

        $res=CateModel::addCateAddCreateTable($data);
        if($res){
            return Json::successful('添加认证类别成功!');
        }else{
            return Json::fail('添加认证类别失败!');
        }

    }

    /**
     * 显示编辑认证类别表单页.
     * @return \think\Response
     */
    public function edit()
    {
        $id=osx_input('id',0,'intval');
        $data = CateModel::get($id);
        if(!$data) return Json::fail('数据不存在!');
        $field = [
            Form::input('name','认证名称',$data['name']),
            Form::input('table_name','对应英文表名',$data['table_name'])->required('英文，唯一')->placeholder('只能最多二十个英文字母')->readonly('true'),
            Form::input('desc','描述',$data['desc']),
            Form::select('type_id','认证类型',(string)$data->type_id)->setOptions(function (){
                $list = typeModel::all()->toArray();
                $types=array();
                foreach ($list as $type){
                    $types[] = ['value'=>$type['id'],'label'=>$type['name']];
                }
                return $types;
            })->filterable(1),
            Form::frameImageOne('icon','认证图标（60*60,用户认证后头像处显示）',Url::build('admin/widget.images/index',array('fodder'=>'icon')),$data['icon'])->icon('image'),
            Form::frameImageOne('image','类别图标（256*256，用于认证类别选择页面）',Url::build('admin/widget.images/index',array('fodder'=>'image')),$data['image'])->icon('image'),
            Form::radio('status','状态',$data['status'])->options([['value'=>0,'label'=>'关闭'],['value'=>1,'label'=>'开启']]),
            Form::number('sort','排序',$data['sort'])
        ];
        $form = Form::make_post_form('修改认证类别',$field,Url::build('update',array('id'=>$id)),3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存更新的认证类别
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update(Request $request)
    {
        $id=osx_input('id',0,'intval');
        $data = Util::postMore([
            'name',
            'table_name',
            'desc',
            'type_id',
            'image',
            'icon',
            ['status',1],
            ['sort',0],
            ['update_time',time()],
            ],$request);
        if(!$data['name']) return Json::fail('请输入认证名称');
        if(!$data['table_name']) return Json::fail('请输入英文表名');
        //
        $rule = [
            'table_name'  => 'require|alphalowercase',
        ];
        $msg = [
            'table_name.require' => '请输入英文表名',
            'table_name.alphalowercase'   => '请输入小写英文表名',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check($data)) {
            return Json::fail($validate->getError());
        }
        $cate=CateModel::get($id);
        if(!$cate) return Json::fail('编辑的记录不存在!');
        if(CateModel::where('table_name',$data['table_name'])->where('id','neq',$id)->count()) return Json::fail('该表名已存在');
        CateModel::edit($data,$id);

        //如果有认证图标改动，同步用户认证图标
        if ($cate['icon']!= $data['icon'] && $cate['icon']) {
            //更新认证图标
            $user['icon'] = $data['icon'];
            $res = db('user')->where('cate_id',$cate['id'])->update($user);
        }
       
        return Json::successful('修改成功!');
    }

    /**
     * 删除指定认证类别
     * @return \think\Response
     */
    public function delete()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('参数错误，请重新打开');
        $res = CateModel::delData($id);
        if(!$res)
            return Json::fail(CateModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }


    /**
     * 显示编辑资料项单页.
     * @return \think\Response
     */
    public function datum()
    {
        $id=osx_input('id',0,'intval');
        $data = CateModel::get($id);
        if(!$data) return Json::fail('数据不存在!');
        $where['type_id'] = array('like','%'.$data['type_id'].'%');
        $datums=DatumModel::getList($where);
        $cate_datums=$data->catedatums->toArray();
        foreach ($datums as $key => $value) {
            $value['datum']=[0];
            foreach ($cate_datums as $k => $val) {
                if ($val['datum_id']==$value['id']) {
                    $value['datum']=[$val['datum_id']];
                }
            }
            $field[]=Form::checkbox($value['field'],$value['name'],$value['datum'])->options([['value'=>$value['id'],'label'=>'']]);
        }
        
        $form = Form::make_post_form('修改资料项',$field,Url::build('update_datum',array('id'=>$id)),3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存更新的资料项
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update_datum(Request $request)
    {
        $id=osx_input('id',0,'intval');
        $cate = CateModel::get($id);
        if(!$cate) return Json::fail('编辑的记录不存在!');
        $where['type_id'] = array('like','%'.$cate['type_id'].'%');
        $datums=DatumModel::getList($where);
        $post=[];
        foreach ($datums as $key => $value) {
            $post[$key]=[$value['field'],[0]];
        }
        $data = Util::postMore($post,$request);//提交的数据
        $res=CateModel::updateDatumAndChangeTableColumn($data,$id,$datums);
        if($res){
            return Json::successful('成功!');
        }else{
            return Json::fail('失败!');
        }
    }


    /**
     * 显示编辑认证特权单页.
     * @return \think\Response
     */
    public function privilege()
    {
        $id=osx_input('id',0,'intval');
        $data = CateModel::get($id);
        if(!$data) return Json::fail('数据不存在!');
        $privileges=PrivilegeModel::getList([]);
        $cate_privileges=$data->cateprivileges->toArray();
        foreach ($privileges as $key => $value) {
            $value['privilege']=[0];
            foreach ($cate_privileges as $k => $val) {
                if ($val['privilege_id']==$value['id']) {
                    $value['privilege']=[$val['privilege_id']];
                }
            }
            $field[]=Form::checkbox($value['name'],$value['name'],$value['privilege'])->options([['value'=>$value['id'],'label'=>'']]);
        }
        $form = Form::make_post_form('修改认证特权',$field,Url::build('update_privilege',array('id'=>$id)),3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存更新的认证特权
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update_privilege(Request $request)
    {
        $id=osx_input('id',0,'intval');
        $cate = CateModel::get($id);
        //dump($id);dump($cate);exit;
        if(!$cate) return Json::fail('编辑的记录不存在!');
        $privileges=PrivilegeModel::getList([]);
        $post=[];
        foreach ($privileges as $key => $value) {
            $post[$key]=[$value['name'],[0]];
        }
        $data = Util::postMore($post,$request);//提交的数据
        $exist_data=$cate->cateprivileges->toArray();//已经存在的关联数据
        $save_data=['cate_id'=>$id];//
        $del_where=['cate_id'=>$id];//
        foreach ($data as $key => $value) {
            $value=$value[0];
            if ($value) {
                $update_where=[];
                $save_data['create_time']=time();
                $save_data['privilege_id']=$value;
                foreach ($exist_data as $k => $val) {
                    if ($value==$val['privilege_id']) {
                        //更新
                        $update_where['id']=$val['id'];
                        unset($save_data['create_time']);
                        $save_data['update_time']=time();
                        $save_data['privilege_id']=$value;
                        break;
                    }
                }
                if ($update_where) {
                    CatePrivilegeModel::where($update_where)->update($save_data);
                }else{
                    if (isset($save_data['privilege_id'])) {
                        CatePrivilegeModel::set($save_data);
                    }
                }


            }else{
                foreach ($privileges as $ke => $v) {
                    if ($key==$v['name']) {
                        $del_where['privilege_id']=$v['id'];
                    }
                }
                CatePrivilegeModel::where($del_where)->delete();
            }
        }
        self::saveUserRed();
        return Json::successful('成功!');
    }

    /**
     * 红色字的保存
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.13
     */
    public function saveUserRed(){
        $cate_id=CatePrivilegeModel::where(['privilege_id'=>5])->column('cate_id');
        $u_id=db('certification_entity')->where(['cate_id'=>['in',$cate_id],'status'=>1])->column('uid');
        db('user')->where(['status'=>1])->update(['is_red'=>0]);
        $data['is_red']= 1;
        db('user')->where(['uid'=>['in',$u_id]])->update($data);
    }

    /**
     * 显示编辑认证条件单页.
     * @return \think\Response
     */
    public function condition()
    {
        $id=osx_input('id',0,'intval');
        $data = CateModel::get($id);
        if(!$data) return Json::fail('数据不存在!');
        $conditions=ConditionModel::getList([]);
        $cate_conditions=$data->cateconditions->toArray();
        foreach ($conditions as $key => $value) {
            $value['condition']=[0];
            $value['condition_value']=0;
            foreach ($cate_conditions as $k => $val) {
                if ($val['condition_id']==$value['id']) {
                    $value['condition']=[$val['condition_id']];
                    $value['condition_value']=$val['condition_value'];
                }
            }
            $field[]=Form::checkbox($value['name'],$value['desc'],$value['condition'])->options([['value'=>$value['id'],'label'=>'']]);
            if(strpos($value['desc'],'≥') !== false){ 
                $field[]=Form::number($value['name'].'_value','数值',$value['condition_value']);
            }
        }
        
        $form = Form::make_post_form('修改认证条件',$field,Url::build('update_condition',array('id'=>$id)),3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存更新的认证特权
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update_condition(Request $request)
    {
        $id=osx_input('id',0,'intval');
        $id=$id?$id:input('id');
        $cate = CateModel::get($id);
        if(!$cate) return Json::fail('编辑的记录不存在!');
        $conditions=ConditionModel::getList([]);
        $post=[];
        foreach ($conditions as $key => $value) {
            $post[]=[$value['name'],[0]];
        }
        $data = Util::postMore($post,$request);//提交的数据
        $exist_data=$cate->cateconditions->toArray();//已经存在的关联数据
        $save_data=['cate_id'=>$id];//
        $del_where=['cate_id'=>$id];//
        foreach ($data as $key => $value) {
            $value=$value[0];
            if ($value) {
                $update_where=[];
                $save_data['create_time']=time();
                $save_data['condition_id']=$value;
                $save_data['condition_value']=input($key.'_value',0,'intval');
                foreach ($exist_data as $k => $val) {
                    if ($value==$val['condition_id']) {
                        //更新
                        $update_where['id']=$val['id'];
                        unset($save_data['create_time']);
                        $save_data['update_time']=time();
                        $save_data['condition_id']=$value;
                        break;
                    }
                }
                if ($update_where) {
                    CateConditionModel::where($update_where)->update($save_data);
                }else{
                    if (isset($save_data['condition_id'])) {
                        CateConditionModel::set($save_data);
                    }
                }
            }else{
                foreach ($conditions as $ke => $v) {
                    if ($key==$v['name']) {
                        $del_where['condition_id']=$v['id'];
                    }
                }
                CateConditionModel::where($del_where)->delete();
            }
        }
        return Json::successful('成功!');
    }

}
