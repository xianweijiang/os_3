<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-01 21:03:13
 */

namespace app\admin\model\certification;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

/**
 * 认证特权  model
 * Class CertificationCondition
 * @package app\admin\model\certification
 */
class CertificationCondition extends ModelBasic
{
    use ModelTrait;
    /**
     * 获取限定条件集合
     * @param array $where
     * @return array
     */
    public static function getList($where)
    {
        $model = new self;
        $list = $model->where('status',1)->where($where)->order('sort DESC,id DESC')->select();
        return $list;
    }
    /**
     * 获取指定列表
     * @param array $params
     * @return page
     */
    public static function getAdminPage($params)
    {
        $model = new self;
        if($params['status'] !== '') $model = $model->where('status',$params['status']);
        if($params['keyword'] !== '') $model = $model->where('name|id','LIKE',"%$params[keyword]%");
        $model = $model->order('sort DESC,id DESC');
        return self::page($model,$params);
    }

}