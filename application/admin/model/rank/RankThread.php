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
class RankThread extends ModelBasic
{
    use ModelTrait;

    public static function rankThreadList($where)
    {
        $model = self::getModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('hot desc')->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $thread=db('com_thread')->where('id',$item['tid'])->find();
            $item['title']=$thread['title'];
            $item['content']=$thread['summary'];
            $item['nickname']=db('user')->where('uid',$thread['author_uid'])->value('nickname');
            $item['create_time']=time_format($thread['create_time']);
            switch($item['time_type']){
                case 1:
                    $item['time_type']='日榜';
                    break;
                case 2:
                    $item['time_type']='周榜';
                    break;
                case 3:
                    $item['time_type']='月榜';
                    break;
                case 4:
                    $item['time_type']='总榜';
                    break;
            }
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
            $model->where('type', $where['type']);
        }
        return $model;
    }

}