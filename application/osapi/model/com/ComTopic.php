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


use app\osapi\model\BaseModel;
use app\osapi\model\user\UserModel;
use think\Cache;

class ComTopic extends BaseModel
{
    /**
     * 热门话题列表
     */
    public static function hotTopicList($class_id)
    {
        $field='id,title,summary,image,view_count,follow_count,post_count';
        $map['status']=1;
        $map['is_hot']=1;
        if($class_id){
            $map['class_id']=$class_id;
        }
        $list = self::where($map)->where('hot_end_time','>',time())->field($field)->order('hot_time desc')->select();
        if($list){
            $list=$list->toArray();
        }
        return $list;
    }

    /**
     * 话题列表
     */
    public static function TopicList($page,$row,$class_id)
    {
        $field='id,title,summary,image,view_count,follow_count,post_count';
        $hot_id=self::where('status',1)->where('is_hot',1)->where('hot_end_time','>',time())->column('id');
        $map['status']=1;
        if($class_id){
            $map['class_id']=$class_id;
        }
        $list = self::where($map)->where('id','not in',$hot_id)->field($field)->page($page,$row)->order('update_time desc')->select();
        if($list){
            $list=$list->toArray();
        }
        return $list;
    }

    /**
     * 话题列表
     */
    public static function SendTopicList($class_id)
    {
        $field='id,title,summary,image,view_count,follow_count,post_count';
        $map['status']=1;
        $map['class_id']=$class_id;
        $list=Cache::get('send_topic_list'.$class_id);
        if(!$list){
            $list = self::where($map)->field($field)->order('update_time desc')->select();
            Cache::tag('index_topic_list')->set('send_topic_list'.$class_id,$list,600);
        }
        if($list){
            $list=$list->toArray();
        }
        return $list;
    }

    /**
     * 我发布的话题话题列表
     */
    public static function UserSendTopicList($page,$row,$uid)
    {
        $field='id,title,summary,image,view_count,follow_count,post_count';
        $follow_id=db('com_topic_follow')->where('status',1)->where('uid',$uid)->order('create_time desc')->column('oid');
        $list = self::where('status','>',0)->where('uid',$uid)->field($field)->page($page,$row)->order('update_time desc')->select();
        if($list){
            $list=$list->toArray();
            foreach ($list as &$value){
                if(in_array($value['id'],$follow_id)){
                    $value['is_follow']=1;
                }else{
                    $value['is_follow']=0;
                }
            }
        }
        return $list;
    }

    /**
     * 我关注的话题话题列表
     */
    public static function UserFollowTopicList($page,$row,$uid)
    {
        $field='id,title,summary,image,view_count,follow_count,post_count';
        $follow_id=db('com_topic_follow')->where('status',1)->where('uid',$uid)->order('create_time desc')->column('oid');
        $list = self::where('status',1)->where('id','in',$follow_id)->field($field)->page($page,$row)->select();
        if($list){
            $list=$list->toArray();
            foreach ($list as &$value){
                if(in_array($value['id'],$follow_id)){
                    $value['is_follow']=1;
                }else{
                    $value['is_follow']=0;
                }
            }
        }
        return $list;
    }

    /**
     * 搜索话题
     */
    public static function  searchTopic($keyword,$page=1,$row=10){
        $topic=self::where('title','like','%'.$keyword.'%')->page($page,$row)->order('update_time desc')->select();
        return $topic;
    }

    public static function add($data)
    {
        $object=self::create($data);
        return $object->getLastInsID();
    }

    /**
     * 话题详情
     */
    public static function detailTopic($id){
        $uid=get_uid();
        $topic=self::where('id',$id)->find()->toArray();
        $topic['is_follow']=db('com_topic_follow')->where('uid',$uid)->where('oid',$id)->where('status',1)->count();
        $topic['nickname']=UserModel::where('uid',$topic['uid'])->value('nickname');
        return $topic;
    }

    /**
     * 获取话题标题
     */
    public static function getTopicTitle($id)
    {
        $title=Cache::get('get_topic_id'.$id);
        if(!$title){
            $title = self::where('id',$id)->value('title');
            Cache::set('get_topic_id'.$id,$title,600);
        }
        return $title;
    }

}