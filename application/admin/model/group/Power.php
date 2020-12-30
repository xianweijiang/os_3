<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/2/5
 * Time: 10:23
 */
namespace app\admin\model\group;
use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;

class Power extends ModelBasic
{
    use ModelTrait;

    public static function addDate($data){
        $data['status']=1;
        $data['create_time']=time();
        return self::set($data);
    }
    public static function editData($data){
        if($data['id']){
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
        return self::where($map)->update(['status'=>$status]);
    }

    public static function get_group_list($map,$page,$limit,$order){
        $data=self::where($map)->page($page,$limit)->order($order)->select();
        foreach ($data as &$v){
            $v['create_time']=date('Y-m-d H:i:s',$v['create_time']);
        }
        $count=self::where($map)->count();
        return compact('count', 'data');
    }

    /**
     * 获取选择项
     * @param $map
     * @param $g_id
     * @param $uid
     * @return false|\PDOStatement|string|\think\Collection
     * @author zxh  zxh@ourstu.com
     *时间；2020.3.27
     */
    public static function getAllPower($map,$g_id,$uid){
        $power=db('power')->where($map)->select();
        //获取选择的power;
        if($uid){
            $choose_power=Group::get_power_value($uid);
            $sign=array_keys($choose_power);
        }else{
            list($choose_power,$sign)=self::get_bind_group_power($g_id);
        }
        foreach($power as &$vo){
            if($vo['input_type']=='radio'){
                $value=explode(',',$vo['value']);
                $vo['value_show']=[];
                foreach ($value as $key=>$v){
                    $list=explode('=>',$v);
                    $valueList=['key'=>$list[0],'label'=>$list[1],'checked'=>''];
                    if(in_array($vo['sign'],$sign)){
                        if($choose_power[$vo['sign']]==$list[0]){
                            $valueList['checked']='checked';
                        }
                    }
                    $vo['value_show'][]=$valueList;
                }
            }else{
                if(in_array($vo['sign'],$sign)){
                    $vo['checked']=$choose_power[$vo['sign']];
                }else{
                    $vo['checked']='';
                }
            }
        }
        unset($key,$v,$vo);
        return $power;
    }

    /**
     * 获取选择项
     * @param $map
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：202.3.27
     */
    public static function get_power($map){
        $power=db('power')->where($map)->field('sign')->select();
        return array_column($power,'sign');
    }

    /**
     * 绑定权限
     * @param $g_id
     * @param $powers
     * @return int
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.27
     */
    public static function bind_group_power($g_id,$powers){
        $data=[];
        $level=Group::where(['id'=>$g_id])->value('level');
        $value['g_id']=$g_id;
        $value['level']=$level;
        $value['update_time']=time();
        $sign=[];
        foreach ($powers as $key=>$v){
            $value['sign']=$key;
            $sign[]=$key;
            $value['value']=$v;
            $data[]=$value;
        }
        unset($v);
        $table=db('bind_group_power');
        self::beginTrans();
        $res1=$table->where(['g_id'=>$g_id,'sign'=>['in',$sign]])->delete();
        $res2=$table->insertAll($data);
        if($res1!==false&&$res2){
            self::commit();
            return true;
        }else{
            self::rollback();
            return false;
        }
    }

    /**
     * 获取group内的选择权限
     * @param $g_id
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.27
     */
    public static function get_bind_group_power($g_id){
        $choose_power=db('bind_group_power')->where(['g_id'=>$g_id])->group('sign')->field('sign,value')->order('update_time desc')->select();
        $sign=array_column($choose_power,'sign');
        $data=[];
        foreach ($choose_power as $v){
            $data[$v['sign']]=$v['value'];
        }
        return [$data,$sign];
    }

    /**
     * 自定绑定权限的方法
     * @param $uid
     * @param $g_id
     * @param string $time
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.9
     */
    public static function add_bind_group_uid($uid,$g_id,$time=''){
        $count=db('bind_group_uid')->where(['g_id'=>$g_id,'uid'=>$uid])->count();
        $data['uid']=$uid;
        $data['g_id']=$g_id;
        $data['end_time']=$time;
        $data['create_time']=time();
        $data['status']=1;
        if($count){
            db('bind_group_uid')->where(['g_id'=>$g_id,'uid'=>$uid])->update($data);
        }else{
            db('bind_group_uid')->insert($data);
        }
    }
    /**
     * 自动删除绑定用户组的方法
     * @param $uid
     * @param $g_id
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.9
     */
    public static function delete_bind_group_uid($uid,$g_id){
        db('bind_group_uid')->where(['g_id'=>$g_id,'uid'=>$uid])->update(['status'=>-1]);
    }
}