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
class Rank extends ModelBasic
{
    use ModelTrait;

    public static function rankList($where)
    {
        $model = self::getModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('id asc')->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            if(in_array($item['id'],array(5,7))){
                if($item['frequency']>24){
                    $item['frequency']=$item['frequency']/24;
                    $item['frequency']=$item['frequency'].'天';
                }else{
                    $item['frequency']=$item['frequency'].'小时';
                }
            }else{
                $item['frequency']='/';
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

        }
        return $model;
    }

}