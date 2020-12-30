<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\rank;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;

/**
 * 订单管理Model
 * Class StoreOrder
 * @package app\admin\model\store
 */
class RankTopic extends ModelBasic
{
    use ModelTrait;

    public static function rankTopicList($where)
    {
        $model = self::getModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('id asc')->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $topic=db('com_topic')->where('id',$item['oid'])->find();
            $item['title']=$topic['title'];
            $item['image']=$topic['image'];
            $item['nickname']=db('user')->where('uid',$topic['uid'])->value('nickname');
            $item['class_name']=db('com_topic_class')->where('id',$topic['class_id'])->value('name');
            $item['create_time']=time_format($topic['create_time']);
            $item['update_time']=time_format($topic['update_time']);
            $item['view_count']=$topic['view_count'];
            $item['follow_count']=$topic['follow_count'];
            $item['post_count']=$topic['post_count'];
        }
        //普通列表
        $count = self::getModelObject($where)->count();
        return compact('count', 'data');
    }

    public static function getModelObject($where = [])
    {
        $model = new self();
        if (!empty($where)) {
            if(isset($where['order']) && $where['order']!=''){
                $model=$model->order(self::setOrder($where['order']));
            }
        }
        return $model;
    }


}