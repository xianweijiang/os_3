<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-01 21:15:46
 */

namespace app\commonapi\model\certification;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

/**
 * 认证条件  model
 * Class CertificationCondition
 * @package app\commonapi\model\certification
 */
class CertificationCondition extends ModelBasic
{
    use ModelTrait;
    /**
     * 获取api认证条件列表
     * @param array $where
     * @return array
     */
    public static function getApiPage($params)
    {
        $model = new self;
        if($params['status'] !== '') $model = $model->where('status',$params['status']);
        if($params['keyword'] !== '') $model = $model->where('name|id','LIKE',"%$params[keyword]%");
        $list = $model->order('sort DESC,id DESC')->page($params['page'], $params['page_num'])->select();
        return $list;
    }

}