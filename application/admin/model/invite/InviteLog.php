<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\invite;

use service\PHPExcelService;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;

/**
 * 产品管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class InviteLog extends ModelBasic
{
    use ModelTrait;

    /*
     * 获取产品列表
     * @param $where array
     * @return array
     *
     */
    public static function InviteLogList($where){
        $model = new self;
        $model = self::getModelObject($where,$model);
        $data=($data=$model->order('id desc')->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $item['nickname']=db('user')->where('uid',$item['uid'])->value('nickname');
            $item['avatar']=db('user')->where('uid',$item['uid'])->value('avatar');
            $item['father_nickname']=db('user')->where('uid',$item['father_uid'])->value('nickname');
            $item['create_time']=time_format($item['create_time']);
        }
        $count=self::getModelObject($where,$model)->count();
        return compact('count','data');
    }

    public static function getModelObject($where,$model){
        if (!empty($where)) {
            if(isset($where['uid']) && $where['uid']!=''){
                $uids=db('user')->where('uid|nickname','like','%'.$where['uid'].'%')->column('uid');
                $model->where('uid','in',$uids);
            }
            if(isset($where['father_uid']) && $where['father_uid']!=''){
                $uids=db('user')->where('uid|nickname','like','%'.$where['father_uid'].'%')->column('uid');
                $model->where('father_uid','in',$uids);
            }
            if($where['data'] !== ''){
                $model = self::getModelTime($where,$model,'create_time');
            }
        }
        return $model;
    }

}