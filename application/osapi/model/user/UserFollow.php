<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/6/10
 * Time: 14:40
 */

namespace app\osapi\model\user;


use app\osapi\model\BaseModel;
use app\osapi\model\common\Support;

class UserFollow extends BaseModel
{
    /**
     * 获取关注列表
     * @param $uid
     * @param $page
     * @param $row
     * @param string $order
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getFollowList($uid,$page=1,$row=10,$order='create_time desc')
    {
        $map=[
            'uid'=>$uid,
            'status'=>1
        ];
        $has_uid=
        $uids=self::where($map)->order($order)->field('follow_uid as uid,create_time,alias')->select()->toArray();
        $is_self=get_uid()==$uid?1:0;
        $list=self::_toUserList($uids,1,$is_self,$page,$row);
        return $list;
    }

    public static function getFollowListAll($uid,$order='create_time desc')
    {
        $map=[
            'uid'=>$uid,
            'status'=>1
        ];
        $uids=self::where($map)->order($order)->field('follow_uid as uid,create_time,alias')->limit(200)->select()->toArray();
        $is_self=get_uid()==$uid?1:0;
        $list=self::_toUserListALL($uids,1,$is_self);
        return $list;
    }

    /**
     * 获取粉丝列表
     * @param $uid
     * @param $page
     * @param $row
     * @param string $order
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getFansList($uid,$page=1,$row=10,$order='create_time desc')
    {
        $map=[
            'follow_uid'=>$uid,
            'status'=>1
        ];
        $uids=self::where($map)->order($order)->field('uid,create_time,alias')->select()->toArray();
        $list=self::_toUserList($uids,'','',$page,$row);
        return $list;
    }

    /**
     * 获取用户信息列表
     * @param $uids
     * @param int $follow
     * @param int $is_self
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _toUserList($uids,$follow=0,$is_self=0,$page=1,$row=10)
    {
        $uid=get_uid();
        if(count($uids)){
            //认证图标
            $icon_field=is_icon('');
            $fields = 'uid,nickname,sex,fans,signature,exp,avatar,'.$icon_field.'is_red';
            $uid_list=array_column($uids,'uid');
            $uids=array_combine($uid_list,$uids);
            $map=[
                'uid'=>['in',$uid_list],
                'status'=>1
            ];
            $list = UserModel::where($map)->page($page,$row)->field($fields)->select()->toArray();
            $is_follow_ids=self::where('uid',$uid)->where('status',1)->column('follow_uid');
            foreach ($list as &$val){
                $other_info=$uids[$val['uid']];
                $val['create_time']=$other_info['create_time'];//关注时间
                $val['grade']=UserModel::cacugrade($val['exp']);
                $val['is_follow'] = in_array($val['uid'],$is_follow_ids)?1:0;
                $val['avatar_64']=thumb_path($val['avatar'],64,64);
                $val['avatar_128']=thumb_path($val['avatar'],128,128);
                $val['avatar_256']=thumb_path($val['avatar'],256,256);
                if($follow&&$is_self){
                    $val['alias']=$other_info['alias'];//用户对关注对象的备注
                }
            }
            unset($val,$other_info);
            return $list;
        }else{
            return [];
        }
    }

    private static function _toUserListAll($uids,$follow=0,$is_self=0)
    {
        $uid=get_uid();
        if(count($uids)){
            //认证图标
            $icon_field=is_icon('');
            $fields = 'uid,nickname,sex,fans,signature,exp,avatar,'.$icon_field.'is_red';
            $uid_list=array_column($uids,'uid');
            $uids=array_combine($uid_list,$uids);
            $map=[
                'uid'=>['in',$uid_list],
                'status'=>1
            ];
            $list = UserModel::where($map)->field($fields)->select()->toArray();
            $is_follow_ids=self::where('uid',$uid)->where('status',1)->column('follow_uid');
            foreach ($list as &$val){
                $other_info=$uids[$val['uid']];
                $val['create_time']=$other_info['create_time'];//关注时间
                $val['grade']=UserModel::cacugrade($val['exp']);
                $val['is_follow'] = in_array($val['uid'],$is_follow_ids)?1:0;
                $val['avatar_64']=thumb_path($val['avatar'],64,64);
                $val['avatar_128']=thumb_path($val['avatar'],128,128);
                $val['avatar_256']=thumb_path($val['avatar'],256,256);
                if($follow&&$is_self){
                    $val['alias']=$other_info['alias'];//用户对关注对象的备注
                }
            }
            unset($val,$other_info);
            return $list;
        }else{
            return [];
        }
    }

    /**
     * 关注或取消关注用户，并更新好友关系
     * @param $follow_uid
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function doFollow($follow_uid)
    {
        $self_uid=get_uid();
        if($self_uid*$follow_uid==0||$follow_uid==$self_uid){
            return false;
        }
        $map=[
            'uid'=>$self_uid,
            'follow_uid'=>$follow_uid,
        ];
        $data=db('user_follow')->where($map)->find();
        if($data){
            if($data['status']==1){//已关注改成取消关注
                $status=0;
                //被取消关注人的粉丝数减一
                $fans_count=self::where('follow_uid',$follow_uid)->where('status',1)->count();
                UserModel::where('uid',$follow_uid)->setField('fans',$fans_count-1);
                //自己的关注数减一
                $follow_count=self::where('uid',$self_uid)->where('status',1)->count();
                UserModel::where('uid',$self_uid)->setField('follow',$follow_count-1);
                $res1=db('user_follow')->where($map)->update(['status'=>$status]);
                action_log($self_uid,6,'用户关注操作,row中存放被关注人uid','',$follow_uid);
                UserFriend::remarkFriend($follow_uid,0);

                //减任务积分
                self::canceltask();
                //减行为积分
                self::cancelaction();

                if($res1){
                    website_connect_notify($self_uid,0,$follow_uid,'osapi_user_follow_no');//通知第三方平台，任务回调
                    return true;
                }else{
                    return false;
                }
            }else{//取消关注状态改成关注状态
                $status=1;
                UserModel::where('uid',$follow_uid)->setInc('fans');
                UserModel::where('uid',$self_uid)->setInc('follow');
                $res1=db('user_follow')->where($map)->update(['status'=>$status,'create_time'=>time()]);
                action_log($self_uid,7,'用户取消关注操作,row中存放被取消关注人uid','',$follow_uid);

                UserFriend::remarkFriend($follow_uid,1);

                //加任务积分
                self::finishtask();
                //加行为积分
                self::finishaction();
                if($res1){
                    website_connect_notify($self_uid,0,$follow_uid,'osapi_user_follow_yes');//通知第三方平台，任务回调
                    return true;
                }else{
                    return false;
                }
            }
        }else{//新增关注状态
            $info=[
                'uid'=>get_uid(),
                'follow_uid'=>$follow_uid,
                'create_time'=>time(),
                'status'=>1
            ];
            UserModel::where('uid',$follow_uid)->setInc('fans');
            UserModel::where('uid',$self_uid)->setInc('follow');
            action_log($self_uid,6,'用户关注操作,row中存放被关注人uid','',$follow_uid);
            UserFriend::remarkFriend($follow_uid,1);

            $res2=self::add($info);

            //加任务积分
            self::finishtask();
            //加行为积分
            self::finishaction();
            if($res2){
                website_connect_notify($self_uid,0,$follow_uid,'osapi_user_follow_yes');//通知第三方平台，任务回调
                return true;
            }else{
                return false;
            }
        }
    }

    public static function finishtask()
    {
        $uid=get_uid();
        $guanzhu = db('system_renwu')->where('jifenflag','guanzhu')->find();
        if($guanzhu['status'] == 1){
            $followcount = self::whereTime('create_time',date('Y-m-d'))->where('uid',$uid)->where('status',1)->count();
            if($followcount == $guanzhu['require'] && $followcount >=1 ){//
                //增加积分
                Support::addrenwuscore($guanzhu,$followcount) ;

            }
        }
    }
    public static function canceltask()
    {
        $uid=get_uid();
        $guanzhu = db('system_renwu')->where('jifenflag','guanzhu')->find();
        if($guanzhu['status'] == 1){
            $followcount = self::whereTime('create_time',date('Y-m-d'))->where('uid',$uid)->where('status',1)->count();
            if($followcount +1  == $guanzhu['require'] && $followcount >=1 ){//小于等于设定值时，加积分
                //减任务积分
                Support::subrenwuscore($guanzhu,$followcount+1) ;

            }
        }
    }

    public static function finishaction()
    {
        $uid=get_uid();
        $guanzhu = db('system_rule_action')->where('actionflag','guanzhu')->find();
        $count =  self::whereTime('create_time',date('Y-m-d'))->where('uid',$uid)->where('status',1)->count();
        Support::addjifen($guanzhu,$count,$uid) ;
    }
    public static function cancelaction()
    {
        $uid=get_uid();
        $guanzhu = db('system_rule_action')->where('actionflag','guanzhu')->find();
        $count =  self::whereTime('create_time',date('Y-m-d'))->where('uid',$uid)->where('status',1)->count();
        Support::subjifen($guanzhu,$count,$uid) ;
    }


    /**
     * 判断是否已经关注
     * @param $uid
     * @param $follow_uid
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function isFollow($uid,$follow_uid)
    {
        $map=[
            'uid'=>$uid,
            'follow_uid'=>$follow_uid,
            'status'=>1
        ];
        $res=self::where($map)->count();
        if($res==1){
            return true;
        }else{
            return false;
        }
    }

}