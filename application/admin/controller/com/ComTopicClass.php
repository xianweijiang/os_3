<?php
namespace app\admin\controller\com;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Cache;
use think\Request;
use app\admin\model\com\ComTopicClass as TopicClassModel;
use app\admin\model\com\ComTopic;
use think\Url;
use app\admin\model\user\User as UserModel;
use app\admin\model\system\SystemAdmin;

/**
 * 版块控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class ComTopicClass extends AuthController
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
    public function class_list(){
        $where = Util::getMore([
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(TopicClassModel::ClassList($where));
    }

    public function set_admin()
    {
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $field = [
            Form::input('name','分类名称'),
            Form::number('sort','排序')->col(8),
            Form::radio('status','状态',1)->options([
                ['value'=>1,'label'=>'开启'],
                ['value'=>0,'label'=>'禁用'],
            ])->col(Form::col(24)),
        ];
        $form = Form::make_post_form('添加分类',$field, Url::build('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'name',
            'sort',
            'status',
        ],$request);
        if(!$data['name']) return Json::fail('请输入话题分类');
        TopicClassModel::set($data);
        Cache::rm('topic_class_list');
        return Json::successful('添加成功!');
    }


    /**
     * @return mixed|\think\response\Json|void
     */
    public function edit()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $class = TopicClassModel::get($id);
        if(!$class) return Json::fail('数据不存在!');
        $field = [
            Form::input('name','分类名称',$class->getData('name')),
            Form::number('sort','排序',$class->getData('sort'))->col(8),
            Form::radio('status','状态',$class->getData('status'))->options([
                ['value'=>1,'label'=>'开启'],
                ['value'=>0,'label'=>'禁用'],
            ])->col(Form::col(24)),
        ];
        $form = Form::make_post_form('编辑版块',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /** 修改订单提交更新
     * @param Request $request
     */
    public function update(Request $request)
    {
        $id=osx_input('id',0,'intval');
        $data = Util::postMore([
            'name',
            'sort',
            'status',
        ],$request);
        if(!$data['name']) return Json::fail('请输入话题分类');
        TopicClassModel::edit($data, $id);
        Cache::rm('topic_class_list');
        return Json::successful('修改成功!');
    }

    /**
     * 删除
     *
     * @return json
     */
    public function del(){
        $id=osx_input('id',0,'intval');
        if($id==''){
            JsonService::fail('缺少参数');
        }
        $topic=ComTopic::where('class_id',$id)->where('status',1)->value('id');
        if($topic){
            return JsonService::fail('请先删除该分类下的话题');
        }
        $res=TopicClassModel::where('id',$id)->delete();
        Cache::rm('topic_class_list');
        if($res!==false){
            return JsonService::successful('删除成功');
        }else{
            return JsonService::fail('删除失败');
        }
    }


}
