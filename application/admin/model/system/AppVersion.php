<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/2/5
 * Time: 10:23
 */
namespace app\admin\model\system;
use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;

class AppVersion extends ModelBasic
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

    public static function get_version_list($map,$page,$limit,$order){
        $data=self::where($map)->page($page,$limit)->order($order)->select();
        $count=self::where($map)->count();
        return compact('count', 'data');
    }
}