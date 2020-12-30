<?php

namespace app\admin\model\com;

use service\PHPExcelService;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService;

/**
 * 广告位 model
 * Class ComForum
 * @package app\admin\model\com
 */
class ComAdv extends ModelBasic
{
	use ModelTrait;

	// 自动写入时间戳
	/*protected $autoWriteTimestamp = 'datetime';
	protected $dateFormat         = 'Y-m-d H:i:s';*/

	public static function AdvList($where){
		trace($where);
		$map                 = [];
		$map['type'] = $where['type'];

		if($where['status'] != ''){
			$map['status'] = $where['status'];
		}
		// if($where['name']){
		// 	$map['name'] = ['like', "%{$where['name']}%"];
		// }
		$model = self::where($map)->field(['*']);
		$model->page((int)$where['page'], (int)$where['limit']);
        $model->order($where['order']);
		$data = ($data = $model->select()) && count($data) ? $data->toArray() : [];
        foreach($data as &$val){
            $val['create_time']=time_format($val['create_time']);
            $val['update_time']=time_format($val['update_time']);
        }
		$count = self::where($map)->count();
		return compact('count', 'data');
	}
}