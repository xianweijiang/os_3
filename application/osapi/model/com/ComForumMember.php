<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/6/3
 * Time: 16:35
 */

namespace app\osapi\model\com;


use app\commonapi\model\Gong;
use app\osapi\model\BaseModel;
use app\osapi\model\user\UserModel;
use app\osapi\model\user\UserTaskNew;
use app\osapi\model\com\ComForum;
class ComForumMember extends BaseModel
{
    /**
     * 获取用户版块数
     * @param $uid
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function getForumCount($uid)
    {
        $count=self::where('uid', $uid)->where('status', 1)->count();
        return $count;
    }

    /**
     * 判断是否是版块成员
     * @param $uid
     * @param $forum_id
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function isForumUser($uid,$forum_id)
    {
        $count=self::where('fid',$forum_id)->where('uid',$uid)->where('status',1)->count();
        if($count>0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 加入或取消加入版块
     * @param $forum_id
     * @param $uid
     * @return $this|int|string
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function addToForum($forum_id,$uid)
    {
        if($forum_id==0||$uid==0){
            return false;
        }
        $map['uid']=$uid;
        $map['fid']=$forum_id;
        $res = self::where($map)->find();
        //查看版块关注表中是否有记录
        self::startTrans();
        try{
            if ($res) {
                if ($res['status'] == 1) {  //若有记录并且已加入，则改为退出状态，并将该版块用户数减一
                    $is_admin=ComForum::_ForumAdmin($forum_id,$uid);
                    if($is_admin['admin_one']==0 && $is_admin['admin_two']==0){
                        $status = 0;
                        $now_count=self::where('fid',$forum_id)->where('status',1)->count();
                        ComForum::where('id', $forum_id)->setField('member_count',$now_count-1);

                        $forum_count=self::getForumCount($uid);
                        UserModel::where('uid',$uid)->setField('forum_count',$forum_count-1);
                        //行为加积分
                        Gong::actionsub('jiarubankuai','com_forum_member','uid') ;

                        //退出版块日志
                        action_log($uid,2,'退出版块');

                        website_connect_notify($uid,$forum_id,0,'osapi_com_addToForum_out');//通知第三方平台，任务回调
                    }else{
                        return 2;
                    }
                } else {  //若有记录并且已退出版块，则改为加入，并将该版块用户数加一
                    $status = 1;
                    ComForum::where('id', $forum_id)->setInc('member_count');
                    UserModel::where('uid',$uid)->setInc('forum_count');

                    //行为加积分
                    Gong::actionadd('jiarubankuai','com_forum_member','uid') ;

                    //加入版块日志
                    action_log($uid,1,'加入版块');

                    website_connect_notify($uid,$forum_id,0,'osapi_com_addToForum_in');//通知第三方平台，任务回调

                }
                self::update(['status'=>$status],$map); //更新操作
            } else {  //若没有记录则添加加入记录，并将该版块用户数加一
                self::add(['uid' => $uid, 'fid' => $forum_id,'status'=>1,'create_time'=>time()]);
                ComForum::where('id', $forum_id)->setInc('member_count');
                UserModel::where('uid',$uid)->setInc('forum_count');
                //加入版块日志
                action_log($uid,1,'加入版块');

                //行为加积分
                Gong::actionadd('jiarubankuai','com_forum_member','uid') ;

                website_connect_notify($uid,$forum_id,0,'osapi_com_addToForum_in');//通知第三方平台，任务回调

            }
            UserTaskNew::newAddToForum($uid); //加入版块新手任务
            self::commitTrans();
            return true;
        }catch (\Exception $e){
            self::rollbackTrans();
            self::setErrorInfo('操作失败：'.self::getErrorInfo().$e->getMessage());
            dump(self::getErrorInfo().$e->getMessage());exit;
            return false;
        }
    }


    /**
     * 获取用户加入的版块
     * @param $uid
     * @param int $page
     * @param int $row
     * @param string $order
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getUserForumList($uid,$page=1,$row=10,$order='create_time desc',$access,$video_is_on)
    {
        $map=[
            'uid'=>$uid,
            'status'=>1
        ];
        if($access[1] == '微信小程序' && $video_is_on==0){
            $map['type']=['neq',6];
        }
        $forum_list=self::where($map)->page($page,$row)->field('fid,create_time')->order($order)->select()->toArray();
        if(count($forum_list)){
            $fids=array_column($forum_list,'fid');
            $forum_list=array_combine($fids,$forum_list);
            $map_forum=[
                'id'=>['in',$fids],
                'status'=>1
            ];
            $forums=ComForum::where($map_forum)->field('id,pid,name,thread_count,post_count,member_count,support_count,summary,logo,type')->select()->toArray();
            $sort_arr = [];//用于排序
            foreach ($forums as &$val){
                $val['add_in_time']=$forum_list[$val['id']]['create_time'];//加入版块时间
                $val['is_member'] = true;//查询条件是用户加入的版块，所以当前状态肯定是已加入，无需再判断
                $val['logo_src']=get_root_path($val['logo']);

                $sort_arr[] = $val['add_in_time'];//用于排序
            }
            unset($val);
            array_multisort($sort_arr,SORT_DESC,$forums);//按加入时间排序
            $count=ComForum::where($map_forum)->count();
            return ['list'=>$forums,'count'=>$count];
        }else{
            return [];
        }
    }

    /**
     * 获取用户加入的版块
     * @param $uid
     * @param int $page
     * @param int $row
     * @param string $order
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getUserFollow($uid)
    {
        $map=[
            'uid'=>$uid,
            'status'=>1
        ];
        $forum_list=self::where($map)->field('fid,create_time')->select()->toArray();
        return $forum_list;
    }

}