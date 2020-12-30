<?php
namespace app\admin\controller\com;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use app\admin\model\com\ComNav as NavModel;
use think\Url;
use think\Cache;
use app\admin\model\system\SystemAttachment;
use app\admin\model\user\User as UserModel;

/**
 * 版块控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class ComNav extends AuthController
{
	public function index(){
        $type=osx_input('type',1,'intval');
        $status=osx_input('status','','text');
		$this->assign('type', $type);
		$this->assign('status', $status);
		return $this->fetch();
	}

	public function nav_list(){
		$where = Util::getMore([
            ['status',''],
            ['name', ''],
            ['type', 1],
            ['order',''],
            ['page',1],
            ['limit',20],
            ['excel',0]
        ]);
        return JsonService::successlayui(NavModel::NavList($where));
	}

	/**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $type=osx_input('type',1,'intval');
        $field = [
            Form::input('name','名称')->col(Form::col(24)),
            /*Form::select('jump_page','跳转页面')->setOptions(function(){
                $menus=[['value'=>1,'label'=>'首页'],['value'=>2,'label'=>'版块列表'],['value'=>3,'label'=>'版块主页'],['value'=>4,'label'=>'商城首页'],
                    ['value'=>5,'label'=>'商品分类'],['value'=>6,'label'=>'限时秒杀'],['value'=>7,'label'=>'超值拼团'],['value'=>8,'label'=>'领券中心'],
                    ['value'=>9,'label'=>'砍价低价拿'],['value'=>10,'label'=>'我的'],['value'=>11,'label'=>'签到'],['value'=>12,'label'=>'任务中心'],
                    ['value'=>13,'label'=>'会员中心'],['value'=>14,'label'=>'我的积分'],['value'=>15,'label'=>'兑换记录'],['value'=>16,'label'=>'如何赚积分']];
                return $menus;
            })->filterable(1),
            Form::input('jump_id','跳转页面id','跳转到【版块主页】需填写对应版块的ID，其他可不填此项'),*/
            Form::frameInputOne('url','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'url')))->icon('link-select'),
            Form::frameImageOne('icon','图标(360*360)',Url::build('admin/widget.images/index',array('fodder'=>'icon')))->icon('image')->width('100%')->height('500px'),
            Form::number('sort','排序')->col(8),
            Form::radio('status','状态',1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
            Form::hidden('type', $type),
        ];
        $form = Form::make_post_form('创建导航',$field, Url::build('save'),2);
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
            'type',
            'icon',
            'sort',
            'status',
//            'jump_page',
//            'jump_id',
            'url',
        ],$request);
        if(!$data['name']) return Json::fail('请输入导航名称');
        if(!$data['url']) return Json::fail('请选择 跳转链接设置');
        NavModel::set($data);
        Cache::rm('nav'.$data['type']);
        return Json::successful('创建导航成功!');
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update(Request $request)
    {
        $id=osx_input('id',0,'intval');
        $data = Util::postMore([
            'name',
            'url',
            'type',
            'icon',
            'sort',
            'status',
//            'jump_page',
//            'jump_id',
            'url',
        ],$request);
        if(!$data['name']) return Json::fail('请输入导航名称');
        if(!$data['url']) return Json::fail('请选择 跳转链接设置');
        NavModel::edit($data,$id);
        Cache::rm('nav'.$data['type']);
        return Json::successful('修改成功!');
    }

   /**
     * 显示编辑资源表单页.
     * @return \think\Response
     */
    public function edit()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $data = NavModel::get($id);
        if(!$data) return Json::fail('数据不存在!');

        $field = [
            Form::input('name','名称', $data['name'])->col(Form::col(24)),
            /*Form::select('jump_page','跳转页面',(string)$data['jump_page'])->setOptions(function(){
                $menus=[['value'=>1,'label'=>'首页'],['value'=>2,'label'=>'版块列表'],['value'=>3,'label'=>'版块主页'],['value'=>4,'label'=>'商城首页'],
                    ['value'=>5,'label'=>'商品分类'],['value'=>6,'label'=>'限时秒杀'],['value'=>7,'label'=>'超值拼团'],['value'=>8,'label'=>'领券中心'],
                    ['value'=>9,'label'=>'砍价低价拿'],['value'=>10,'label'=>'我的'],['value'=>11,'label'=>'签到'],['value'=>12,'label'=>'任务中心'],
                    ['value'=>13,'label'=>'会员中心'],['value'=>14,'label'=>'我的积分'],['value'=>15,'label'=>'兑换记录'],['value'=>16,'label'=>'如何赚积分']];
                return $menus;
            })->filterable(1),
            Form::input('jump_id','跳转页面id(版块主页填写)', $data['jump_id']),*/
            Form::frameInputOne('url','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'url')), $data['url'])->icon('link-select'),
            Form::frameImageOne('icon','图标(360*360)',Url::build('admin/widget.images/index',array('fodder'=>'icon')), $data['icon'])->icon('image')->width('100%')->height('500px'),
            Form::number('sort','排序', $data['sort'])->col(8),
            Form::radio('status','状态', $data['status'])->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
            Form::hidden('type', $data['type']),
        ];
        $form = Form::make_post_form('编辑导航',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 快速编辑
     *
     * @return json
     */
    public function quick_edit(){
        $id=osx_input('id',0,'intval');
        $field=osx_input('field','','text');
        $value=osx_input('value','','text');
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(NavModel::where(['id'=>$id])->update([$field=>$value])){
            $type=NavModel::where(['id'=>$id])->value('type');
            Cache::rm('nav'.$type);
            return JsonService::successful('保存成功');
        }else{
            return JsonService::fail('保存失败');
        }
    }

       /**
     * 删除指定资源
     * @return \think\Response
     */
    public function delete()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        if(!NavModel::be(['id'=>$id])) return $this->failed('数据不存在');
        NavModel::destroy($id);
        $type=NavModel::where(['id'=>$id])->value('type');
        Cache::rm('nav'.$type);
        return Json::successful('删除成功');
    }
}