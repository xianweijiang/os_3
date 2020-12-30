<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\prohibit;

use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;

/**
 * 产品管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class ProhibitReason extends ModelBasic
{
    use ModelTrait;

    /*
     * 获取产品列表
     * @param $where array
     * @return array
     *
     */
    public static function ReasonList($where){
        $model = new self;
        $model = self::getModelObject($where,$model);
        $data=($data=$model->order('id desc')->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        $count=self::getModelObject($where,$model)->count();
        return compact('count','data');
    }

    public static function getModelObject($where,$model){
        if (!empty($where)) {
        }
        $model->where('status',1);
        return $model;
    }

}