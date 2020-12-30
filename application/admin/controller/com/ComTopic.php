<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */
namespace app\admin\controller\com;

use Api\Express;
use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\JsonService as Json;
use think\Cache;
use service\UtilService as Util;
use think\Request;
use think\Url;
use app\admin\model\order\StoreOrder as StoreOrderModel;
use app\admin\model\com\ComTopic as TopicModel;
use app\admin\model\com\ComThread;
use app\admin\model\com\ComTopicClass;
use app\osapi\model\user\UserModel;

/**
 * 订单管理控制器 同一个订单表放在一个控制器
 * Class StoreOrder
 * @package app\admin\controller\store
 */
class ComTopic extends AuthController
{
    /**
     * @return mixed
     */
    public function index()
    {
        $status=osx_input('status','','text');
        $class_id=osx_input('class_id','','intval');
        $id=osx_input('id','','intval');
        $this->assign([
            'year'       => getMonth('y'),
            'orderCount' => StoreOrderModel::orderCount(),
            'status'     => $status,
            'id'     => $id,
            'class' => ComTopicClass::getClassList(),
            'class_id'=>$class_id
        ]);
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
            Form::input('title','话题标题'),
            Form::select('class_id','分类')->setOptions(function(){
                $list = ComTopicClass::getClassList('',1);
                if(empty($list)){
                    $menus=[];
                }else{
                    foreach ($list as $menu){
                        $menus[] = ['value'=>$menu['id'],'label'=>$menu['name']];
                    }
                }
                return $menus;
            })->filterable(1),
            Form::frameImageOne('image','封面(180*180)',Url::build('admin/widget.images/index',array('fodder'=>'image')))->icon('image'),
            Form::textarea('summary','话题简介'),
            Form::input('seo_title','SEO标题'),
            Form::textarea('seo_key','SEO关键词')->placeholder('关键词之间用“,”隔开'),
            Form::textarea('seo_summary','SEO描述'),
        ];
        $form = Form::make_post_form('添加话题',$field, Url::build('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function hot_time()
    {
        $id=osx_input('id',0,'intval');
        $field = [
            Form::date('hot_end_time','推荐有效期至'),
        ];
        $form = Form::make_post_form('编辑版块',$field,Url::build('save_hot',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function save_hot(Request $request)
    {
        $id=osx_input('id',0,'intval');
        $data = Util::postMore([
            'hot_end_time',
        ],$request);
        if(!$data['hot_end_time']) return Json::fail('请输入有效期');
        $count=TopicModel::where('is_hot',1)->where('status',1)->where('hot_end_time','>',time())->count();
        if($count>=10){
            $hot_id=TopicModel::Where('status',1)->where('is_hot',1)->where('hot_end_time','>',time())->order('hot_time asc')->value('id');
            TopicModel::Where('id',$hot_id)->update(['is_hot'=>0]);
        }
        $data['is_hot']=1;
        $data['hot_end_time']=strtotime($data['hot_end_time']);
        TopicModel::edit($data, $id);
        Cache::clear('index_hot_topic_list');
        Cache::clear('index_topic_list');
        Cache::clear('user_send_topic_list');
        Cache::clear('user_follow_topic_list');
        return Json::successful('添加成功!');
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
            'title',
            'class_id',
            'summary',
            ['image',''],
            ['seo_title',''],
            ['seo_key',''],
            ['seo_summary',''],
        ],$request);
        if(!$data['title']) return Json::fail('请输入话题标题');
        if(!$data['class_id']) return Json::fail('请输入分类');
        if(!$data['summary']) return Json::fail('请输入话题简介');
        if($data['seo_title']==''){
            $data['seo_title']=$data['title'];
        }
        if($data['seo_summary']==''){
            $data['seo_summary']=$data['summary'];
        }
        $data['uid']=1;
        $data['create_time']=time();
        $data['update_time']=time();
        $data['status']=1;
        TopicModel::set($data);
        Cache::clear('index_hot_topic_list');
        Cache::clear('index_topic_list');
        Cache::clear('user_send_topic_list');
        Cache::clear('user_follow_topic_list');
        return Json::successful('添加成功!');
    }

    /**
     * 获取头部订单金额等信息
     * return json
     *
     */
    public function getBadge(){
        $where = Util::postMore([
            ['status',''],
            ['real_name',''],
            ['is_del',0],
            ['data',''],
            ['type',''],
            ['order','']
        ]);
        return JsonService::successful(StoreOrderModel::getBadge($where));
    }


    /**
     * 获取帖子主题列表
     * return json
     */
    public function topic_list(){
        $status=osx_input('status','','text');
        $where = Util::getMore([
            ['status',$status],
            ['title',''],
            ['id',''],
            ['uid',''],
            ['class',''],
            ['class_id',''],
            ['is_hot', ''],
            ['data',''],
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(TopicModel::TopicList($where));
    }

    /**
     * @return mixed|\think\response\Json|void
     */
    public function edit()
    {
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $topic = TopicModel::get($id);
        if(!$topic) return Json::fail('数据不存在!');
        $field = [
            Form::input('title','话题标题',$topic->getData('title')),
            Form::select('class_id','分类',(string)$topic->getData('class_id'))->setOptions(function(){
                $list = ComTopicClass::getClassList('',1);
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['name']];
                }
                return $menus;
            })->filterable(1),
            Form::frameImageOne('image','封面',Url::build('admin/widget.images/index',array('fodder'=>'image')),$topic->getData('image'))->icon('image'),
            Form::textarea('summary','话题简介',$topic->getData('summary')),
            Form::input('seo_title','SEO标题',$topic->getData('seo_title')),
            Form::textarea('seo_key','SEO关键词',$topic->getData('seo_key'))->placeholder('关键词之间用“,”隔开'),
            Form::textarea('seo_summary','SEO描述',$topic->getData('seo_summary')),
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
            'title',
            'class_id',
            'summary',
            ['image',''],
            ['seo_title',''],
            ['seo_key',''],
            ['seo_summary',''],
        ],$request);
        if(!$data['title']) return Json::fail('请输入话题标题');
        if(!$data['class_id']) return Json::fail('请输入分类');
        if(!$data['summary']) return Json::fail('请输入话题简介');
        if($data['seo_title']==''){
            $data['seo_title']=$data['title'];
        }
        if($data['seo_summary']==''){
            $data['seo_summary']=$data['summary'];
        }
        TopicModel::edit($data, $id);
        Cache::clear('index_hot_topic_list');
        Cache::clear('index_topic_list');
        Cache::clear('user_send_topic_list');
        Cache::clear('user_follow_topic_list');
        Cache::rm('topic_detail'.$id);
        return Json::successful('修改成功!');
    }


    public function view(){
        $id=osx_input('id',0,'intval');
        if(!$id) return $this->failed('数据不存在');
        $topic = TopicModel::get($id);
        if(!$topic) return Json::fail('数据不存在!');
        $topic['nickname']=db('user')->where('uid',$topic['uid'])->value('nickname');
        $topic['class_name']=ComTopicClass::where('id',$topic['class_id'])->value('name');
        $topic['create_time']=time_format($topic['create_time']);
        $this->assign('topic',$topic);
        return $this->fetch('view');
    }

    /**
     * 批量审核版块
     *
     * @return json
     */
    public function verify(){
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要审核的版块');
        }else{
            $res = TopicModel::where('id','in',$post['ids'])->update(['status'=>1]);
            if($res!==false){
                Cache::clear('index_hot_topic_list');
                Cache::clear('index_topic_list');
                Cache::clear('user_send_topic_list');
                Cache::clear('user_follow_topic_list');
                return JsonService::successful('审核成功');
            } else{
                return JsonService::fail('审核失败');
            }
        }
    }


    /**
     * 批量删除
     *
     * @return json
     */
    public function delete(){
        $post=Util::postMore([
            ['ids',[]]
        ]);
        if(empty($post['ids'])){
            return JsonService::fail('请选择需要删除的话题');
        }else{
            $has=0;
            foreach($post['ids'] as &$v){
                $thread=ComThread::where('find_in_set(:id,oid)',['id'=>$v])->where('status',1)->value('id');
                if($thread){
                   $has=1;
                }
            }
            if($has==1){
                return JsonService::fail('请先删除话题下的动态');
            }
            $res = TopicModel::where('id','in',$post['ids'])->update(['status'=>-1]);
            if($res!==false){
                Cache::clear('index_hot_topic_list');
                Cache::clear('index_topic_list');
                Cache::clear('user_send_topic_list');
                Cache::clear('user_follow_topic_list');
                return JsonService::successful('删除成功');
            }else
                return JsonService::fail('删除失败');
        }
    }

    /**
     * 清理指定资源
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
            return JsonService::fail('请选择需要清理的话题');
        }else{
            $res = TopicModel::destroy($post['ids']);
            if($res){
                return JsonService::successful('清理成功');
            }
            else{
                return JsonService::fail('清理失败');
            }
        }
    }


    /**
     * 快速编辑
     *
     * @return json
     */
    public function quick_edit(){
        $field=osx_input('field','','text');
        $id=osx_input('id',0,'intval');
        $value=osx_input('value','','text');
        if($field=='status'&&$value==-1){
            $thread=ComThread::where('find_in_set(:id,oid)',['id'=>$id])->where('status',1)->value('id');
            if($thread){
                return JsonService::fail('请先删除话题下的动态');
            }
        }
        if(TopicModel::where(['id'=>$id])->update([$field=>$value])){
            Cache::clear('index_hot_topic_list');
            Cache::clear('index_topic_list');
            Cache::clear('user_send_topic_list');
            Cache::clear('user_follow_topic_list');
            return JsonService::successful('保存成功');
        } else{
            return JsonService::fail('保存失败');
        }
    }

    
    public function move(){
        $ids=osx_input('ids','','text');
        if($this->request->isPost()){
            $data = $this->request->post();
            // halt($data);
            if(!$data['ids']){
                return JsonService::fail('请选择迁移后的分类');
            }
            $class_id=$data['class_id'];
            if(TopicModel::where('id', 'in', $ids)->update(['class_id'=>$class_id])){
                Cache::clear('index_hot_topic_list');
                Cache::clear('index_topic_list');
                Cache::clear('user_send_topic_list');
                Cache::clear('user_follow_topic_list');
                return JsonService::successful('成功');
            } else{
                return JsonService::fail('失败');
            }
        }
        $cascader_classes = ComTopicClass::cascader_class();
        $field = [
            Form::cascader('class_id', '所选话题迁移到分类')->data($cascader_classes),
            Form::hidden('ids', $ids),
        ];
        $form = Form::make_post_form('迁移话题到其他分类',$field, Url::build('move'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

}
