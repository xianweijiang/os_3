<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\invite;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;

/**
 * 订单管理Model
 * Class StoreOrder
 * @package app\admin\model\store
 */
class InviteReward extends ModelBasic
{
    use ModelTrait;

    public static function rewardList($where)
    {
        $model = new self();
        $model = $model->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('level asc')->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as $key=>&$item){
            $item['level']='等级'.$item['level'];
            $item['reward']=json_decode($item['reward'],true);
            if($item['reward_type']=='积分奖励'){
                if($item['reward']){
                    foreach ($item['reward'] as &$value){
                        $value['name']=db('system_rule')->where('flag',$value['flag'])->value('name');
                    }
                }
            }
            if($key==0){
                $item['number']='1-'.$item['num'];
            }else{
                $num=$data[$key-1]['num']+1;
                $item['number']=$num.'-'.$item['num'];
            }
        }
        $count = self::count();
        return compact('count', 'data');
    }


}