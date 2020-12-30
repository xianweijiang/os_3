<?php
namespace app\admin\controller\com;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use app\admin\model\com\ComAdv as AdvModel;
use think\Url;
use app\admin\model\system\SystemAttachment;
use app\admin\model\user\User as UserModel;
use think\Cache;

/**
 * 版块控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class ComAdv extends AuthController
{
	public function index(){
        $type=osx_input('type',1,'intval');
        $status=osx_input('status','','text');
		$this->assign('type', $type);
		$this->assign('status', $status);
		return $this->fetch();
	}

	public function adv_list(){
        $type=osx_input('type',1,'intval');
		$where = Util::getMore([
            ['status',''],
            ['name', ''],
            ['type', $type],
            ['order','status desc,sort asc'],
            ['page',1],
            ['limit',20],
            ['excel',0]
        ]);
        return JsonService::successlayui(AdvModel::AdvList($where));
	}

	/**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $type=osx_input('type',1,'intval');
        if(in_array($type,array('1','3','6','10','12'))){
            $field = [
                Form::input('name','标题')->col(Form::col(24)),
//                Form::select('jump_page','跳转页面')->setOptions(function(){
//                    $menus=[['value'=>1,'label'=>'帖子详情'],['value'=>2,'label'=>'商品详情'],['value'=>3,'label'=>'公告详情']];
//                    return $menus;
//                })->filterable(1),
//                Form::input('jump_id','跳转页面id'),
                Form::frameInputOne('url','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'url')))->icon('link-select'),
                Form::frameImageOne('pic','图片(700*350)',Url::build('admin/widget.images/index',array('fodder'=>'pic')))->icon('image')->width('100%')->height('500px'),
                Form::number('sort','排序', 1)->col(8),
                Form::radio('status','状态',1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
                Form::hidden('type', $type),
            ];
            $form = Form::make_post_form('创建广告',$field, Url::build('save'),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }elseif($type==7){
            $field = [
                Form::input('name','标题')->col(Form::col(24)),
//                Form::select('jump_page','跳转页面')->setOptions(function(){
//                    $menus=[['value'=>1,'label'=>'帖子详情'],['value'=>2,'label'=>'商品详情'],['value'=>3,'label'=>'公告详情']];
//                    return $menus;
//                })->filterable(1),
//                Form::input('jump_id','跳转页面id'),
                Form::frameInputOne('url','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'url')))->icon('link-select'),
                Form::frameImageOne('pic','图片(700*250)',Url::build('admin/widget.images/index',array('fodder'=>'pic')))->icon('image')->width('100%')->height('500px'),
                Form::number('sort','排序', 1)->col(8),
                Form::radio('status','状态',1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
                Form::hidden('type', $type),
            ];
            $form = Form::make_post_form('创建广告',$field, Url::build('save'),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        } elseif($type==13){
            $field = [
                Form::input('name','标题')->col(Form::col(24)),
//                Form::select('jump_page','跳转页面')->setOptions(function(){
//                    $menus=[['value'=>1,'label'=>'帖子详情'],['value'=>2,'label'=>'商品详情'],['value'=>3,'label'=>'公告详情']];
//                    return $menus;
//                })->filterable(1),
//                Form::input('jump_id','跳转页面id'),
                Form::frameInputOne('url','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'url')))->icon('link-select'),
                Form::frameImageOne('pic','图片(720*280)',Url::build('admin/widget.images/index',array('fodder'=>'pic')))->icon('image')->width('100%')->height('500px'),
                Form::number('sort','排序', 1)->col(8),
                Form::radio('status','状态',1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
                Form::hidden('type', $type),
            ];
            $form = Form::make_post_form('创建广告',$field, Url::build('save'),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }else{
            $field = [
                Form::input('name','标题')->col(Form::col(24)),
//                Form::select('jump_page','跳转页面')->setOptions(function(){
//                    $menus=[['value'=>1,'label'=>'帖子详情'],['value'=>2,'label'=>'商品详情'],['value'=>3,'label'=>'公告详情']];
//                    return $menus;
//                })->filterable(1),
//                Form::input('jump_id','跳转页面id'),
                Form::frameInputOne('url','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'url')))->icon('link-select'),
                Form::frameImageOne('pic','图片(700*200)',Url::build('admin/widget.images/index',array('fodder'=>'pic')))->icon('image')->width('100%')->height('500px'),
                Form::number('sort','排序', 1)->col(8),
                Form::radio('status','状态',1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
                Form::hidden('type', $type),
            ];
            $form = Form::make_post_form('创建广告',$field, Url::build('save'),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }

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
            'pic',
            'sort',
            'status',
            'url',
//            'jump_page',
//            'jump_id'
        ],$request);
        $data['create_time']=time();
        $data['update_time']=time();
        if(!$data['name']) return Json::fail('请输入广告名称');
        AdvModel::set($data);
        Cache::rm('adv'.$data['type']);
        return Json::successful('创建广告成功!');
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
            'pic',
            'sort',
            'status',
            'url',
//            'jump_page',
//            'jump_id'
        ],$request);
        $data['update_time']=time();
        if(!$data['name']) return Json::fail('请输入广告名称');
        AdvModel::edit($data,$id);
        Cache::rm('adv'.$data['type']);
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
        $data = AdvModel::get($id);
        if(!$data) return Json::fail('数据不存在!');
        if(in_array($data['type'],array('1','3','6','10','12'))){
            $field = [
                Form::input('name','标题', $data['name'])->col(Form::col(24)),
//                Form::select('jump_page','跳转页面',(string)$data['jump_page'])->setOptions(function(){
//                    $menus=[['value'=>1,'label'=>'帖子详情'],['value'=>2,'label'=>'商品详情'],['value'=>3,'label'=>'公告详情']];
//                    return $menus;
//                })->filterable(1),
//                Form::input('jump_id','跳转页面id', $data['jump_id']),
                Form::frameInputOne('url','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'url')),$data['url'])->icon('link-select'),
                Form::frameImageOne('pic','图片(700*350)',Url::build('admin/widget.images/index',array('fodder'=>'pic')), $data['pic'])->icon('image')->width('100%')->height('500px'),
                Form::number('sort','排序', $data['sort'])->col(8),
                Form::radio('status','状态', $data['sort'])->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
                Form::hidden('type', $data['type']),
            ];
            $form = Form::make_post_form('编辑广告',$field,Url::build('update',array('id'=>$id)),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }elseif($data['type']==7){
            $field = [
                Form::input('name','标题', $data['name'])->col(Form::col(24)),
//                Form::select('jump_page','跳转页面',(string)$data['jump_page'])->setOptions(function(){
//                    $menus=[['value'=>1,'label'=>'帖子详情'],['value'=>2,'label'=>'商品详情'],['value'=>3,'label'=>'公告详情']];
//                    return $menus;
//                })->filterable(1),
//                Form::input('jump_id','跳转页面id', $data['jump_id']),
                Form::frameInputOne('url','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'url')),$data['url'])->icon('link-select'),
                Form::frameImageOne('pic','图片(700*250)',Url::build('admin/widget.images/index',array('fodder'=>'pic')), $data['pic'])->icon('image')->width('100%')->height('500px'),
                Form::number('sort','排序', $data['sort'])->col(8),
                Form::radio('status','状态', $data['sort'])->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
                Form::hidden('type', $data['type']),
            ];
            $form = Form::make_post_form('编辑广告',$field,Url::build('update',array('id'=>$id)),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }elseif($data['type']==13){
            $field = [
                Form::input('name','标题', $data['name'])->col(Form::col(24)),
//                Form::select('jump_page','跳转页面',(string)$data['jump_page'])->setOptions(function(){
//                    $menus=[['value'=>1,'label'=>'帖子详情'],['value'=>2,'label'=>'商品详情'],['value'=>3,'label'=>'公告详情']];
//                    return $menus;
//                })->filterable(1),
//                Form::input('jump_id','跳转页面id', $data['jump_id']),
                Form::frameInputOne('url','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'url')),$data['url'])->icon('link-select'),
                Form::frameImageOne('pic','图片(720*280)',Url::build('admin/widget.images/index',array('fodder'=>'pic')), $data['pic'])->icon('image')->width('100%')->height('500px'),
                Form::number('sort','排序', $data['sort'])->col(8),
                Form::radio('status','状态', $data['sort'])->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
                Form::hidden('type', $data['type']),
            ];
            $form = Form::make_post_form('编辑广告',$field,Url::build('update',array('id'=>$id)),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }else{
            $field = [
                Form::input('name','标题', $data['name'])->col(Form::col(24)),
//                Form::select('jump_page','跳转页面',(string)$data['jump_page'])->setOptions(function(){
//                    $menus=[['value'=>1,'label'=>'帖子详情'],['value'=>2,'label'=>'商品详情'],['value'=>3,'label'=>'公告详情']];
//                    return $menus;
//                })->filterable(1),
//                Form::input('jump_id','跳转页面id', $data['jump_id']),
                Form::frameInputOne('url','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'url')),$data['url'])->icon('link-select'),
                Form::frameImageOne('pic','图片(700*200)',Url::build('admin/widget.images/index',array('fodder'=>'pic')), $data['pic'])->icon('image')->width('100%')->height('500px'),
                Form::number('sort','排序', $data['sort'])->col(8),
                Form::radio('status','状态', $data['sort'])->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]]),
                Form::hidden('type', $data['type']),
            ];
            $form = Form::make_post_form('编辑广告',$field,Url::build('update',array('id'=>$id)),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }
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
        if(AdvModel::where(['id'=>$id])->update([$field=>$value])){
            $type=AdvModel::where(['id'=>$id])->value('type');
            Cache::rm('adv'.$type);
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
        if(!AdvModel::be(['id'=>$id])) return $this->failed('数据不存在');
        AdvModel::destroy($id);
        $type=AdvModel::where(['id'=>$id])->value('type');
        Cache::rm('adv'.$type);
        return Json::successful('删除成功');
    }
}