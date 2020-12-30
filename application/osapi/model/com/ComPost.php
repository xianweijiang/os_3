<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/6/3
 * Time: 9:13
 */

namespace app\osapi\model\com;



use app\osapi\model\BaseModel;
use app\osapi\model\com\ComThread;
use app\osapi\model\user\UserFollow;
use app\osapi\model\user\UserModel;
use app\osapi\model\user\UserTaskDay;
use app\osapi\model\user\UserTaskNew;
use app\osapi\model\common\Support;
use app\osapi\controller\User;

class ComPost extends BaseModel
{

    /**
     * 回复主题帖或者楼中楼评论
     * @param $data
     * @return bool|int|string
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function createPost($data)
    {
        $data['content']=html($data['content']);
        $data['content']=self::_limitPictureCount($data['content']);
        $data['content']=html($data['content']);

        $data['image']=json_encode(self::_contentToImage($data['content']));
        $data['position']=0;
        self::startTrans();
        try{
            $post_id=self::add($data);
            if($data['to_reply_id']==0) {//楼层在数据添加完后再重置
                $map=[
                    'id'=>['<',$post_id],
                    'level'=>1,
                    'tid'=>$data['tid']
                ];
                $position=1+self::where($map)->count();
                self::update(['position'=>$position],['id'=>$post_id]);
            }
            $forum_data=[
                'last_post_time'=>$data['create_time'],
            ];
            ComForum::update($forum_data,['id'=>$data['fid']]);
            $thread_data=[
                'last_post_time'=>$data['create_time'],
                'last_post_uid'=>$data['author_uid']
            ];
            ComThread::update($thread_data,['id'=>$data['tid']]);
            ComThread::where('id',$data['tid'])->setInc('reply_count');
            self::where('id',$data['to_reply_id'])->setInc('comment_count');

            UserTaskNew::newPost($data['author_uid']); //回帖新手任务
            UserTaskDay::dayPost($data['author_uid']); //每日回帖任务
            action_log($data['author_uid'],4,'回帖或楼中楼评论','com_post',$post_id);
            self::commitTrans();
            return $post_id;
        }catch (\Exception $e){
            self::rollbackTrans();
            self::setErrorInfo('回帖过程中出现异常！发布失败：'.self::getErrorInfo().$e->getMessage());
            return false;
        }
    }
    /**
     * 获取今日、昨日、系统总计发帖数（主题、公告、评论）
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getPostCount()
    {
        $tag='header_show_post_count';
        $data=cache($tag);
        if(!$data){
            $data['today'] =self::where('status',1)->whereTime('create_time','d')->count();//今日
            $data['yesterday'] =self::where('status',1)->whereTime('create_time','yesterday')->count();//昨日
            $data['all'] = self::where('status',1)->count();//系统总共
            $data['all']=big_num_show($data['all']);
            cache($tag,$data,60);
        }
        return $data;
    }

    /**
     * 根据uid获取用户帖子总数(暂时没用到)
     * @param $uid
     * @return int|string
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getPostCountByUid($uid)
    {
        $postCount=self::where('author_uid', $uid)->where('status', 1)->count();
        return $postCount;
    }

    /**
     * 获取主题详情页中的评论列表
     * @param $tid 主题id
     * @param int $page
     * @param bool $is_lord 只看楼主
     * @param int $row
     * @param string $sort
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getThreadReplyList($tid, $page=1, $is_lord=false, $row=10, $sort='create_time asc')
    {
        $field = 'id,tid,author_uid,create_time,to_reply_id,content,image,position,comment_count,support_count';
        $lordInfo=null;
        $uid = ComThread::where('id',$tid)->value('author_uid');  //获取帖子作者
        $map=[
            'tid'=>$tid,
            'level'=>1,
            'status'=>1
        ];
//        if ($is_lord) {  //是否只看楼主
//            $map['author_uid']=$uid;
//        }
        $postList = self::where($map)->page($page, $row)->order($sort)->field($field)->select()->toArray();
        $totalCount=self::where($map)->count();
        $postList=self::_postReplyListHandle($postList,true);
        foreach ($postList as &$post) {
            $post = self::getPostReplyList($post['id'], $post);
            $post['content']=emoji_decode($post['content']);
        }
        unset($post);
        return ['list'=>$postList,'totalCount'=>$totalCount];
    }

    /**
     * 获取主题一级评论详情
     * @param $replyId
     * @return array|false|mixed|\PDOStatement|string|\think\Model
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getThreadReplyDetail($replyId)
    {
        $field = 'id,tid,author_uid,create_time,to_reply_id,content,image,position,comment_count';
        $post = self::where('id', $replyId)->field($field)->find();
        $post = json_decode($post, true);  //转为数组
        $uid = ComThread::where('id',$post['tid'])->value('author_uid');  //获取帖子作者
        $post=self::_postReplyHandle($post,$uid);
        return $post;
    }

    /**
     * 评论详情的楼中楼评论
     * @param $to_reply_id 楼层post_id
     * @param $post 楼层信息
     * @param int $page
     * @param int $row
     * @param string $sort
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getPostReplyList($to_reply_id, $post=null, $page = 1, $row = 10, $sort = 'create_time asc')
    {
        $field = 'id,tid,author_uid,create_time,to_reply_id,to_reply_uid,content,image,support_count';
        $map=[
            'to_reply_id'=>$to_reply_id,
            'level'=>2,
            'status'=>1
        ];
        if ($post != null) { //若是数组则获取帖子详情中的5条楼中楼评论
            $postReplyList = self::where($map)->limit(5)->order($sort)->field($field)->select()->toArray();
            if(count($postReplyList)){
                $uids=array_column($postReplyList,'author_uid');
                $author=UserModel::where('uid','in',$uids)->where('status',1)->field('uid,nickname,avatar,icon,exp,is_red')->select()->toArray();
                $author=array_combine(array_column($author,'uid'),$author);
                //获取评论列表
                foreach ($postReplyList as $key=>&$value) {
                    $value['content'] = emoji_decode(content_show($value['content']));  //过滤content
                    if(isset($author[$value['author_uid']])){
                        $value['user'] = $author[$value['author_uid']];  //评论作者信息
                        $value['user']['grade']=UserModel::cacugrade($value['user']['exp']);
                    }else{
                        unset($postReplyList[$key]);
                    }
                }
                unset($value,$key,$author);
            }
            $post['comment'] = $postReplyList;
            return $post;
        } else { //若不是数组则为评论详情页的楼中楼评论
            $postReplyList = self::where($map)->page($page, $row)->order($sort)->field($field)->select()->toArray();
            if(count($postReplyList)){
                $uids=array_column($postReplyList,'author_uid');
                $to_uids=array_column($postReplyList,'to_reply_uid');
                if(count($to_uids)){
                    $uids=array_merge($uids,$to_uids);
                }
                $author=UserModel::where('uid','in',$uids)->where('status',1)->field('uid,nickname,avatar,icon,exp,is_red')->select()->toArray();
                $author=array_combine(array_column($author,'uid'),$author);

                $uid_now=get_uid();
                $post_ids=array_column($postReplyList,'id');
                $support_list=Support::where('row','in',$post_ids)->where('uid',$uid_now)->where('model','reply')->where('status',1)->column('row');

                //获取评论列表
                foreach ($postReplyList as $key=>&$value) {
                    $value['content'] = emoji_decode(content_show($value['content']));   //过滤content
                    $value['time'] = date('m-d', $value['create_time']);
                    $value['detail_time'] = time_to_show($value['create_time']);
                    if(isset($author[$value['author_uid']])){
                        $value['user'] = $author[$value['author_uid']];  //评论作者信息
                        $value['user']['grade']=UserModel::cacugrade($value['user']['exp']);
                    }else{
                        unset($postReplyList[$key]);
                    }
                    if(isset($author[$value['to_reply_uid']])){
                        $value['to_user'] = $author[$value['to_reply_uid']];  //评论作者信息
                        $value['to_user']['grade']=UserModel::cacugrade($value['to_user']['exp']);
                    }else{
                        $value['to_user'] =[];
                    }
                    $value['image'] = json_decode($value['image'], true);  //将图片转换为数组
                    $value['is_support']=in_array($value['id'],$support_list)?true:false;
                }
                unset($value,$key,$support_list,$post_ids,$author);
            }
            return $postReplyList;
        }
    }

    /**
     * 一级评论列表处理-，楼层处理
     * @param $list
     * @param bool $need_user_info 是否需要获取用户信息
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _postReplyListHandle($list,$need_user_info)
    {
        if(count($list)){
            if($need_user_info){
                $author_uids=array_column($list,'author_uid');
                $author=UserModel::where('uid','in',$author_uids)->where('status',1)->field('uid,nickname,avatar,icon,exp,is_red')->select()->toArray();
                $author=array_combine(array_column($author,'uid'),$author);
            }
            foreach ($list as $key=>&$val){
                $val = self::_postReplyHandleCom($val);  //公用部分数据处理
                if($need_user_info){
                    if(isset($author[$val['author_uid']])){
                        $author_info=$author[$val['author_uid']];
                        //认证图标
                        $icon_field=is_icon('');
                        if ($icon_field=='') {
                            unset($author_info['icon']);
                        }
                        $author_info['avatar']=thumb_path($author_info['avatar'],128,128);
                        $author_info['grade']=UserModel::cacugrade($author_info['exp']);
                        $val['user'] =$author_info;
                    }else{
                        unset($list[$key]);
                    }
                }
            }
            unset($val,$key,$author_info,$author);
            $uid_now=get_uid();
            $post_ids=array_column($list,'id');
            $support_list=Support::where('row','in',$post_ids)->where('uid',$uid_now)->where('model','reply')->where('status',1)->column('row');

            if($need_user_info) {
                //$author_uids=array_column($list,'author_uid');
                $is_follow = UserFollow::where('uid', $uid_now)->where('follow_uid', 'in',$author_uids)->where('status',1)->column('follow_uid');
            }
            foreach ($list as &$val){
                $val['is_support']=in_array($val['id'],$support_list)?true:false;

                if ($need_user_info) {
                    $val['user']['is_follow'] = in_array($val['author_uid'],$is_follow)?true:false;
                }
            }
        }
        return $list;
    }

    /**
     * 一级评论处理-公共部分，楼层处理
     * @param $data
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _postReplyHandleCom($data)
    {
        $uid_now=get_uid();
        $data['content'] = content_show($data['content']);  //过滤content
        $data['image'] = json_decode($data['image'],true);  //将图片字段转为数组
        $data['time'] = time_to_show($data['create_time']);  //将时间转化为多少分钟，多少小时前的展示形式
        $data['floor'] = self::_positionHandle($data['position']);  //获取评论楼层信息
        $data['lord'] = ($uid_now == $data['author_uid']) ? true : false;  //判断评论作者是否是楼主
        return $data;
    }

    /**
     * 一级评论处理，楼层处理
     * @param $data
     * @param $uid 主题帖作者id
     * @param bool $need_user_info 是否需要获取用户信息
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _postReplyHandle($data,$uid,$need_user_info=true)
    {
        $uid_now=get_uid();
        $data['is_support']=Support::isSupport('reply',$data['id'],$uid_now);
        $data['support_count']=db('support')->where('row',$data['id'])->where('model','reply')->where('status',1)->count();
        $data['content'] = content_show($data['content']);  //过滤content
        $data['image'] = json_decode($data['image'],true);  //将图片字段转为数组
        $data['time'] = time_to_show($data['create_time']);  //将时间转化为多少分钟，多少小时前的展示形式
        $data['floor'] = self::_positionHandle($data['position']);  //获取评论楼层信息
        $data['lord'] = ($uid == $data['author_uid']) ? true : false;  //判断评论作者是否是楼主
        if ($need_user_info) {
            $data['user'] = UserModel::getUserInfo($data['author_uid']);  //获取用户信息
            $data['user']['is_follow'] = UserFollow::isFollow(get_uid(), $data['author_uid']);
        }
        return $data;
    }


    /**
     * 帖子详情中评论列表的位置转化为楼层
     * @param $position
     * @return int|string
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _positionHandle($position)
    {
        $position = intval($position);
        switch ($position) {
            case 1:
                $position = '沙发';
                break;
            case 2:
                $position = '板凳';
                break;
            case 3:
                $position = '地板';
                break;
            default:
                $position = $position . '楼';
                break;
        }
        return $position;
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
        $maxImageCount = modC('LIMIT_IMAGE', 10);

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
     * 主题列表页面，展示朋友圈回复列表获取
     * @param $thread_id
     * @param int $limit
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getFriendPost($thread_id,$limit=5)
    {
        $field = 'id,tid,author_uid,create_time,to_reply_id,to_reply_uid,content,image';
        $map=[
            'tid'=>$thread_id,
            'status'=>1,
            'level'=>['neq',0]
        ];
        $list=self::where($map)->field($field)->limit($limit)->order('create_time desc')->select()->toArray();
        $list=self::_postReplyListHandle($list,true);
        return $list;
    }

    /**
     * 获取推荐帖子
     */
    public static function getPostRecommend($page=1,$row = 10)
    {
        $time=time()-86400;
        $map=[
            'status'=>1,
            'create_time'=>['>',$time]
        ];
        $list=self::where($map)->field('tid')->page($page, $row)->select()->toArray();
        foreach ($list as &$val){
            $val=ComThread::getPostRecommend($val['tid']);
        }
        unset($val);
        return $list;
    }

}