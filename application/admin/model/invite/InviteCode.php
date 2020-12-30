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
class InviteCode extends ModelBasic
{
    use ModelTrait;

    public static function codeList($where)
    {
        $model = self::getModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('id desc')->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $item['nickname']=db('user')->where('uid',$item['uid'])->value('nickname');
            $item['create_time']=time_format($item['create_time']);
            $item['code']='U'.$item['code'].'D';
        }
        //普通列表
        $count = self::getModelObject($where)->count();
        return compact('count', 'data');
    }

    public static function getModelObject($where = [])
    {
        $model = new self();
        if (!empty($where)) {
            if(isset($where['uid']) && $where['uid']!=''){
                $uids=db('user')->where('uid|nickname','like','%'.$where['uid'].'%')->column('uid');
                $model->where('uid','in',$uids);
            }
        }
        return $model;
    }

}