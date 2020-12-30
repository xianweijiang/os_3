<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-01 21:16:17
 */

namespace app\commonapi\model\certification;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

use think\Config;
use think\Db;
use think\Model;
use think\Request;
use think\Validate;

/**
 * 资料项  model
 * Class CertificationDatum
 * @package app\commonapi\model\certification
 */
class CertificationDatum extends ModelBasic
{
    use ModelTrait;

    public function types()
    {
        return $this->hasMany('CertificationType','id','type_id');
    }
    /**
     * 获取api资料项列表
     * @param array $where
     * @return array
     */
    public static function getApiPage($params)
    {
        $model = new self;
        if($params['status'] !== '') $model = $model->where('status',$params['status']);
        if($params['keyword'] !== '') $model = $model->where('name|id|field','LIKE',"%$params[keyword]%");
        $model = $model->order('sort DESC,id DESC');
        return self::page($model,$params);
    }


}