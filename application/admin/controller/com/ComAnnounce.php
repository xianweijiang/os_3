<?php
namespace app\admin\controller\com;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use app\admin\model\com\ComAnnounce as AnnounceModel;
use think\Url;
use app\admin\model\system\SystemAttachment;
use app\admin\model\user\User as UserModel;
use app\admin\model\com\ComThread as ThreadModel;
use app\admin\model\com\ComThreadClass as ThreadClassModel;
use app\admin\model\com\ComForum as ForumModel;

/**
 * 版块控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class ComAnnounce extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $map_common = ['status'=>1];
        $map_band = ['status'=>0];
        $map_recycle = ['status'=>-1];


        $status=$this->request->param('status');

        //已发布
        $common = AnnounceModel::where($map_common)->count();
        //未发布
        $band = AnnounceModel::where($map_band)->count();
        //已关闭
        $recycle =  AnnounceModel::where($map_recycle)->count();
        $count = array(
            'orderCount'=>1
        );
        $this->assign(compact('status','common','band','recycle','count'));
        return $this->fetch();
    }


    /**
     * 公告列表
     *
     * @return json
     */
    public function announce_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['status',     1],
        ]);
        return JsonService::successlayui(AnnounceModel::AnnounceList($where));
    }

    public function create_announce()
    {
        $select=db('com_forum')->where('status',1)->where('display',1)->where('pid','>',0)->select();
        $this->assign('select',$select);
        $this->assign('style','create');
        return $this->fetch();
    }

    public function select_class(Request $request)
    {
        $data = Util::postMore([
            'id',
        ],$request);
        $select=db('com_thread_class')->where('status',1)->where('fid',$data['id'])->select();
        Json::successful($select);
    }

    /**
     * 新增公告
     */
    public function add_announce(Request $request){
        $data = Util::postMore([
            'title',
            'fid',
            'class_id',
            'start_time',
            ['image',''],
            ['is_auto_image',1],
            ['is_weibo',0],
        ],$request);

        $data['content']=osx_input('post.content','','html');

        $data['create_time']=time();
        $data['is_announce']=1;
        $data['author_uid']=1;
        $data['status']=1;
        $data['from']='HouTai';
        $data['type']=10;
        $result = ThreadModel::createThread($data); //新增帖子内容到数据库，事务写法，过程中涉及很多数据库操作
        $map['fid']=$data['fid'];
        $map['tid']=$result;
        $map['class_id']=$data['class_id'];
        $map['title']=$data['title'];
        $map['content']=$data['content'];
        $map['uid']=1;
        $map['create_time']=$data['create_time'];
        $map['start_time']=$data['start_time'];
        if($map['start_time']>time()){
            $map['status']=0;
        }else{
            $map['status']=1;
        }
        $res=AnnounceModel::createAnnounce($map);
        if ($result && $res==1) {
            db('user')->where('uid', 1)->setInc('post_count');
            db('com_forum')->where('id', $data['fid'])->setInc('post_count');
            $res=array();
            $res['thread_id']=$result;
            $res['info']='发布成功';
            Json::successful($res);
        } else {
            JsonService::fail('创建公告失败');
        }
    }

    public function find_users(){
        $nickname=osx_input('nickname','','text');
        $users=UserModel::where('nickname|uid|phone','like',"%$nickname%")->limit(10)->select()->toArray();
        $data=array();
        if($users){

            foreach ($users as $v){
                if($v){
                    $data[]=array('value'=>$v['uid'],'name'=>$v['nickname']);
                }
            }
        }

        return Json::successlayui(count($users),$data,'成功');

    }

    /**
     * 新增公告
     */
    public function edit_announce(Request $request){
        $data = Util::postMore([
            'id',
            'title',
            'fid',
            'class_id',
            'start_time',
        ],$request);

        $data['content']=osx_input('post.content','','html');

        $map['fid']=$data['fid'];
        $map['class_id']=$data['class_id'];
        $map['title']=$data['title'];
        $map['content']=$data['content'];
        $data['content']=json_encode($data['content']);
//        $map['create_time']=$data['create_time'];
        $map['start_time']=$data['start_time'];
        if($map['start_time']>time()){
            $map['status']=0;
        }else{
            $map['status']=1;
        }
        $res=AnnounceModel::where('id',$data['id'])->update($map);

        $tid=AnnounceModel::where('id',$data['id'])->value('tid');
        unset($data['id']);
        $result = ThreadModel::where('id',$tid)->update($data); //新增帖子内容到数据库，事务写法，过程中涉及很多数据库操作
        if ($res!==false && $result!==false) {
            $res='编辑成功';
            Json::successful($res);
        } else {
            JsonService::fail('编辑失败');
        }
    }

    public function view(){
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $announce = AnnounceModel::getOne($id);
        if(!$announce) return Json::fail('数据不存在!');
        $select=db('com_forum')->where('status',1)->where('display',1)->where('pid','>',0)->select();
        $class=db('com_thread_class')->where('fid',$announce['fid'])->where('status',1)->select();
        $announce['create_time']=strtotime($announce['create_time']);
        $this->assign('announce',$announce);
        $this->assign('select',$select);
        $this->assign('class',$class);
        $this->assign('style','view');
        return $this->fetch('create_announce');
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
            return JsonService::fail('请选择需要还原的公告');
        }else{
            $res = $res=AnnounceModel::where('id','in',$post['ids'])->update(['status'=>1]);
            if($res)
                return JsonService::successful('还原成功');
            else
                return JsonService::fail('还原失败');
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function remove()
    {
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要清理的公告');
        }else{
            $res = AnnounceModel::destroy($post['ids']);
            if($res)
                return JsonService::successful('清理成功');
            else
                return JsonService::fail('清理失败');
        }
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
            'title',
            'start_time',
            'fid',
        ],$request);

        $data['content']=osx_input('post.content','','html');

        $data['start_time']=strtotime($data['start_time']);
        AnnounceModel::edit($data,$id);
        return Json::successful('成功');
    }

    /**
     * 删除指定资源
     * @return \think\Response
     */
    public function delete()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $res=AnnounceModel::delete_ann($id);
        if($res==1){
            return Json::successful('删除成功');
        }else{
            JsonService::fail('删除失败');
        }
    }

    public function close()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $res=AnnounceModel::close($id);
        if($res==1){
            return Json::successful('关闭成功');
        }else{
            JsonService::fail('关闭失败');
        }
    }

    public function open()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $res=AnnounceModel::open($id);
        if($res==1){
            return Json::successful('推送成功');
        }else{
            JsonService::fail('推送失败');
        }
    }



    /**
     * 显示编辑资源表单页.
     * @return \think\Response
     */
    public function edit()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $announce = AnnounceModel::getOne($id);
        if(!$announce) return Json::fail('数据不存在!');
        $select=db('com_forum')->where('status',1)->where('display',1)->where('pid','>',0)->select();
        $class=db('com_thread_class')->where('status',1)->where('fid',$announce['fid'])->select();
        $announce['create_time']=strtotime($announce['create_time']);
        $this->assign('announce',$announce);
        $this->assign('select',$select);
        $this->assign('class',$class);
        $this->assign('style','edit');
        return $this->fetch('create_announce');
    }

}
