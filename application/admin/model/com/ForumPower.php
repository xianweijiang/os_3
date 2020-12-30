<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/2/5
 * Time: 10:23
 */
namespace app\admin\model\com;
use app\admin\model\group\Group;
use app\admin\model\group\Power;
use app\admin\model\system\SystemConfig;
use app\osapi\model\user\UserModel;
use think\Cache;
use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;

class ForumPower extends ModelBasic
{
    use ModelTrait;

    public static function addDate($data){
        return self::set($data);
    }

    public static function editData($data){
        if(self::where(['id'=>$data['id']])->count()){
            Cache::rm('_forum_power_'.$data['id']);
            return self::where(['id'=>$data['id']])->update($data);
        }else{
            return self::addDate($data);
        }
    }
    public static function getDate($id){
        $map['id']=$id;
        $data=self::where($map)->cache('_forum_power_'.$id)->find();
              //初始化权限
        if(empty($data)){
            $data['audit']=$data['visit']=$data['send_thread']=$data['send_comment']=0;
            $data['browse']='';
            $data['forum_id_id']='2,3,4';
            $data['forum_id_name']='管理员,版主,超级版主';
        }else{
            $data=$data->toArray();
            $data['forum_id_id']='';
            $data['forum_id_name']='';
        }
        $sign=['visit','send_thread','send_comment','browse','forum_id'];
        foreach ($sign  as $vo){
            $data[$vo.'_id']= $data[$vo.'_group_id']= $data[$vo.'_name']='';
        }
        unset($vo);
        return $data;
    }
    public static function setStatus($map,$status){
        //将用户变成匿名
        return self::where($map)->update(['status'=>$status]);
    }

    public static function get_user_power($action,$uid){

    }

    /**
     * 获取用户版块权限
     * @param $uid
     * @param $forum
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.3
     */
    public static function get_forum_power($uid,$forum){
        //判断分区是否是私密分区
        $is_private=self::is_private_pid_forum($forum,$uid);
        if(!$is_private){
            //不在私密圈子的范围内
            $power['audit']=-1;
            $power['visit']= $power['send_thread']=$power['send_comment']=$power['browse_power']=0;
            $power['browse']=['eq',$uid];
            return $power;
        }

        $forum_group=self::getDate($forum);
        $type=['audit','visit','send_thread','send_comment','browse'];
        //是否是版主或者超级版主
        if($uid){
            $is_admin=db('bind_group_uid')->where(['uid'=>$uid,'g_id'=>['in',[2,3]],'status'=>1])->count();
        }else{
            $is_admin=false;
        }
        //是否是不受限制用户组
        $no_limit=self::is_power($forum,'forum_id',$uid);

        //默认权限全部都给与
        $power['audit']=$power['visit']= $power['send_thread']=$power['send_comment']=$power['browse_power']=1;
        $power['browse']='';
        if($is_admin||$no_limit||!$forum_group){
            return $power;
        }

//        //是否关注版块
//        $is_follow=db('com_forum_member')->where(['uid'=>$uid,'fid'=>$forum,'status'=>1])->count();
        //是否加入版块
        $is_audit=db('com_forum_member')->where(['uid'=>$uid,'fid'=>$forum,'status'=>1])->count();
        $is_follow=$is_audit;
        //是否是版主
        $is_admin=db('com_forum_admin')->where(['uid'=>$uid,'fid'=>$forum,'status'=>1])->count();
        foreach ($type as $v){
            switch ($v){
                case 'audit':
                    //是否开启访问审核0不审核 1审核
                    if($forum_group['audit']==1){
                        if($is_audit){
                            $audit=1;
                        }else{
                            $audit=0;
                        }
                    }else{
                        $audit=1;
                    }
                    $power['audit']=$audit;
                    break;
                case 'visit':
                    //访问权限 0=>'公开'，1=>'加入版块的' 2=>'指定用户组'
                    if($forum_group['visit']==0||self::is_power($forum,'visit',$uid)||($forum_group['visit']==1&&$is_audit>0)){
                        $visit=1;
                    }else{
                        $visit=0;
                    }
                    $power['visit']=$visit;
                    break;
                case 'send_thread':
                    //发帖权限 0=>'同版块访问'，1=>'关注用户发帖'，2=>'仅版主发帖',3=>'制定用户组'
                    if($forum_group['send_thread']==0){
                        $send_thread=$power['visit'];
                    }elseif($forum_group['send_thread']==1){
                        $send_thread=$is_follow?1:0;
                    }elseif($forum_group['send_thread']==3){
                        $send_thread=self::is_power($forum,'send_thread',$uid)?1:0;
                    }else{
                        $send_thread=0;
                    }
                    $power['send_thread']=$send_thread;
                    break;
                case 'send_comment':
                    //评论权限 0=>'同版块访问'，1=>'仅允许关注版块的用户评论'，2=>'仅作者及版主评论（该版块版主及超级版主）',3=>'制定用户组'
                    if($forum_group['send_comment']==0){
                        $send_comment=$power['visit'];
                    }elseif ($forum_group['send_comment']==1&&$is_follow){
                        $send_comment=1;
                    }elseif($forum_group['send_comment']=3){
                        $send_comment=self::is_power($forum,'send_comment',$uid)?1:0;
                    }else{
                        $send_comment=0;
                    }
                    $power['send_comment']=$send_comment;
                    break;
                case 'browse':
                    //浏览权限 0=>'同版块访问'，1=>'仅作者及版主可见'，2=>'仅作者及指定用户组可见';
                    $browse='';
                    if($forum_group['browse']==0){
                        if($power['visit']!=1){
                            $browse=['eq',$uid];
                        }
                    }elseif($forum_group['browse']==1){
                        //获取版主
                        if(!$is_admin){
                            $browse=['eq',$uid];
                        }
                    }elseif($forum_group['browse']==2){
                        //指定用户组
                        if(!self::is_power($forum,'browse',$uid)){
                            $browse=['eq',$uid];
                        }
                    }
                    $power['browse']=$browse;
                    break;
                case 'browse_power':
                    //浏览权限 0=>'同版块访问'，1=>'仅作者及版主可见'，2=>'仅作者及指定用户组可见';
                    //仅作者在调用这个的外部判断
                    $browse=1;
                    if($forum_group['browse']==0){
                        $browse=$power['visit'];
                    }elseif($forum_group['browse']==1){
                        if($is_admin){
                            $browse=1;
                        }
                    }elseif($forum_group['browse']==2){
                        //指定用户组
                        if(self::is_power($forum,'browse',$uid)){
                            $browse=1;
                        }
                    }else{
                        $browse=0;
                    }
                    $power['browse_power']=$browse;
            }
        }
        unset($v);
        return $power;

    }

    /**
     * 判断用户组是否有权限
     * @param $f_id
     * @param $action
     * @param $uid
     * @return array|boolean
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.3
     */
    public static function is_power($f_id,$action,$uid){
        $user_g_id=Group::get_group_by_uid($uid);
        $g_id=self::get_user_by_group($f_id,$action);
        return array_intersect($g_id,$user_g_id);
    }

    /**
     * 判断分区用户在分区内是否有权限
     * @param $fid
     * @param $uid
     * @return bool
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.17
     */
    public static function is_private_pid_forum($fid,$uid){
        $pid=db('com_forum')->where(['id'=>$fid])->value('pid');
        $pid_two=db('com_forum')->where(['id'=>$pid])->value('pid');
        $pid_two=$pid_two?$pid_two:$pid;
        $group=db('com_forum')->where(['id'=>$pid_two])->value('group');
        if($group){
            $user_g_id=Group::get_group_by_uid($uid);
            $group=explode(',',$group);
            if(!array_intersect($group,$user_g_id)){
                return false;
            }
        }
        return true;
    }

    /**
     * 获取版块权限的group
     * @param $f_id
     * @param $action
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.9
     */
    public static function get_user_by_group($f_id,$action){
        $group=db('bind_forum_group')->where(['bind_forum'=>$f_id,'type'=>$action,'status'=>1])->value('group');
        $g_id=explode(',',$group);
        return $g_id;
    }

    /**
     * 获取私密版块id集合
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.16
     */
    public static function get_private_id(){
        $ids_all=Cache::get('private_id');
        if(!$ids_all){
            $ids=self::whereOr(['visit'=>['gt',0]])->whereOr(['audit'=>1])->whereOr(['browse'=>['gt',0]])->column('id');
            $forum_id=db('com_forum')->where(['group'=>['neq','']])->column('id');
            //是否存在私密分区
            if($forum_id){
                $ids_two=db('com_forum')->where(['pid'=>['in',$forum_id]])->column('id');
                if($ids_two){
                    $ids_three=db('com_forum')->where(['pid'=>['in',$ids_two]])->column('id');
                }else{
                    $ids_three=[0];
                }
            }else{
                $ids_two=[0];
                $ids_three=[0];
            }
            $ids_all=array_merge($ids,$ids_two,$ids_three);
            if($ids_all){
                $ids_all=array_unique($ids_all);
            }
            Cache::set('private_id',$ids_all,3600);
        }
        return $ids_all?$ids_all:[0];
    }


    /**
     * 获取私密版块下帖子的id集合
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.16
     */
    public static function get_private_id_com(){
        $ids_all=self::get_private_id();
        $thread_id=Cache::get('com_thread_private_id');
        if(!$thread_id){
            $thread_id=db('com_thread')->where(['fid'=>['in',$ids_all]])->column('id');
            Cache::set('com_thread_private_id',$thread_id,3600);
        }
        return $thread_id?$thread_id:[0];
    }
    /**
     * 获取对应权限组的uid
     * @param $group
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.9
     */
    public static function get_group_user($group){
        //系统的用户
        $value=[];
        $is_all=false;
        foreach ($group as $g_id){
            $map=[];
            switch ($g_id){
                case 7:break;
                case 8:$is_all=true;break;
                case 9:
                    $map['status']=1;
                    $map['prohibit_time']=['gt',time()];
                    $user=db('report')->where($map)->field('to_uid')->select();
                    $user=array_column($user,'to_uid');
                    $value= array_merge($value,$user);
                    break;
                case 10:
                    $map['status']=0;
                    $user=db('user')->where($map)->column('uid');
                    $value= array_merge($value,$user);
                    break;
                default:
                    //晋级用户组
                    $type=Group::where(['id'=>$g_id])->field('bind_condition,type')->find();
                    if($type['type']==3) {
                        $exp_min = db('system_user_grade')->where(['id'=>$type['bind_condition']])->value('experience');
                        $exp_max = db('system_user_grade')->where(['experience'=>['gt',$exp_min]])->value('experience');
                        $map['exp']=['between',[$exp_min,$exp_max]];
                        $user=db('user')->where($map)->column('uid');
                        $value= array_merge($value,$user);
                    }else{
                        $user=db('bind_group_uid')->where(['g_id'=>$g_id,'status'=>1])->column('uid');
                        $value= array_merge($value,$user);
                    }
            }
            $value=array_unique($value);
            return [$value,$is_all];
        }
    }

    /**
     * 获取获取
     * @param $uid
     * @param $fid
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.3
     */
    public static function get_forum_user_power($uid,$fid){
        $tag='power_forum_value_fid_'.$fid.'_uid_'.$uid;
        $power=Cache::get($tag);
        if(!$power){
            $power=self::get_forum_power($uid,$fid);
            Cache::tag('thread_power_cache_fid')->set($tag,$power,10*60);
        }
        return $power;
    }

    /**
     * 编辑版块权限
     * @param $data
     * @return bool
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.7
     */
    public static function edit_forum_power($data){
        if(!$data['id']) return false;
        //绑定用户权限组组
        $keys=array_keys($data);
        $res1=db('bind_forum_group')->where(['bind_forum'=>$data['id'],'type'=>['in',$keys]])->update(['status'=>-1]);
        $sign=['visit'=>2,'send_thread'=>3,'send_comment'=>3,'browse'=>2];
        $value['bind_forum']=$data['id'];
        $value['status']=1;
        $add=[];
        foreach ($sign as $key=>$v){
            if(array_key_exists($key,$data)&&$v==$data[$key]){
                $value['type']=$key;
                $value['group']=$data[$key.'_id'];
                $add[]=$value;
            }
        }
        unset($key,$v);
        if(array_key_exists('forum_id',$data)){
            if($data['forum_id']){
                $value['type']='forum_id';
                $value['group']=$data['forum_id'];
                $add[]=$value;
            }
        }
        self::startTrans();
        if($add){
            $res=db('bind_forum_group')->insertAll($add);
        }else{
            $res=true;
        }
        //更新
        $res2=self::editData($data);
        if($res&&$res2!==false&&$res1!==false){
            self::commit();
            return true;
        }else{
            self::rollback();
            return false;
        }
    }

    /**
     * 获取版块的权限
     * @param $forum_id
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.8
     */
    public static function get_power_by_forum($forum_id){
        $data=self::getDate($forum_id);
        $group=db('bind_forum_group')->where(['bind_forum'=>$forum_id,'status'=>1])->select();
        //权限
        foreach ($group as $key=>$v){
            if(array_key_exists($v['type'],$data)||$v['type']=='forum_id') {
                $data[$v['type'] . '_id'] = $v['group'];
                $g_id = explode(',', $v['group']);
                $name = Group::where(['id' => ['in', $g_id]])->field('name')->select()->toArray();
                $name = array_column($name, 'name');
                $data[$v['type'] . '_name'] = implode(',', $name);
                $data[$v['type'].'_group_id']=$v['id'];
            }
        }
        unset($v);
        return $data;
    }

    /**
     * 当用户权限变更时候,清除缓存
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.20
     */
    public static function clear_cache(){
        //帖子列表清楚
        Cache::clear('thread_list_cache');
        //私密版块id
        Cache::rm('private_id');
        //清除版块权限
        Cache::clear('thread_power_cache_fid');
        //私密帖
        Cache::clear('com_thread_private_id');
    }
}