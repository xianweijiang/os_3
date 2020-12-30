<?php
namespace app\admin\controller\com;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Cache;
use think\Request;
use app\admin\model\com\ComThreadClass as ThreadClassModel;
use app\admin\model\com\ComForum as ForumModel;
use think\Url;
use app\admin\model\system\SystemAttachment;
use app\admin\model\user\User as UserModel;

/**
 * 版块控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class ComThreadClass extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $status=osx_input('status',1,'intval');
        $this->assign('fid',$this->request->get('fid',''));
        $this->assign('cate',ForumModel::getCatTierList());
        $this->assign('status', $status);
        return $this->fetch();
    }
    /**
     * 异步查找版块
     *
     * @return json
     */
    public function class_list(){
        $where = Util::getMore([
            ['status', $this->request->param('status','')],
            ['fid',$this->request->param('fid','')],
            ['name',''],
            ['page',1],
            ['limit',20],
            ['order','']
        ]);
        // trace($where);
        if($where['status']==1){
            $where['status']='';
        }
        $this->request->param('fid')!=''?$where['fid']=$this->request->param('fid'):1;
        return JsonService::successlayui(ThreadClassModel::ClassList($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $field = [
            Form::select('fid','所属版块')->setOptions(function(){
                $list = ForumModel::getCatTierList();
                $menus=[];
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['name'],'disabled'=>$menu['pid']== 0];//];
                }
                return $menus;
            })->filterable(1),
            Form::input('name','主题分类名称')->col(Form::col(24)),
            Form::input('summary','分类描述')->type('textarea'),
           /* Form::frameImageOne('icon','分类图片(305*305px)',Url::build('admin/widget.images/index',array('fodder'=>'icon')))->icon('image')->width('100%')->height('500px'),*/
            //Form::Switches('moderators','是否仅管理员可用',0)->openStr('是')->closeStr('否')->size('default'),
            Form::radio('status','状态',1)->options([['label'=>'启用','value'=>1],['label'=>'禁用','value'=>0]]),
            Form::number('sort','排序')->col(8),
        ];
        $form = Form::make_post_form('创建版块分类',$field,Url::build('save'),2);
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
            ['fid',''],
            'name',
            'summary',
            /*['icon', ''],*/
           /* 'moderators',*/
            'sort',
            'status'
        ],$request);
        if(!$data['fid']) return Json::fail('必须关联一个版块');
        if(!$data['name']) return Json::fail('请输入版块名称');
        ThreadClassModel::set($data);
        Cache::rm('forum_other_info_fid_'.$data['fid']);
        return Json::successful('创建成功!');
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
            'fid',
            'name',
            'summary',
            /*['icon', ''],*/
            'sort',
           /* 'moderators',*/
            'status'
        ],$request);
        if(!$id) return Json::fail('数据不存在');
        if(!$data['fid']) return Json::fail('必须关联一个版块');
        if(!$data['name']) return Json::fail('请输入主题分类名称');
        //缓存处理
        $old_fid=ThreadClassModel::where('id',$id)->value('fid');
        if($old_fid!=$data['fid']){
            Cache::rm('forum_other_info_fid_'.$old_fid);
            Cache::rm('forum_other_info_fid_'.$data['fid']);
        }
        //缓存处理 end
        ThreadClassModel::edit($data,$id);
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
        $class = ThreadClassModel::get($id);
        if(!$class) return Json::fail('数据不存在!');

        $list = ForumModel::getCatTierList();
        $menus=[];;
        foreach ($list as $menu){
            $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['name'],'disabled'=>$menu['id']== $id];
        }
        $field = [
           Form::select('fid','所属版块',(string)$class->getData('fid'))->setOptions($menus)->filterable(1),
            Form::input('name','主题分类名称',$class->getData('name')),
            Form::input('summary','分类描述',$class->getData('summary'))->type('textarea'),
            // Form::frameImageOne('icon','分类图片(305*305px)',Url::build('admin/widget.images/index',array('fodder'=>'icon')),$class->getData('icon'))->icon('image')->width('100%')->height('500px'),
            //Form::Switches('moderators','是否仅管理员可用',$class->getData('moderators'))->openStr('是')->closeStr('否')->size('default'),
            Form::radio('status','状态',$class->getData('status'))->options([['label'=>'正式','value'=>1],['label'=>'禁用','value'=>0]]),
            Form::number('sort','排序',$class->getData('sort'))->col(8),

        ];
        $form = Form::make_post_form('编辑主题分类',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 设置禁用启用某个分类
     *
     * @return json
     */
    public function set_status(){
        $post=Util::getMore([
            ['status',0],
            ['id',0]
        ]);
        $status=$post['status'];
        $id=$post['id'];
        ($status=='' || $id=='') && JsonService::fail('缺少参数');
        $res=ThreadClassModel::where(['id'=>$id])->update(['status'=>(int)($status)]);
        if($res){
            //缓存处理
            $old_fid=ThreadClassModel::where(['id'=>$id])->value('fid');
            Cache::rm('forum_other_info_fid_'.$old_fid);
            //缓存处理 end
            return JsonService::successful($status==1 ? '启用成功':'禁用成功');
        }else{
            return JsonService::fail($status==1 ? '启用失败':'删除失败');
        }
    }

    /**
     * 批量还原
     *
     * @return json
     */
    public function restore(){
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要还原的版块');
        }else{
            $res=ThreadClassModel::where('id','in',$post['ids'])->update(['status'=>1, 'update_time'=>date('Y-m-d H:i:s')]);
            if($res)
                return JsonService::successful('还原成功');
            else
                return JsonService::fail('还原失败');
        }
    }

    /**
     * 设置审核单个版块
     *
     * @return json
     */
    public function set_moderators(){
        $id=osx_input('id',0,'intval');
        $moderators=osx_input('moderators',0,'intval');
        ($moderators=='' || $id=='') && JsonService::fail('缺少参数');
        $res=ThreadClassModel::where(['id'=>$id])->update(['moderators'=>(int)($moderators)]);
        if($res){
            return JsonService::successful($moderators==1 ? '设为管理员可发布成功':'设为任何人均可发布成功');
        }else{
            return JsonService::fail($moderators==1 ? '设为仅管理员可发布失败':'设为任何人均可发布失败');
        }
    }


    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_class(){
        $field=osx_input('field','','text');
        $id=osx_input('id',0,'intval');
        $value=osx_input('value','','text');
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(ThreadClassModel::where(['id'=>$id])->update([$field=>$value])){
            //缓存处理
            $old_fid=ThreadClassModel::where(['id'=>$id])->value('fid');
            Cache::rm('forum_other_info_fid_'.$old_fid);
            //缓存处理 end
            return JsonService::successful('保存成功');
        }else{
            return JsonService::fail('保存失败');
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete()
    {
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要清理的版块');
        }else{
            $old_fids=ThreadClassModel::where('id','in',$post['ids'])->column('fid');
            $res=ThreadClassModel::where('id','in',$post['ids'])->delete();
            if($res !== false){
                //缓存处理
                foreach ($old_fids as $old_fid){
                    Cache::rm('forum_other_info_fid_'.$old_fid);
                }
                unset($old_fids,$old_fid);
                //缓存处理 end
                return JsonService::successful('清理成功');
            }else{
                return JsonService::fail('清理失败');
            }
        }
    }


    /**添加版主
     */
    public function set_admin(){
        $id=osx_input('id',0,'intval');
        $d=ForumModel::get($id);
        if(!$d){

        }else{
            $admin_uid=$d['admin_uid'];
            $admins=explode(',',$admin_uid);
            $admins_user=array();
            foreach ($admins as $v){
                if($v)
                $admins_user[]=array('uid'=>$v,'user'=>UserModel::getUserInfos($v));
            }
           // dump($admins_user);exit;
            $this->assign(compact('d','admins_user'));
        }

        return $this->fetch();
    }

    public function get_users(){
        $nickname=osx_input('nickname','','text');
        $users=UserModel::where('nickname|uid','like',"%$nickname%")->select()->toArray();
        $data=array();
        if($users){

            foreach ($users as $v){
                if($v){
                    $data[]=array('value'=>$v['uid'],'name'=>$v['nickname']);
                }
            }
        }

        return Json::successlayui(count($users),$data,'获取成功');

    }


    public function save_admin_uids(){
        $fid=osx_input('fid','','text');
        $uids=osx_input('uids','','text');
        $fid=='' && JsonService::fail('缺少参数');
        if(ForumModel::where(['id'=>$fid])->update(['admin_uid'=>$uids])!==false)
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }
}
