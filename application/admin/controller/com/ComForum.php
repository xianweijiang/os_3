<?php
namespace app\admin\controller\com;

use app\admin\controller\AuthController;
use app\admin\model\com\ComThread;
use app\admin\model\com\ForumPower;
use app\admin\model\group\Group;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Cache;
use think\Request;
use app\admin\model\com\ComForum as ForumModel;
use think\Url;
use app\admin\model\system\SystemAttachment;
use app\admin\model\user\User as UserModel;

/**
 * 版块控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class ComForum extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $pid=osx_input('pid','','text');
        $level = $this->request->get('level',0);

        $this->assign('level', $level);
        $map_common = ['status'=>1];
        $map_band = ['status'=>0];
        $map_recycle = ['status'=>-1];
        $map_need_verify = ['status'=>2];
        $map_draft = ['status'=>3];
        $status=$this->request->param('status');
        //获取分类
        $this->assign('cate',ForumModel::getCatTierList());
        //正常
        $common = ForumModel::where($map_common)->count();
        //禁用
        $band = ForumModel::where($map_band)->count();
        //已删除
        $recycle =  ForumModel::where($map_recycle)->count();
        //待审核
        $need_verify = ForumModel::where($map_need_verify)->count();
        // 草稿箱
        $draft = ForumModel::where($map_draft)->count();
        $count = array(
            'orderCount'=>1
        );
        $this->assign(compact('status','common','band','need_verify','recycle','count', 'draft','pid'));
        return $this->fetch();
    }

    public function sub_index()
    {
        $pid=osx_input('pid','','text');
        $this->assign('pid', $pid);
        return $this->fetch();
    }

  public function create_table()
  {
      $where=Util::getMore([
          ['id',''],
      ]);

      if($where['id']){
          $forum=ForumModel::get($where['id']);
          if($forum['group']){
              $g_id=explode(',',$forum['group']);
              $g_name=Group::where(['id'=>['in',$g_id]])->field('name')->select()->toArray();
              $g_name=array_column($g_name,'name');
              $forum['g_name']=implode(',',$g_name);
          }else{
              $forum['g_name']='';
          }
          $url=Url('update_one');
      }else{
          $url=Url('saveOne');
          $forum['name']=$forum['g_name']=$forum['group']=$forum['sort']=$forum['summary']='';
      }
      $this->assign([
          'forum'=>$forum,
          'id'=>$where['id'],
          'url'=>$url,
      ]);
     return $this->fetch();
  }

    /**
     * 权限组选择器
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.3
     */
  public function user_select()
  {
      $group=Group::get_all_group();
      $where=Util::getMore([
          ['name',1],
          ['id',0],
          ['type',0]
      ]);

      $group_id=['2','3','4'];
      switch ($where['type']){
          case 0:
              if($where['id']){
                  $group_id=db('bind_forum_group')->where(['id'=>$where['id']])->value('group');
                  $group_id=explode(',',$group_id);
              }
              break;
          case 1:
              if($where['id']){
                  $group_id=db('com_forum')->where(['id'=>$where['id']])->value('group');
                  $group_id=explode(',',$group_id);
              }
              break;
      }
      $this->assign([
          'group'=>$group,
          'name'=>$where['name'],
          'group_id'=>$group_id,
//          'group_name'=>$group_name,
      ]);

      return $this->fetch();
  }

    public function create_power()
    {
        $where=Util::getMore([
            ['id',''],
        ]);
        $data=ForumPower::get_power_by_forum($where['id']);
//        dump($data);exit;
        $this->assign([
            'id'=>$where['id'],
            'data'=>$data,
        ]);
        return $this->fetch();
    }

    public function edit_forum_power(){
        $data=Util::postMore([
            ['id',''],
            ['audit',''],
            ['visit',''],
            ['send_thread',''],
            ['send_comment',''],
            ['browse',''],
            ['visit_id',''],
            ['send_comment_id',''],
            ['send_thread_id',''],
            ['send_comment_id',''],
            ['browse_id',''],
            ['forum_id',''],
        ]);
        //删除传的值
        if($data['visit']==2&&$data['visit_id']==''){
            return JsonService::fail('请选择指定访问权限的用户组');
        }
        if($data['send_comment']==3&&$data['send_comment_id']==''){
            return JsonService::fail('请选择指定评论权限的用户组');
        }
        if($data['send_thread']==3&&$data['send_thread_id']==''){
            return JsonService::fail('请选择指定发帖权限的用户组');
        }
        if($data['browse']==2&&$data['browse_id']==''){
            return JsonService::fail('请选择指定浏览权限的用户组');
        }

        foreach ($data as $key=>$vo){
            if(empty($vo)&&$vo!=="0"){
                unset($data[$key]);
            }
        }
        $res=ForumPower::edit_forum_power($data);
        if($res){
            //清除权限缓存
            ForumPower::clear_cache();
            return JsonService::successful('成功');
        }else{
            return JsonService::fail('失败');
        }

    }
    /**
     * 异步查找版块
     *
     * @return json
     */
    public function forum_list(){
        $level=osx_input('level','','text');
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['name',''],
            ['is_hot',''],
            ['excel',0],
            ['order',''],
            ['status',     1],
            ['pid',        ''],
            ['data',       ''],
            ['display',    ''],
            ['time_field', 'create_time'],
        ]);
        if($level){
            $where['level'] = $level;
        }
        return JsonService::successlayui(ForumModel::ForumList($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
//        $this->assign(['title'=>'添加版块','action'=>Url::build('save'),'rules'=>$this->rules()->getContent()]);
//        return $this->fetch('public/common_form');
        $field = [
            Form::select('pid','父级')->setOptions(function(){
                $list = ForumModel::where('pid',0)->where('status',1)->select()->toArray();
                if(empty($list)){
                    $menus=[];
                }else{
                    foreach ($list as $menu){
                        $menus[] = ['value'=>$menu['id'],'label'=>$menu['name']];//,'disabled'=>$menu['pid']== 0];
                    }
                }
                return $menus;
            })->required('版块分类必填')->filterable(1),
            Form::input('name','版块名称')->col(Form::col(24))->required('版块名必填'),
            Form::input('title','版块标语')->col(Form::col(24))->placeholder('在版块主页显示，建议10个字左右'),
            Form::input('summary','版块简介')->type('textarea'),
            Form::input('content','版块规则')->type('textarea'),
            // Form::select('type','版面类型',1)->setOptions(function(){
            //     //版面类型,1.普通版面,2.微博,3.朋友圈,4.资讯,5.活动,6.视频横版,7.视频竖版
            //     $menus=[['value'=>1,'label'=>'普通版面'],['value'=>2,'label'=>'微博']
            //         ,['value'=>3,'label'=>'朋友圈'],['value'=>4,'label'=>'资讯']
            //         ,['value'=>5,'label'=>'活动'],['value'=>6,'label'=>'视频横版（PGC为主）']
            //         ,['value'=>7,'label'=>'视频竖版（UGC为主）']];
            //     return $menus;
            // })->filterable(1),
            Form::radio('type','版块类型',1)->options([
                ['value'=>1,'label'=>'普通帖子'],
                ['value'=>2,'label'=>'动态'],
                // ['value'=>2,'label'=>'常规帖子'],
                //['value'=>3,'label'=>'朋友圈'],
                ['value'=>4,'label'=>'资讯'],
                //['value'=>5,'label'=>'活动'],
                ['value'=>6,'label'=>'视频（横屏）'],
                //['value'=>7,'label'=>'视频（竖屏）'],
                ['value'=>8,'label'=>'聚合版块（支持全部类型）'],
            ])->col(Form::col(12))->required('版块类型必选'),
            Form::radio('default_follow','注册默认关注',0)->options([
                ['value'=>0,'label'=>'否'],
                ['value'=>1,'label'=>'是'],
            ])->col(Form::col(24))->required('默认关注必选'),
            Form::radio('is_audit','是否开启人工审核',0)->options([
                ['value'=>1,'label'=>'开启'],
                ['value'=>0,'label'=>'关闭'],
            ]),
            Form::frameImageOne('logo','版块logo(120*120px)',Url::build('admin/widget.images/index',array('fodder'=>'logo')))->icon('image')->width('100%')->height('500px'),
            Form::frameImageOne('background','版块背景图(750*300)',Url::build('admin/widget.images/index',array('fodder'=>'background')))->icon('image')->width('100%')->height('500px'),
            /*Form::radio('allow_user_group', '发帖权限', 1)->options([
                ['value'=>'1','label'=>'注册用户全开放'],
                ['value'=>'2','label'=>'仅限关注用户'],
                ['value'=>'3','label'=>'仅限管理员'],
            ])->col(Form::col(12))->required('发帖权限必选'),
            Form::radio('allow_post','允许评论',1)->options([['label'  => '是','value'=>1],['label'=>'否','value'=>0]]),
            Form::radio('is_private','是否私密',0)->options([['label'  => '是','value'=>1],['label'=>'否','value'=>0]]),
            Form::radio('need_verify','发帖审核',1)->options([['label' => '全部需要审核','value'=>1],['label'=>'全部不需要审核','value'=>2]]),*/
            Form::number('sort','排序')->col(8),
        ];
        $form = Form::make_post_form('创建版块',$field,Url::build('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function createOne()
    {
        $field = [
            Form::input('name','分区名称')->col(Form::col(24))->required('分区名必填'),
            Form::input('summary','分区描述')->type('textarea'),
            Form::number('sort','排序')->col(8),
        ];
        $form = Form::make_post_form('创建分区',$field,Url::build('saveOne'),2);
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
            ['pid',0],
            'name',
            'summary',
            'type',
            'logo',
            'title',
            'content',
            'background',
            'is_private',
            'default_follow',
            // 'allow_user_group',
            'allow_post',
            'sort',
            'need_verify',
            'is_audit'
        ],$request);
        $data['status']=1;
        $data['is_private']=0;
        $data['allow_post']=1;
        $data['need_verify']=1;
        $data['create_time']=time();
        $data['allow_user_group'] = input('allow_user_group/a', []);

        if(!$data['name']) return Json::fail('请输入版块名称');
        $data['allow_user_group'] = implode(',',$data['allow_user_group']);
//        $data['admin_uid'] = $this->adminId;
        ForumModel::set($data);
        //清除权限缓存
        ForumPower::clear_cache();
        return Json::successful('成功');
    }

    public function saveOne(Request $request)
    {
        $data = Util::postMore([
            ['pid',0],
            'name',
            'summary',
            ['type',1],
            'sort',
            ['group',''],
            ['jurisdiction','hide']
        ],$request);
        if(!$data['name']) return Json::fail('请输入版块名称');
        $data['status']=1;
        if($data['jurisdiction']!='show'){
            unset($data['jurisdiction'],$data['group']);
            $data['group']='';
        }
        ForumModel::set($data);
        return Json::successful('成功');
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
            'pid',
            'name',
            'summary',
            'title',
            'content',
            'type',
            'logo',
            'default_follow',
            'sort',
            'false_num',
            'background',
            'is_audit'
        ],$request);
        $data['is_private']=0;
        $data['allow_post']=1;
        $data['need_verify']=1;


        if(!$data['name']) return Json::fail('请输入版块名称');
        ForumModel::edit($data,$id,'id');
        Cache::rm('forum_index_top_detail_fid_'.$id);
        return Json::successful('成功');
    }

    public function update_one(Request $request)
    {
        $id=osx_input('id',0,'intval');
        $data = Util::postMore([
            'name',
            'summary',
            'sort',
            ['group',''],
            ['jurisdiction','hide']
        ],$request);
        $data['type']             = input('type/a', []);
        $data['is_private']=0;
        $data['allow_post']=1;
        $data['need_verify']=1;
        if($data['jurisdiction']!='show'){
            $data['group']='';
        }
        if(count($data['type']) > 1 && in_array(1, $data['type'])){
            return Json::fail('版块类型选中聚合后无需勾选其他类型');
        }

        if(!$data['name']) return Json::fail('请输入版块名称');
        ForumModel::edit($data,$id,'id');
        Cache::rm('forum_index_top_detail_fid_'.$id);
        //清除权限缓存
        ForumPower::clear_cache();
        return Json::successful('成功');
    }

    /**
     * 显示编辑资源表单页.
     * @return \think\Response
     */
    public function edit()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $forum = ForumModel::get($id);
        if(!$forum) return Json::fail('数据不存在!');

        $list = ForumModel::where('pid',0)->where('status',1)->select()->toArray();
        if($list){
            foreach ($list as $menu){
                $menus[] = ['value'=>$menu['id'],'label'=>$menu['name'],'disabled'=>$menu['id']== $id];
            }
        }else{
            $menus=[];
        }
        $field = [
           Form::select('pid','版块分类',(string)$forum->getData('pid'))->setOptions($menus)->required('版块分类必填')->filterable(1),
            Form::input('name','版块名称',$forum->getData('name')),
            Form::input('title','版块标语',$forum->getData('title')),
            Form::input('summary','版块简介',$forum->getData('summary'))->type('textarea'),
            Form::input('content','版块规则',$forum->getData('content'))->type('textarea'),
            Form::radio('type','版块类型', $forum['type'])->options([
                ['value'=>1,'label'=>'普通帖子'],
                ['value'=>2,'label'=>'动态'],
//                // ['value'=>2,'label'=>'常规帖子'],
//                //['value'=>3,'label'=>'朋友圈'],
                ['value'=>4,'label'=>'资讯'],
//                //['value'=>5,'label'=>'活动'],
                ['value'=>6,'label'=>'视频（横屏）'],
//                //['value'=>7,'label'=>'视频（竖屏）'],
                ['value'=>8,'label'=>'聚合版块（支持全部类型）']
            ])->col(Form::col(12))->required('版块类型必选'),
            Form::radio('default_follow','注册默认关注',$forum['default_follow'])->options([
                ['value'=>0,'label'=>'否'],
                ['value'=>1,'label'=>'是'],
            ])->col(Form::col(24))->required('默认关注必选'),
            Form::radio('is_audit','是否开启人工审核',$forum['is_audit'])->options([
                ['value'=>0,'label'=>'关闭'],
                ['value'=>1,'label'=>'开启'],
            ]),
//            Form::select('is_hot','是否推荐版块',(string)$forum->getData('is_hot'))->setOptions(function(){
//                $menus=[['value'=>0,'label'=>'否'],['value'=>1,'label'=>'是']];
//                return $menus;
//            })->filterable(1),
            Form::input('false_num','虚拟关注人数',$forum->getData('false_num')),
            // Form::select('type','版面类型',(string)$forum->getData('type'))->setOptions(function(){
            //     //版面类型,1.普通版面,2.微博,3.朋友圈,4.资讯,5.活动,6.视频横版,7.视频竖版
            //     $menus=[['value'=>1,'label'=>'普通版面'],['value'=>2,'label'=>'微博']
            //         ,['value'=>3,'label'=>'朋友圈'],['value'=>4,'label'=>'资讯']
            //         ,['value'=>5,'label'=>'活动'],['value'=>6,'label'=>'视频横版（PGC为主）']
            //         ,['value'=>7,'label'=>'视频竖版（UGC为主）']];
            //     return $menus;
            // })->filterable(1),
            Form::frameImageOne('logo','版块logo(110*110px)',Url::build('admin/widget.images/index',array('fodder'=>'logo')),$forum->getData('logo'))->icon('image')->width('100%')->height('500px'),
            Form::frameImageOne('background','版块背景图',Url::build('admin/widget.images/index',array('fodder'=>'background')),$forum->getData('background'))->icon('image')->width('100%')->height('500px'),
            /*Form::radio('allow_user_group', '发帖权限', $forum['allow_user_group'])->options([
                ['value'=>'1','label'=>'注册用户全开放'],
                ['value'=>'2','label'=>'仅限关注用户'],
                ['value'=>'3','label'=>'仅限管理员'],
            ])->col(Form::col(12))->required('发帖权限必选'),
            Form::radio('allow_post','允许评论', $forum['allow_post'])->options([['label'  => '是','value'=>1],['label'=>'否','value'=>0]]),
            Form::radio('is_private','是否私密', $forum['is_private'])->options([['label'  => '是','value'=>1],['label'=>'否','value'=>0]]),
            Form::radio('need_verify','发帖审核',$forum['need_verify'])->options([['label' => '全部需要审核','value'=>1],['label'=>'全部不需要审核','value'=>2]]),
            Form::radio('status','状态',$forum->getData('status'))->options([['label'=>'正式','value'=>1],['label'=>'已驳回','value'=>0]]),*/
            Form::number('sort','排序',$forum->getData('sort'))->col(8),

        ];
        $form = Form::make_post_form('编辑版块',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function edit_one()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $forum = ForumModel::get($id);
        if(!$forum) return Json::fail('数据不存在!');

        $list = ForumModel::getCatTierList();
        if($list){
            foreach ($list as $menu){
                $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['name'],'disabled'=>$menu['id']== $id];
            }
        }else{
            $menus=[];
        }
        $field = [
//            Form::select('pid','版块分类',(string)$forum->getData('pid'))->setOptions($menus)->filterable(1),
            Form::input('name','版块名称',$forum->getData('name')),
            Form::input('summary','版块描述',$forum->getData('summary'))->type('textarea'),
 //           Form::radio('type','版块类型', $forum['type'])->options([
//                ['value'=>1,'label'=>'普通帖子'],
//                //['value'=>2,'label'=>'微博'],
//                // ['value'=>2,'label'=>'常规帖子'],
//                //['value'=>3,'label'=>'朋友圈'],
//                ['value'=>4,'label'=>'资讯'],
//                //['value'=>5,'label'=>'活动'],
//                //['value'=>6,'label'=>'视频（横屏）'],
//                //['value'=>7,'label'=>'视频（竖屏）'],
//                //['value'=>8,'label'=>'聚合版块（支持全部类型）']
//            ])->col(Form::col(12))->required('版块类型必选'),
//            Form::select('is_hot','是否推荐版块',(string)$forum->getData('is_hot'))->setOptions(function(){
//                $menus=[['value'=>0,'label'=>'否'],['value'=>1,'label'=>'是']];
//                return $menus;
//            })->filterable(1),
//            Form::input('false_num','虚拟关注人数',$forum->getData('false_num')),
            // Form::select('type','版面类型',(string)$forum->getData('type'))->setOptions(function(){
            //     //版面类型,1.普通版面,2.微博,3.朋友圈,4.资讯,5.活动,6.视频横版,7.视频竖版
            //     $menus=[['value'=>1,'label'=>'普通版面'],['value'=>2,'label'=>'微博']
            //         ,['value'=>3,'label'=>'朋友圈'],['value'=>4,'label'=>'资讯']
            //         ,['value'=>5,'label'=>'活动'],['value'=>6,'label'=>'视频横版（PGC为主）']
            //         ,['value'=>7,'label'=>'视频竖版（UGC为主）']];
            //     return $menus;
            // })->filterable(1),
            //Form::frameImageOne('logo','版块logo(110*110px)',Url::build('admin/widget.images/index',array('fodder'=>'logo')),$forum->getData('logo'))->icon('image')->width('100%')->height('500px'),
            //Form::frameImageOne('background','版块背景图',Url::build('admin/widget.images/index',array('fodder'=>'background')),$forum->getData('background'))->icon('image')->width('100%')->height('500px'),
            /*Form::radio('allow_user_group', '发帖权限', $forum['allow_user_group'])->options([
                ['value'=>'1','label'=>'注册用户全开放'],
                ['value'=>'2','label'=>'仅限关注用户'],
                ['value'=>'3','label'=>'仅限管理员'],
            ])->col(Form::col(12))->required('发帖权限必选'),
            Form::radio('allow_post','允许评论', $forum['allow_post'])->options([['label'  => '是','value'=>1],['label'=>'否','value'=>0]]),
            Form::radio('is_private','是否私密', $forum['is_private'])->options([['label'  => '是','value'=>1],['label'=>'否','value'=>0]]),
            Form::radio('need_verify','发帖审核',$forum['need_verify'])->options([['label' => '全部需要审核','value'=>1],['label'=>'全部不需要审核','value'=>2]]),
            Form::radio('status','状态',$forum->getData('status'))->options([['label'=>'正式','value'=>1],['label'=>'已驳回','value'=>0]]),*/
            Form::number('sort','排序',$forum->getData('sort'))->col(8),

        ];
        $form = Form::make_post_form('编辑版块',$field,Url::build('update_one',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 设置审核单个版块
     *
     * @return json
     */
    public function set_verify(){
        $display=osx_input('display',0,'intval');
        $id=osx_input('id',0,'intval');
        ($display==='' || $id=='') && JsonService::fail('缺少参数');
        $res=ForumModel::where(['id'=>$id])->update(['display'=>(int)($display==1?0:1)]);
        if($res){
            Cache::rm('forum_index_top_detail_fid_'.$id);
            return JsonService::successful('成功');
        }else{
            return JsonService::fail('失败');
        }
    }

    /**
     * 批量审核版块
     *
     * @return json
     */
    public function forum_verify(){
        $status=osx_input('status',1,'intval');
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要审核的版块');
        }else{
            $res=ForumModel::where('id','in',$post['ids'])->update(['status'=>$status]);
            if($res)
                return JsonService::successful('成功');
            else
                return JsonService::fail('失败');
        }
    }

    /**
     * 批量删除版块
     *
     * @return json
     */
    public function del(){
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要删除的版块');
        }else{
            $res=ForumModel::where('id','in',$post['ids'])->update(['status'=>-1]);
            if($res){
                foreach ($post['ids'] as $forum_id){
                    Cache::rm('forum_index_top_detail_fid_'.$forum_id);
                }
                unset($forum_id);
                Cache::clear('forum_list');
                return JsonService::successful('成功');
            }else{
                return JsonService::fail('失败');
            }
        }
    }

    /**
     * 批量驳回版块
     *
     * @return json
     */
    public function ban(){
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要驳回的版块');
        }else{
            $res=ForumModel::where('id','in',$post['ids'])->update(['status'=>0]);
            if($res){
                foreach ($post['ids'] as $forum_id){
                    Cache::rm('forum_index_top_detail_fid_'.$forum_id);
                }
                unset($forum_id);
                Cache::clear('forum_list');
                return JsonService::successful('成功');
            }else{
                return JsonService::fail('失败');
            }
        }
    }

    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_forum(){
        $id=osx_input('id',0,'intval');
        $field=osx_input('field','','text');
        $value=osx_input('value','','text');
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if($value==-1 && $field=='status'){
            $count=ForumModel::where('pid',$id)->where('status','>',-1)->count();
            if($count>0){
                return JsonService::fail('请先删除该版块下的子版块');
            }
            $thread=ComThread::where('fid',$id)->where('status','>',-1)->count();
            if($thread>0){
                return JsonService::fail('请先删除该版块中包含的已审核/待审核/已驳回内容');
            }
        }
        if(ForumModel::where(['id'=>$id])->update([$field=>$value,'update_time'=>time()]))
            return JsonService::successful('成功');
        else
            return JsonService::fail('失败');
    }

    /**
     * 删除指定资源
     * @return \think\Response
     */
    public function delete()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        if(!ForumModel::be(['id'=>$id])) return $this->failed('版块数据不存在');
        ForumModel::destroy($id);
        //     return Json::fail(ForumModel::getErrorInfo('恢复失败,请稍候再试!'));
        // else
        return Json::successful('成功');
    }


    /**
     * 批量清理版块
     *
     * @return json
     */
    public function remove(){
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要清理的版块');
        }else{
            $res=ForumModel::destroy($post['ids']);
            if($res)
                return JsonService::successful('成功');
            else
                return JsonService::fail('失败');
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

        return Json::successlayui(count($users),$data,'成功');

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

    // 查看详情
    public function edit_content(){
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $forum = ForumModel::get($id);
        $forum['type_text'] = ForumModel::getTypeText($forum);
        if(!$forum) return Json::fail('数据不存在!');
        $field = [
            Form::input('pid', '父级', $forum['pid']?  ForumModel::getFieldById($forum['pid'], 'name'):'顶级分区')->col(Form::col(24))->readonly(true),
            Form::input('name','版块名称', $forum['name'])->col(Form::col(24))->readonly(true),
            //Form::input('type', '发帖权限', $forum['type_text'])->readonly(true),
            Form::frameImageOne('logo','版块logo(80*80px)',Url::build('admin/widget.images/index',array('fodder'=>'logo')), $forum['logo'])->icon('image')->width('100%')->height('500px'),
            /*Form::input('allow_edit_rules', '发帖权限', $forum['allow_edit_rules_text'])->readonly(true),
            Form::input('allow_post', '允许评论', '是')->readonly(true),
            Form::input('need_verify', '发帖审核', ForumModel::need_verify_texts[$forum['need_verify']])->readonly(true),*/
            Form::frameImageOne('background','版块背景图',Url::build('admin/widget.images/index',array('fodder'=>'background')), $forum['background'])->icon('image')->width('100%')->height('500px'),
            Form::input('summary','版块描述')->type('textarea')->readonly(true),
            Form::number('sort','排序')->col(8)->readonly(true),
            Form::input('status', '状态', ForumModel::status_texts[$forum['status']])->readonly(true),
        ];
        $form = Form::make_post_form('详情',$field,Url::build('save'),2);
        $form->hiddenSubmitBtn(true);
        $form->hiddenResetBtn(true);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');

    }
}
