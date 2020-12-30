<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\system;

use service\PHPExcelService;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use app\admin\model\shop\ShopColumn as ColumnModel;
use app\admin\model\order\StoreOrder;
use app\admin\model\system\SystemConfig;

/**
 * 产品管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class Script extends ModelBasic
{
    use ModelTrait;

    /*
     * 获取产品列表
     * @param $where array
     * @return array
     *
     */
    public static function ScriptLogList($where){
        $model = new self;
        $model = self::getModelObject($where,$model);
        $data=($data=$model->order('id desc')->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            switch($item['type']){
                case 1:
                    $item['type'] = '每十分钟执行一次';
                    break;
                case 2:
                    $item['type'] = '每小时执行一次';
                    break;
                case 3:
                    $item['type'] = '每天凌晨1点执行一次';
                    break;
                case 4:
                    $item['type'] = '每天凌晨8点执行一次';
                    break;
                case 5:
                    $item['type'] = '每周执行一次';
                    break;
            }
            switch($item['status']){
                case 1:
                    $item['status'] = '执行成功';
                    break;
                case 0:
                    $item['status'] = '执行失败';
                    break;
            }
            $item['create_time']=time_format($item['create_time']);
        }
        $count=self::getModelObject($where,$model)->count();
        return compact('count','data');
    }

    public static function getModelObject($where,$model){
        if (!empty($where)) {

        }
        return $model;
    }

}