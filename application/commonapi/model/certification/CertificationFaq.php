<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-01 21:16:36
 */

namespace app\commonapi\model\certification;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

/**
 * 常见问题  model
 * Class CertificationFaq
 * @package app\commonapi\model\certification
 */
class CertificationFaq extends ModelBasic
{
    use ModelTrait;
    /**
     * 获取api常见问题列表
     * @param array $where
     * @return array
     */
    public static function getApiPage($params)
    {
        if (!isset($params['page'])) {
            $params['page']=0;
        }
        if (!isset($params['page_num'])) {
            $params['page_num']=20;
        }
        $model = new self;
        $where['status']=1;
        $field='*';
        $list = $model
            ->order('sort DESC')
            ->field($field)
            ->where($where)
            ->page($params['page'], $params['page_num'])->select();
        return $list;
    }

}