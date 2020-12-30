<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\pc;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;


class PcSet extends ModelBasic
{
    use ModelTrait;



    public static function getModelObject($where = [])
    {
        $model = new self();
        if (!empty($where)) {

        }
        return $model;
    }

}