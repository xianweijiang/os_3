<?php

namespace app\admin\model\column;

use basic\ModelBasic;
use traits\ModelTrait;

class ColumnText extends ModelBasic
{
    use ModelTrait;
    public static function systemCouponIssuePage($pid,$page = 1,$limit = 20)
    {
    	$page = intval($page);
    	$limit = intval($limit);
        $data = self::where('pid',$pid)->page(($page-1)*$limit,$limit)->select()->toArray();
        $count =  self::where('pid',$pid)->count();
        return ['data' => $data,'count' => $count, 'total' => ceil($count / $limit)];
    }

    static public function contents($id)
    {
        return self::where('id',$id)->find()->toArray();
    }
    static public function updateMV($data,$id)
    {
        return self::save($data,$id);
    }
}
