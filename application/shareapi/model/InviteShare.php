<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/18
 * Time: 17:08
 */

namespace app\shareapi\model;


use basic\ModelBasic;
use traits\ModelTrait;

class InviteShare extends ModelBasic
{
    use ModelTrait;

    public static function haiBaoList($where)
    {
        $model=new self();
        if(in_array($where['status'],[0,1])){
            $map['status']=$where['status'];
        }else{
            $map['status']=['in',[0,1]];
        }
        $model = $model->where($map)->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('id desc')->select()) && count($data) ? $data->toArray() : [];
        $count = $model->where($map)->count();
        return compact('count', 'data');
    }
}