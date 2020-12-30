<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/2/5
 * Time: 10:23
 */
namespace app\admin\model\com;
use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;

class CommentTemplate extends ModelBasic
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

    public static function get_template_list($map,$page,$limit,$order){
        $data=self::where($map)->page($page,$limit)->order($order)->select();
        foreach ($data as &$v){
            $v['create_time']=date('Y-m-d H:i:s',$v['create_time']);
        }
        $count=self::where($map)->count();
        return compact('count', 'data');
    }

    public static function get_vest_template(){
       $content= self::where(['status'=>1])->field('content')->select()->toArray();
       return array_column($content,'content');
    }
}