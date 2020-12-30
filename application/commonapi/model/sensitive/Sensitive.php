<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\commonapi\model\sensitive;


use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;

/**
 * 订单管理Model
 * Class StoreOrder
 * @package app\admin\model\store
 */
class Sensitive extends ModelBasic
{
    use ModelTrait;


    public static function editSensitive($data,$id){
        $res=self::where('id',$id)->update($data);
        if($res){
            return 1;
        }else{
            return 0;
        }
    }

    public static function getOne($id){
        $res=self::where('id',$id)->find()->toArray();
        return $res;
    }

    public static function sensitiveList($where)
    {
        $model = self::getModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('create_time desc')->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            switch($item['level']){
                case 1:
                    $item['level'] = '替换';
                    break;
                case 2:
                    $item['level'] = '删除';
                    break;
                case 3:
                    $item['level'] = '审核';
                    break;
            }
            $item['create_time']=time_format($item['create_time']);
        }
        //普通列表
        $count = self::count();
        return compact('count', 'data');
    }

    public static function getModelObject($where = [])
    {
        $model = new self();
        if (!empty($where)) {
            if(isset($where['sensitive']) && $where['sensitive']!=''){
                $model->where('sensitive','like','%'.$where['sensitive'].'%');
            }
        }
        return $model;
    }

}