<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/22
 * Time: 9:39
 */

namespace app\commonapi\controller;


use app\admin\model\com\ForumPower;
use app\osapi\model\com\ComThread;
use app\osapi\model\com\ComTopic;
use basic\ControllerBasic;
use think\Config;
use app\commonapi\model\rank\RankUser;
use app\commonapi\model\rank\RankThread;
use app\commonapi\model\rank\RankTopic;
use app\commonapi\model\rank\RankSearch;
use think\Cache;
use app\osapi\model\user\UserModel;
use app\commonapi\model\rank\Rank as RankModel;

class Rank extends ControllerBasic
{

    /**
     * qhy
     * 榜单列表
     */
    public function RankList(){
        $list=Cache::get('rank_list');
        if(!$list){
            $list=RankModel::where('status',1)->order('sort asc')->select()->toArray();
            foreach($list as &$value){
                switch($value['id']){
                    case 1;
                        $thread=RankThread::where('type',1)->where('tid','not in',ForumPower::get_private_id_com())->where('time_type',4)->order('sort asc')->limit(3)->column('tid');
                        foreach($thread as &$val){
                            $val=ComThread::where('id',$val)->value('title');
                        }
                        unset($val);
                        $value['rank_list']=$thread;
                        break;
                    case 2;
                        $thread=RankThread::where('type',2)->where('tid','not in',ForumPower::get_private_id_com())->where('time_type',4)->order('sort asc')->limit(3)->column('tid');
                        foreach($thread as &$val){
                            $val=ComThread::where('id',$val)->value('title');
                        }
                        unset($val);
                        $value['rank_list']=$thread;
                        break;
                    case 3;
                        $thread=RankThread::where('type',3)->where('tid','not in',ForumPower::get_private_id_com())->where('time_type',4)->order('sort asc')->limit(3)->column('tid');
                        foreach($thread as &$val){
                            $val=ComThread::where('id',$val)->value('content');
                            $val=json_decode($val);
                        }
                        unset($val);
                        $value['rank_list']=$thread;
                        break;
                    case 4;
                        $thread=RankThread::where('type',4)->where('tid','not in',ForumPower::get_private_id_com())->where('time_type',4)->order('sort asc')->limit(3)->column('tid');
                        foreach($thread as &$val){
                            $val=ComThread::where('id',$val)->value('title');
                        }
                        unset($val);
                        $value['rank_list']=$thread;
                        break;
                    case 5;
                        $topic=RankTopic::where('status',1)->order('sort asc')->limit(3)->column('oid');
                        foreach($topic as &$val){
                            $val=ComTopic::where('id',$val)->value('title');
                        }
                        unset($val);
                        $value['rank_list']=$topic;
                        break;
                    case 6;
                        $user=RankUser::where('status',1)->order('rank asc')->limit(3)->column('nickname');
                        $value['rank_list']=$user;
                        break;
                    case 7;
                        $serach=RankSearch::where('status',1)->where('is_del',0)->where('end_time',0)->whereOr('end_time','>',time())->order('sort desc,num desc')->limit(3)->column('keyword');
                        $value['rank_list']=$serach;
                        break;
                }
            }
            Cache::set('rank_list',$list,3600);
        }
        $this->apiSuccess($list);
    }

    /**
     * qhy
     * 人气榜首次导入
     */
    public function UserRankFirst(){
        $res=RankUser::firstUser();
        if($res===false){
            echo '失败';
        }else {
            echo '成功';
        }
    }


    /**
     * qhy
     * 热搜榜列表
     */
    public function SearchRankList(){
        $list=Cache::get('search_rank_list');
        if(!$list){
            $list=RankSearch::getList();
            $rank=RankModel::where('id',7)->find();
            $time=$rank['frequency']*3600;
            Cache::set('search_rank_list',$list,$time);
        }
        foreach($list as &$value){
            if($value['end_time'] < time() && $value['end_time'] > 0){
                Cache::rm('search_rank_list');
                self::SearchRankList();
            }
        }
        unset($value);
        $this->apiSuccess($list);
    }

    /**
     * qhy
     * 话题榜列表
     */
    public function TopicRankList(){
        $list=Cache::get('topic_rank_list');
        if(!$list){
            $list=RankTopic::getList();
            Cache::set('topic_rank_list',$list);
        }
        $this->apiSuccess($list);
    }

    /**
     * qhy
     * 人气榜列表
     */
    public function UserRankList(){
        $type=input('type','all','text');
        $uid=get_uid();
        switch($type){
            case 'all':
                $list=Cache::get('user_rank_list_all'.$uid);
                if(!$list){
                    $list=RankUser::getList($type,$uid);
                    Cache::tag('user_rank_list')->set('user_rank_list_all'.$uid,$list);
                }
                $rank=RankUser::where('uid',$uid)->find();
                break;
            case 'week':
                $list=Cache::get('user_rank_list_week'.$uid);
                if(!$list){
                    $list=RankUser::getList($type,$uid);
                    Cache::tag('user_rank_list')->set('user_rank_list_week'.$uid,$list);
                }
                $rank=RankUser::where('uid',$uid)->find();
                break;
            default:
                $list=Cache::get('user_rank_list_all'.$uid);
                if(!$list){
                    $list=RankUser::getList($type,$uid);
                    Cache::tag('user_rank_list')->set('user_rank_list_all'.$uid,$list);
                }
                $rank=RankUser::where('uid',$uid)->find();
                break;
        }
        $data['rank']=$rank;
        $data['list']=$list;
        $this->apiSuccess($data);
    }

    /**
     * qhy
     * 热评榜列表
     */
    public function ThreadRankList(){
        $type=input('post.type/d',1);
        $time_type=input('post.time_type/d',1);
        $uid=get_uid();
        $list=Cache::get('thread_rank_list_type_'.$type.'_'.$time_type.'_'.$uid);
        if(!$list){
            $list=RankThread::getList($type,$time_type);
            Cache::tag('thread_rank_list')->tag('thread_rank_list'.$uid)->set('thread_rank_list_type_'.$type.'_'.$time_type.'_'.$uid,$list);
        }
        $fids=array_column($list,'fid');
        $forumList=db('com_forum')->where('id','in',$fids)->field('id,name,type')->select();
        $forumList=array_combine(array_column($forumList,'id'),$forumList);
        $tids=array_column($list,'id');
        $is_collect_ids=db('collect')->where('uid',$uid)->where('tid','in',$tids)->where('status',1)->column('tid');
        $post_ids=array_column($list,'post_id');
        $is_support_ids=db('support')->where('model','thread')->where('uid',$uid)->where('status',1)->where('row','in',$post_ids)->column('row');
        $author_uids=array_column($list,'author_uid');
        $is_follow_uids=db('user_follow')->where('uid',$uid)->where('follow_uid','in',$author_uids)->where('status',1)->column('follow_uid');
        foreach ($list as &$val){
            $val['is_collect'] = in_array($val['id'],$is_collect_ids)?1:0;//判断当前用户是否对帖子收藏
            $val['is_support'] = in_array($val['post_id'],$is_support_ids)?true:false;;  //判断当前用户是否对帖子点过赞
            $val['user'] = UserModel::getUserInfo($val['author_uid'],$val['fid']);
            $val['user']['is_follow'] = in_array($val['author_uid'],$is_follow_uids)?true:false;;  //判断当前用户是否对作者关注
            $val['create_time']=time_to_show($val['create_time']);
            $val['forum']=isset($forumList[$val['fid']])?$forumList[$val['fid']]:null;
        }
        unset($val);
        $this->apiSuccess($list);
    }

}