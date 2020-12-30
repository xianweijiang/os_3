<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-10 16:49:48
 */

namespace app\admin\model\certification;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

/**
 * 认证类别认证特权中间表  model
 * Class CertificationCatePrivilege
 * @package app\admin\model\certification
 */
class CertificationCatePrivilege extends ModelBasic
{
    use ModelTrait;
    public function privilege()
    {
        return $this->hasOne('CertificationPrivilege','id','privilege_id');
    }
}