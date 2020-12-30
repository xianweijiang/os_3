<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/29
 * Time: 15:53
 */

namespace app\shareapi\model;


use basic\ModelBasic;
use traits\ModelTrait;

class InviteLevel extends ModelBasic
{
    use ModelTrait;

    /**
     * 分销商推广人列表
     * @param $where
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function sellerChildList($where)
    {
        $model = self::_getChildModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('create_time desc')->select()) && count($data) ? $data->toArray() : [];
        $user_list=array();
        if(count($data)){
            $uids=array_column($data,'uid');
            $user_list=db('user')->where('uid','in',$uids)->field('uid,nickname,avatar,phone')->select();
            $user_list=array_combine(array_column($user_list,'uid'),$user_list);
        }
        //下线列表
        foreach ($data as &$item){
            $item['user_info']=isset($user_list[$item['uid']])?$user_list[$item['uid']]:['uid'=>$item['uid'],'nickname'=>'','avatar'=>'','phone'=>''];
            $item['is_seller']=is_seller($item['uid']);
            $item['create_time']=time_format($item['create_time']);
        }
        unset($item);

        $count = self::_getChildModelObject($where)->count();
        return compact('count', 'data');
    }

    /**
     * 获取连表Model
     * @param $where
     * @return object
     */
    private static function _getChildModelObject($where = [])
    {
        $model = new self();
        if (!empty($where)) {
            // data 日期
            $model->where(function($query) use($where){
                switch ($where['select_date']) {
                    case 'yesterday':
                    case 'today':
                    case 'week':
                    case 'month':
                    case 'year':
                        $query->whereTime('create_time', $where['select_date']);
                        break;
                    case 'quarter':
                        $start = strtotime(Carbon::now()->startOfQuarter());
                        $end   = strtotime(Carbon::now()->endOfQuarter());
                        $query->whereTime('create_time', 'between', [$start, $end]);
                        break;
                    case '':
                        ;
                        break;
                    default:
                        $between = explode(' - ', $where['select_date']);
                        $query->whereTime('create_time', 'between', [$between[0], $between[1]]);
                        break;
                }
            });

            if(isset($where['key_word']) && $where['key_word']!=''){
                $uids = db('user')->where('nickname|phone','LIKE',"%{$where['key_word']}%")->column('uid');
                if(intval($where['key_word'])>0){
                    $where['key_word']=intval($where['key_word']);
                    if(count($uids)){
                        $uids[]=$where['key_word'];
                        $model->where('uid', 'in', $uids);
                    }else{
                        $model->where('uid', $where['key_word']);
                    }
                }else{
                    if(count($uids)){
                        $model->where('uid', 'in', $uids);
                    }
                }
            }

            $where['seller_uid']=intval($where['seller_uid']);
            if($where['seller_uid']>0){
                switch ($where['type']){
                    case 'level1':
                        $model->where('father1',$where['seller_uid']);
                        break;
                    case 'level2':
                        $model->where('father2',$where['seller_uid']);
                        break;
                    case 'all':
                    default:
                        $model->where('father1|father2',$where['seller_uid']);
                        break;
                }
            }else{
                $model->where('father1',-1);//查询空数据
            }
        }
        return $model;
    }
}