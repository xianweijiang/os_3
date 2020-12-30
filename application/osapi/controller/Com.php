<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/24
 * Time: 17:08
 */

namespace app\osapi\controller;


use app\admin\model\com\ForumPower;
use app\commonapi\model\Gong;
use app\commonapi\model\TencentFile;
use app\osapi\model\com\ComForum;
use app\osapi\model\com\ComForumAdmin;
use app\osapi\model\com\ComForumMember;
use app\osapi\model\com\ComPost;
use app\osapi\model\com\ComThread;
use app\osapi\model\com\ComThreadClass;
use app\osapi\model\com\Report;
use app\osapi\model\user\UserFollow;
use app\osapi\model\user\UserModel;
use app\osapi\model\user\UserTaskNew;
use app\osapi\model\user\UserReward;
use app\osapi\model\com\Message;
use app\osapi\model\com\ComDraft;
use app\wechat\sdk\WechatAuth;
use tests\thinkphp\library\think\dbTest;
use think\Cache;
use app\admin\model\system\SystemConfig;
use app\osapi\model\common\Support;
use app\osapi\model\common\Blacklist;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;
use app\commonapi\controller\Sensitive;
use app\osapi\model\com\ComTopic;
use app\osapi\model\com\VisitAudit;
use app\osapi\model\com\Prohibit;
use app\osapi\model\com\ComForumAdminApply;

class Com extends Base
{
    /**
     * 首页帖子列表
     */
    public function indexThread(){
        $type=input('type','recommend','text');
        $page = input('page',1);
        $row = input('row', 10);
        $uid=get_uid();
        $access=$this->access;
        $video_is_on=SystemConfig::getValue('xcx_video');
        switch ($type){
            case 'follow':
                $followList=Cache::get('add_follow_uid_'.$uid);
                if(!$followList){
                    $followList = UserFollow::getFollowListAll($uid);
                    Cache::set('add_follow_uid_'.$uid,$followList,10*60);
                }
                if($followList){
                    $followListCache=Cache::get('index_follow_'.$page.$row.'_'.$uid);
                    if(!$followListCache){
                        $uids=array_column($followList,'uid');
                        array_push($uids,$uid);//增加上自己
                        $postListOne =  ComThread::getPostFollow($uids,$page,$row);
                        if(!$postListOne){
                            $postListOne=false;
                        }
                        $followListCache = ['list'=>$postListOne,'recache_time'=>time(),'time_end'=>time()+10*60];
                        Cache::tag('thread_list_cache')->set('index_follow_'.$page.$row.'_'.$uid,$followListCache,10*60);
                    }
                    $postListOne=$followListCache['list'];
                    if($postListOne!=false){
                        if($uid){
                            $selfHasChange=Cache::get('index_follow_list_change_'.$uid);//有点赞、评论时该用户重新获取帖子的点赞评论数
                            if($selfHasChange>$followListCache['recache_time']){
                                $postListOne = ComThread::reGetSupportNum($postListOne);
                                if($followListCache['time_end']>time()){//有效期还有一段时间
                                    $has_time=intval($followListCache['time_end'])-time();
                                    $followListCache['list']=$postListOne;
                                    $followListCache['recache_time']=time();
                                    Cache::tag('thread_list_cache')->set('index_follow_'.$page.$row.'_'.$uid,$followListCache,$has_time);
                                }
                            }
                        }
                        $postListOne=ComThread::initListUserRelation($postListOne,false);
                    }
                    $userList['user']=$followList;
                    $userList['post']=$postListOne;
                    $postList=array();
                    $forum_list=array();
                }else{
                    $postListCache=Cache::get('index_no_follow_');
                    if(!$postListCache){
                        //用户列表
                        $user_recommend_list = db('user_recommend')->where('status',1)->order('sort asc')->select();
                        if($user_recommend_list){
                            $count=count($user_recommend_list);
                            $user_ids=array_column($user_recommend_list,'uid');
                            //版块权限排除私密帖子
                            $mav['status']=1;
                            $mav['id']=['not in',ForumPower::get_private_id()];
                            $ids=ComForum::where($mav)->column('id');
                            if($count>99){
                                $map=[
                                    'status'=>1,
                                    'fid'=>['in',$ids],
                                    'author_uid'=>['in',$user_ids],
                                    'is_massage'=>0,
                                    'type'=>1,
                                    'index_top'=>0,
                                ];
                                $thread=ComThread::where($map)->field('*,max(id)')->group('author_uid')->select()->toArray();
                                $user_list=ComThread::threadListHandle($thread);
                            }else{
                                $limit=100-$count;
                                $user_no_recommend=UserModel::where('status',1)->where('uid','not in',$user_ids)->order('post_count desc')->limit($limit)->column('uid');
                                $user_ids=array_merge($user_ids,$user_no_recommend);

                                $map=[
                                    'status'=>1,
                                    'author_uid'=>['in',$user_ids],
                                    'is_massage'=>0,
                                    'type'=>1,
                                    'index_top'=>0,
                                    //版块权限排除私密帖子
                                    'fid'=>['not in',ForumPower::get_private_id()],
                                ];
                                $thread=ComThread::where($map)->field('*,max(id)')->group('author_uid')->select()->toArray();
                                $thread=array_combine(array_column($thread,'author_uid'),$thread);
                                $thread_list=[];
                                foreach($user_ids as $val){
                                    if(isset($thread[$val])){
                                        $thread_list[]=$thread[$val];
                                    }
                                }
                                unset($val);
                                $user_list=ComThread::threadListHandle($thread_list);
                            }
                        }else{
                            $user_no_recommend=UserModel::where('status',1)->order('fans desc,post_count desc')->limit(100)->column('uid');
                            $map=[
                                'status'=>1,
                                'author_uid'=>['in',$user_no_recommend],
                                'is_massage'=>0,
                                'type'=>1,
                                'index_top'=>0,
                                //版块权限排除私密帖子
                                'fid'=>['not in',ForumPower::get_private_id()],
                            ];
                            $thread=ComThread::where($map)->field('*,max(id)')->group('author_uid')->select()->toArray();
                            $user_list=ComThread::threadListHandle($thread);
                        }
                        $postListCache = ['list'=>$user_list,'recache_time'=>time(),'time_end'=>time()+10*60];
                        Cache::tag('thread_list_cache')->set('index_no_follow_',$postListCache,10*60);
                    }
                    $user_list=$postListCache['list'];
                    if($user_list!=false){
                        if($uid){
                            $selfHasChange=Cache::get('index_no_follow_list_change_'.$uid);//有点赞、评论时该用户重新获取帖子的点赞评论数
                            if($selfHasChange>$postListCache['recache_time']){
                                $user_list = ComThread::reGetSupportNum($user_list);
                                if($postListCache['time_end']>time()){//有效期还有一段时间
                                    $has_time=intval($postListCache['time_end'])-time();
                                    $postListCache['list']=$user_list;
                                    $postListCache['recache_time']=time();
                                    Cache::tag('thread_list_cache')->set('index_no_follow_',$postListCache,$has_time);
                                }
                            }
                        }
                        $user_list=ComThread::initListUserRelation($user_list,false);
                    }
                    $userList['user']=$followList;
                    $userList['post']=$user_list;
                    $postList=array();
                    $forum_list=array();
                }
                break;
            case 'forum':
                $get_forum_recommend=true;
                //私密的圈子id
                $no_show_id=ForumPower::get_private_id();
                if($uid){
                    $forum_ids=Cache::get('add_forum_ids_'.$uid);
                    if(!$forum_ids){
                        $ids=ComForum::where('status',1)->where('id','not in',$no_show_id)->column('id');
                        if($access[1] == '微信小程序' && $video_is_on==0){
                            $video_forum=ComForum::where('status',1)->where('type',6)->column('id');
                            $forum_ids=ComForumMember::where('uid',$uid)->where('status',1)->where('fid','in',$ids)->where('fid','not in',$video_forum)->limit(200)->column('fid');
                        }else{
                            $forum_ids=ComForumMember::where('uid',$uid)->where('status',1)->where('fid','in',$ids)->limit(200)->column('fid');
                        }
                        Cache::tag('forum_list')->tag('add_forum_list')->set('add_forum_ids_'.$uid,$forum_ids,24*60);
                    }
                    if(count($forum_ids)){
                        $show_forum_list=Cache::get('add_forum_show_list_1_10_'.$uid);
                        if(!$show_forum_list){
                            $show_forum_list=ComForumMember::getUserForumList($uid,1,10,'create_time desc',$access,$video_is_on);
                            Cache::tag('forum_list')->tag('add_forum_list')->set('add_forum_show_list_1_10_'.$uid,$show_forum_list,24*60);
                        }
                        $forum_list['forum_follow']=$show_forum_list;
                        $forum_list['forum_list']='';
                        $forum_list['thread_list']=ComThread::getForumThread($forum_ids,$page,$row);
                        $userList=array();
                        $postList=array();
                        $get_forum_recommend=false;
                    }
                }

                if($get_forum_recommend){//未登录或者未关注版块
                    $forum_recommend=Cache::get('index_recommend_forum_list');
                    if(!$forum_recommend){
                        $map['display']=1;
                        $map['pid']=array('>',0);
                        $map['status']=1;
                        $map['id']=['not in',$no_show_id];
                        if($access[1] == '微信小程序' && $video_is_on==0){
                            $video_forum=ComForum::where('status',1)->where('type',6)->column('id');
                            $map['id']=['not in',$video_forum];
                        }
                        $forum_recommend=ComForum::where($map)->order('is_hot desc，post_count desc')->limit(20)->select();
                        if($forum_recommend){
                            $forum_recommend=$forum_recommend->toArray();
                        }
                        foreach($forum_recommend as &$val){
                            $val['is_member'] = false;
                        }
                        unset($val);
                        Cache::tag('thread_list_cache')->set('index_recommend_forum_list',$forum_recommend,10*60);
                    }
                    $forum_list['forum_list']=$forum_recommend;
                    $forum_list['forum_follow']='';
                    $forum_list['thread_list']='';
                    $userList=array();
                    $postList=array();
                }
                break;
            case 'recommend':
                $postListCache=Cache::get('index_recommend_'.$page.$row);
                if(!$postListCache){
                    $postList = ComThread::getPostRecommend($page,$row,$access,$video_is_on);
                    $postListCache = ['list'=>$postList,'recache_time'=>time(),'time_end'=>time()+10*60];
                    Cache::tag('thread_list_cache')->set('index_recommend_'.$page.$row,$postListCache,10*60);
                }
                $postList=$postListCache['list'];
                if($postList!=false){
                    if($uid){
                        $selfHasChange=Cache::get('index_recommend_list_change_'.$uid);//有点赞、评论时该用户重新获取帖子的点赞评论数
                        if($selfHasChange>$postListCache['recache_time']){
                            $postList = ComThread::reGetSupportNum($postList);
                            if($postListCache['time_end']>time()){//有效期还有一段时间
                                $has_time=intval($postListCache['time_end'])-time();
                                $postListCache['list']=$postList;
                                $postListCache['recache_time']=time();
                                Cache::tag('thread_list_cache')->set('index_recommend_'.$page.$row,$postListCache,$has_time);
                            }
                        }
                    }
                    $postList=ComThread::initListUserRelation($postList);
                }
                $userList=array();
                $forum_list=array();
                break;
            case 'weibo':
                $postListCache=Cache::get('index_weibo_'.$page.$row);
                if(!$postListCache){
                    $postList = ComThread::getPostWeibo($page,$row);
                    $postListCache = ['list'=>$postList,'recache_time'=>time(),'time_end'=>time()+10*60];
                    Cache::tag('thread_list_cache')->set('index_weibo_'.$page.$row,$postListCache,10*60);
                }
                $postList=$postListCache['list'];
                $weibo_ids=array_column($postList,'id');
                ComThread::where('id','in',$weibo_ids)->setInc('view_count');
                if($postList!=false){
                    if($uid){
                        $selfHasChange=Cache::get('index_weibo_list_change_'.$uid);//有点赞、评论时该用户重新获取帖子的点赞评论数
                        if($selfHasChange>$postListCache['recache_time']){
                            $postList = ComThread::reGetSupportNum($postList);
                            if($postListCache['time_end']>time()){//有效期还有一段时间
                                $has_time=intval($postListCache['time_end'])-time();
                                $postListCache['list']=$postList;
                                $postListCache['recache_time']=time();
                                Cache::tag('thread_list_cache')->set('index_weibo_'.$page.$row,$postListCache,$has_time);
                            }
                        }
                    }
                    $postList=ComThread::initListUserRelation($postList);
                }
                $userList=array();
                $forum_list=array();
                break;
            case 'video':
                if($access[1] == '微信小程序' && $video_is_on==0){
                    $postList=array();
                    $userList=array();
                    $forum_list=array();
                }else{
                    $postListCache=Cache::get('index_video_'.$page.$row);
                    if(!$postListCache){
                        $postList = ComThread::getPostVideo($page,$row);
                        $postListCache = ['list'=>$postList,'recache_time'=>time(),'time_end'=>time()+10*60];
                        Cache::tag('thread_list_cache')->set('index_video_'.$page.$row,$postListCache,10*60);
                    }
                    $postList=$postListCache['list'];
                    if($postList!=false){
                        if($uid){
                            $selfHasChange=Cache::get('index_video_list_change_'.$uid);//有点赞、评论时该用户重新获取帖子的点赞评论数
                            if($selfHasChange>$postListCache['recache_time']){
                                $postList = ComThread::reGetSupportNum($postList);
                                if($postListCache['time_end']>time()){//有效期还有一段时间
                                    $has_time=intval($postListCache['time_end'])-time();
                                    $postListCache['list']=$postList;
                                    $postListCache['recache_time']=time();
                                    Cache::tag('thread_list_cache')->set('index_video_'.$page.$row,$postListCache,$has_time);
                                }
                            }
                        }
                        $postList=ComThread::initListUserRelation($postList);
                    }
                    $userList=array();
                    $forum_list=array();
                }
                break;
            default:
                $postListCache=Cache::get('index_recommend_'.$page.$row);
                if(!$postListCache){
                    $postList = ComThread::getPostRecommend($page,$row,$access,$video_is_on);
                    $postListCache = ['list'=>$postList,'recache_time'=>time(),'time_end'=>time()+10*60];
                    Cache::tag('thread_list_cache')->set('index_recommend_'.$page.$row,$postListCache,10*60);
                }
                $postList=$postListCache['list'];
                if($postList!=false){
                    if($uid){
                        $selfHasChange=Cache::get('index_recommend_list_change_'.$uid);//有点赞、评论时该用户重新获取帖子的点赞评论数
                        if($selfHasChange>$postListCache['recache_time']){
                            $postList = ComThread::reGetSupportNum($postList);
                            if($postListCache['time_end']>time()){//有效期还有一段时间
                                $has_time=intval($postListCache['time_end'])-time();
                                $postListCache['list']=$postList;
                                $postListCache['recache_time']=time();
                                Cache::tag('thread_list_cache')->set('index_recommend_'.$page.$row,$postListCache,$has_time);
                            }
                        }
                    }
                    $postList=ComThread::initListUserRelation($postList);
                }
                $userList=array();
                $forum_list=array();
                break;
        }
        $data['user']=$userList;
        $data['thread']=$postList;
        $data['forum']=$forum_list;
        $this->apiSuccess($data);
    }

    /**
     * 选择主题后请求获取帖子列表
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function threadList()
    {
        $uid = get_uid();
        $fid = input('post.fid/d', 0);
        $class_id = input('post.tid/d', 0);
        $type = input('post.type', 0);
        $need = input('post.need', '');
        $sort = input('post.sort', 'create_time desc');
        $page = input('post.page', 1);
        $row = input('post.row', 10);

        $list = ComThread::getThreadList($uid, $fid, $page, $row, $type,$need, $class_id, $sort);  //通用的获取帖子列表方法
        $this->apiSuccess($list);
    }

    /**
     * 社区首页版块列表，只到二级
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function forumList()
    {
        $page=input('page',1,'intval');
        $row=input('row',10,'intval');
        $data['list'] = ComForum::getForumSecond($page, $row); //获取版块列表，只包含一级和二级版块
        $this->apiSuccess($data);
    }

    /**
     * 发帖版块列表
     */
    public function forumSend()
    {
        $uid=get_uid();
        $ids=ComForumMember::where('uid',$uid)->where('status',1)->column('fid');
        $data['list']=ComForum::getForumSend(); //获取版块列表
        foreach($data['list'] as &$val){
            foreach($val['child_list'] as &$value){
                if(in_array($value['id'],$ids)){
                    $value['is_follow']=1;
                }else{
                    $value['is_follow']=0;
                }
            }
            unset($value);
        }
        unset($val);
        $this->apiSuccess($data);
    }

    /**
     * 发帖分类选择
     */
    public function forumSendClass()
    {
        $fid=input('forum_id','','intval');
        $data['list'] = ComThreadClass::getThreadClass($fid); //获取分类列表
        $this->apiSuccess($data);
    }

    /**
     * 社区分类版块一级效果
     */
    public function forumListOne()
    {
        $uid=get_uid();
        $ids=ComForumMember::where('uid',$uid)->where('status',1)->column('fid');
        $access=$this->access;
        $video_is_on=SystemConfig::getValue('xcx_video');
        $data['list'] = ComForum::getForumOne($access,$video_is_on); //获取版块列表
        foreach($data['list'] as &$val){
            foreach($val['child_list'] as &$value){
                if(in_array($value['id'],$ids)){
                    $value['is_follow']=1;
                }else{
                    $value['is_follow']=0;
                }
            }
            unset($value);
        }
        unset($val);
        $this->apiSuccess($data);
    }

    /**
     * 获取二级三级版块
     */
    public function forumListTwo(){
        $fid=input('forum_id','','intval');
        $page=input('page',1,'intval');
        $row=input('row',10,'intval');
        $data['list'] = ComForum::getForumTwo($fid,$page, $row); //获取版块列表
        $this->apiSuccess($data);
    }

    // 分享接口
    public function share()
    {
        $sharetype=osx_input('sharetype');
        $data = [
            'sharetype' => $sharetype,
            'uid'       => get_uid(),
            'create_time'=>time()
        ] ;
        db('user_share')->insert($data);
        //增加任务积分
        Gong::finishtask('fenxiang','user_share','uid') ;
        //增加行为积分
        Gong::actionadd('fenxiang','user_share','uid') ;
        //首次发帖
        Gong::firstaction('user_share','shoucifenxiang','uid');
    }

    /**
     * 普通发帖
     */
    public function sendThread()
    {
        $uid=$this->_needLogin();
        //判断是否是处于禁言
        if(Report::is_prohibit($uid)){
            $this->apiError('你正在处于禁言中');
        }
        //发帖上限
        action_power('send_thread_count',$uid);

        $fid = input('post.fid/d', 0);
        //版块设置发帖权限
        forum_power_error('send_thread',$uid,$fid);

        $old_uid = input('post.uid/d', 0);
        $class_id = input('post.class_id/d', 0);
        $type = input('post.type/d', 1);//对应get_thread_type()
        $title = input('post.title/s', '','text');
        $image = input('post.image', 0);
        $is_weibo = input('post.is_weibo/d', 0);
        $content = input('post.content', '','edit_filter'); //若不用该过滤方法，标签和图片无法转化为编码字符保存，而是直接被过滤
        $from = input('post.from', '','text');
        $video_id = input('post.video_id', '','text');//视频腾讯云上的id
        $video_cover = input('post.video_cover', '','text');//视频封面（可无）
        $topic = input('post.topic', '','text');
        $oid = input('post.oid', '','text');
        $video_url = input('post.video_url', '','text');
        $audio_url = input('post.audio_url', '','text');//音频地址
        $audio_id = input('post.audio_id', '','text');//视频腾讯云上的音频id
        $audio_time = input('post.audio_time', 0,'intval');//音频时长

        $id = input('post.id', 0);
        $product_id = input('post.product_id', 0);
        $fid = intval($fid);
        $product_count  = explode(",", $product_id);
        $product_count=count($product_count);
        if($product_count>0&&$product_id!=0){
            action_power('share_goods',$uid);
        }
        $is_prohibit=Prohibit::where('uid',$uid)->where('status',1)->where('fid',$fid)->where('end_time','>',time())->count();
        if($is_prohibit>0){
            $this->apiError(['info'=>'您在该版块已被禁言']);
        }
        if($id>0){
            $now_uid=get_uid();
            $old_fid = ComThread::where('id', $id)->value('fid');
            $old_uid = ComThread::where('id', $id)->value('author_uid');
            if($old_uid!=$now_uid){
                action_power('edit_thread',$now_uid);
                $is_admin=ComForum::_ForumAdmin($old_fid,$now_uid);
                if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
                    $this->apiError(['info'=>'权限不足，无法操作！']);
                }
            }else{
                action_power('edit_my_thread',$now_uid);
            }
        }
        $title_limit=SystemConfig::getValue('forum_num_limit');
        $title_limit_down=SystemConfig::getValue('forum_num_limit_down');
        $content_limit=SystemConfig::getValue('forum_content_limit');
        $content_limit_dowm=SystemConfig::getValue('forum_content_limit_down');
        $product_limit=SystemConfig::getValue('forum_product_limit');
        $post_time_limit=SystemConfig::getValue('post_time_limit');

        $limit_video_down=SystemConfig::getValue('video_content_down');
        $title_video_down=SystemConfig::getValue('video_title_down');
        $limit_video=SystemConfig::getValue('video_limit');
        $title_video=SystemConfig::getValue('video_title');
        $product_limit_video=SystemConfig::getValue('video_product');
        $content_video=SystemConfig::getValue('video_content');
        $limit_weibo_content=SystemConfig::getValue('weibo_content_limit');
        $limit_weibo_store=SystemConfig::getValue('weibo_store_limit');
        if($type==1&&$is_weibo==0){
            //普通帖子
            action_power('send_thread',$uid);

            if($post_time_limit > 0){
                $now_time=time()-86400;
                $time_count=ComThread::where('author_uid',$uid)->where('type',1)->where('create_time','>',$now_time)->where('from','neq','HouTai')->count();
                if($time_count>$post_time_limit){
                    $this->apiError(['info'=>'24小时内发帖次数超过上限，请稍后再来吧']);
                }
            }
            if (mb_strlen(text($title),'UTF-8') > $title_limit) {
                $this->apiError(['info'=>'标题字数超过后台设置字数上限:'.$title_limit]);
            }
            if (mb_strlen(text($title),'UTF-8') < $title_limit_down) {
                $this->apiError(['info'=>'标题字数不能少于'.$title_limit_down.'字']);
            }
            if($content_limit_dowm > 0){
                if (mb_strlen(text(htmlspecialchars_decode($content)),'UTF-8') < $content_limit_dowm) {
                    $this->apiError(['info'=>'内容字数不能少于'.$content_limit_dowm.'字']);
                }
            }
            if($content_limit > 0){
                if (mb_strlen(text(htmlspecialchars_decode($content)),'UTF-8') > $content_limit) {
                    $this->apiError(['info'=>'内容文字数超过后台设置字数上限:'.$content_limit]);
                }
            }
            if($product_limit > 0){
                if ($product_count > $product_limit){
                    $this->apiError(['info'=>'商品分享数量超过后台设置上限:'.$product_limit]);

                }
            }
        }elseif($type==6&&$is_weibo==0){
            //发视频权限
            action_power('send_video',$uid);
            //视频
            if($limit_video > 0){
                $now_time=time()-86400;
                $time_count=ComThread::where('author_uid',$uid)->where('type',6)->where('create_time','>',$now_time)->where('from','neq','HouTai')->count();
                if($time_count>$limit_video){
                    $this->apiError(['info'=>'24小时内发帖次数超过上限，请稍后再来吧']);
                }
            }
            if (mb_strlen(text($title),'UTF-8') > $title_video) {
                $this->apiError(['info'=>'标题字数超过后台设置字数上限:'.$title_video]);
            }
            if (mb_strlen(text($title),'UTF-8') < $title_video_down) {
                $this->apiError(['info'=>'标题字数不能少于'.$title_video_down.'字']);
            }
            if($limit_video_down>0){
                if (mb_strlen(text(htmlspecialchars_decode($content)),'UTF-8') < $limit_video_down) {
                    $this->apiError(['info'=>'内容字数不能少于'.$limit_video_down.'字']);
                }
            }
            if($content_video>0){
                if (mb_strlen(text(htmlspecialchars_decode($content)),'UTF-8') > $content_video) {
                    $this->apiError(['info'=>'内容文字数超过后台设置字数上限:'.$content_video]);
                }
            }
            if($product_limit_video > 0){
                if ($product_count > $product_limit_video) {
                    $this->apiError(['info'=>'商品分享数量超过后台设置上限:'.$product_limit_video]);
                }
            }
        }elseif($is_weibo==1){
            //发微博权限
            action_power('send_weibo',$uid);
            if($limit_weibo_content>0){
                if (mb_strlen(text(htmlspecialchars_decode($content)),'UTF-8') > $limit_weibo_content) {
                    $this->apiError(['info'=>'内容文字数超过后台设置字数上限:'.$limit_weibo_content]);
                }
            }
            if($limit_weibo_store > 0){
                if ($product_count > $limit_weibo_store) {
                    $this->apiError(['info'=>'商品分享数量超过后台设置上限:'.$limit_weibo_store]);
                }
            }
        }
        if(!get_thread_type($type)){
            $this->apiError(['info'=>'非法格式']);
        }
        if ($fid == '') {
            $this->apiError(['info'=>'缺少版块ID']);
        }
        if ($title == ''&&$is_weibo==0) {
            $this->apiError(['info'=>'请输入标题']);
        }
        $forum_info=ComForum::get($fid);
        if(!$forum_info||$forum_info['status']!=1){
            $this->apiError(['info'=>'版块不存在或已禁用']);
        }
        if($forum_info['is_private']==1){
            $is_member=ComForumMember::isForumUser($uid,$fid);
            if(!$is_member){
                $this->apiError(['info'=>'该版块为私密版块，非版块成员不能发帖']);
            }
        }
       /* if (strpos($_SERVER['HTTP_USER_AGENT'], 'miniprogram') !== false) {
            $token=Cache::get('miniprogram_token');
            if(!$token){
                $appId = SystemConfig::getValue('routine_appId');
                $appSecret = SystemConfig::getValue('routine_appsecret');
                $wx = new WechatAuth($appId,$appSecret);
                $token= $wx->getAccessToken();
                Cache::set('miniprogram_token',$token,7200);
            }
            $weixin['content']=$content;
            $url = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token=".$token['access_token'];
            $info = $this->http_request($url,json_encode($weixin,JSON_UNESCAPED_UNICODE));
            $info =  json_decode($info,true);
            if($info['errcode']==87014){
                $this->apiError(['info'=>'内容含有违法违规内容，请重新发布']);
            }
        }*/
        $content=Sensitive::sensitive($content,'社区帖子');
        $title=Sensitive::sensitive($title,'社区帖子');
        //判断是否是审核
        $is_audit=db('com_forum')->where(['id'=>$fid])->value('is_audit');
        $status=2;
        $info=',待审核';
        if($is_audit!=1&&action_power('audit',$uid)==1){
            $status=1;
            $info='';
        }
//        action_power('audit',$uid);
//        $status=$is_audit==1?2:1;
//        $info=$is_audit==1?',待审核':'';
        //写入帖子的内容
        if($is_weibo==1){
            $summary=$content;
        }else{
            $summary = mb_substr(text(strip_tags($content, '<p></p><br><span></span>')),0,60,'UTF-8'); //获取内容的前60个字符作为摘要
        }
        $data = [
            'id'=>$id,
            'author_uid' => $uid,
            'content' => $content,
            'fid' => $fid,
            'is_weibo' => $is_weibo,
            'class_id' => $class_id,
            'summary' => $summary,
            'type'=>$type,
            'title'=>$title,
            'from'=>$from,
            'image'=>$image,
            'product_id'=>$product_id,
            'video_url'=>$video_url,
            'audio_url'=>$audio_url,
            'audio_time'=>$audio_time,
            'audio_id'=>$audio_id,
            'status'=>$status,//2待审核，1发布
            'video_id'=>$video_id,//视频腾讯云上的id
            'video_cover'=>$video_cover,//视频封面（可无）
        ];
        TencentFile::uploadTencentVOD(['file_id'=>$video_id,'media_url'=>$video_url,'cover_url'=>$video_cover,'type'=>'video']);
        if($id>0){
            $data['create_time']=ComThread::where('id',$id)->value('create_time');
            $data['update_time']=time();
            $data['author_uid']=$old_uid;
        }else{
            $data['create_time']=time();
            $data['update_time']=time();
        }
        if($oid>0){
            $data['oid']=$oid;
        }
        if($oid==0 && $topic!=''){
            $topic=explode(',',$topic);$oid=array();
            foreach ($topic as $v){
                $topic_id=ComTopic::where('title',$v)->where('status','>',0)->value('id');
                if($topic_id){
                    $oid[]=$topic_id;
                }else{
                    $topic_data['uid']=$uid;
                    $topic_data['title']=$v;
                    $topic_data['status']=2;
                    $topic_data['create_time']=time();
                    $oid[]=ComTopic::add($topic_data);
                }
            }
            unset($v);
            $oid=implode(",",$oid);
            $data['oid']=$oid;
        }
        $result = ComThread::createThread($data); //新增帖子内容到数据库，事务写法，过程中涉及很多数据库操作
        if ($result) {

            if($id==0){
                Cache::rm('all_thread_count_follow_1');
                Cache::rm('all_thread_count_follow_2');
                Cache::rm('all_thread_count_recommend');
                Cache::rm('all_thread_count_weibo');
                Cache::rm('all_thread_count_video');
                db('user')->where('uid', $uid)->setInc('post_count');
                db('com_forum')->where('id', $fid)->setInc('post_count');
                if($oid>0){
                    db('com_topic')->where('id',$oid)->setInc('post_count');
                }
                $time=time()-86400;
                $newThread=ComThread::where('status',1)->where('fid',$fid)->where('create_time','>',$time)->limit(5)->order('create_time desc')->column('id');
                ComThread::where('fid',$data['fid'])->update(['is_new'=>0]);
                ComThread::where('id','in',$newThread)->update(['is_new'=>1]);
                $set1=MessageTemplate::getMessageSet(7);
                $nickname=UserModel::where('uid',$uid)->value('nickname');
                $set1['template']=str_replace('{用户昵称}', $nickname, $set1['template']);
                $fans_list=UserFollow::where('follow_uid',$uid)->where('status',1)->column('uid');
                $link_id=ComThread::where('id',$result)->value('post_id');
                if($set1['status']==1){
                    $message1=array();
                    $data2['from_uid']=$uid;
                    $data2['content']=$set1['template'];
                    $data2['type_id']=1;
                    $data2['title']=$set1['title'];
                    $data2['from_type']=1;
                    $data2['route']='thread';
                    $data2['link_id']=$link_id;
                    $data2['create_time']=time();
                    $data2['send_time']=time();
                    $map1=$data2;
                    foreach($fans_list as &$value){
                        $data2['to_uid']=$value;
                        $message1[]=$data2;
                    }
                    unset($value);
                    Message::insertAll($message1);
                    $message_list1=Message::where($map1)->select()->toArray();
                    $data3['is_read']=0;
                    if($set1['popup']==1){
                        $data3['is_popup']=0;
                    }else{
                        $data3['is_popup']=1;
                        $data3['popup_time']=time();
                    }
                    $data3['is_sms']=1;
                    $data3['sms_time']=time();
                    $data3['type']=1;
                    $data3['create_time']=time();
                    $message_read1=array();
                    foreach($message_list1 as &$item){
                        $data3['uid']=$item['to_uid'];
                        $data3['message_id']=$item['id'];
                        $message_read1[]=$data3;
                    }
                    MessageRead::insertAll($message_read1);
                }
                $is_admin=ComForum::where('id',$fid)->where('admin_uid',$uid)->count();
                if($is_admin){
                    $forum_list=ComForumMember::where('fid',$fid)->where('status',1)->where('uid','not in',$fans_list)->where('uid','neq',$uid)->column('uid');
                    $forum_name=ComForum::where('id',$fid)->value('name');
                    $length_name=mb_strlen($forum_name,'UTF-8');
                    if($length_name>4){
                        $forum_name=mb_substr($forum_name,0,4,'UTF-8').'…';
                    }
                    $set2=MessageTemplate::getMessageSet(8);
                    $set2['template']=str_replace('{版块名称}', $forum_name, $set2['template']);
                    $set2['template']=str_replace('{用户昵称}', $nickname, $set2['template']);
                    if($set1['status']==1){
                        $message2=array();
                        $data4['from_uid']=$uid;
                        $data4['content']=$set2['template'];
                        $data4['type_id']=1;
                        $data4['title']=$set2['title'];
                        $data4['from_type']=1;
                        $data4['route']='thread';
                        $data4['link_id']=$link_id;
                        $data4['create_time']=time();
                        $data4['send_time']=time();
                        $map2=$data4;
                        foreach($forum_list as &$value){
                            $data4['to_uid']=$value;
                            $message2[]=$data4;
                        }
                        unset($value);
                        Message::insertAll($message2);
                        $message_list2=Message::where($map2)->select()->toArray();
                        $data5['is_read']=0;
                        if($set2['popup']==1){
                            $data5['is_popup']=0;
                        }else{
                            $data5['is_popup']=1;
                            $data5['popup_time']=time();
                        }
                        $data5['is_sms']=1;
                        $data5['sms_time']=time();
                        $data5['type']=1;
                        $data5['create_time']=time();
                        $message_read2=array();
                        foreach($message_list2 as &$item){
                            $data5['uid']=$item['to_uid'];
                            $data5['message_id']=$item['id'];
                            $message_read2[]=$data5;
                        }
                        MessageRead::insertAll($message_read2);
                    }
                }

                //待审核的时候发送给版主超级版主待审核信息
                if($status==2){
                    //发送短信到版主
                    $pid=db('com_forum')->where('id',$fid)->value('pid');
                    $map['status']=1;
                    $map['fid']=['in',[$fid,$pid]];
                    $user=db('com_forum_admin')->where($map)->group('uid')->column('uid');
                    $set=MessageTemplate::getMessageSet(55);
                    foreach ($user as $v){
                        //发送消息
                        $template=$set['template'];
                        $now_uid=get_uid();
                        if($set['status']==1){
                            $message_id=Message::sendMessage($v,$now_uid,$template,1,$set['title'],1,'','thread_apply','');
                            $read_id=MessageRead::createMessageRead($v,$message_id,$set['popup'],1);
                        }
                        if($set['sms']==1&&$set['status']==1){
                            $account=UserModel::where('uid',$v)->value('phone');
                            $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                            $template='【'.$config['cl_sms_sign'].'】'.$template;
                            $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                            $sms=json_decode($sms,true);
                            if ($sms['code']==0) {
                                $read_data['is_sms']=1;
                                $read_data['sms_time']=time();
                                MessageRead::where('id',$read_id)->update($read_data);
                            }
                        }
                    }
                }
            }
            if($id==0){
                website_connect_notify($uid,$result,0,'osapi_com_sendThread');//通知第三方平台，任务回调


                //增加任务积分
                Gong::finishtask('fatie','com_thread','author_uid') ;
                //增加行为积分
                Gong::actionadd('fatie','com_thread','author_uid') ;
                //首次发帖
                Gong::firstaction('com_thread','shoucifatie','author_uid');
            }
            $res['thread_id']=$result;
            $res['info']='发布成功'.$info;
            Cache::rm('user_info_'.$uid);

            Cache::set('forum_post_has_new_fid_'.$fid.'_uid_'.$uid,time(),10*60);
            Cache::set('forum_post_has_new_fid_'.$fid.'_cid_'.$class_id.'_uid_'.$uid,time(),10*60);

            Cache::set('forum_index_top_detail_has_change_fid_'.$fid.'_uid_'.$uid,1,10*60);
            if($id){
                Cache::clear('thread_detail_tag_'.$id);//编辑帖子时清除帖子缓存
            }
            $this->apiSuccess($res);
        } else {
            //普通帖子
            action_power('send_thread',$uid);
            $data2 = [
                'author_uid' => $uid,
                'content' => $content,
                'fid' => $fid,
                'class_id' => $class_id,
                'summary' => $summary,
                'type'=>$type,
                'title'=>$title,
                'image'=>$image,
                'product_id'=>$product_id,
                'status'=>1,
            ];
            ComDraft::createDraft($data2);
            $error_info=ComThread::getErrorInfo();
            $this->apiError($error_info);
        }
    }


    private function http_request($url, $data = null)
    {
        $postUrl = $url;
        $curlPost = $data;
        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($curl, CURLOPT_HEADER, 0);//设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_POST, true);//post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS,$curlPost);//提交的参数
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($curl);//运行curl
        curl_close($curl);

        return $data;
    }

    public function draft()
    {
        $uid=$this->_needLogin();
        $fid = input('post.fid/d', 0);
        $class_id = input('post.class_id/d', 0);
        $type = input('post.type/d', 1);//对应get_thread_type()
        $title = input('post.title/s', '','text');
        $image = input('post.image', 0);
        $content = input('post.content', '','edit_filter'); //若不用该过滤方法，标签和图片无法转化为编码字符保存，而是直接被过滤
        $product_id = input('post.product_id', 0);
        $fid = intval($fid);
        if(!get_thread_type($type)){
            $this->apiError('非法格式');
        }
        if ($fid == '') {
            $this->apiError('缺少版块ID');
        }
        if ($title == '') {
            $this->apiError('请输入标题');
        }
        $forum_info=ComForum::get($fid);
        if(!$forum_info||$forum_info['status']!=1){
            $this->apiError('版块不存在或已禁用');
        }
        if($forum_info['is_private']==1){
            $is_member=ComForumMember::isForumUser($uid,$fid);
            if(!$is_member){
                $this->apiError('该版块为私密版块，非版块成员不能发帖');
            }
        }
        //写入内容
        $summary = mb_substr(text(strip_tags($content, '<p></p><br><span></span>')),0,40,'UTF-8'); //获取内容的前40个字符作为摘要
        $data = [
            'author_uid' => $uid,
            'content' => $content,
            'fid' => $fid,
            'class_id' => $class_id,
            'summary' => $summary,
            'type'=>$type,
            'title'=>$title,
            'image'=>$image,
            'product_id'=>$product_id,
            'status'=>1,
        ];
        $result = ComDraft::createDraft($data);
        if ($result) {
            $res['thread_id']=$result;
            $res['info']='草稿保存成功';
            $this->apiSuccess($res);
        } else {
            $error_info=ComThread::getErrorInfo();
            $this->apiError($error_info);
        }
    }

    public function draft_list(){
        $uid=$this->_needLogin();
        $data=ComDraft::where('author_uid',$uid)->where('status',1)->select();
        $this->apiSuccess($data);
    }

    public function draft_del(){
        $uid=$this->_needLogin();
        $id = input('post.id', 0);
        $type = input('post.type', 'one','text');
        if($id>0 && $type=='one'){
            $data['status']=-1;
            $res=ComDraft::where('id',$id)->update($data);
        }else{
            $data['status']=-1;
            $res=ComDraft::where('author_uid',$uid)->update($data);
        }
        if($res!==false){
            $this->apiSuccess('删除成功');
        }else{
            $this->apiSuccess('删除失败');
        }
    }

    /**
     * 版块首页信息获取
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function forumGet()
    {
        $uid = get_uid();
        $forumId=input('fid',0,'intval');
        $forum['forum'] = ComForum::getForumDetail($forumId, $uid);  //获取版块详情信息
        $this->apiSuccess($forum);
    }

    /**
     * 社区版块主页(论坛型)
     */
    public function forumIndex()
    {
        $uid = get_uid();
        $forumId=input('fid',0,'intval');
        $forum['forum'] = ComForum::getForumDetail($forumId, $uid);  //获取版块详情信息
        $tag='forum_other_info_fid_'.$forumId;
        $other_info=Cache::get($tag);
        if(!$other_info){
            $other_info['thread'] = ComThreadClass::getThreadClass($forumId);  //获取版块下的分类
            $other_info['notice'] = ComThread::getPostTop($forumId); //获取置顶帖子
            $other_info['detail_top'] = ComThread::getPostDetailTop($forumId); //获取置顶帖子
            Cache::set($tag,$other_info,10*60);
        }
        $forum['thread'] = $other_info['thread'];
        $forum['notice'] = $other_info['notice'];
        $forum['detail_top'] = $other_info['detail_top'];
        $this->apiSuccess($forum);
    }

    /**
     * 社区版块主页(论坛型)
     */
    public function getIndexTop()
    {
        $uid=get_uid();
        $other_info=Cache::get('com_index_top'.$uid);
        if(!$other_info){
            $other_info['index_top'] = ComThread::getPostIndexTop($uid); //获取置顶帖子
            Cache::tag('com_index_top')->set('com_index_top'.$uid,$other_info,10*60);
        }
        $data['index_top']=$other_info['index_top'];
        $this->apiSuccess($data);
    }

    /**
     * 社区版块主页帖子列表
     */
    public function forumPostList()
    {
        $forumId=input('fid',0,'intval');
        $type=input('type','hot','text');
        $tid=input('tid',0,'intval');
        $page=input('page',1,'intval');
        $row=input('row',10,'intval');
        $order=input('order','create_time','text');
        $forum=ComForum::getOne($forumId);
        $access=$this->access;
        $video_is_on=SystemConfig::getValue('xcx_video');
        switch ($type){
            case 'all':
                $forumList = ComThread::getPostAll($forumId,$page,$row,$order,$access,$video_is_on);
                break;
            case 'hot':
                $forumList = ComThread::getPostHot($forumId,$page,$row,$access,$video_is_on);
                break;
            case 'essence':
                $forumList = ComThread::getPostEssence($forumId,$page,$row,$access,$video_is_on);
                break;
            case 'class':
                $forumList = ComThread::getPostClass($forumId,$tid,$page,$row,$order,$access,$video_is_on);
                break;
            default:
                $forumList = ComThread::getPostAll($forumId,$page,$row,$order,$access,$video_is_on);
                break;
        }
        if($forum['type']==2){
            $weibo_ids=array_column($forumList['list'],'id');
            ComThread::where('id','in',$weibo_ids)->setInc('view_count');
        }
        $this->apiSuccess($forumList);
    }

    /**
     * 加入或退出版块
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function addToForum()
    {
        $uid=$this->_needLogin();
        $forumId=input('post.id',0,'intval');
        $action='audit';
        $power=forum_power($action,$uid,$forumId);
        if($power!=1){
            $fid=ComForum::where('id',$forumId)->field('id,logo,name')->find();
            $this->apiSuccess([$action=>$power,'fid'=>$fid],'获取权限成功');
        }
        $res=ComForumMember::addToForum($forumId,$uid);
        if($res){
            if($res===2){
                $this->apiError( '管理团队成员无法直接取关，请卸任后再试');
            }
            Cache::clear('add_forum_list');
            Cache::set('forum_index_top_detail_has_change_fid_'.$forumId.'_uid_'.$uid,1,10*60);
            //加入版块 加分
            $this->apiSuccess('操作成功！');
        }else{
            $this->apiError('操作失败！');
        }
    }

    /**
     * 获取版块下主题分类
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function threadClassList()
    {
        $fid = input('post.fid/d', 0);
        $thread = ComThreadClass::getThreadClass($fid); //获取主题分类
        $this->apiSuccess($thread);
    }


    /**
     * 发帖、发资讯、发动态、发活动
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function threadAdd()
    {
        $uid=$this->_needLogin();
        //判断是否是处于禁言
        if(Report::is_prohibit($uid)){
            $this->apiError('你正在处于禁言中');
        }
        $thread_id = input('post.post_id/d', 0);
        $forum_id = input('post.fid/d', 0);
        $thread_class_id = input('post.tid/d', 0);
        $type = input('post.type_id/d', 1);//对应get_thread_type()
        $title = input('post.title/s', '','text');
        $cover_id = input('post.cover_id/d', 0);
        $content = input('post.content', '','edit_filter'); //若不用该过滤方法，标签和图片无法转化为编码字符保存，而是直接被过滤
        $pos = input('post.pos/s', '','text');

        //判断是不是编辑模式
        $isEdit = $thread_id ? true : false;
        $forum_id = intval($forum_id);
        if (strlen(text(htmlspecialchars_decode($content))) < 20) {
            $this->apiError('内容文字数少于20个字符');
        }
        if(!get_thread_type($type)){
            $this->apiError('非法格式');
        }
        $checkTitle=$checkCover=0;
        $checkForum=1;
        switch ($type) {
            case 1://普通版面
                $checkTitle=1;
                break;
            case 4://资讯
                $checkTitle=1;
                $checkCover=1;
                break;
            case 5://活动
                $checkTitle=1;
                $checkCover=1;
                break;
            case 8://公告只有后台能发，这里只是提示，不会走到这一步
                $this->apiError('公告只有后台能发');
                $checkTitle=1;
                $checkForum=0;
                break;
            default:
                break;
        }
        if ($checkForum&&$forum_id == 0) {
            $this->apiError('缺少版块ID');
        }
        if ($checkTitle&&$title == '') {
            $this->apiError('请输入正确格式的标题');
        }
        if ($checkCover&&$cover_id == '0') {
            $this->apiError('请先上传封面');
        }
        $forum_info=ComForum::get($forum_id);
        if(!$forum_info||$forum_info['status']!=1){
            $this->apiError('版块不存在或已禁用');
        }
        if($forum_info['is_private']==1){
            $is_member=ComForumMember::isForumUser($uid,$forum_id);
            if(!$is_member){
                $this->apiError('该版块为私密版块，非版块成员不能发帖');
            }
        }
        //写入帖子的内容
        if ($isEdit) {
            //TODO
        } else {
            $summary = mb_substr(text(strip_tags($content, '<p></p><br><span></span>')),0,40,'UTF-8'); //获取内容的前40个字符作为摘要
            $data = [
                'author_uid' => $uid,
                'content' => $content,
                'fid' => $forum_id,
                'class_id' => $thread_class_id,
                'pos' => $pos,
                'summary' => $summary,
                'type'=>$type,
                'title'=>$title,
                'cover'=>get_img_info($cover_id,'path'),
                'is_announce'=>0,//公告只有后台能发
                'from'=>'phone',
                'status'=>$forum_info['need_verify']==1?2:1,//2待审核，1发布
                'create_time'=>time()
            ];
            $result = ComThread::createThread($data); //新增帖子内容到数据库，事务写法，过程中涉及很多数据库操作
            if ($result) {
                $res['thread_id']=$result;
                $res['info']='发布成功';
                $this->apiSuccess($res);
            } else {
                $error_info=ComThread::getErrorInfo();
                $this->apiError($error_info);
            }
        }
    }

    /**
     * 回复主题帖或者楼中楼评论
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function postAdd()
    {
        $uid=$this->_needLogin();
        //判断是否是处于禁言
        if(Report::is_prohibit($uid)){
            $this->apiError('你正在处于禁言中');
        }
        //发评论
        action_power('send_comment',$uid);

        $to_reply_uid = input('post.to_reply_uid/d', 0); //回复评论时的评论作者uid
        $thread_id = input('post.thread_id/d', '0'); //主题帖子id
        $thread=ComThread::get($thread_id);
        //版块内发评论权限
        $send_comment_power=db('forum_power')->where(['id'=>$thread['fid']])->value('send_comment');
        if(!($thread['author_uid']==$uid&&$send_comment_power==2)){
            forum_power_error('send_comment',$uid,$thread['fid']);
        }

        $to_reply_id = input('post.to_reply_id/d', 0); //对应楼层帖子post_id
        $content = input('post.content', '','');
        $is_black=Blacklist::isBlack($to_reply_uid,$uid);
        if ($is_black) {
            $this->apiError(['info'=>'由于对方的权限设置，您无法进行该操作']);
        }
        if (!$content) {
            $this->apiError(['info'=>'内容文字不能为空！']);
        }
        $level = ($to_reply_id != 0) ? 2 : 1; //判断是楼中楼评论还是楼层，$to_reply_id有值，说明是楼中楼评论
        if($to_reply_id){
            $postInfo=ComPost::get($to_reply_id);
            if(!$postInfo||$postInfo['status']!=1){
                $this->apiError(['info'=>'楼层不存在或已删除！']);
            }
            if(!$thread_id){
                $thread_id=$postInfo['tid'];
            }
            if($postInfo['tid']!=$thread_id){
                $this->apiError(['info'=>'非法操作！']);
            }
        }

        if(!$thread||$thread['status']!=1){
            $this->apiError(['info'=>'主题帖子不存在或已删除！']);
        }
        $content=Sensitive::sensitive($content,'社区评论');
        $data = [
            'author_uid' => $uid,
            'to_reply_uid' => $to_reply_uid,
            'fid' => $thread['fid'],
            'tid' => $thread_id,
            'content' => emoji_encode($content),
            'to_reply_id' => $to_reply_id,//对应楼层帖子id
            'level' => $level,
            'is_thread'=>0,
            'status'=>1,
            'create_time'=>time(),
            'from'=>'phone',
        ];
        $result = ComPost::createPost($data); //添加评论操作
        Cache::clear('recommend'.$uid);
        if ($result) {
            ComThread::where('id',$thread_id)->update(['last_post_time'=>time()]);
            $to_reply_thread_id=db('com_thread')->where('id',$thread_id)->value('post_id');
            $nickname=UserModel::where('uid',$uid)->value('nickname');
            if($uid!=$to_reply_uid){
                if($level==2){
                    $set=MessageTemplate::getMessageSet(6);
                    if($set['status']==1){
                        $template=str_replace('{用户昵称}', $nickname, $set['template']);
                        $message_id=Message::sendMessage($to_reply_uid,$uid,$template,2,$set['title'],2,'','reply',$to_reply_thread_id);
                        MessageRead::createMessageRead($to_reply_uid,$message_id,$set['popup'],2);
                    }
                }else{
                    $type=ComThread::where('id',$thread_id)->value('type');
                    if($type==1){
                        $set=MessageTemplate::getMessageSet(3);
                        if($set['status']==1){
                            $template=str_replace('{用户昵称}', $nickname, $set['template']);
                            $message_id=Message::sendMessage($to_reply_uid,$uid,$template,2,$set['title'],2,'','reply',$to_reply_thread_id);
                            MessageRead::createMessageRead($to_reply_uid,$message_id,$set['popup'],2);
                        }
                    }elseif($type==4){
                        $set=MessageTemplate::getMessageSet(4);
                        if($set['status']==1){
                            $template=str_replace('{用户昵称}', $nickname, $set['template']);
                            $message_id=Message::sendMessage($to_reply_uid,$uid,$template,2,$set['title'],2,'','reply',$to_reply_thread_id);
                            MessageRead::createMessageRead($to_reply_uid,$message_id,$set['popup'],2);
                        }
                    }elseif($type==6){
                        $set=MessageTemplate::getMessageSet(5);
                        if($set['status']==1){
                            $template=str_replace('{用户昵称}', $nickname, $set['template']);
                            $message_id=Message::sendMessage($to_reply_uid,$uid,$template,2,$set['title'],2,'','reply',$to_reply_thread_id);
                            MessageRead::createMessageRead($to_reply_uid,$message_id,$set['popup'],2);
                        }
                    }
                }
            }
            //加任务积分
            Gong::finishtask('pinglun','com_post','author_uid') ;
            //首次评论
            Gong::firstaction('com_post','shoucipinglun','author_uid') ;
            //行为加积分
            Gong::actionadd('pinglun','com_post','author_uid') ;
            $res['post_id']=$result;
            $res['info']='发布成功';
            census('comment',1);
            Cache::clear('thread_detail_tag_'.$thread_id);
            Cache::set('forum_post_has_new_fid_'.$thread['fid'].'_uid_'.$uid,time(),10*60);
            Cache::set('forum_post_has_new_fid_'.$thread['fid'].'_cid_'.$thread['class_id'].'_uid_'.$uid,time(),10*60);

            if($level==2){
                website_connect_notify($uid,$to_reply_id,$to_reply_uid,'osapi_com_postAdd_post');//通知第三方平台，任务回调
            }else{
                website_connect_notify($uid,$thread_id,$thread['author_uid'],'osapi_com_postAdd_thread');//通知第三方平台，任务回调
            }


            $this->apiSuccess($res);
        } else {
            $error_info=ComPost::getErrorInfo();
            $this->apiError($error_info);
        }
    }


    /**
     * 帖子详情中的主题帖子信息
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function threadInfo()
    {
        $thread_id = input('post.thread_id/d', 0);

        $data = ComThread::threadInfo($thread_id); //获取帖子详细信息
        //判断板块是否是私密版块
        $ids=ForumPower::get_private_id();
        if(in_array($data['fid'],$ids)){
            $now_login=$this->_needLogin();
        }else{
            $now_login=get_uid();
        }
        //访问权限
        forum_power_error('visit',$now_login,$data['fid']);
        //详情页判断浏览权限是否存在
        $uid=db('com_thread')->where(['id'=>$thread_id])->value('author_uid');
        if($uid!=$now_login){
            forum_power_error('browse_power',$now_login,$data['fid']);
        }
        $status=db('com_thread')->where(['id'=>$thread_id])->value('status');
        //是否是版主
        $is_admin=ComForum::_ForumAdmin($data['fid'],$now_login);
        if(!$data||!count($data)||$status==-1)  $this->apiError('该帖子已被删除');

        if($status==2&&$uid!=$now_login&&$is_admin['admin_one']!=1&&$is_admin['admin_two']!=1&&$is_admin['admin_three']!=1) $this->apiError('该帖子未审核或已删除');
        if($data){
            ComThread::viewOnce($thread_id);
            if($data['oid']){
                $oid=explode(',',$data['oid']);
                foreach($oid as &$value){
                    db('com_topic')->where('id',$value)->setInc('view_count');
                }
                unset($value);
            }
        }
        $this->apiSuccess($data);
    }

    /**
     * 主题详情中的评论信息，默认带上5条楼中楼评论
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function threadReply()
    {
        $page=input('post.page/d',1);
        $row=input('post.row/d',10);
        $type=input('post.type',1);
        $thread_id = input('post.tid/d', 0);
        $is_lord = input('post.lord/b', false);
        if($type==2){
            $sort='support_count desc,create_time desc';
        }else{
            $sort='create_time desc';
        }
        $list = ComPost::getThreadReplyList($thread_id ,$page, $is_lord, $row, $sort); //获取帖子的评论列表
        $this->apiSuccess($list);
    }

    /**
     * 主题评论详情信息，某一楼评论详情信息，不包含该楼层的楼中楼评论信息
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function threadReplyInfo()
    {
        $replyId = input('post.reply_id/d',0);
        $post = ComPost::getThreadReplyDetail($replyId); //获取帖子的评论列表
        $this->apiSuccess($post);
    }

    /**
     * 获取楼中楼回复列表
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function postReply()
    {
        $replyId = input('post.reply_id/d',0);
        $page=input('post.page/d',1);
        $row=input('post.row/d',10);
        $sort=input('post.sort','create_time asc','text');
        $list=ComPost::getPostReplyList($replyId,null,$page,$row,$sort);
        $this->apiSuccess($list);
    }

    /**
     * 帖子打赏
     */
    public function forum_post_reward()
    {
        $uid = $this->_needLogin();
        $data['uid'] = $uid; //当前用户id
        $data['pid'] = input('post.pid/d', 0); //被打赏帖子id
        $data['author_uid'] = input('post.uid/d', 0); //帖子作者id
        $data['amount'] = input('post.amount/d', 0); //打赏积分数量
        $data['type'] = input('post.type/d', 0); //打赏积分类型
        $data['content'] = input('post.content', 0, 'text'); //打赏时加的备注
        $re = UserReward::rewardPost($data); //对应的积分加减操作
        if ($re) {

            website_connect_notify($uid,$data['pid'],$data['author_uid'],'osapi_com_forum_post_reward');//通知第三方平台，任务回调
            $this->apiSuccess('打赏成功');
        } else {
            $this->apiError('打赏失败');
        }
    }

    /**
     * 获取公告
     */
    public function announce(){
        $list=db('com_announce')->where('status',1)->where('start_time','<',time())->limit(3)->select();
        $this->apiSuccess($list);
    }

    /**
     * 获取咨询列表
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.21
     */
    public function getNewsList(){
        $page=input('page',1);
        $limit=input('limit',10);
        $forumId=input('fid',0,'intval');
        $tid=input('tid',0,'intval');
        $type=input('type','hot','text');
        $order2=input('order','create_time','text');
        $map['type']=4;
        $map['status']=1;
        $map['fid']=$forumId;
        switch ($type){
            case 'hot':
                $order='view_count desc';
                $list = db('com_thread')->where($map)->page($page,$limit)->order($order)->select();
                foreach($list as &$value){
                    $value['user']=UserModel::getUserInfo($value['author_uid']);
                    $value['create_time']=time_to_show($value['create_time']);
                }
                unset($value);
                $totalCount=db('com_thread')->where($map)->count();
                break;
            case 'all':
                $order='create_time desc';
                $list = db('com_thread')->where($map)->page($page,$limit)->order($order)->select();
                foreach($list as &$value){
                    $value['user']=UserModel::getUserInfo($value['author_uid']);
                    $value['create_time']=time_to_show($value['create_time']);
                    $value['last_reply_time']=ComPost::where('tid',$value['id'])->where('is_thread',1)->where('status',1)->order('create_time desc')->value('create_time');
                }
                unset($value);
                if($order2=='reply_time'){
                    $last_reply_time = array_column($list,'last_reply_time');
                    array_multisort($last_reply_time,SORT_DESC,$list);
                }
                $totalCount=db('com_thread')->where($map)->count();
                break;
            case 'essence':
                $map['is_essence']=1;
                $order='create_time desc';
                $list = db('com_thread')->where($map)->page($page,$limit)->order($order)->select();
                foreach($list as &$value){
                    $value['user']=UserModel::getUserInfo($value['author_uid']);
                    $value['create_time']=time_to_show($value['create_time']);
                }
                unset($value);
                $totalCount=db('com_thread')->where($map)->count();
                break;
            case 'class':
                $map['class_id']=$tid;
                $order='create_time desc';
                $list = db('com_thread')->where($map)->page($page,$limit)->order($order)->select();
                foreach($list as &$value){
                    $value['user']=UserModel::getUserInfo($value['author_uid']);
                    $value['create_time']=time_to_show($value['create_time']);
                    $value['last_reply_time']=ComPost::where('tid',$value['id'])->where('is_thread',1)->where('status',1)->order('create_time desc')->value('create_time');
                }
                unset($value);
                if($order2=='reply_time'){
                    $last_reply_time = array_column($list,'last_reply_time');
                    array_multisort($last_reply_time,SORT_DESC,$list);
                }
                $totalCount=db('com_thread')->where($map)->count();
                break;
            default:
                $order='create_time desc';
                $list = db('com_thread')->where($map)->page($page,$limit)->order($order)->select();
                foreach($list as &$value){
                    $value['user']=UserModel::getUserInfo($value['author_uid']);
                    $value['create_time']=time_to_show($value['create_time']);
                    $value['last_reply_time']=ComPost::where('tid',$value['id'])->where('is_thread',1)->where('status',1)->order('create_time desc')->value('create_time');
                }
                unset($value);
                if($order2=='reply_time'){
                    $last_reply_time = array_column($list,'last_reply_time');
                    array_multisort($last_reply_time,SORT_DESC,$list);
                }
                $totalCount=db('com_thread')->where($map)->count();
                break;
        }
        $this->apiSuccess(['list'=>$list,'totalCount'=>$totalCount]);
    }

    /**
     * 资讯详情页
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.21
     */
    public function getNewsDetail(){
        $id=input('id',0);
        if(!$id){
            $this->apiError('请上传id');
        }
        $map['id']=$id;
        $data = db('com_thread')->where($map)->find();
        $data['user']=UserModel::getUserInfo($data['author_uid']);
        $this->apiSuccess($data);
    }

    /**
     * 删除帖子
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.21
     */
    public function deleteCom(){
        $id=input('id',0);
        $del_user=input('del_type','user','text');
        $fid = ComThread::where('id', $id)->value('fid');
        $uid = ComThread::where('id', $id)->value('author_uid');
        $now_uid=get_uid();
        if($uid!=$now_uid){
            action_power('delete_thread',$now_uid);
            $is_admin=ComForum::_ForumAdmin($fid,$now_uid);
            if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
                $this->apiError('权限不足，无法操作！');
            }
            if($is_admin['admin_two']==1){
                $data['operation_identity']=2;
            }elseif($is_admin['admin_three']==1){
                $data['operation_identity']=4;
            }else{
                $data['operation_identity']=3;
            }
            $data['operation_uid']=$now_uid;
        }
        $data['status']=-1;
        $data['del_user']=$del_user;
        $old_status=db('com_thread')->where(['id'=>$id])->value('status');
        $res= db('com_thread')->where(['id'=>$id])->update($data);
        $res2= db('com_post')->where(['tid'=>$id])->update(['status'=>-1]);
        if($res!==false&&$res2!==false){
            //扣除版块帖子数
            ComForum::where('id', $fid)->setDec('post_count');
            UserModel::where('uid', $uid)->setDec('post_count');
            if($del_user=='forum_admin'){
                db('com_forum_admin')->where('uid',$now_uid)->setInc('del');
                $set=MessageTemplate::getMessageSet(9);
                $time=time_format(time());
                $forum_name=ComForum::where('id',$fid)->value('name');
                //删除数量减少1
                if($old_status==1){
                    ComForum::where('id',$fid)->setDec('post_count',1);
                }
                $title = ComThread::where('id', $id)->value('title');
                $length_title=mb_strlen($title,'UTF-8');
                $length_name=mb_strlen($forum_name,'UTF-8');
                if($length_title>7){
                    $title=mb_substr($title,0,7,'UTF-8').'…';
                }
                if($length_name>4){
                    $forum_name=mb_substr($forum_name,0,4,'UTF-8').'…';
                }
                $template=str_replace('{年月日时分}', $time, $set['template']);
                $template=str_replace('{版块名称}', $forum_name, $template);
                $template=str_replace('{帖子标题}', $title, $template);
                if($set['status']==1){
                    $message_id=Message::sendMessage($uid,$now_uid,$template,1,$set['title'],1);
                    $read_id=MessageRead::createMessageRead($uid,$message_id,$set['popup'],1);
                }
                if($set['sms']==1&&$set['status']==1){
                    $account=UserModel::where('uid',$uid)->value('phone');
                    $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                    $template='【'.$config['cl_sms_sign'].'】'.$template;
                    $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                    $sms=json_decode($sms,true);
                    if ($sms['code']==0) {
                        $read_data['is_sms']=1;
                        $read_data['sms_time']=time();
                        MessageRead::where('id',$read_id)->update($read_data);
                    }
                }
            }
            //帖子被删除扣除积分
            $uid = db('com_thread')->where('id',$id)->value('author_uid') ;
            Gong::delaction('beishantie',$uid) ;

            if($del_user=='forum_admin') {//版主删除帖子
                website_connect_notify($now_uid,$id,$uid,'osapi_com_deleteCom_forum_admin');//通知第三方平台，任务回调
            }else{
                website_connect_notify($uid,$id,0,'osapi_com_deleteCom_user');//通知第三方平台，任务回调
            }
            Cache::rm('user_info_'.$uid);
            Cache::clear('thread_detail_tag_'.$id);
            Cache::clear('thread_list_cache');
            $this->apiSuccess('删除成功');
        }else{
            $this->apiSuccess('删除失败');
        }
    }

    /**
     * 删除评论
     */
    public function deleteComPost(){
        $id=input('id',0);
        $del_user=input('del_type','user','text');
        $uid = ComPost::where('id', $id)->value('author_uid');
        $fid = ComPost::where('id', $id)->value('fid');
        $now_uid=get_uid();
        if($uid!=$now_uid){
            action_power('delete_comment',$now_uid);
            $is_admin=ComForum::_ForumAdmin($fid,$now_uid);
            if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
                $this->apiError('权限不足，无法操作！');
            }
        }
        $res= db('com_post')->where(['id'=>$id])->update(['status'=>-1,'del_user'=>$del_user]);
        if($res!==false){
            if($del_user=='forum_admin'){
                $content= ComPost::where('id', $id)->value('content');
                $tid = ComPost::where('id', $id)->value('tid');
                $set=MessageTemplate::getMessageSet(10);
                $time=time_format(time());
                $title = ComThread::where('id', $tid)->value('title');
                if(!$title){
                    $title=db('com_thread')->where('id', $tid)->value('content');
                    $title=json_decode($title,true);
                }
                $length_title=mb_strlen($title,'UTF-8');
                $length_content=mb_strlen($content,'UTF-8');
                if($length_title>7){
                    $title=mb_substr($title,0,7,'UTF-8').'…';
                }
                if($length_content>4){
                    $content=mb_substr($content,0,4,'UTF-8').'…';
                }
                $template=str_replace('{年月日时分}', $time, $set['template']);
                $template=str_replace('{评论内容}', $content, $template);
                $template=str_replace('{帖子标题}', $title, $template);
                if($set['status']==1){
                    $message_id=Message::sendMessage($uid,$now_uid,$template,1,$set['title'],1);
                    $read_id=MessageRead::createMessageRead($uid,$message_id,$set['popup'],1);
                }
                if($set['sms']==1&&$set['status']==1){
                    $account=UserModel::where('uid',$uid)->value('phone');
                    $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                    $template='【'.$config['cl_sms_sign'].'】'.$template;
                    $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                    $sms=json_decode($sms,true);
                    if ($sms['code']==0) {
                        $read_data['is_sms']=1;
                        $read_data['sms_time']=time();
                        MessageRead::where('id',$read_id)->update($read_data);
                    }
                }
            }

            if($del_user=='forum_admin') {//版主删除帖子
                website_connect_notify($now_uid,$id,$uid,'osapi_com_deleteComPost_forum_admin');//通知第三方平台，任务回调
            }else{
                website_connect_notify($uid,$id,0,'osapi_com_deleteComPost_user');//通知第三方平台，任务回调
            }
            //帖子评论数量减少1
            $tid = ComPost::where('id', $id)->value('tid');
            db('com_thread')->where(['id'=>$tid])->setDec('reply_count',1);
            //上级评论数量减少1
            $tid = ComPost::where('id', $id)->value('to_reply_id');
            ComPost::where(['id'=>$tid])->setDec('comment_count',1);
            $this->apiSuccess('删除成功');
        }else{
            $this->apiSuccess('删除失败');
        }
    }

    /**
     * 举报帖子
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.25
     */
    public function reportForum(){
        $uid = $this->_needLogin();
        $data['uid']=$uid;
        $data['to_uid']=input('to_uid',0);
        if(!$data['to_uid']){
            $this->apiError('请选择举报用户');
        }
        $data['content']=input('content',0);
        if(!$data['content']){
            $this->apiError('请输入举报帖子');
        }

        $data['reason']=input('reason',0);
        $is_report=db('report')->where(['uid'=>$uid,'to_uid'=>$data['to_uid'],'content'=>$data['content'],'is_deal'=>0])->count();
        if($is_report>0){
            $this->apiError('该帖子你已经举报过了,管理员正在努力处理中');
        }
        $data['other_reason']=input('other_reason',0);
        if(!$data['reason']&&!$data['other_reason']){
            $this->apiError('请选择举报理由');
        }
        $forum=db('com_thread')->where(['id'=>$data['content']])->find();
        $data['plate']=$forum['fid'];
        $data['create_time']=time();
        $data['status']=1;
        $data['is_deal']=0;
        $type=db('com_thread')->where(['id'=>$data['content']])->field('is_weibo,type,oid')->find();
        switch ($type['type']){
            case 6:$type_value=2;break;
            case 7:$type_value=2;break;
            default:
                if($type['is_weibo']){
                    $type_value=5;
                }elseif ($type['oid']>0){
                    $type_value=6;
                }else{
                    $type_value=1;
                }

        }
        $data['type']=$type_value;
        $res=Report::addForumReport($data);
        if($res){
            website_connect_notify($uid,$data['content'],$data['to_uid'],'osapi_com_reportForum');//通知第三方平台，任务回调
            $this->apiSuccess('举报成功');
        }else{
            $this->apiSuccess('举报失败');
        }
    }

    /**
     * 获取投诉原因
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.26
     */
    public function getReportReason(){
        $data=Report::getReportReasonList();
        $this->apiSuccess($data);
    }
    /**
     * 举报帖子评论
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.25
     */
    public function reportComment(){
        $uid = $this->_needLogin();
        $data['uid']=$uid;
        $data['to_uid']=input('to_uid',0);
        if(!$data['to_uid']){
            $this->apiError('请选择举报用户');
        }
        $data['content']=input('content',0);
        if(!$data['content']){
            $this->apiError('请输入举报评论');
        }
        $is_report=db('report')->where(['uid'=>$uid,'to_uid'=>$data['to_uid'],'content'=>$data['content'],'is_deal'=>0])->count();
        if($is_report>0){
            $this->apiError('该评论你已经举报过了,管理员正在努力处理中');
        }
        $data['reason']=input('reason',0);
        $data['other_reason']=input('other_reason',0);
        if(!$data['reason']&&!$data['other_reason']){
            $this->apiError('请选择举报理由');
        }
        $forum=db('com_post')->where(['id'=>$data['content']])->value('fid');
//        $forum=db('com_post')->where(['id'=>$forum['fid']])->find();
        $data['plate']=$forum;
        $data['create_time']=time();
        $data['status']=1;
        $data['is_deal']=0;
        $data['type']=3;
        $res=Report::addForumReport($data);
        if($res){
            website_connect_notify($uid,$data['content'],$data['to_uid'],'osapi_com_reportComment');//通知第三方平台，任务回调
            $this->apiSuccess('举报成功');
        }else{
            $this->apiError('举报失败');
        }
    }

    /**
     * 版主管理
     */
    public function management(){
        $type=input('type','recommend','text');
        $is_on=input('is_on',1);
        $id = input('id',0);
        $end_time=input('end_time/d',0);
        $end_time=$end_time*86400;
        $end_time=time()+$end_time;
        $uid=$this->_needLogin();
        $thread= ComThread::where('id',$id)->find()->toArray();
        $is_admin=ComForum::_ForumAdmin($thread['fid'],$uid);
        if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
            $this->apiError('权限不足，无法操作！');
        }
        $forum_name=ComForum::where('id',$thread['fid'])->value('name');
        if($thread['is_weibo']==1){
            $thread['content']=json_decode($thread['content']);
            $length_title=mb_strlen($thread['content'],'UTF-8');
            if($length_title>7){
                $thread_sns=mb_substr($thread['content'],0,7,'UTF-8').'…';
            }else{
                $thread_sns=$thread['content'];
            }
        }else{
            $length_title=mb_strlen($thread['title'],'UTF-8');
            if($length_title>7){
                $thread_sns=mb_substr($thread['title'],0,7,'UTF-8').'…';
            }else{
                $thread_sns=$thread['title'];
            }
        }
        $length_name=mb_strlen($forum_name,'UTF-8');
        if($length_name>4){
            $forum_name=mb_substr($forum_name,0,4,'UTF-8').'…';
        }
        $time=time_format(time());
        $now_uid=get_uid();
        $link_id=ComThread::where('id',$id)->value('post_id');
        switch($type){
            case 'essence':
                action_power('add_digest',$uid);
                if($is_on==1){
                    $essence_num=ComThread::where('is_essence',1)->where('essence_uid',$uid)->whereTime('essence_time','today')->where('status',1)->count();
                    if($is_admin['admin_two']==1){
                        $super_admin_num=SystemConfig::getValue('super_forum_admin_essence');
                        if($essence_num>=$super_admin_num){
                            $this->apiError('超级版主日加精数超出后台限制，无法再加精');
                        }
                    }else{
                        $admin_num=SystemConfig::getValue('forum_admin_essence');
                        if($essence_num>=$admin_num){
                            $this->apiError('版主日加精数超出后台限制，无法再加精');
                        }
                    }
                }
                $data['essence_uid']=$uid;
                $data['essence_time']=time();
                $data['is_essence']=$is_on;
                $res=ComThread::where('id',$id)->update($data);
                Cache::rm('forum_other_info_fid_'.$thread['fid']);
                Cache::rm('thread_detail_'.$id);
                Cache::set('forum_post_has_new_fid_'.$thread['fid'].'_uid_'.$now_uid,time(),10*60);
                if($is_on==1){
                    $guanzhu = db('system_rule_action')->where('actionflag','beijiajing')->find();
                    Support::addjifen($guanzhu,1,$thread['author_uid']);
                    $set=MessageTemplate::getMessageSet(11);
                    $template=str_replace('{年月日时分}', $time, $set['template']);
                    $template=str_replace('{版块名称}', $forum_name, $template);
                    $template=str_replace('{帖子标题}', $thread_sns, $template);
                    if($set['status']==1){
                        $message_id=Message::sendMessage($thread['author_uid'],$now_uid,$template,1,$set['title'],1,'','thread',$link_id);
                        $read_id=MessageRead::createMessageRead($thread['author_uid'],$message_id,$set['popup'],1);
                    }
                    if($set['sms']==1&&$set['status']==1){
                        $account=UserModel::where('uid',$thread['author_uid'])->value('phone');
                        $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                        $template='【'.$config['cl_sms_sign'].'】'.$template;
                        $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                        $sms=json_decode($sms,true);
                        if ($sms['code']==0) {
                            $read_data['is_sms']=1;
                            $read_data['sms_time']=time();
                            MessageRead::where('id',$read_id)->update($read_data);
                        }
                    }
                }
                if($res===false){
                    $this->apiError('加精失败！');
                }else{
                    db('com_forum_admin')->where('uid',$now_uid)->setInc('essence');
                    if($is_on==1){
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_essence');//通知第三方平台，任务回调
                    }else{
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_un_essence');//通知第三方平台，任务回调
                    }

                    $this->apiSuccess('加精成功！');
                }
                break;
            case 'top':
                action_power('set_top',$uid,['set_top'=>1]);
                if($is_on==1){
                    $top_num=ComThread::where('is_top',1)->where('top_uid',$uid)->whereTime('top_time','today')->where('status',1)->count();
                    if($is_admin['admin_two']==1){
                        $super_admin_num=SystemConfig::getValue('super_forum_admin_top');
                        if($top_num>=$super_admin_num){
                            $this->apiError('超级版主日置顶数超出后台限制，无法再置顶');
                        }
                    }else{
                        $admin_num=SystemConfig::getValue('forum_admin_top');
                        if($top_num>=$admin_num){
                            $this->apiError('版主日置顶数超出后台限制，无法再置顶');
                        }
                    }
                }
                $data['is_top']=$is_on;
                $data['top_time']=time();
                $data['top_uid']=$uid;
                $data['top_end_time']=$end_time;
                $is_top=ComThread::where('id',$id)->value('detail_top');
                if($is_top==1){
                    $this->apiError('该帖子已详情置顶！');
                }
                $res=ComThread::where('id',$id)->update($data);
                Cache::rm('forum_other_info_fid_'.$thread['fid']);
                Cache::rm('thread_detail_'.$id);
                Cache::set('forum_post_has_new_fid_'.$thread['fid'].'_uid_'.$now_uid,time(),10*60);
                if($is_on==1){
                    $guanzhu = db('system_rule_action')->where('actionflag','beizhiding')->find();
                    Support::addjifen($guanzhu,1,$thread['author_uid']);
                    $set=MessageTemplate::getMessageSet(12);
                    $template=str_replace('{年月日时分}', $time, $set['template']);
                    $template=str_replace('{版块名称}', $forum_name, $template);
                    $template=str_replace('{帖子标题}', $thread_sns, $template);
                    if($set['status']==1){
                        $message_id=Message::sendMessage($thread['author_uid'],$now_uid,$template,1,$set['title'],1,'','thread',$link_id);
                        $read_id=MessageRead::createMessageRead($thread['author_uid'],$message_id,$set['popup'],1);
                    }
                    if($set['sms']==1&&$set['status']==1){
                        $account=UserModel::where('uid',$thread['author_uid'])->value('phone');
                        $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                        $template='【'.$config['cl_sms_sign'].'】'.$template;
                        $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                        $sms=json_decode($sms,true);
                        if ($sms['code']==0) {
                            $read_data['is_sms']=1;
                            $read_data['sms_time']=time();
                            MessageRead::where('id',$read_id)->update($read_data);
                        }
                    }
                }
                if($res===false){
                    $this->apiError('置顶失败！');
                }else{
                    db('com_forum_admin')->where('uid',$now_uid)->setInc('top');

                    if($is_on==1){
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_top');//通知第三方平台，任务回调
                    }else{
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_un_top');//通知第三方平台，任务回调
                    }
                    $this->apiSuccess('置顶成功！');
                }
                break;
            case 'detail_top':
                action_power('set_top',$uid,['set_top'=>2]);
                if($is_on==1){
                    $top_num=ComThread::where('detail_top',1)->where('detail_top_uid',$uid)->whereTime('detail_top_time','today')->where('status',1)->count();
                    if($is_admin['admin_two']==1){
                        $super_admin_num=SystemConfig::getValue('super_forum_admin_detail_top');
                        if($top_num>=$super_admin_num){
                            $this->apiError('超级版主日详情置顶数超出后台限制，无法再置顶');
                        }
                    }else{
                        $admin_num=SystemConfig::getValue('forum_admin_detail_top');
                        if($top_num>=$admin_num){
                            $this->apiError('版主日详情置顶数超出后台限制，无法再置顶');
                        }
                    }
                }
                $data['detail_top']=$is_on;
                $data['detail_top_uid']=$uid;
                $data['detail_top_time']=time();
                $data['detail_top_end_time']=$end_time;
                $is_top=ComThread::where('id',$id)->value('is_top');
                if($is_top==1){
                    $this->apiError('该帖子已普通置顶！');
                }
                if($is_on==1){
                    $old_fid=ComThread::where('id',$id)->value('fid');
                    ComThread::where('detail_top',1)->where('fid',$old_fid)->update(['detail_top'=>0]);
                }
                $res=ComThread::where('id',$id)->update($data);
                Cache::rm('forum_other_info_fid_'.$thread['fid']);
                Cache::rm('thread_detail_'.$id);
                Cache::set('forum_post_has_new_fid_'.$thread['fid'].'_uid_'.$now_uid,time(),10*60);
                if($is_on==1){
                    $guanzhu = db('system_rule_action')->where('actionflag','beizhiding')->find();
                    Support::addjifen($guanzhu,1,$thread['author_uid']);
                    $set=MessageTemplate::getMessageSet(12);
                    $template=str_replace('{年月日时分}', $time, $set['template']);
                    $template=str_replace('{版块名称}', $forum_name, $template);
                    $template=str_replace('{帖子标题}', $thread_sns, $template);
                    if($set['status']==1){
                        $message_id=Message::sendMessage($thread['author_uid'],$now_uid,$template,1,$set['title'],1,'','thread',$link_id);
                        $read_id=MessageRead::createMessageRead($thread['author_uid'],$message_id,$set['popup'],1);
                    }
                    if($set['sms']==1&&$set['status']==1){
                        $account=UserModel::where('uid',$thread['author_uid'])->value('phone');
                        $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                        $template='【'.$config['cl_sms_sign'].'】'.$template;
                        $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                        $sms=json_decode($sms,true);
                        if ($sms['code']==0) {
                            $read_data['is_sms']=1;
                            $read_data['sms_time']=time();
                            MessageRead::where('id',$read_id)->update($read_data);
                        }
                    }
                }
                if($res===false){
                    $this->apiError('置顶失败！');
                }else{
                    db('com_forum_admin')->where('uid',$now_uid)->setInc('top');

                    if($is_on==1){
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_detail_top');//通知第三方平台，任务回调
                    }else{
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_un_detail_top');//通知第三方平台，任务回调
                    }
                    $this->apiSuccess('置顶成功！');
                }
                break;
            case 'index_top':
                action_power('set_top',$uid,['set_top'=>3]);
                if($is_on==1){
                    $top_num=ComThread::where('index_top',1)->where('index_top_uid',$uid)->whereTime('index_top_time','today')->where('status',1)->count();
                    if($is_admin['admin_two']==1){
                        $super_admin_num=SystemConfig::getValue('super_forum_admin_index_top');
                        if($top_num>=$super_admin_num){
                            $this->apiError('超级版主日首页置顶数超出后台限制，无法再置顶');
                        }
                    }else{
                        $admin_num=SystemConfig::getValue('forum_admin_index_top');
                        if($top_num>=$admin_num){
                            $this->apiError('版主日首页置顶数超出后台限制，无法再置顶');
                        }
                    }
                }
                $data['index_top']=$is_on;
                $data['index_top_uid']=$uid;
                $data['index_top_time']=time();
                $data['index_top_end_time']=$end_time;
                if($is_on==1){
                    $index_top_count=ComThread::where('index_top', 1)->count();
                    if($index_top_count>4){
                        $first_id=ComThread::where('index_top', 1)->order('index_top_time asc')->value('id');
                        ComThread::where('id',$first_id)->update(['index_top' => 0]);
                    }
                }
                $res=ComThread::where('id',$id)->update($data);
                Cache::clear('com_index_top');
                Cache::clear('thread_list_cache');
                Cache::rm('thread_detail_'.$id);
                Cache::rm('forum_other_info_fid_'.$thread['fid']);
                Cache::set('forum_post_has_new_fid_'.$thread['fid'].'_uid_'.$now_uid,time(),10*60);
                if($is_on==1){
                    $guanzhu = db('system_rule_action')->where('actionflag','beizhiding')->find();
                    Support::addjifen($guanzhu,1,$thread['author_uid']);
                    $set=MessageTemplate::getMessageSet(12);
                    $template=str_replace('{年月日时分}', $time, $set['template']);
                    $template=str_replace('{版块名称}', $forum_name, $template);
                    $template=str_replace('{帖子标题}', $thread_sns, $template);
                    if($set['status']==1){
                        $message_id=Message::sendMessage($thread['author_uid'],$now_uid,$template,1,$set['title'],1,'','thread',$link_id);
                        $read_id=MessageRead::createMessageRead($thread['author_uid'],$message_id,$set['popup'],1);
                    }
                    if($set['sms']==1&&$set['status']==1){
                        $account=UserModel::where('uid',$thread['author_uid'])->value('phone');
                        $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                        $template='【'.$config['cl_sms_sign'].'】'.$template;
                        $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                        $sms=json_decode($sms,true);
                        if ($sms['code']==0) {
                            $read_data['is_sms']=1;
                            $read_data['sms_time']=time();
                            MessageRead::where('id',$read_id)->update($read_data);
                        }
                    }
                }
                if($res===false){
                    $this->apiError('置顶失败！');
                }else{
                    db('com_forum_admin')->where('uid',$now_uid)->setInc('top');

                    if($is_on==1){
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_index_top');//通知第三方平台，任务回调
                    }else{
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_un_index_top');//通知第三方平台，任务回调
                    }
                    $this->apiSuccess('置顶成功！');
                }
                break;
            case 'recommend':
                action_power('recommend',$uid);
                if($is_on==1){
                    $recommend_num=ComThread::where('is_recommend',1)->where('recommend_uid',$uid)->whereTime('recommend_time','today')->where('status',1)->count();
                    if($is_admin['admin_two']==1){
                        $super_admin_num=SystemConfig::getValue('super_forum_admin_recommend');
                        if($recommend_num>=$super_admin_num){
                            $this->apiError('超级版主日推荐数超出后台限制，无法再推荐');
                        }
                    }else{
                        $admin_num=SystemConfig::getValue('forum_admin_recommend');
                        if($recommend_num>=$admin_num){
                            $this->apiError('版主日推荐数超出后台限制，无法再推荐');
                        }
                    }
                }
                $data['is_recommend']=$is_on;
                $data['recommend_time']=time();
                $data['recommend_uid']=$uid;
                $data['recommend_end_time']=$end_time;
                $res=ComThread::where('id',$id)->update($data);
                Cache::rm('forum_other_info_fid_'.$thread['fid']);
                Cache::rm('thread_detail_'.$id);
                Cache::clear('thread_list_cache');
                Cache::set('forum_post_has_new_fid_'.$thread['fid'].'_uid_'.$now_uid,time(),10*60);
                if($is_on==1){
                    $guanzhu = db('system_rule_action')->where('actionflag','beituijian')->find();
                    Support::addjifen($guanzhu,1,$thread['author_uid']);
                }
                if($res===false){
                    $this->apiError('推荐失败！');
                }else{
                    db('com_forum_admin')->where('uid',$now_uid)->setInc('recommend');

                    if($is_on==1){
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_recommend');//通知第三方平台，任务回调
                    }else{
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_un_recommend');//通知第三方平台，任务回调
                    }
                    $this->apiSuccess('推荐成功！');
                }
                break;
            case 'light':
                action_power('height_line',$uid);
                $data['high_light']=$is_on;
                $data['light_time']=time();
                $data['light_end_time']=$end_time;
                $res=ComThread::where('id',$id)->update($data);
                Cache::rm('forum_other_info_fid_'.$thread['fid']);
                Cache::rm('thread_detail_'.$id);
                Cache::set('forum_post_has_new_fid_'.$thread['fid'].'_uid_'.$now_uid,time(),10*60);
                if($res===false){
                    $this->apiError('加粗失败！');
                }else{
                    db('com_forum_admin')->where('uid',$now_uid)->setInc('light');
                    if($is_on==1){
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_light');//通知第三方平台，任务回调
                    }else{
                        website_connect_notify($now_uid,$id,$thread['author_uid'],'osapi_com_management_un_light');//通知第三方平台，任务回调
                    }
                    $this->apiSuccess('加粗成功！');
                }
                break;
        }
    }

    /**
     * 版主判断
     */
    public function is_forum_admin(){
        $fid=osx_input('fid',0,'intval');
        $comment_id=osx_input('comment_id',0,'intval');
        $uid=get_uid();
        $data=ComForum::_ForumAdmin($fid,$uid,$comment_id);
        $this->apiSuccess($data);
    }


    /**
     * 热门话题列表
     */
    public function hot_topic_list(){
        $class_id=osx_input('post.class_id','','intval');
        $list=Cache::get('index_hot_topic_list_'.$class_id);
        if(!$list){
            $list=ComTopic::hotTopicList($class_id);
            Cache::tag('index_hot_topic_list')->set('index_hot_topic_list_'.$class_id,$list,3600);
        }
        $this->apiSuccess($list);
    }

    /**
     * 话题列表
     */
    public function topic_list(){
        $page=osx_input('post.page',1,'intval');
        $row=osx_input('post.row',10,'intval');
        $class_id=osx_input('post.class_id','','intval');
        $list=Cache::get('index_topic_list_'.$page.'_'.$row.'_'.$class_id);
        if(!$list){
            $list=ComTopic::TopicList($page,$row,$class_id);
            Cache::tag('index_topic_list')->set('index_topic_list_'.$page.'_'.$row.'_'.$class_id,$list,600);
        }
        $this->apiSuccess($list);
    }

    /**
     * 话题分类
     */
    public function topic_class_list(){
        $list=Cache::get('topic_class_list');
        if(!$list){
            $list=db('com_topic_class')->where('status',1)->order('sort asc')->select();
            Cache::set('topic_class_list',$list,3600);
        }
        $this->apiSuccess($list);
    }

    /**
     * 话题详情
     */
    public function topic_detail(){
        $id=osx_input('post.id','','intval');
        $topic=Cache::get('topic_detail'.$id);
        if(!$topic){
            $topic=ComTopic::detailTopic($id);
            Cache::set('topic_detail'.$id,$topic,3600);
        }
        $this->apiSuccess($topic);
    }

    /**
     * 话题动态列表
     */
    public function topic_weibo_list(){
        $id=osx_input('post.id','','intval');
        $type=osx_input('post.type','','text');
        $uid=get_uid();
        $page=osx_input('post.page',1,'intval');
        $row=osx_input('post.row',10,'intval');
        $postListCache=Cache::get('index_topic_weibo_'.$page.'_'.$row.'_'.$id.$type);
        if(!$postListCache){
            $postList = ComThread::getTopicWeibo($page,$row,$id,$type);
            $postListCache = ['list'=>$postList,'recache_time'=>time(),'time_end'=>time()+10*60];
            Cache::tag('thread_list_cache')->set('index_topic_weibo_'.$page.'_'.$row.'_'.$id.$type,$postListCache,10*60);
        }
        $postList=$postListCache['list'];
        $weibo_ids=array_column($postList,'id');
        ComThread::where('id','in',$weibo_ids)->setInc('view_count');
        if($postList!=false){
            if($uid){
                $selfHasChange=Cache::get('index_weibo_list_change_'.$uid);//有点赞、评论时该用户重新获取帖子的点赞评论数
                if($selfHasChange>$postListCache['recache_time']){
                    $postList = ComThread::reGetSupportNum($postList);
                    if($postListCache['time_end']>time()){//有效期还有一段时间
                        $has_time=intval($postListCache['time_end'])-time();
                        $postListCache['list']=$postList;
                        $postListCache['recache_time']=time();
                        Cache::tag('thread_list_cache')->set('index_topic_weibo_'.$page.'_'.$row.'_'.$id.$type,$postListCache,$has_time);
                    }
                }
            }
            $postList=ComThread::initListUserRelation($postList);
        }
        $this->apiSuccess($postList);
    }

    /**
     * 发布话题
     */
    public function sendTopic(){
        $class=Cache::get('topic_class_list');
        if(!$class){
            $class=db('com_topic_class')->where('status',1)->order('sort asc')->select();
            Cache::set('topic_class_list',$class,3600);
        }
        foreach ($class as &$val){
            $val['topic']=ComTopic::SendTopicList($val['id']);
        }
        $this->apiSuccess($class);
    }

    /**
     * qhy
     * 版主申请
     */
    public function forumAdminApply(){
        $reason=osx_input('post.reason','','text');
        $fid=osx_input('post.fid','','intval');
        $uid=$this->_needLogin();
        $is_admin=ComForumAdmin::where('fid',$fid)->where('status',1)->where('uid',$uid)->count();
        if($is_admin>0){
            $this->apiError('您已经是该板块版主，无法再提交审核');
        }
        $follow=ComForumMember::where('status',1)->where('fid',$fid)->where('uid',$uid)->find();
        if(!$follow){
            $this->apiError('请先关注版块，再提交审核');
        }
        $apply=ComForumAdminApply::where('fid',$fid)->where('uid',$uid)->where('status',1)->find();
        if($apply){
            $this->apiError('已经提交申请，请等待审核通过');
        }
        $res=ComForumAdminApply::forumAdminApply($reason,$fid,$uid);
        if($res!==false){
            $this->apiSuccess('申请成功，请等待审核通过');
        }else{
            $this->apiError('申请失败');
        }
    }

    /**
     * qhy
     * 版主列表
     */
    public function forumAdminList(){
        $fid=osx_input('post.fid','','intval');
        $list=ComForumAdmin::getForumAdminList($fid);
        $this->apiSuccess($list);
    }

    /**
     * qhy
     * 我管理的版块列表
     */
    public function myForumAdmin(){
        $uid=$this->_needLogin();
        $list=ComForumAdmin::getMyForumAdmin($uid);
        $this->apiSuccess($list);
    }

    /**
     * qhy
     * 版块成员管理列表
     */
    public function ForumAdminMember(){
        $fid=osx_input('post.fid','','intval');
        $status=osx_input('post.status',1,'intval');
        $page = osx_input('page',1,'intval');
        $row = osx_input('row', 10,'intval');
        $type=osx_input('post.type','all','text');
        $uid=$this->_needLogin();
        action_power('group_manage',$uid);
        switch($type){
            case 'all':
                $fids=self::_ForumAdminAll($uid);
                $list=VisitAudit::ForumAdminMemberAll($fids,$status,$page,$row);
                break;
            default:
                $is_admin=ComForum::_ForumAdmin($fid,$uid);
                if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
                    $this->apiError('权限不足，无法操作！');
                }
                $list=VisitAudit::ForumAdminMember($fid,$status,$page,$row);
                break;
        }

        $this->apiSuccess($list);
    }

    /**
     * qhy
     * 成员审核
     */
    public function ForumAdminMemberApply(){
        $id=osx_input('post.id','','text');
        $status=osx_input('post.status',1,'intval');
        $is_move=osx_input('post.is_move',0,'intval');
        $reason=osx_input('post.reason','','text');
        $now_uid=$this->_needLogin();
        $ids=explode(",",$id);
        $fid=db('com_forum_member')->where('id',$ids[0])->value('fid');
        $is_admin=ComForum::_ForumAdmin($fid,$now_uid);

        action_power('audit_visit',$now_uid);

        if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
            $this->apiError('权限不足，无法操作！');
        }
        $admin_uid=ComForumAdmin::where('status',1)->column('uid');
        $uids=VisitAudit::where('id','in',$ids)->column('uid');
        foreach($uids as $v){
            if(in_array($v,$admin_uid)){
                $this->apiError('无法对管理组成员进行此操作');
            }
        }
        unset($v);
        $res=VisitAudit::ForumAdminMemberApply($ids,$status,$now_uid,$reason,$is_move);
        if($res!==false){
            ForumPower::clear_cache();
            $this->apiSuccess('操作成功');
        }else{
            $this->apiSuccess('操作失败');
        }
    }

    /**
     * qhy
     * 帖子审核列表
     */
    public function ForumAdminThread(){
        $fid=osx_input('post.fid','','intval');
        $page = osx_input('page',1,'intval');
        $row = osx_input('row', 10,'intval');
        $type=osx_input('post.type','all','text');
        $uid=$this->_needLogin();
        switch($type){
            case 'all':
                $fids=self::_ForumAdminAll($uid);
                $list=ComThread::getThreadApplyAll($page,$row,$fids);
                break;
            default:
                $is_admin=ComForum::_ForumAdmin($fid,$uid);
                if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
                    $this->apiError('权限不足，无法操作！');
                }
                $list=ComThread::getThreadApply($page,$row,$fid);
                break;
        }
        $this->apiSuccess($list);
    }

    /**
     * qhy
     * 帖子审核
     */
    public function ForumAdminThreadApply(){
        $id=osx_input('post.id','','text');
        $status=osx_input('post.status',1,'intval');
        $reason=osx_input('post.reason','','text');
        $now_uid=$this->_needLogin();
        $ids=explode(",",$id);
        ComThread::beginTrans();
        foreach($ids as &$v){
            $thread=db('com_thread')->where(['id'=>$v])->find();

            $is_admin=ComForum::_ForumAdmin($thread['fid'],$now_uid);
            //版主审核权限
            action_power('audit_content',$now_uid);
            if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
                $this->apiError('权限不足，无法操作！');
            }
            $res=ComThread::ForumAdminThreadApply($v,$thread,$now_uid,$is_admin,$status,$reason);
            if($res===false){
                ComThread::rollbackTrans();
                $this->apiError('操作失败');
            }
            //出现新的标签
            $time=time()-86400;
            $newThread=db('com_thread')->where('status',1)->where('fid',$thread['fid'])->where('create_time','>',$time)->limit(5)->order('create_time desc')->column('id');
            db('com_thread')->where('fid',$thread['fid'])->update(['is_new'=>0]);
            db('com_thread')->where('id','in',$newThread)->update(['is_new'=>1]);

        }
        unset($v);
        ComThread::commitTrans();

        $this->apiSuccess('操作成功');

    }

    /**
     * qhy
     * 版主申请列表
     */
    public function ForumAdminApplyList(){
        $fid=osx_input('post.fid','','intval');
        $page = osx_input('page',1,'intval');
        $row = osx_input('row', 10,'intval');
        $type=osx_input('post.type','all','text');
        $uid=$this->_needLogin();
        switch($type){
            case 'all':
                $adminTwo=ComForumAdmin::where('uid',$uid)->where('status',1)->where('level',2)->column('fid');
                if(!$adminTwo){
                    $this->apiError('权限不足，无法操作！');
                }
                $fids=ComForum::where('pid','in',$adminTwo)->where('status',1)->column('id');
                $list=ComForumAdminApply::ForumAdminApplyListAll($fids,$page,$row);
                break;
            default:
                $is_admin=ComForum::_ForumAdmin($fid,$uid);
                if($is_admin['admin_two']==0 && $is_admin['admin_three']==0){
                    $this->apiError('权限不足，无法操作！');
                }
                $list=ComForumAdminApply::ForumAdminApplyList($fid,$page,$row);
                break;
        }
        $this->apiSuccess($list);
    }

    /**
     * qhy
     * 版主审核
     */
    public function ApplyForumAdmin(){
        $id=osx_input('post.id','','intval');
        $status=osx_input('post.status',2,'intval');
        $reason=osx_input('post.reason','','text');
        $now_uid=$this->_needLogin();
        $fid=ComForumAdminApply::where('id',$id)->value('fid');
        $is_admin=ComForum::_ForumAdmin($fid,$now_uid);
        if($is_admin['admin_two']==0 && $is_admin['admin_three']==0){
            $this->apiError('权限不足，无法操作！');
        }
        $res=ComForumAdminApply::ApplyForumAdmin($id,$status,$reason);
//        //版主审核权限
//        action_power('audit_admin',$now_uid);
        if($res!==false){
            $forum_name=ComForum::where(['id'=>$fid])->value('name');
            $uid=ComForumAdminApply::where('id',$id)->value('uid');
            if($status==2){
                //发送消息
                $set=MessageTemplate::getMessageSet(52);
                $template=str_replace('{版块名称}', $forum_name, $set['template']);
                if($set['status']==1){
                    $message_id=Message::sendMessage($uid,$now_uid,$template,1,$set['title'],1);
                    $read_id=MessageRead::createMessageRead($uid,$message_id,$set['popup'],1);
                }
                if($set['sms']==1&&$set['status']==1){
                    $account=UserModel::where('uid',$uid)->value('phone');
                    $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                    $template='【'.$config['cl_sms_sign'].'】'.$template;
                    $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                    $sms=json_decode($sms,true);
                    if ($sms['code']==0) {
                        $read_data['is_sms']=1;
                        $read_data['sms_time']=time();
                        MessageRead::where('id',$read_id)->update($read_data);
                    }
                }
            }
            if($status==0){
                //发送消息
                $set=MessageTemplate::getMessageSet(53);
                $template=str_replace('{驳回原因}', $reason, $set['template']);
                if($set['status']==1){
                    $message_id=Message::sendMessage($uid,$now_uid,$template,1,$set['title'],1);
                    $read_id=MessageRead::createMessageRead($uid,$message_id,$set['popup'],1);
                }
                if($set['sms']==1&&$set['status']==1){
                    $account=UserModel::where('uid',$uid)->value('phone');
                    $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                    $template='【'.$config['cl_sms_sign'].'】'.$template;
                    $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
                    $sms=json_decode($sms,true);
                    if ($sms['code']==0) {
                        $read_data['is_sms']=1;
                        $read_data['sms_time']=time();
                        MessageRead::where('id',$read_id)->update($read_data);
                    }
                }
            }
            $this->apiSuccess('操作成功');
        }else{
            $this->apiSuccess('操作失败');
        }
    }

    /**
     * qhy
     * 禁言列表
     */
    public function prohibitList(){
        $fid=osx_input('post.fid','','intval');
        $type=osx_input('post.type','all','text');
        $page = osx_input('page',1,'intval');
        $row = osx_input('row', 10,'intval');
        $uid=$this->_needLogin();
        switch($type){
            case 'all':
                $fids=self::_ForumAdminAll($uid);
                $list=Prohibit::prohibitListAll($fids,$page,$row);
                break;
            default:
                $is_admin=ComForum::_ForumAdmin($fid,$uid);
                if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
                    $this->apiError('权限不足，无法操作！');
                }
                $list=Prohibit::prohibitList($fid,$page,$row);
                break;
        }
        $this->apiSuccess($list);
    }

    /**
     * qhy
     * 解除禁言
     */
    public function RelieveProhibit(){
        $id=osx_input('post.id','','intval');
        $now_uid=$this->_needLogin();
        $fid=db('prohibit')->where('id',$id)->value('fid');
        $is_admin=ComForum::_ForumAdmin($fid,$now_uid);
        action_power('forum_prohibit',$now_uid);
        if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
            $this->apiError('权限不足，无法操作！');
        }
        $res=Prohibit::RelieveProhibit($id,$now_uid,$is_admin);
        if($res!==false){
            $this->apiSuccess('操作成功');
        }else{
            $this->apiSuccess('操作失败');
        }
    }

    /**
     * 禁言
     */
    public function prohibit(){
        $fid=osx_input('fid',0,'intval');
        $other_reason=osx_input('other_reason','','text');
        $prohibit_time=osx_input('prohibit_time',0,'intval');
        $prohibit_reason=osx_input('prohibit_reason',0,'intval');
        $prohibit_uid=osx_input('uid',0,'intval');
        $now_uid=$this->_needLogin();
        $is_admin=ComForum::_ForumAdmin($fid,$now_uid);
        if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
            $this->apiError('权限不足，无法操作！');
        }
        $user_is_admin=ComForum::_ForumAdmin($fid,$prohibit_uid);
        if($user_is_admin['admin_one']==1 || $user_is_admin['admin_two']==1 || $user_is_admin['admin_three']==1){
            $this->apiError('无法禁言管理组成员');
        }
        action_power('forum_prohibit',$now_uid);
        $res=Prohibit::doProhibit($is_admin,$now_uid,$prohibit_time,$prohibit_uid,$other_reason,$prohibit_reason,$fid);
        if($res===false){
            $this->apiError('禁言失败！');
        }else{
            $this->apiSuccess('禁言成功！');
        }
    }

    /**
     * qhy
     * 禁言时间和理由
     */
    public function ProhibitType(){
        $data['reason']=db('prohibit_reason')->where('status',1)->order('sort desc')->select();
        $data['time']=db('report_prohibit')->where('status',1)->order('sort desc')->select();
        $this->apiSuccess($data);
    }

    /**
     * qhy
     * 管理版块信息
     */
    public function ForumInfoAdmin(){
        $fid=osx_input('post.fid','','intval');
        $type=osx_input('post.type','all','text');
        $uid=$this->_needLogin();
        switch($type){
            case 'all':
                $fids=self::_ForumAdminAll($uid);
                $yesterday['fans']=ComForumMember::where('fid','in',$fids)->where('status',1)->whereTime('create_time','yesterday')->count();
                $yesterday['thread']=ComThread::where('fid','in',$fids)->where('status',1)->whereTime('create_time','yesterday')->count();
                $yesterday['post']=ComPost::where('fid','in',$fids)->where('status',1)->where('is_thread',0)->whereTime('create_time','yesterday')->count();
                $all['fans']=ComForumMember::where('fid','in',$fids)->where('status',1)->count();
                $all['thread']=ComThread::where('fid','in',$fids)->where('status',1)->count();
                $all['post']=ComPost::where('fid','in',$fids)->where('status',1)->where('is_thread',0)->count();
                $data['member']=db('com_forum_member')->where('status',2)->where('fid','in',$fids)->count();
                $data['thread']=ComThread::where('status',2)->where('fid','in',$fids)->count();
                $data['admin']=db('com_forum_admin_apply')->where('status',1)->where('fid','in',$fids)->count();
                $data['prohibit']=db('prohibit')->where('status',1)->where('end_time','>',time())->where('fid','in',$fids)->count();
                break;
            default:
                $is_admin=ComForum::_ForumAdmin($fid,$uid);
                if($is_admin['admin_one']==0 && $is_admin['admin_two']==0 && $is_admin['admin_three']==0){
                    $this->apiError('权限不足，无法操作！');
                }
                $yesterday['fans']=ComForumMember::where('fid',$fid)->where('status',1)->whereTime('create_time','yesterday')->count();
                $yesterday['thread']=ComThread::where('fid',$fid)->where('status',1)->whereTime('create_time','yesterday')->count();
                $yesterday['post']=ComPost::where('fid',$fid)->where('status',1)->where('is_thread',0)->whereTime('create_time','yesterday')->count();
                $all['fans']=ComForumMember::where('fid',$fid)->where('status',1)->count();
                $all['thread']=ComThread::where('fid',$fid)->where('status',1)->count();
                $all['post']=ComPost::where('fid',$fid)->where('status',1)->where('is_thread',0)->count();
                $data['user']=db('com_forum_member')->where('status',1)->where('fid',$fid)->count();
                $data['member']=db('com_forum_member')->where('status',2)->where('fid',$fid)->count();
                $data['thread']=ComThread::where('status',2)->where('fid',$fid)->count();
                if($is_admin['admin_two']==1){
                    $data['admin']=db('com_forum_admin_apply')->where('status',1)->where('fid',$fid)->count();
                }else{
                    $data['admin']=false;
                }
                $data['prohibit']=db('prohibit')->where('status',1)->where('end_time','>',time())->where('fid',$fid)->count();
                break;
        }
        $list['yesterday']=$yesterday;
        $list['all']=$all;
        $list['data']=$data;
        $this->apiSuccess($list);
    }

    private function _ForumAdminAll($uid){
        //判断是否是管理组
        $is_admin=db('bind_group_uid')->where(['uid'=>$uid,'g_id'=>2,'status'=>1,'end_time'=>[['eq',0],['gt',time()],'or']])->count();
        if($is_admin){
            $fids=ComForum::where('status',1)->column('id');
        }else{
            $is_forum_admin=ComForumAdmin::where('uid',$uid)->where('status',1)->find();
            if(!$is_forum_admin){
                $this->apiError('权限不足，无法操作！');
            }
            $adminOne=ComForumAdmin::where('uid',$uid)->where('status',1)->where('level',1)->column('fid');
            $adminTwo=ComForumAdmin::where('uid',$uid)->where('status',1)->where('level',2)->column('fid');
            $adminTwoFid=ComForum::where('pid','in',$adminTwo)->where('status',1)->column('id');
            $fids=array_merge($adminOne,$adminTwoFid);
        }
        return $fids;
    }


    /**
     * qhy
     * 成员管理搜索
     */
    public function searchForumMember(){
        $key=osx_input('post.key','','text');
        $fid=osx_input('post.fid','','intval');
        $uids = db('user')->where('nickname','like','%'.$key.'%')->column('uid');
        $admin_uid=ComForumAdmin::where('status',1)->column('uid');
        $list=ComForumMember::where('fid',$fid)->where('status',1)->where('uid','not in',$admin_uid)->where('uid','in',$uids)->limit(10)->order('create_time desc')->select()->toArray();
        $forum=ComForum::where('id',$fid)->field('id,name,type')->find()->toArray();
        foreach($list as $k=>&$value){
            $value['forum_name']=$forum;
            $value['user'] = UserModel::getUserInfo($value['uid']);  //获取用户信息
            //如果用户不存在，则过滤该条记录
            if($value['user']===null){
                unset($list[$k]);
            }
        }
        unset($k);
        unset($value);
        $list=array_values($list);
        $data['list']=$list;
        $data['count']=ComForumAdmin::where('fid',$fid)->where('status',1)->where('uid','not in',$admin_uid)->where('uid','in',$uids)->count();
        $this->apiSuccess($data);
    }

}