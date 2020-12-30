<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/30
 * Time: 14:52
 */

namespace app\osapi\model\com;


use app\admin\model\com\ForumPower;
use app\osapi\model\BaseModel;
use app\osapi\model\com\ComForumMember;
use think\Cache;

class ComForum extends BaseModel
{
    /**
     * 获取全部版块信息渲染版块主页,只到二级
     * @param int $page
     * @param int $row
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getForumSecond($page=1,$row=10)
    {
        $map['pid']=0;
        $map['status']=1;
        //获取一级版块列表
        $list = self::where($map)->field('id,name,list_style,title,content')->order('sort desc')->page($page, $row)->select()->toArray();
        foreach ($list as $k => &$v) {
            $map['pid']=$v['id'];
            //获取每个一级版块下的二级列表
            $subList = self::where($map)->field('id,pid,name,logo,summary,post_count,member_count,title,content')->order('sort desc')->select()->toArray();
            $v['child_list'] = self::_handleForumLevel($subList);
            unset($val);
        }
        unset($v);
        return $list;
    }

    public static function getOne($id)
    {
        $list=Cache::get('getOne'.$id);
        if(!$list){
            $list=self::where('id',$id)->find();
            Cache::set('getOne'.$id,$list,86400);
        }
        return $list;
    }

    public static function getForumOne($access,$video_is_on)
    {
        $map['status']=1;
        $map['display']=1;
        if($access[1] == '微信小程序' && $video_is_on==0){
            $video_forum=self::where('status',1)->where('type',6)->column('id');
            $map['id']=['not in',$video_forum];
        }
        //获取所有版块列表
        $list2=Cache::get('getForumOne');
        if(!$list2){
            $list = self::where($map)->field('id,name,logo,background,summary,is_hot,false_num,post_count,type,pid,title,content')->order('sort asc,is_hot desc,false_num desc')->select()->toArray();
            $list2=array();
            foreach($list as &$v){
                if($v['pid']==0){
                    $child_list=array();
                    foreach($list as &$val){
                        if($val['pid']==$v['id']){
                            $child_list[]=self::_handleForumLevel($val);
                        }
                    }
                    unset($val);
                    $v['child_list']=$child_list;
                    $list2[]=$v;
                }
            }
            unset($v);
            Cache::set('getForumOne',$list2,600);
        }
        return $list2;
    }

    public static function getForumTwo($fid,$page=1,$row=10)
    {
        $uid=get_uid();
        $map['pid']=$fid;
        $map['status']=1;
        //获取一级版块列表
        $list = self::where($map)->field('id,name,logo,background,summary,is_hot,false_num,post_count,type,pid,title,content')->order('sort desc,is_hot desc,false_num desc')->page($page, $row)->select()->toArray();
        foreach($list as $k => &$v){
            $res=ComForumMember::isForumUser($uid,$v['id']);
            if($res){
                $v['is_follow']=1;
            }else{
                $v['is_follow']=0;
            }
            $map['pid']=$v['id'];
            //获取每个一级版块下的二级列表
            $subList = self::where($map)->field('id,name,logo,background,summary,is_hot,false_num,post_count,type,title,content,pid')->order('sort desc,is_hot desc,false_num desc')->select()->toArray();
            $v['child_list'] = self::_handleForumLevel($subList);
        }
        return $list;
    }

    public static function getForumSend()
    {
        $map['status']=1;
        $map['display']=1;
        $map['type']=array('in',array(1,2,6,8));
        //获取所有版块列表
        $list2=Cache::get('getForumSend');
        if(!$list2){
            //版块权限排除私密帖子
            $mav['status']=1;
            $mav['id']=['not in',ForumPower::get_private_id()];
            $forum_ids=ComForum::where($mav)->column('id');
            $list = self::where($map)->where('id','in',$forum_ids)->field('id,name,logo,background,summary,is_hot,false_num,post_count,type,pid,title,content')->order('sort asc,is_hot desc,false_num desc')->select()->toArray();
            $list2=array();
            foreach($list as &$v){
                if($v['pid']==0){
                    $child_list=array();
                    foreach($list as &$val){
                        if($val['pid']==$v['id']){
                            $child_list[]=self::_handleForumLevel($val);
                        }
                    }
                    unset($val);
                    $v['child_list']=$child_list;
                    $list2[]=$v;
                }
            }
            unset($v);
            Cache::set('getForumSend',$list2,600);
        }
        return $list2;
    }

    /**
     * 处理每级版块的信息
     * @param $list
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _handleForumLevel($list)
    {
        $news=ComThread::whereTime('create_time', 'd')->column('fid');
        $list['logo_src'] = get_root_path($list['logo']);
        //获取当天新增的帖子数
        $list['new']=0;
        foreach($news as &$v){
            if($list['id']==$v){
                $list['new']=$list['new']+1;
            }
        }
        $list['post_count']=ComThread::where('fid',$list['id'])->where('status',1)->count();
        $follow_count=db('com_forum_member')->where('fid',$list['id'])->where('status',1)->count();
        $list['follow_count']=$follow_count+$list['false_num'];
        return $list;
    }

    /**
     * 获取具体版块的详情信息
     * @param $forumId
     * @param int $uid
     * @return array|mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getForumDetail($forumId, $uid = 0)
    {
        $reload=false;//是否不读缓存，直接重新加载数据
        if($uid){
            $has_change_info_tag='forum_index_top_detail_has_change_fid_'.$forumId.'_uid_'.$uid;
            $has_change_info=Cache::get($has_change_info_tag);
            if($has_change_info){
                $reload=true;//不读缓存，直接重新加载数据
                Cache::rm($has_change_info_tag);
            }
        }
        $tag='forum_index_top_detail_fid_'.$forumId;
        if($reload){//不读缓存，直接重新加载数据
            $forum=false;
        }else{//优先读缓存
            $forum=Cache::get($tag);
        }
        if(!$forum){
            $forum = self::where('id', $forumId)->field('id,pid,name,post_count,member_count,summary,logo,background,false_num,type,title,content')->find();
            $forum = self::_forumDetailCheck($forum, $uid);  //版块信息处理
            Cache::set($tag,$forum,10*60);
        }
        if ($uid != 0) {
            $forum['is_member'] = ComForumMember::isForumUser($uid, $forum['id']);
            //若传了uid参数，则判断当前uid用户是否加入了该版块
        } else {
            $forum['is_member'] = false;
        }
        return $forum;
    }

    /**
     * 获取版块的分类数，logo
     * @param $forum
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _forumDetailCheck($forum)
    {
        $forum['thread_class_count'] = ComThreadClass::where('fid', $forum['id'])->where('status',1)->count();
        $forum['post_count'] = ComThread::where('fid', $forum['id'])->where('status',1)->count();
        $follow_count=db('com_forum_member')->where('fid',$forum['id'])->where('status',1)->count();
        $forum['follow_count']=$follow_count+$forum['false_num'];
        $forum['logo_src'] = get_root_path($forum['logo']);
        return $forum;
    }


    /**
     * 搜索版块
     */
    public static function  searchForum($keyword,$page=1,$row=10){
        $map['id']=['not in',ForumPower::get_private_id()];
        $map['name']=['like','%'.$keyword.'%'];
        $map['status']=1;
        $thread=self::where($map)->page($page, $row)->field('id,pid,name,type,status,logo,background,create_time,summary,admin_uid,level,is_hot,false_num,member_count,title,content')->order('create_time desc')->select();
        return $thread;
    }

    /**
     * qhy
     * 是否是管理员
     */
    public static function _ForumAdmin($fid,$uid,$comment_id=''){
        //判断是否是管理组
        $is_admin=db('bind_group_uid')->where(['uid'=>$uid,'g_id'=>2,'status'=>1,'end_time'=>[['eq',0],['gt',time()],'or']])->count();

        $pid=self::where('id',$fid)->value('pid');
        $data['admin_one']=db('com_forum_admin')->where('fid',$fid)->where('uid',$uid)->where('status',1)->count();
        $data['admin_two']=db('com_forum_admin')->where('fid',$pid)->where('uid',$uid)->where('status',1)->count();
        $data['admin_three']=$is_admin?1:0;

        //判断是否是自己的评论,可以删除自己的帖子
        if($comment_id){
            $com_uid=db('com_post')->where(['id'=>$comment_id])->value('author_uid');
            if($uid==$com_uid) $data['admin_one']=1;
        }
        return $data;
    }

}