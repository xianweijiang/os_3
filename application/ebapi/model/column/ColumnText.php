<?php

namespace app\ebapi\model\column;

use basic\ModelBasic;
use traits\ModelTrait;

class ColumnText extends ModelBasic
{
	use ModelTrait;
	/**
	* 目录
	*/
    public static function getCatalog($gid, $field='*', $orderBy, $page=1, $size=1000)
    {
		return self::where('pid',$gid)
				->where('is_show',1)
				->field($field)
				->order($orderBy)
				->limit(($page-1)*$size,$size)
				->select();
    }

    /**
    * 总条数
    */
    public static function getCatalogCount($gid)
    {
		return self::where('pid',$gid)
				->where('is_show',1)
				->count();
    }

    /**
    * 详情
    */
    public static function getContent($id)
    {
		return self::where('id',$id)
				->where('is_show',1)
				->find();
    }
}
