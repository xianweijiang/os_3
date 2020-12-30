<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-07 08:59:38
 */

namespace app\commonapi\model\certification;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

/**
 * 认证实体  model
 * Class CertificationEntity
 * @package app\commonapi\model\certification
 */
class CertificationEntity extends ModelBasic
{
    use ModelTrait;
    /**
     * 判断限定条件是否存在
     * @param array $where
     * @return array
     */
    public static function isExist($params)
    {
        $model = new self;
        if (isset($params['id'])) {
            $where['id']=$params['id'];
        }
        if (isset($params['cate_id'])) {
            $where['cate_id']=$params['cate_id'];
        }
        $where['uid']=$params['uid'];
        $where['status']=['neq',-2];
        $data=$model = $model->where($where)->find();
        return $data;
    }
    /**
     * 设置已读
     * @param array $where
     * @return flag
     */
    public static function setRead($id)
    {
        $model = new self;
        $data['is_read']=1;
        $data['update_time']=time();
        $flag=$model = $model::edit($data,$id);
        return $flag;
    }

}