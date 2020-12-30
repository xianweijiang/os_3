<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-01 21:16:50
 */

namespace app\commonapi\model\certification;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

/**
 * 认证特权  model
 * Class CertificationPrivilege
 * @package app\commonapi\model\certification
 */
class CertificationPrivilege extends ModelBasic
{
    use ModelTrait;
    /**
     * 获取api认证特权列表
     * @param array $where
     * @return array
     */
    public static function getApiPage($params)
    {
        $model = new self;
        if($params['status'] !== '') $model = $model->where('status',$params['status']);
        if($params['keyword'] !== '') $model = $model->where('name|id','LIKE',"%$params[keyword]%");
        $model = $model->order('sort DESC,id DESC');
        return self::page($model,$params);
    }

}