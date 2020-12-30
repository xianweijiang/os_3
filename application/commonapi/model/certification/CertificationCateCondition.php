<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-10 16:12:28
 */

namespace app\commonapi\model\certification;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

/**
 * 认证类别认证条件中间表  model
 * Class CertificationCateCondition
 * @package app\commonapi\model\certification
 */
class CertificationCateCondition extends ModelBasic
{
    use ModelTrait;

    public function condition()
    {
        return $this->hasOne('CertificationCondition','id','condition_id');
    }

}