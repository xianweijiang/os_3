<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use service\CacheService;
use service\JsonService as Json;
use service\UtilService;
use think\Log;
use think\Cache;
use think\Request;

/**
 * 首页控制器
 * Class Clear
 * @package app\admin\controller
 *
 */
class Correct extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 版块数据修正
     */
    public function correct_forum(){
        $forum=db('com_forum')->select();
        foreach($forum as &$value){
            $data['post_count']=db('com_thread')->where('fid',$value['id'])->where('status',1)->count();
            $data['member_count']=db('com_forum_member')->where('fid',$value['id'])->where('status',1)->count();
            db('com_forum')->where('id',$value['id'])->update($data);
        }
        unset($value);
        return Json::successful('版块数据修正成功!');
    }

    /**
     * 帖子数据修正
     */
    public function correct_thread(){
        if(Request::instance()->isPost()){
            $args=UtilService::postMore([
                ['page',1],
                ['row',1000]
            ]);
            $page=$args['page'];
            $row=$args['row'];
            $thread=db('com_thread')->page($page,$row)->field('id,post_id')->order('id asc')->select();
            $ids=array_column($thread,'id');
            $reply_count_list=db('com_post')->where('tid','in',$ids)->where('is_thread',0)->where('status',1)->group('tid')->field('tid,count(tid) as reply_count')->select();
            $reply_count_list=array_combine(array_column($reply_count_list,'tid'),$reply_count_list);

            $collect_count_list=db('collect')->where('tid','in',$ids)->where('status',1)->group('tid')->field('tid,count(tid) as collect_count')->select();
            $collect_count_list=array_combine(array_column($collect_count_list,'tid'),$collect_count_list);

            $post_ids=array_column($thread,'post_id');
            $support_count_list=db('support')->where('row','in',$post_ids)->where('model','thread')->where('status',1)->group('row')->field('row,count(row) as support_count')->select();
            $support_count_list=array_combine(array_column($support_count_list,'row'),$support_count_list);

            $sql='UPDATE '.config('database.prefix').'com_thread SET ';
            $sql=$sql.'reply_count = CASE id ';
            foreach ($ids as $val){
                if(isset($reply_count_list[$val])){
                    $reply_count=$reply_count_list[$val]['reply_count'];
                }else{
                    $reply_count=0;
                }
                $sql=$sql.' WHEN '.$val.' THEN '.$reply_count;
            }
            unset($val,$reply_count,$reply_count_list);
            $sql=$sql.' END ';

            $sql=$sql.', collect_count = CASE id ';
            foreach ($ids as $val){
                if(isset($collect_count_list[$val])){
                    $collect_count=$collect_count_list[$val]['collect_count'];
                }else{
                    $collect_count=0;
                }
                $sql=$sql.' WHEN '.$val.' THEN '.$collect_count;
            }
            unset($val,$collect_count,$collect_count_list);
            $sql=$sql.' END ';

            $sql=$sql.',support_count = CASE post_id ';
            foreach ($post_ids as $val){
                if(isset($support_count_list[$val])){
                    $support_count=$support_count_list[$val]['support_count'];
                }else{
                    $support_count=0;
                }
                $sql=$sql.' WHEN '.$val.' THEN '.$support_count;
            }
            unset($val,$support_count,$support_count_list);
            $sql=$sql.' END ';

            $sql=$sql.'WHERE id IN ('.implode(',',$ids).')';
            db()->execute($sql);
            unset($sql);

            $return_data=[
                'has_more'=>(count($thread)==$row?1:0),
                'now_page'=>$page,
                'do_num'=>count($thread)
            ];
            return Json::successful($return_data);
        }else{
            $this->assign('title','帖子数据修正');
            $this->assign('link_url',url('admin/system.correct/correct_thread'));
            return $this->fetch('long-link');
        }
    }

    /**
     * 用户数据修正
     */
    public function correct_user(){
        if(Request::instance()->isPost()){
            $args=UtilService::postMore([
                ['page',1],
                ['row',1000]
            ]);
            $page=$args['page'];
            $row=$args['row'];
            $uids=db('user')->page($page,$row)->order('uid asc')->column('uid');

            $fans_count_list=db('user_follow')->where('follow_uid','in',$uids)->where('status',1)->group('follow_uid')->field('follow_uid,count(follow_uid) as fans_count')->select();
            $fans_count_list=array_combine(array_column($fans_count_list,'follow_uid'),$fans_count_list);

            $follow_count_list=db('user_follow')->where('uid','in',$uids)->where('status',1)->group('uid')->field('uid,count(uid) as follow_count')->select();
            $follow_count_list=array_combine(array_column($follow_count_list,'uid'),$follow_count_list);

            $post_count_list=db('com_thread')->where('author_uid','in',$uids)->where('status',1)->group('author_uid')->field('author_uid,count(author_uid) as post_count')->select();
            $post_count_list=array_combine(array_column($post_count_list,'author_uid'),$post_count_list);

            $collect_count_list=db('collect')->where('uid','in',$uids)->where('status',1)->group('uid')->field('uid,count(uid) as collect_count')->select();
            $collect_count_list=array_combine(array_column($collect_count_list,'uid'),$collect_count_list);

            $sql='UPDATE '.config('database.prefix').'user SET ';
            $sql=$sql.'fans = CASE uid ';
            foreach ($uids as $val){
                if(isset($fans_count_list[$val])){
                    $fans_count=$fans_count_list[$val]['fans_count'];
                }else{
                    $fans_count=0;
                }
                $sql=$sql.' WHEN '.$val.' THEN '.$fans_count;
            }
            unset($val,$fans_count,$fans_count_list);
            $sql=$sql.' END ';

            $sql=$sql.', follow = CASE uid ';
            foreach ($uids as $val){
                if(isset($follow_count_list[$val])){
                    $follow_count=$follow_count_list[$val]['follow_count'];
                }else{
                    $follow_count=0;
                }
                $sql=$sql.' WHEN '.$val.' THEN '.$follow_count;
            }
            unset($val,$follow_count,$follow_count_list);
            $sql=$sql.' END ';

            $sql=$sql.',post_count = CASE uid ';
            foreach ($uids as $val){
                if(isset($post_count_list[$val])){
                    $post_count=$post_count_list[$val]['post_count'];
                }else{
                    $post_count=0;
                }
                $sql=$sql.' WHEN '.$val.' THEN '.$post_count;
            }
            unset($val,$post_count,$post_count_list);
            $sql=$sql.' END ';

            $sql=$sql.',collect = CASE uid ';
            foreach ($uids as $val){
                if(isset($collect_count_list[$val])){
                    $collect_count=$collect_count_list[$val]['collect_count'];
                }else{
                    $collect_count=0;
                }
                $sql=$sql.' WHEN '.$val.' THEN '.$collect_count;
            }
            unset($val,$collect_count,$collect_count_list);
            $sql=$sql.' END ';

            $sql=$sql.'WHERE uid IN ('.implode(',',$uids).')';
            db()->execute($sql);
            unset($sql);
            $return_data=[
                'has_more'=>(count($uids)==$row?1:0),
                'now_page'=>$page,
                'do_num'=>count($uids)
            ];
            return Json::successful($return_data);
        }else{
            $this->assign('title','用户数据修正');
            $this->assign('link_url',url('admin/system.correct/correct_user'));
            return $this->fetch('long-link');
        }
    }

}


