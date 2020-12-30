<?php
namespace app\admin\controller\user;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Request;
use app\admin\model\user\RenwuNav as NavModel;
use think\Url;
use think\Cache;


/**
 * 版块控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class RenwuNav extends AuthController
{
	public function index($status = 1){
		$this->assign('status', $status);
		return $this->fetch();
	}

	public function nav_list(){
		$where = Util::getMore([
            ['status',1],
            ['order',''],
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(NavModel::NavList($where));
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
            'title',
            'link',
            'image',
            'sort',
            'content',
        ],$request);
        $data['update_time']=time();
        if(!$data['title']) return Json::fail('请输入导航标题');
        if(!$data['content']) return Json::fail('请输入导航描述');
        if(!$data['link']) return Json::fail('请选择跳转链接设置');
        $length_title=mb_strlen($data['title'],'UTF-8');
        if($length_title>5){
            return Json::fail('标题不能超过5个字');
        }
        $length_content=mb_strlen($data['content'],'UTF-8');
        if($length_content>7){
            return Json::fail('描述不能超过7个字');
        }
        NavModel::edit($data,$id);
        Cache::rm('renwu_nav');
        return Json::successful('修改成功!');
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
        $data = NavModel::get($id);
        if(!$data) return Json::fail('数据不存在!');
        $field = [
            Form::input('title','标题', $data['title'])->col(Form::col(24)),
            Form::input('content','描述', $data['content'])->col(Form::col(24)),
            Form::frameInputOne('link','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'link')), $data['link'])->icon('link-select'),
            Form::frameImageOne('image','图片(150*150)',Url::build('admin/widget.images/index',array('fodder'=>'image')), $data['image'])->icon('image')->width('100%')->height('500px'),
            Form::number('sort','排序', $data['sort'])->col(8),
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
    public function quick_edit($field='',$id='',$value=''){
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(NavModel::where(['id'=>$id])->update([$field=>$value])){
            Cache::rm('renwu_nav');
            return JsonService::successful('保存成功');
        }else{
            return JsonService::fail('保存失败');
        }
    }

}