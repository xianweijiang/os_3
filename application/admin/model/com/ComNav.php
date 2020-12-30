<?php
/**
 *
 * @author: cyx<cyx@ourstu.com>
 * @day: 2019/4/12
 */

namespace app\admin\model\com;

use service\PHPExcelService;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService;
use app\admin\model\user\User as UserModel;
/**
 * 版块 model
 * Class ComForum
 * @package app\admin\model\com
 */
class ComNav extends ModelBasic
{
    use ModelTrait;

    public static function NavList($where){
    	$map                 = [];
    	$map['type']         = $where['type'];
    	if($where['status'] != ''){
    		$map['status'] = $where['status'];
    	}
    	if($where['name']){
    		$map['name'] = ['like', "%{$where['name']}%"];
    	}
    	$model = self::where($map)->field(['*']);
    	$model->page((int)$where['page'], (int)$where['limit']);
    	$data = ($data = $model->select()) && count($data) ? $data->toArray() : [];
    	$count = self::where($map)->count();
        return compact('count', 'data');
    }
}