<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\rank;

use service\PHPExcelService;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;

/**
 * 产品管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class RankUser extends ModelBasic
{
    use ModelTrait;

    public static function rankUserList($where)
    {
        $model = self::getModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('id asc')->select()) && count($data) ? $data->toArray() : [];
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