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
class ComTopicClass extends ModelBasic
{
    use ModelTrait;

    public static function getClassList(){
        $list=self::where('status',1)->order('sort asc')->select();
        if($list){
            $list=$list->toArray();
        }
        return $list;
    }
    /*
     * 异步获取分类列表
     * @param $where
     * @return array
     */
    public static function ClassList($where){
        $model = self::getModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('sort asc')->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $item['topic_count'] = db('com_topic')->where('class_id',$item['id'])->count();
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
        return $model;
    }


}