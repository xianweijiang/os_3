<?php

namespace app\ebapi\model\read;

use basic\ModelBasic;
use traits\ModelTrait;


class UserRead extends ModelBasic
{
    use ModelTrait;

    public static function getUserRead($uid,$pid)
    {
        return self::where(['uid'=>$uid,'pid'=>$pid])
                ->limit(1)
                ->find();
    }
    static public function updates($data,$id)
    {
    	return self::where('id',$id)->update($data);
    }
}
