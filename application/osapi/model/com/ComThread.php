<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/30
 * Time: 13:10
 */

namespace app\osapi\model\com;


use app\admin\model\com\ForumPower;
use app\commonapi\model\TencentFile;
use app\ebapi\model\read\Collect;
use app\osapi\model\BaseModel;
use app\osapi\model\common\Support;
use app\osapi\model\common\Blacklist;
use app\osapi\model\user\UserFollow;
use app\osapi\model\user\UserModel;
use think\Cache;
use app\admin\model\system\SystemConfig;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;

class ComThread extends BaseModel
{
    /**
     * todo
     * 迁移说明
     * pid-》id
     * abstract-》summary
     * commert_count->reply_count
     * is_recommend->is_top
     * 新的缺失data转发数据记录
     */
    protected static $field_list= 'id,fid,type,false_view,is_announce,post_id,read_perm,author_uid,title,is_weibo,oid,detail_top,index_top,is_new,create_time,image,from,last_post_time,last_post_uid,update_time,view_count,reply_count,class_id,cover,status,sort,support_count,share_count,collect_count,high_light,is_essence,is_top,attachment_id,is_verify,stick_reply,summary,pos,position,product_id,is_massage,video_id,video_cover,video_url,audio_id,audio_url,audio_time,light_end_time,is_recommend,recommend_end_time,index_top_end_time,detail_top_end_time,top_end_time';
    /**
     * 获取帖子列表，need用于区分是全部、精华还是推荐
     * @param int $uid
     * @param $fid 版块id
     * @param int $page
     * @param int $row
     * @param int $type 帖子类型;对应get_thread_ype();
     * @param string $need
     * @param int $class_id
     * @param string $sort
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getThreadList($uid = 0, $fid, $page = 1, $row = 10, $type = 0,$need='', $class_id = 0, $sort = 'create_time desc')
    {
        $map['status']=1;
        if(get_thread_type($type)){//按帖子类型筛选
            $map['type']=$type;
        }
        if ($class_id != 0) {  //如有分类ID，则查取具体分类下的帖子
            $map['class_id']=$class_id;
            /**
             * todo
             * 迁移说明
             * 不存在position（帖子位置）字段
             */
            //$map['position']=['>',0];
        } else {  //如没有分类ID，则查取版块下的帖子或者无版块ID时，获取推荐帖子列表
            if ($fid != 0) {  //有版块ID
                $map['fid']=$fid;
                switch ($need){
                    case 'get_essence'://精华
                        $map['is_essence']=1;
                        break;
                    case 'get_top'://推荐、置顶
                        $map['is_top']=1;
                        break;
                    default://全部
                        break;
                }
            } else {  //获取推荐帖子
                $map['is_top']=1;
            }
        }
        $threadList = self::where($map)->order($sort)->field(self::$field_list)->where('is_massage',0)->page($page, $row)->select()->toArray();
        $count_num = self::where($map)->count();
        $threadList = self::threadListHandleStart($threadList,false); //帖子列表进行
        $threadList = self::_threadFriendsHandle($threadList);

        return ['list'=>$threadList,'totalCount'=>$count_num];
    }

    /**
     * 详情页专用 帖子信息处理：列表页不要调用该方法，调用threadListHandleStart()
     * @param $thread
     * @return null
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private static function _threadHandle($thread)
    {
        $thread=json_decode($thread,true);
        $thread['content'] = json_decode($thread['content']);
        if($thread['oid']){
            $oid=explode(',',$thread['oid']);
            $topic=array();
            foreach ($oid as &$v){
                $topic[]=ComTopic::getTopicTitle($v);
            }
            $thread['topic']=$topic;
            unset($v);
        }
        if($thread['recommend_end_time']<time()){
            $thread['is_recommend']=0;
        }
        if($thread['top_end_time']<time()){
            $thread['is_top']=0;
        }
        if($thread['detail_top_end_time']<time()){
            $thread['detail_top']=0;
        }
        if($thread['index_top_end_time']<time()){
            $thread['index_top']=0;
        }
        if($thread['is_announce']==1 || $thread['type']==4 || $thread['from']=='HouTai'){
        }else{
            $thread['content'] = content_show($thread['content']);
        }
        if($thread['summary']==''){//容错处理，对于历史没有简介的帖子进行简介赋值
            $thread['summary']= mb_substr(text(strip_tags(json_decode($thread['content']), '<p></p><br><span></span>')),0,40,'UTF-8'); //获取内容的前40个字符作为摘要
        }
        $thread['summary'] = str_replace('&nbsp;','',$thread['summary']);
        $thread['forum'] = ComForum::getOne($thread['fid']);
        $thread['last_reply_time']=$thread['last_post_time'];
        $thread['user'] = UserModel::getUserInfo($thread['author_uid'],$thread['fid']);  //获取用户信息
        if ($thread['user'] == null) {  //若帖子的用户被删除时候，帖子也从列表中删除
            return null;
        }
        $thread=self::_threadHandleCom($thread);
        return $thread;
    }
    public static function setListCache($uid)
    {
        Cache::set('index_recommend_list_change_'.$uid,time(),10*60);
        Cache::set('index_weibo_list_change_'.$uid,time(),10*60);
        Cache::set('index_video_list_change_'.$uid,time(),10*60);
        Cache::set('index_follow_list_change_'.$uid,time(),10*60);
        Cache::set('index_no_follow_list_change_'.$uid,time(),10*60);
        Cache::clear('thread_list_cache');

        Cache::set('forum_post_list_change_uid_'.$uid,time(),10*60);
        return true;
    }

    /**
     * 帖子列表信息处理入口：针对不需要做缓存的列表，调用该入口
     * @param $list
     * @param bool $add_level
     * @return array|mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function threadListHandleStart($list,$add_level=false){
        $list=self::threadListHandle($list);
        $list=self::initListUserRelation($list,$add_level);
        return $list;
    }

    /**
     * 帖子列表公用部分信息处理：针对需要做缓存的列表，直接调用该入口
     * @param $list
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function threadListHandle($list)
    {
        $fids=array_column($list,'fid');
        $forumList=db('com_forum')->where('id','in',$fids)->field('id,name,type')->select();
        $forumList=array_combine(array_column($forumList,'id'),$forumList);
        foreach ($list as $key=>&$val){
            if($val['oid']){
                $oid=explode(',',$val['oid']);
                $topic=array();
                foreach ($oid as &$v){
                    $topic[]=ComTopic::getTopicTitle($v);
                }
                $val['topic']=$topic;
                unset($v);
            }
            if($val['recommend_end_time']<time()){
                $val['is_recommend']=0;
            }
            if($val['top_end_time']<time()){
                $val['is_top']=0;
            }
            if($val['detail_top_end_time']<time()){
                $val['detail_top']=0;
            }
            if($val['index_top_end_time']<time()){
                $val['index_top']=0;
            }
            $val['forum']=isset($forumList[$val['fid']])?$forumList[$val['fid']]:null;
            $val['last_reply_time']=$val['last_post_time'];
            $val['user'] = UserModel::getUserInfo($val['author_uid'],$val['fid']);  //获取用户信息
            if ($val['user'] == null) {  //若帖子的用户被删除时候，帖子也从列表中删除
                unset($list[$key]);
                continue;
            }
            $val=self::_threadHandleCom($val);
            if($val['video_id']){
                $val['psign']=self::_videoKey($val['video_id']);
            }
            if($val['video_url']){
                $getYunConfig = TencentFile::ifYunUpload();
                $val['video_url']=TencentFile::yunKeyMediaUrl($val['video_url'],$getYunConfig['pkey']);
            }
        }
        unset($val);
        return $list;
    }

    private static function _videoKey($video_id){
        $getYunConfig = TencentFile::ifYunUpload();
        //Header加密
        $Header['alg']='HS256';
        $Header['typ']='JWT';
        $Header=json_encode($Header,JSON_UNESCAPED_UNICODE);
        $Header_base64=self::base64UrlEncode($Header);
        //UrlAccessInfo转换成json
        $tr=TencentFile::expireTime();
        $getRandom = md5(uniqid(microtime(true),true));
        $urlAccessInfo['t']=''.$tr;
        $urlAccessInfo['rlimit']=3;
        $urlAccessInfo['us']=$getRandom;
        //PayLoad加密
        $Payload['appId']=intval($getYunConfig['appid']);;
        $Payload['fileId']=$video_id;
        $Payload['currentTimeStamp']=time();
        $Payload['expireTimeStamp']=time()+10800;
        $Payload['urlAccessInfo']=$urlAccessInfo;
        $Payload=json_encode($Payload,JSON_UNESCAPED_UNICODE);
        $Payload_base64=self::base64UrlEncode($Payload);

        //获得签名并加密
        $Signature = hash_hmac("sha256", $Header_base64.'.'.$Payload_base64, $getYunConfig['pkey'],true);
        $Signature_base64=self::base64UrlEncode($Signature);
        $token=$Header_base64.'.'.$Payload_base64.'.'.$Signature_base64;
        return $token;
    }

    /**
     * base64UrlEncode   https://jwt.io/  中base64UrlEncode编码实现
     * @param string $input 需要编码的字符串
     * @return string
     */
    private static function base64UrlEncode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }


    /**
     * 帖子列表中个人相关部分信息处理：针对需要做缓存的列表，直接调用该入口
     * @param $threadList
     * @param bool $add_level
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function initListUserRelation($threadList,$add_level=true)
    {
        $uid=get_uid();
        $threadListNew=[];
        if($uid==0){
            foreach ($threadList as $val){
                $val['is_collect'] = false;
                $val['is_support'] = false;  //判断当前用户是否对帖子点过赞
                $val['is_black'] = 0;
                $val['user']['is_follow'] = false;
                $val['create_time']=time_to_show($val['create_time']);
                if($add_level){
                    $threadListNew[]=[$val];//前端需要再套一层
                }else{
                    $threadListNew[]=$val;
                }
            }
        }else{
            $tids=array_column($threadList,'id');
            $is_collect_ids=db('collect')->where('uid',$uid)->where('tid','in',$tids)->where('status',1)->column('tid');
            $post_ids=array_column($threadList,'post_id');
            $is_support_ids=db('support')->where('model','thread')->where('uid',$uid)->where('status',1)->where('row','in',$post_ids)->column('row');
            $author_uids=array_column($threadList,'author_uid');
            $is_follow_uids=db('user_follow')->where('uid',$uid)->where('follow_uid','in',$author_uids)->where('status',1)->column('follow_uid');
            $is_black_uids=Blacklist::where('uid',$uid)->where('status',1)->column('black_uid');
            foreach ($threadList as $val){
                $val['is_collect'] = in_array($val['id'],$is_collect_ids)?1:0;
                $val['is_support'] = in_array($val['post_id'],$is_support_ids)?true:false;;  //判断当前用户是否对帖子点过赞
                $val['user']['is_follow'] = in_array($val['author_uid'],$is_follow_uids)?true:false;;  //判断当前用户是否关注
                $val['user']['is_black'] = in_array($val['author_uid'],$is_black_uids)?1:0;
                $val['create_time']=time_to_show($val['create_time']);
                if($add_level){
                    $threadListNew[]=[$val];//前端需要再套一层
                }else{
                    $threadListNew[]=$val;
                }
            }
        }
        return $threadListNew;
    }

    /**
     * 单个帖子部分公用部分信息处理，全部公用部分信息需要调用threadListHandle()
     * @param $thread
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private static function _threadHandleCom($thread)
    {

        if($thread['summary']==''){//容错处理，对于历史没有简介的帖子进行简介赋值
            $content=db('com_thread')->where('id',$thread['id'])->value('content');
            $thread['summary']= mb_substr(text(strip_tags(json_decode($content), '<p></p><br><span></span>')),0,40,'UTF-8'); //获取内容的前40个字符作为摘要
            db('com_thread')->where('id',$thread['id'])->setField('summary',$thread['summary']);
        }
        $thread['summary'] = summary_show($thread['summary']);
        $thread['summary'] =  emoji_decode(content_show(str_replace('&nbsp;','',$thread['summary'])));

        //将图片字段转为数组
        $imgs = json_decode($thread['image'],true);

        if(is_array($imgs)){
            $thread['image']=$imgs;
            $thread['image_cj']=$imgs;
            $thread['image_cj_2']=$imgs;
            $width=count($thread['image_cj'])==1?912:456;
            foreach($thread['image_cj'] as &$value){
                $value=thumb_path($value,$width,456);
            }
            unset($value);
            foreach($thread['image_cj_2'] as &$v){
                $v=thumb_path($v,700,700);
            }
            unset($v);
            foreach($thread['image'] as &$val){
                $val=get_root_path($val);
            }

            unset($val);
        }else{
            if($thread['image']!='null' && $thread['image']!='' && $thread['image']!='[]') {
                $thread['image_cj'][0] = thumb_path($thread['image'], 1400);
                $thread['image_cj_2'][0] = thumb_path($thread['image'], 700);
                $thread['image']=get_root_path($thread['image']);
                $arr=array();
                $arr[]=$thread['image'];
                $thread['image']=$arr;
            }
        }
        $product_ids = explode(",", $thread['product_id']);
        if($thread['product_id']&&$thread['product_id']!='') {
            if (count($product_ids) > 0) {
                $products = db('store_product')->where('id', 'in', $product_ids)->select();
                foreach ($products as &$value) {
                    $value['image_150'] = thumb_path($value['image'], 150, 150);
                    $value['image_350'] = thumb_path($value['image'], 350, 350);
                    $value['image_750'] = thumb_path($value['image'], 750, 750);
                    $value=[$value];
                }
                unset($value);
                $product_ids = $products;
            }
        }else{
            $product_ids=[[]];
        }
        $thread['product_id']=$product_ids;
        $thread['cover']=$thread['cover']==''?'':get_root_path($thread['cover']);//获取封面路径
        $thread['time'] = date('m-d H:i', $thread['create_time']);
        $thread['view_count']=$thread['view_count']+$thread['false_view'];
        $thread = self::_threadShareHandle($thread);  //判断是否是分享的帖子
        return $thread;
    }


    /**
     * 转发帖的处理，包括图片和内容处理
     * @param $value
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _threadShareHandle($value)
    {
        /**
         * todo
         * 转移说明
         * 当前没有转发帖标识，没有data字段
         */
        if (0&&($value['data'] != null || $value['data'] != '')) {  //若为转发帖，data中会有原帖子的ID
            /*$pid = unserialize($value['data']);
            if ($pid['postId'] != '') {
                $share = self::where('pid', $pid['postId'])->field('fid,pid,tid,abstract,author_uid,image')->find();
                $share = json_decode($share, true);
                $share['user'] = $this->userInfo->getUserInfo($share['author_uid']);
                $share['abstract'] = htmlspecialchars_decode(op_t($share['abstract']));  //帖子不获取内容，只获取摘要，将编码转化为标签
                $share['abstract'] = text(strip_tags($share['abstract'], '<p></p><br><span></span>'));  //只保留部分标签
                $share['image'] = json_decode($share['image'], true);  //将图片字段转为数组
                //$share = $this->postImgCheck($share);
                //$share['content'] = text(strip_tags($share['content'], '<p></p><br><span></span>'));
                $value['share_content'] = $share;
            } else {
                $value['share_content'] = null;
            }*/
        } else {
            $value['share_content'] = null;
        }
        return $value;
    }

    /**
     * 若是朋友圈版块，则需要单独的点赞和评论处理
     * @param $threadList
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _threadFriendsHandle($threadList)
    {
        foreach ($threadList as &$v) {
            if($v['type']==3){
                $v['support_users']=[];
                if($v['support_count']>0){
                    $map=[
                        'model'=>'thread',
                        'row'=>$v['id'],
                        'status'=>1
                    ];
                    $uids = Support::where($map)->order('create_time desc')->limit(50)->column('uid');
                    if(count($uids)){
                        $map_support_user=[
                            'uid'=>['in',$uids],
                            'status'=>1
                        ];
                        $v['support_users']=UserModel::where($map_support_user)->order("field(uid,".implode(',',$uids).")")->field('uid,avatar,nickname')->select()->toArray();
                        foreach ($v['support_users'] as &$user){
                            $user['avatar']=get_root_path($user['avatar']);
                            $user['avatar_64']=thumb_path($user['avatar'],64,64);
                            $user['avatar_128']=thumb_path($user['avatar'],128,128);
                            $user['avatar_256']=thumb_path($user['avatar'],256,256);
                        }
                        unset($user);
                    }
                }
                $v['friends_reply'] = ComPost::getFriendPost($v['id'],5);
            }
        }
        unset($v);
        return $threadList;
    }

    /**
     * 获取版块首页精华、公告、置顶帖子列表-每项获取1个
     * @param $forum_id
     * @return mixed|string
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getNoticeList($forum_id)
    {
        $data = array();
        $field='id,fid,class_id,title';
        $map['fid']=$forum_id;
        $order='create_time desc';
        //获取版块内公告信息
        $announce = self::field($field)->where($map)->where('is_announce', 1)->order($order)->find();
        if ($announce != '') {
            $data['announce'] = $announce;
        }
        //获取版块内置顶帖子信息
        $top = self::field($field)->where($map)->where('is_top', 1)->order($order)->find();
        if ($top != '') {
            $data['top'] = $top;
        }
        //获取版块内精华帖信息
        $essence = self::field($field)->where($map)->where('is_essence', 1)->order($order)->find();
        if ($essence != '') {
            $data['essence'] = $essence;
        }
        $data = json_decode(json_encode($data), true);
        return $data;
    }

    public static function createThread($data)
    {
        $data['content']=html($data['content']);
        $data['content']=self::_limitPictureCount($data['content']);
        $data['content']=html($data['content']);

        //$data['image']=json_encode(self::_contentToImage($data['content']));

        $thread_data=$data;
        $thread_data['content']=json_encode($thread_data['content']);
        $post_data=[
            'fid'=>$data['fid'],
            'is_thread'=>1,
            'level'=>0,
            'author_uid'=>$data['author_uid'],
            'title'=>$data['title'],
            'create_time'=>$data['create_time'],
            'status'=>$data['status'],
            'content'=>$data['content'],
            'from'=>$data['from'],
            'image'=>$data['image']
        ];


        self::beginTrans();
        try{
            if(!$data['id']){
                $thread_data['is_new']=1;
                $thread_id=self::add($thread_data);
                $post_data['tid']=$thread_id;
                $post_id=ComPost::add($post_data);
                self::update(['post_id'=>$post_id],['id'=>$thread_id]);
                census('forum',1);
                /*UserTaskNew::newSendThread($data['author_uid']); //发帖新手任务
                UserTaskDay::daySendThread($data['author_uid']); //每日发帖任务*/
                action_log($data['author_uid'],3,'发布主题帖','com_thread',$thread_id);
            }else{
                self::where(['id'=>$data['id']])->update($thread_data);
                ComPost::update($post_data,['tid'=>$data['id'],'is_thread'=>1,'level'=>0]);
                $thread_id=$data['id'];
            }
            self::commitTrans();
            return $thread_id;
        }catch (\Exception $e){
            self::rollbackTrans();
            self::setErrorInfo('发布过程中出现异常！发布失败：'.self::getErrorInfo().$e->getMessage());
            return false;
        }
    }

    /**
     * 图片限制
     * @param $content
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _limitPictureCount($content){
        //默认最多显示10张图片
        $maxImageCount = '40';

        //正则表达式配置
        $beginMark = 'BEGIN0000hfuidafoidsjfiadosj';
        $endMark = 'END0000fjidoajfdsiofjdiofjasid';
        $imageRegex = '/<img(.*?)\\>/i';
        $reverseRegex = "/{$beginMark}(.*?){$endMark}/i";

        //如果图片数量不够多，那就不用额外处理了。
        $imageCount = preg_match_all($imageRegex, $content);
        if ($imageCount <= $maxImageCount) {
            return $content;
        }

        //清除伪造图片
        $content = preg_replace($reverseRegex, "<img$1>", $content);

        //临时替换图片来保留前$maxImageCount张图片
        $content = preg_replace($imageRegex, "{$beginMark}$1{$endMark}", $content, $maxImageCount);

        //替换多余的图片
        $content = preg_replace($imageRegex, "[图片]", $content);

        //将替换的东西替换回来
        $content = preg_replace($reverseRegex, "<img$1>", $content);

        //返回结果
        return $content;
    }

    /**
     * 将content中的图片信息提取出来,取前三张图
     * @param $content
     * @return array|null
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _contentToImage($content)
    {
        $content = htmlspecialchars_decode(text($content));  //将编码过的字符转回html标签
        preg_match_all('/<img[^>]*\>/', $content, $match);  //获取图片标签
        if (is_array($match[0])) {  //若有多张图片，循环处理
            foreach ($match[0] as $k => &$v) {
                if($k==3){
                    break;
                }
                $img = substr(substr($v, 10), 0, -2);
                //从10开始才是src路径，然后再截取去掉最后的标签符号
                $length = "-" . strlen(strstr($v, 'width'));
                //组件传上来的img标签里会自动有width属性，计算这部分长度然后也去掉
                $imgs[] = substr($img, 0, $length);
                //去掉width属性，此时只剩下一个完整路径
            }
            unset($v);
        } else {  //单图处理
            $imgs[] = substr(substr($match[0], 10), 0, -2);
        }
        if ($match[0] == null) {
            $imgs = null;
        }
        return $imgs;
    }


    /**
     * 获取用户主题帖列表
     * @param $uid
     * @param int $page
     * @param int $row
     * @param string $order
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getUserThreadList($uid,$page=1,$row=10,$type=1,$order='create_time desc')
    {
        $map['author_uid']=$uid;
        if($type==2){
            $map['is_weibo']=1;
        }elseif($type==1){
            $map['is_weibo']=0;
            $map['type']=$type;
        }elseif($type==3){
            $map['type']=1;
        }else{
            $map['type']=$type;
        }
        if($uid==get_uid()){
            $map['status']=['in',[0,1,2]];
        }else{
            $map['status']=1;
        }

        if($uid!=get_uid()){
            $map['fid']=['not in',ForumPower::get_private_id()];
        }
        $list = self::where($map)->field(self::$field_list)->page($page,$row)->order($order)->select()->toArray();
        $totalCount=self::where($map)->count();
        $list = self::threadListHandleStart($list);

        return ['list'=>$list,'totalCount'=>$totalCount];
    }

    /**
     * 主题详情信息
     * @param $thread_id
     * @return array|false|mixed|null|\PDOStatement|string|\think\Model
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function threadInfo($thread_id)
    {
        $tag='thread_detail_'.$thread_id;
        $thread=Cache::get($tag);
        if($thread===false){
            $field = self::$field_list.",content";
            $thread=self::where('id',$thread_id)->field($field)->find();
            if(!$thread){
                return null;
            }
            $thread=self::_threadHandle($thread);
            Cache::tag('thread_detail_tag_'.$thread_id)->set($tag,$thread);
        }
        if($thread){
            $uid=get_uid();

            $tag_reget='thread_detail_view_num_reget_'.$thread_id.'_uid_'.$uid;
            $tag_other='thread_detail_view_num_'.$thread_id;
            $thread_reget=Cache::get($tag_reget);
            if($thread_reget){
                $thread_other=false;
                Cache::rm($tag_reget);
            }else{
                $thread_other=Cache::get($tag_other);
            }
            if($thread_other===false){
                $thread_other=self::where('id',$thread_id)->field('id,reply_count,share_count,support_count,view_count,collect_count')->find();
                Cache::tag('thread_detail_tag_'.$thread_id)->set($tag_other,$thread_other,60);
            }
            $thread['reply_count']=$thread_other['reply_count'];
            $thread['share_count']=$thread_other['share_count'];
            $thread['collect_count']=$thread_other['collect_count'];
            $thread['support_count']=$thread_other['support_count'];
            $thread['view_count']=$thread_other['view_count']+$thread['false_view'];
            if($thread['video_id']){
                $thread['psign']=self::_videoKey($thread['video_id']);
            }
            if($thread['video_url']){
                $getYunConfig = TencentFile::ifYunUpload();
                $thread['video_url']=TencentFile::yunKeyMediaUrl($thread['video_url'],$getYunConfig['pkey']);
            }
            if($thread['audio_url']){
                $getYunConfig = TencentFile::ifYunUpload();
                $thread['audio_url']=TencentFile::yunKeyMediaUrl($thread['audio_url'],$getYunConfig['pkey']);
            }
            if($uid==0){
                $thread['is_collect'] = false;
                $thread['is_support'] = false;  //判断当前用户是否对帖子点过赞
                $thread['is_black'] =0;
                $thread['user']['is_follow'] = false;
                $thread['create_time']=time_to_show($thread['create_time']);
            }else{
                $thread['is_collect'] =  db('collect')->where('uid',$uid)->where('tid',$thread['id'])->where('status',1)->count();
                $thread['is_support'] =  Support::isSupport('thread', $thread['post_id']);  //判断当前用户是否对帖子点过赞
                $thread['is_black'] =  Blacklist::isBlack($uid,$thread['author_uid']);  //判断当前用户是否对帖子点过赞
                $thread['user']['is_follow'] = UserFollow::isFollow($uid,$thread['author_uid']);  //判断当前用户是否对帖子点过赞
                $thread['create_time']=time_to_show($thread['create_time']);
            }
            return $thread;
        }else{
            return null;
        }
    }

    /**
     * 查看量自增1
     * @param $thread_id
     * @return int|true
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function viewOnce($thread_id)
    {
        $res=self::where('id', $thread_id)->setInc('view_count');
        return $res;
    }

    /**
     * 获取置顶帖子
     */
    public static function getPostTop($forumId)
    {
        $field = 'id,title,content';
        $map=[
            'fid'=>$forumId,
            'status'=>1,
            'is_top'=>1,
        ];
        $list=self::where($map)->where('top_end_time','>',time())->field($field)->order('top_time desc')->select()->toArray();
        foreach ($list as &$value){
            $value['content']=json_decode($value['content']);
        }
        return $list;
    }

    /**
     * 获取详情置顶帖子
     */
    public static function getPostDetailTop($forumId)
    {
        $field = self::$field_list.',content';
        $map=[
            'fid'=>$forumId,
            'status'=>1,
            'detail_top'=>1,
        ];
        $list=self::where($map)->where('detail_top_end_time','>',time())->field($field)->order('detail_top_time desc')->find();
        if($list){
            $list=self::_threadHandle($list);
            $list['create_time']=time_format($list['create_time']);
        }
        return $list;
    }

    /**
     * 获取首页置顶帖子
     */
    public static function getPostIndexTop()
    {
        $field = self::$field_list.',content';
        $map=[
            'status'=>1,
            'index_top'=>1,
            'fid'=>['not in',ForumPower::get_private_id()],
        ];
        $list=self::where($map)->where('index_top_end_time','>',time())->field($field)->order('index_top_time desc')->select();
        if($list){
            $list=$list->toArray();
            foreach($list as &$value){
                $value['content']=json_decode($value['content']);
                $value['user'] = UserModel::getUserInfo($value['author_uid'],$value['fid']);
            }
            unset($value);
            $list=self::threadListHandle($list);
            $list=self::initListUserRelation($list,false);
        }
        return $list;
    }

    /**
     * 获取热门帖子
     * @param $forumId
     * @param $page
     * @param $row
     * @return array
     */
    public static function getPostHot($forumId,$page,$row,$access,$video_is_on)
    {
        $map=[
            'fid'=>$forumId,
            'status'=>1,
            'is_massage'=>0,
            'detail_top'=>0
        ];
        if($access[1] == '微信小程序' && $video_is_on==0){
            $map['type']=['neq',6];
        }
        $tag='forum_post_list_essence_fid_'.$forumId.'_'.$page.$row.'order_view_count';
        $order='view_count desc,create_time desc';
        $postList=self::forumPostList($map,$page,$row,$order,$tag);
        $count=self::where($map)->count();
        return ['list'=>$postList,'count'=>$count];
    }

    /**
     * 统一处理版块帖子列表获取，带缓存
     * @param $map
     * @param $page
     * @param $row
     * @param $order
     * @param string $tag 列表缓存tag
     * @param bool $checkNew 是否校验最新帖子（对于最新帖子列表和分类帖子列表需要校验，自己发了新帖子，再次获取时第一页需要清除缓存重新获取）
     * @param string $checkNewTag checknewtag
     * @return array|mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function forumPostList($map,$page,$row,$order,$tag,$checkNew=false,$checkNewTag='')
    {
        $field = self::$field_list;
        $uid=get_uid();
        //权限判断 是否拥查看权限
        $power=forum_power('browse',$uid,$map['fid']);
        if($power){
            $map['author_uid']=$power;
        }
        //权限判断end
        $postListCache=Cache::get($tag.$uid);
        if($page==1&&$postListCache){
            if($checkNew){
                $checkNewTag=$checkNewTag.$uid;
                $selfHasNew=Cache::get($checkNewTag);
                if(intval($selfHasNew)>intval($postListCache['recache_time'])){
                    $postListCache=null;//缓存之后有发新贴，需要清除缓存重新获取数据
                }
            }
        }
        if(!$postListCache){
            if($order=='create_time'){
                $order='create_time desc';
            }
            $list=self::where($map)->field($field)->page($page,$row)->order($order)->select()->toArray();
            $list=self::threadListHandle($list);
            $postListCache = ['list'=>$list,'recache_time'=>time(),'time_end'=>time()+10*60];
            Cache::tag('thread_list_cache')->set($tag,$postListCache,10*60);
        }
        $postList=$postListCache['list'];
        if($postList!=false){
            if($uid){
                $tag_user_has_change='forum_post_list_change_uid_'.$uid;
                $selfHasChange=Cache::get($tag_user_has_change);//有点赞、评论时该用户重新获取帖子的点赞评论数
                if($selfHasChange>$postListCache['recache_time']){
                    $postList = ComThread::reGetSupportNum($postList);
                    if($postListCache['time_end']>time()){//有效期还有一段时间
                        $has_time=intval($postListCache['time_end'])-time();
                        $postListCache['list']=$postList;
                        $postListCache['recache_time']=time();
                        Cache::tag('thread_list_cache')->set($tag.$uid,$postListCache,$has_time);
                    }
                }
            }
            $postList=ComThread::initListUserRelation($postList,false);
        }
        return $postList;
    }

    /**
     * 获取最新帖子
     * @param $forumId
     * @param $page
     * @param $row
     * @param string $order
     * @return array
     */
    public static function getPostAll($forumId,$page,$row,$order='create_time desc',$access,$video_is_on)
    {
        $map=[
            'fid'=>$forumId,
            'status'=>1,
            'is_massage'=>0,
            'detail_top'=>0
        ];
        if($access[1] == '微信小程序' && $video_is_on==0){
            $map['type']=['neq',6];
        }
        if($order=='reply_time') {
            $tag='forum_post_list_all_f'.$forumId.'_'.$page.$row.'order_reply_time';
            $order='last_post_time desc,create_time desc';
        }else{
            $tag='forum_post_list_all_f'.$forumId.'_'.$page.$row;
        }
        $check_new_tag='forum_post_has_new_fid_'.$forumId.'_uid_';
        $postList=self::forumPostList($map,$page,$row,$order,$tag,true,$check_new_tag);
        $count=self::where($map)->count();
        return ['list'=>$postList,'count'=>$count];
    }

    /**
     * 获取加精帖子
     * @param $forumId
     * @param $page
     * @param $row
     * @return array
     */
    public static function getPostEssence($forumId,$page,$row,$access,$video_is_on)
    {
        $map=[
            'fid'=>$forumId,
            'status'=>1,
            'is_essence'=>1,
            'detail_top'=>0
        ];
        if($access[1] == '微信小程序' && $video_is_on==0){
            $map['type']=['neq',6];
        }
        $tag='forum_post_list_essence_f'.$forumId.'_'.$page.$row.'order_reply_time';
        $order='last_post_time desc,create_time desc';
        $postList=self::forumPostList($map,$page,$row,$order,$tag);
        $count=self::where($map)->count();
        return ['list'=>$postList,'count'=>$count];
    }

    /**
     * 获取分类帖子
     * @param $forumId
     * @param $tid
     * @param $page
     * @param $row
     * @param string $order
     * @return array
     */
    public static function getPostClass($forumId,$tid,$page,$row,$order='create_time desc',$access,$video_is_on)
    {
        $map=[
            'fid'=>$forumId,
            'class_id'=>$tid,
            'status'=>1,
            'is_massage'=>0,
            'detail_top'=>0
        ];
        if($access[1] == '微信小程序' && $video_is_on==0){
            $map['type']=['neq',6];
        }
        $tag = 'forum_post_list_class_f'.$forumId.'_class_'.$tid.'_'.$page.$row;
        if($order=='reply_time') {
            $tag = $tag.'order_reply_time';
            $order = 'last_post_time desc,create_time desc';
        }
        $check_new_tag='forum_post_has_new_fid_'.$forumId.'_cid_'.$tid.'_uid_';
        $postList=self::forumPostList($map,$page,$row,$order,$tag,true,$check_new_tag);
        $count=self::where($map)->count();
        return ['list'=>$postList,'count'=>$count];
    }

    /**
     * 获取收藏帖子
     */
    public static function getPostCollect($uid,$page=1,$row=10,$order='create_time desc',$type)
    {
        $field = self::$field_list;
        $ids=db('collect')->where('uid',$uid)->order($order)->column('tid');
        if(empty($ids)){
            $list=array();
            return $list;
        }
        $map['id']=['in',$ids];
        $map['status']=1;
        //判断收藏的类型
        switch ($type){
            case 'post':
                $map['type']=1;
                $map['is_weibo']=0;
                break;
            case 'weibo':
                $map['type']=1;
                $map['is_weibo']=1;
                break;
            case 'video':
                //todo
                $map['type']=6;
                $map['is_weibo']=0;
                break;
            case 'news':
                //todo
                $map['type']=4;
                $map['is_weibo']=0;
                break;
            default:
                $map['type']=1;
                $map['is_weibo']=0;
                break;
        }
        $list = self::where($map)->field($field)->page($page,$row)->order($order)->select()->toArray();
        $list=self::threadListHandleStart($list,false);
        return $list;
    }

    /**
     * 获取推荐帖子
     */
    public static function getPostRecommend($page,$row,$access,$video_is_on)
    {
        //$time=time()-86400;
        $map=[
            'status'=>1,
            'is_massage'=>0,
            //'create_time'=>['>',$time]
        ];
        if($access[1] == '微信小程序' && $video_is_on==0){
            $map['type']=['neq',6];
        }
        $field = self::$field_list;
        //版块权限排除私密帖子
        $mav['status']=1;
        $mav['id']=['not in',ForumPower::get_private_id()];
        $forum_ids=ComForum::where($mav)->column('id');
        $recommend_count=self::where($map)->where('is_recommend',1)->where('recommend_end_time','>',time())->where('fid','in',$forum_ids)->where('index_top',0)->count();
        if($recommend_count>$row){
            $counts=self::where($map)->where('is_recommend',1)->where('fid','in',$forum_ids)->where('recommend_end_time','>',time())->where('index_top',0)->page($page,$row)->select()->toArray();
            $counts=count($counts);
            if($counts==$row){
                $thread=self::where('is_recommend',1)->where($map)->where('fid','in',$forum_ids)->where('recommend_end_time','>',time())->where('index_top',0)->order('recommend_time desc')->select()->toArray();
                $list=$thread;
            }else{
                $default_page=intval($recommend_count/$row);
                $page=$page-$default_page;
                if($counts>0){
                    $thread=self::where('is_recommend',1)->where($map)->where('recommend_end_time','>',time())->where('fid','in',$forum_ids)->where('index_top',0)->order('recommend_time desc')->select()->toArray();
                }else{
                    $thread=array();
                }
                $all_ids=self::where('status',1)->where('is_recommend',1)->where('index_top',0)->where('fid','in',$forum_ids)->column('tid');
                $map['id']=array('not in',$all_ids);
                $list=self::where($map)->where('fid','in',$forum_ids)->where('index_top',0)->field($field)->page($page,$row)->order('create_time desc,view_count desc')->select()->toArray();
                if(count($thread)){
                    if(count($list)){
                        $list=array_merge($thread,$list);
                    }else{
                        $list=$thread;
                    }
                }
            }
        }else{//推荐数小于$row
            $counts=self::where($map)->where('is_recommend',1)->where('fid','in',$forum_ids)->where('recommend_end_time','>',time())->where('index_top',0)->page($page,$row)->count();
            if($counts>0){
                if($page==1){
                    $thread=self::where('is_recommend',1)->where($map)->where('fid','in',$forum_ids)->where('recommend_end_time','>',time())->where('index_top',0)->order('recommend_time desc')->select()->toArray();
                }else{
                    $thread=array();
                }
            }else{
                $thread=array();
            }
            $list=self::where($map)->where('is_recommend',0)->where('fid','in',$forum_ids)->where('index_top',0)->field($field)->page($page,$row)->order('create_time desc,view_count desc')->select()->toArray();
            if(count($thread)){
                if(count($list)){
                    $list=array_merge($thread,$list);
                }else{
                    $list=$thread;
                }
            }
        }

        if(empty($list)){
            $postListTwo=false;
        }else{
            $postListTwo=self::threadListHandle($list);
        }
        return $postListTwo;
    }

    /**
     * 获取动态
     */
    public static function getPostWeibo($page,$row)
    {
        $map=[
            'status'=>1,
            'is_massage'=>0,
            'is_weibo'=>1
        ];
        $field = self::$field_list;
        //版块权限排除私密帖子
        $mav['status']=1;
        $mav['id']=['not in',ForumPower::get_private_id()];
        $forum_ids=ComForum::where($mav)->column('id');
        $list=self::where($map)->where('fid','in',$forum_ids)->where('index_top',0)->field($field)->page($page,$row)->order('create_time desc,view_count desc')->select();
        if(empty($list)){
            $postListTwo=false;
        }else{
            $list=$list->toArray();
            $postListTwo=self::threadListHandle($list);
        }
        return $postListTwo;
    }

    /**
     * 获取话题动态
     */
    public static function getTopicWeibo($page,$row,$id,$type)
    {
        $map=[
            'status'=>1,
            'is_massage'=>0,
            'is_weibo'=>1,
        ];
        if($type=='hot'){
            $order='reply_count desc,view_count desc';
        }else{
            $order='create_time desc,view_count desc';
        }
        $field = self::$field_list;
        $forum_ids=ComForum::where('status',1)->column('id');
        $list=self::where($map)->where('fid','in',$forum_ids)->where('find_in_set(:id,oid)',['id'=>$id])->where('index_top',0)->field($field)->page($page,$row)->order($order)->select();
        if(empty($list)){
            $postListTwo=false;
        }else{
            $list=$list->toArray();
            $postListTwo=self::threadListHandle($list);
        }
        return $postListTwo;
    }

    /**
     * 获取视频
     */
    public static function getPostVideo($page,$row)
    {
        $map=[
            'status'=>1,
            'is_massage'=>0,
            'type'=>6
        ];
        $field = self::$field_list;
        //版块权限排除私密帖子
        $mav['status']=1;
        $mav['id']=['not in',ForumPower::get_private_id()];
        $forum_ids=ComForum::where($mav)->column('id');
        $list=self::where($map)->where('fid','in',$forum_ids)->where('index_top',0)->field($field)->page($page,$row)->order('create_time desc,view_count desc')->select();
        if(empty($list)){
            $postListTwo=false;
        }else{
            $list=$list->toArray();
            $postListTwo=self::threadListHandle($list);
        }
        return $postListTwo;
    }

    /**
     * 获取审核帖子
     */
    public static function getThreadApply($page,$row,$fid)
    {
        $map=[
            'status'=>2,
            'fid'=>$fid,
        ];
        $field = self::$field_list;
        $list=self::where($map)->field($field)->page($page,$row)->order('create_time desc,view_count desc')->select();
        if(empty($list)){
            $list=false;
        }else{
            $list=$list->toArray();
            $list=self::threadListHandle($list);
        }
        return $list;
    }

    /**
     * 获取全部审核帖子
     */
    public static function getThreadApplyAll($page,$row,$fids)
    {
        $map=[
            'status'=>2,
            'fid'=>array('in',$fids),
        ];
        $field = self::$field_list;
        $list=self::where($map)->field($field)->page($page,$row)->order('create_time desc,view_count desc')->select();
        if(empty($list)){
            $list=false;
        }else{
            $list=$list->toArray();
            $list=self::threadListHandle($list);
        }
        return $list;
    }

    /**
     * 帖子列表，重置帖子点赞数、阅读数、评论数
     * @param $threadList
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function reGetSupportNum($threadList)
    {
        $ids=array_column($threadList,'id');
        $thread_info=self::where('id','in',$ids)->field('id,reply_count,share_count,support_count,view_count')->select()->toArray();
        $thread_info=array_combine(array_column($thread_info,'id'),$thread_info);
        foreach ($threadList as &$val){
            if(isset($thread_info[$val['id']])){
                $one_thread= $thread_info[$val['id']];
            }else{
                continue;
            }
            $one_thread['view_count']=$one_thread['view_count']+$val['false_view'];
            $val=array_merge($val,$one_thread);
        }
        unset($val);
        return $threadList;
    }

    /**
     * 获取关注帖子
     */
    public static function getPostFollow($uids,$page,$row)
    {
        $map=[
            'status'=>1,
            'author_uid'=>['in',$uids],
            'is_massage'=>0,
            'type'=>1,
            'index_top'=>0,
        ];
        $field = self::$field_list;
        //版块权限排除私密帖子
        $mav['status']=1;
        $mav['id']=['not in',ForumPower::get_private_id()];
        $ids=ComForum::where($mav)->column('id');
        $list=self::where($map)->where('fid','in',$ids)->field($field)->page($page,$row)->order('create_time desc,view_count desc')->select()->toArray();
        $list=self::threadListHandle($list);
        return $list;
    }

    /**
     * 获取关注的版块的帖子
     */
    public static function getForumThread($forum_ids,$page=1,$row = 10)
    {
        $field = self::$field_list;
        $map=[
            'fid'=>array('in',$forum_ids),
            'status'=>1,
            'is_massage'=>0,
            'index_top'=>0,
        ];
        $list=self::where($map)->field($field)->page($page, $row)->order('create_time desc')->select()->toArray();
        $list=self::threadListHandleStart($list,false);
        return $list;
    }

    /**
     * 搜索帖子
     */
    public static function  searchThread($keyword,$page=1,$row=10){
        $field=self::$field_list;
        $thread=self::where('title|summary','like','%'.$keyword.'%')->where('fid','not in',ForumPower::get_private_id())->where('status',1)->where('type',1)->where('is_weibo',0)->page($page, $row)->field($field)->order('create_time desc')->select();
        if($thread){
            $thread=$thread->toArray();
        }
        $thread=self::threadListHandleStart($thread,false);
       //$thread['allCount']=self::where('content|title|summary','like','%'.$keyword.'%')->where('status',1)->where('type','in',array(1,2,6))->count();
        return $thread;
    }

    /**
     * 搜索资讯
     */
    public static function  searchThreadNews($keyword,$page=1,$row=10){
        $thread=self::where('title','like','%'.$keyword.'%')->where('fid','not in',ForumPower::get_private_id())->where('status',1)->where('type',4)->page($page, $row)->field(self::$field_list)->order('create_time desc')->select();
        if($thread){
            $thread=$thread->toArray();
        }
        $thread=self::threadListHandleStart($thread,false);
       // $thread['allCount']=self::where('content|title','like','%'.$keyword.'%')->where('status',1)->where('type',4)->count();
        return $thread;
    }

    /**
     * 版块内搜索帖子
     */
    public static function  searchForumThread($keyword,$page=1,$row=10,$fid){
        $thread=self::where('title|summary','like','%'.$keyword.'%')->where('fid',$fid)->where('status',1)->where('type',1)->page($page, $row)->field(self::$field_list)->order('create_time desc')->select();
        if($thread){
            $thread=$thread->toArray();
        }
        $thread=self::threadListHandleStart($thread,false);
        return $thread;
    }
    /**
     * 版块内搜索视频
     */
    public static function  searchForumVideo($keyword,$page=1,$row=10){
        $thread=self::where('title|summary','like','%'.$keyword.'%')->where('fid','not in',ForumPower::get_private_id())->where('type',6)->where('is_weibo',0)->where('status',1)->page($page, $row)->field(self::$field_list)->order('create_time desc')->select();
        if($thread){
            $thread=$thread->toArray();
        }
        $thread=self::threadListHandleStart($thread,false);
        return $thread;
    }
    /**
     * 版块内搜索动态
     */
    public static function  searchForumWeibo($keyword,$page=1,$row=10){
        $thread=self::where('title|summary','like','%'.$keyword.'%')->where('fid','not in',ForumPower::get_private_id())->where('is_weibo',1)->where('status',1)->page($page, $row)->field(self::$field_list)->order('create_time desc')->select();
        if($thread){
            $thread=$thread->toArray();
        }
        $thread=self::threadListHandleStart($thread,false);
        return $thread;
    }
    /**
     * 获取某人的发帖数
     * @param $uid
     * @return int|string
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.19
     */
    public static function getForumCount($uid){
        if($uid){
            $map['author_uid']=$uid;
            $map['status']=['in',[0,1,2]];
            return self::where($map)->count();
        }else{
            return 0;
        }
    }

    /**
     * qhy
     * 帖子审核
     */
    public static function ForumAdminThreadApply($id,$thread,$now_uid,$is_admin,$status,$reason){
        $data['operation_uid']=$now_uid;
        if($is_admin['admin_two']==1){
            $data['operation_identity']=2;
        }elseif($is_admin['admin_three']==1){
            $data['operation_identity']=4;
        }else{
            $data['operation_identity']=3;
        }
        $data['status']=$status;
        if($status==0){
            $data['reject_reason']=$reason;
        }
        if($status==-1){
            $data['delete_reason']=$reason;
        }
        $res=self::where('id',$id)->update($data);
        if($res!==false){
            $forum_name=db('com_forum')->where(['id'=>$thread['fid']])->value('name');
            $thread['title']=$thread['title']?$thread['title']:mb_substr($thread['summary'],0,10).'...';
            if($status==-1){
                db('com_forum')->where('id', $thread['fid'])->setDec('post_count');
                db('user')->where('uid', $thread['author_uid'])->setDec('post_count');
                //发送消息
                $set=MessageTemplate::getMessageSet(42);
                $template=str_replace('{年月日时分}', time_format(time()), $set['template']);
                $template=str_replace('{版块名称}', $forum_name, $template);
                $template=str_replace('{帖子标题}', $thread['title'], $template);
                $template=str_replace('{删除原因}', $reason, $template);
                if($set['status']==1){
                    $message_id=Message::sendMessage($thread['author_uid'],$now_uid,$template,1,$set['title'],1,'','thread',$thread['post_id']);
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
            if($status==0){
                db('com_forum')->where('id', $thread['fid'])->setDec('post_count');
                db('user')->where('uid', $thread['author_uid'])->setDec('post_count');
                //发送消息
                $set=MessageTemplate::getMessageSet(43);
                $template=str_replace('{年月日时分}', time_format(time()), $set['template']);
                $template=str_replace('{版块名称}', $forum_name, $template);
                $template=str_replace('{帖子标题}', $thread['title'], $template);
                $template=str_replace('{驳回原因}', $reason, $template);
                if($set['status']==1){
                    $message_id=Message::sendMessage($thread['author_uid'],$now_uid,$template,1,$set['title'],1,'','thread',$thread['post_id']);
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
            if($status==1){
                //发送消息
                $set=MessageTemplate::getMessageSet(44);
                $template=str_replace('{年月日时分}', time_format(time()), $set['template']);
                $template=str_replace('{版块名称}', $forum_name, $template);
                $template=str_replace('{帖子标题}', $thread['title'], $template);
                if($set['status']==1){
                    $message_id=Message::sendMessage($thread['author_uid'],get_uid(),$template,1,$set['title'],1,'','thread',$thread['post_id']);
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
           return true;
        }else{
            return false;
        }
    }
}