<?php

namespace app\admin\model\column;

use basic\ModelBasic;
use traits\ModelTrait;

class TextUser extends ModelBasic
{
    use ModelTrait;
    static public function getTextUser()
    {
        return self::select();
    }
    public static function setData($data)
    {
        return self::save($data);
    }
}
