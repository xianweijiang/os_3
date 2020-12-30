<?php


namespace app\admin\model\com;

use app\admin\model\user\User;
use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService;
use app\admin\model\com\ComForum as ForumModel;
use app\admin\model\system\SystemAdmin;
/**
 * Class ComThreadClass
 * @package app\admin\model\store
 */
class ComForumAdmin extends ModelBasic
{
    use ModelTrait;

    /*
     * 异步获取分类列表
     * @param $where
     * @return array
     */
    public static function AdminList($where){
        $model = self::getModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('create_time desc')->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $item['forum'] = ForumModel::where('id',$item['fid'])->field('id,name,pid,member_count,post_count')->find();
            switch ($item['level']){
                case 1:
                    $item['level']='版主';
                    break;
                case 2:
                    $item['level']='超级版主';
                    break;
            }
            $item['user']=User::where('uid',$item['uid'])->field('uid,nickname,avatar')->find();
            $item['create_time']=time_format($item['create_time']);
            $item['admin']=SystemAdmin::where('id',$item['admin'])->value('real_name');
        }
        $count=self::getModelObject($where)->count();
        return compact('count','data');
    }
    /**
     * @param $where
     * @return array
     */
    public static function getModelObject($where){
        $model      = new self();
        if (!empty($where)) {
            if(isset($where['level']) && $where['level']!=''){
                $model->where('level',$where['level']);
            }
            if(isset($where['uid']) && $where['uid']!=''){
                $uids=db('user')->where('uid|nickname','like','%'.$where['uid'].'%')->column('uid');
                $model->where('uid','in',$uids);
            }
            if(isset($where['fid']) && $where['fid']!=''){
                $model->where('fid',$where['fid']);
            }
        }
        return $model;
    }

    /**
     * 获取顶级分类
     * @return array
     */
    public static function getCategory($field = 'id,cate_name')
    {
        return self::where('is_show',1)->column($field);
    }

    /**
     * 分级排序列表
     * @param null $model
     * @return array
     */
    public static function getCatTierList($model = null)
    {
        if($model === null) $model = new self();
        return $model->field(['id'=>'value', 'name'=>'label'])->where('status', 1)->select()->toArray();
    }


    public static function delCategory($id){
        $count = self::where('pid',$id)->count();
        if($count)
            return false;
        else{
            return self::del($id);
        }
    }
}