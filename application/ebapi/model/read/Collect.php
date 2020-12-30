<?php

namespace app\ebapi\model\read;

use basic\ModelBasic;
use traits\ModelTrait;

class Collect extends ModelBasic
{
    use ModelTrait;

    public static function getUserCollect($uid,$tid)
    {
        return self::where(['uid'=>$uid,'tid'=>$tid])
                ->limit(1)
                ->find();
    }
}
