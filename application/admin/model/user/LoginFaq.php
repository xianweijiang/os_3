<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-01 21:01:27
 */

namespace app\admin\model\user;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

/**
 * 常见问题  model
 * Class CertificationFaq
 * @package app\admin\model\certification
 */
class LoginFaq extends ModelBasic
{
    use ModelTrait;
    /**
     * 获取指定列表
     * @param array $params
     * @return page
     */
    public static function getAdminPage($params)
    {
        $model = new self;
        if($params['status'] !== '') $model = $model->where('status',$params['status']);
        if($params['keyword'] !== '') $model = $model->where('title|desc|id','LIKE',"%$params[keyword]%");
        $model = $model->order('sort DESC,id DESC');
        return self::page($model,$params);
    }

    public static function delData($id)
    {
        return self::del($id);
    }
}