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
class ComForumAdminApply extends ModelBasic
{
    use ModelTrait;

    /*
     * 异步获取分类列表
     * @param $where
     * @return array
     */
    public static function AdminApplyList($where){
        $model = self::getModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->where('status',1)->order('create_time desc')->select()) && count($data) ? $data->toArray() : [];
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
            if(isset($where['uid']) && $where['uid']!=''){
                $uids=db('user')->where('uid|nickname','like','%'.$where['uid'].'%')->column('uid');
                $model->where('uid','in',$uids);
            }
        }
        return $model;
    }

}