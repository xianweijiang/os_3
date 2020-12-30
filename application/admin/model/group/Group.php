<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/2/5
 * Time: 10:23
 */
namespace app\admin\model\group;
use app\osapi\model\com\Report;
use app\osapi\model\user\UserModel;
use think\Cache;
use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;

class Group extends ModelBasic
{
    use ModelTrait;

    public static function addDate($data){
        $data['status']=1;
        $data['create_time']=time();
        return self::set($data);
    }
    public static function editData($data){
        if($data['id']){
            //更新一下等级
            db('bind_group_power')->where(['g_id'=>$data['id']])->update(['level'=>$data['level']]);
            return self::where(['id'=>$data['id']])->update($data);
        }else{
            return self::addDate($data);
        }
    }
    public static function getDate($id){
        $map['id']=$id;
        return self::where($map)->find();
    }

    public static function setStatus($map,$status){
        //删除的时候,绑定关系解除
        if($status==-1){
            db('bind_group_uid')->where(['g_id'=>$map['id']])->update(['status'=>-1]);
        }
        return self::where($map)->update(['status'=>$status]);
    }

    public static function get_group_list($map,$page,$limit,$order){
        $data=self::where($map)->page($page,$limit)->order($order)->select()->toArray();
        foreach ($data as &$v){
            $v['create_time']=date('Y-m-d H:i:s',$v['create_time']);
        }
        $count=self::where($map)->count();
        return compact('count', 'data');
    }

    public static function start_create(){
        //版主及超级版主的
        $forum=db('com_forum_admin')->where(['status'=>1])->select();
        $data=[];
        $value['status']=1;
        $value['create_time']=time();
        foreach ($forum as $v){
            $value['uid']=$v['uid'];
            $value['g_id']=$v['level']==1?4:3;
            $data[]=$value;
        }
        unset($v);
        db('bind_group_uid')->insertAll($data);
        //晋级类初始化
        $grade=db('system_user_grade')->where(['is_del'=>0])->select();
        $data=[];
        $value['level']=2;
        $value['status']=1;
        $value['create_time']=time();
        $value['cate']='内置';
        $value['type']=3;
        foreach ($grade as $v){
            $value['name']=$v['name'];
            $value['bind_condition']=$v['id'];
            $value['remark']=$v['explain'];
            $data[]=$value;
        }
        unset($v);
        self::insertAll($data);

        //认证类初始化
        $grade=db('certification_cate')->where(['status'=>1])->select();
        $data=[];
        $value['level']=2;
        $value['status']=1;
        $value['create_time']=time();
        $value['cate']='内置';
        $value['type']=5;
        foreach ($grade as $v){
            $value['name']=$v['name'];
            $value['bind_condition']=$v['id'];
            $value['remark']=$v['desc'];
            $data[]=$value;
        }
        unset($v);
        self::insertAll($data);
    }

    /**
     * 自动绑定类型
     * @param $data
     * @return int|string
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.27
     */
    public static function add_group_new($data){
        $data['level']=2;
        $data['status']=1;
        $data['create_time']=time();
        $data['cate']='内置';
        return self::insert($data);
    }

    /**
     * 绑定用户信息
     * @param $ids
     * @param $group
     * @param $time
     * @param $is_deal
     * @return int
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.30
     */
    public static function bind_group_uid($ids,$group,$time,$is_deal=false){
        $uids=explode(',',$ids);
        if($is_deal){
            //重新设置
            db('bind_group_uid')->where(['uid'=>$ids])->update(['status'=>-1]);
            $uids[0]=$ids;
        }else{
            //删除重复的删除
            db('bind_group_uid')->where(['uid'=>['in',$uids],'g_id'=>['in',$group]])->update(['status'=>-1]);
        }
        $data=[];
        foreach ($uids as $v){
            $value['create_time']=time();
            $value['status']=1;
            //清空缓存
            Cache::rm('_group_'.$v.'_power');
            foreach ($group as $key=>$vo){
                $value['uid']=$v;
                $value['g_id']=$vo;
                $value['end_time']=$time[$key]?strtotime($time[$key].' 00:00:00'):0;
                $data[]=$value;
            }
        }
        if(!$data) return true;
       return db('bind_group_uid')->insertAll($data);
    }

    /**
     * 绑定用户组权限
     * @param $uid
     * @return false|\PDOStatement|string|\think\Collection
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.30
     */
    public static function get_bind_group_uid($uid){
        $list = db('bind_group_uid')->where(['uid'=>$uid,'status'=>1])->group('g_id')->select();
        foreach ($list as &$vo){
            $vo['end_time']=$vo['end_time']==0?'':date('Y-m-d',$vo['end_time']);
        }
        unset($vo);
        return $list;
    }

    /**
     * 获取系统的默认组
     * @param $uid
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.30
     */
    public static function get_system_group($uid){
        $group=[];
        if(!$uid){
            //游客管理组
            $group[]=7;
        }else{
            //注册组
            $group[]=8;
        }
        //禁言组
        $is_report=Report::is_prohibit($uid);
        if($is_report){
            $group[]=9;
        }
        //禁用组
        $status=db('user')->where(['uid'=>$uid])->value('status');
        if($status!=1){
            $group[]=10;
        }
        $data['g_id']=$group;
        $data['value']=self::where(['id'=>['in',$group]])->field('name')->select()->toArray();
        $data['value_show']=implode('/',array_column($data['value'],'name'));
        return $data;
    }

    /**
     * 获取晋级用户组
     * @param $uid
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.30
     */
    public static function get_level_group($uid){
        $exp=db('user')->where(['uid'=>$uid])->value('exp');
        $value=db('system_user_grade')->where('is_del',0)->where('experience','gt',$exp)->order('experience','asc')->field('id,name')->find();
//        $value=UserModel::cacugrade($exp);
        $data['g_id'][]=self::where(['bind_condition'=>$value['id'],'type'=>3])->value('id');
        $data['value'][]=$value['name'];
        $data['value_show']=$value['name'];
        return $data;
    }

    /**
     * 获得绑定的用户group_id;
     * @param $uid
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.30
     */
    public static function get_bind_uid_group($uid){
        $group_id=db('bind_group_uid')
            ->where(['uid'=>$uid,'status'=>1,'end_time'=>[['eq',0],['gt',time()],'or']])
            ->group('g_id')->field('g_id')->select();
        $group=array_column($group_id,'g_id');
        return $group;
    }

    /**
     * 获取用户的group_id
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.30
     */
    public static function get_group_by_uid($uid){
        if(!$uid){
            return [7];
        }
        $group=Cache::get('_group_'.$uid.'_power');
        if(!$group){
            $system=self::get_system_group($uid);
            $level=self::get_level_group($uid);
            $bind=self::get_bind_uid_group($uid);
            $cert=self::get_cert_group($uid);
            $group=array_merge($system['g_id'],$level['g_id'],$bind,$cert['g_id']);
            Cache::tag('group_by_uid')->set('_group_'.$uid.'_power',$group,60*10);
        }
        return $group;
    }

    /**
     * 获取认证的列表
     * @param $uid
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.10
     */
    public static function get_cert_group($uid){
        $cert=db('certification_entity')->where(['uid'=>$uid,'status'=>1])->field('cate_id')->select();
        $cate_id=array_column($cert,'cate_id');
        $group=self::where(['bind_condition'=>['in',$cate_id],'type'=>5])->field('id,name')->select()->toArray();
        $data['g_id']=array_column($group,'id');
        $data['value']=array_column($group,'name');
        $data['value_show']= $data['value']?implode('/',array_column($group,'name')):'暂无';
        return $data;
    }

    /**
     * 获取用户的权限
     * @param $uid
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.30
     */
    public static function get_power_value($uid){
        $group=self::get_group_by_uid($uid);
        $giModel =db('bind_group_power');
        $gi_table = $giModel->order('level desc,value desc,update_time desc')->buildSql();//先排序
        $power = $giModel->table($gi_table .'as gi')
            ->field('gi.*')
            ->where(['gi.g_id'=>['in',$group]])
            ->group('gi.sign')
            ->select();
        //获取默认的的权限值
        $power_all=db('power')->where(['status'=>1])->column('sign');
        $data=[];
        foreach ($power_all as $vo){
            $data[$vo]=0;
        }
        unset($vo);
        foreach ($power as $v){
            $data[$v['sign']]=$v['value'];
        }
        unset($v);
        return $data;
    }

    /**
     * 获取用户列表选择
     * @param $g_id
     * @param $model
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.2
     */
    public static function get_group_uid($g_id,$model){
        //系统的用户
        switch ($g_id){
            case 7:break;
            case 8:break;
            case 9:
                $map['status']=1;
                $map['prohibit_time']=['gt',time()];
                $user=db('report')->where($map)->field('to_uid')->select();
                $user=array_column($user,'to_uid');
                $model->where('u.uid','in',$user);
                break;
            case 10:
                $model->where('u.status',0);
                break;
            default:
                //晋级用户组
                $type=self::where(['id'=>$g_id])->field('bind_condition,type')->find();
                if($type['type']==3) {
                    $exp_max= db('system_user_grade')->where(['id'=>$type['bind_condition']])->value('experience');
                    $exp_min = db('system_user_grade')->where(['experience'=>['lt',$exp_max]])->order('experience desc')->value('experience');
                    $exp_min =$exp_min?$exp_min:0;
                    $model->where('u.exp','between',[$exp_min,$exp_max]);
                }elseif($type['type']==5){
                    //认证用户组
                    $user=db('certification_entity')->where(['cate_id'=>$type['bind_condition'],'status'=>1])->field('uid')->select();
                    $user=array_column($user,'uid');
                    $model->where('u.uid','in',$user);
                }else{
                    $user=db('bind_group_uid')->where(['g_id'=>$g_id,'status'=>1])->field('uid')->select();
                    $user=array_column($user,'uid');
                    $model->where('u.uid','in',$user);
                }
        }
        return $model;
    }

    /**
     * 获取诶类型的所有分组
     * @param $type
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.2
     */
    public static function get_type_group($type){
        return self::where(['type'=>$type,'status'=>1])->select();
    }

    /**
     * 获取所有的用户（用户版块绑定全部内容）
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.2
     */
    public static function get_all_group(){
        $data=[
            1=>['name'=>'管理用户组'],
            2=>['name'=>'系统用户组'],
            3=>['name'=>'晋级用户组'],
            4=>['name'=>'会员用户组'],
            5=>['name'=>'认证用户组'],
            6=>['name'=>'自定义用户组'],
        ];
        foreach ($data as $key=>&$v){
            $v['data']=self::get_type_group($key);
        }
        unset($v,$key);
        return $data;
    }
}