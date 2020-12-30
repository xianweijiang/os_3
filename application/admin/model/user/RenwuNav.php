<?php
/**
 *
 * @author: cyx<cyx@ourstu.com>
 * @day: 2019/4/12
 */

namespace app\admin\model\user;

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
class RenwuNav extends ModelBasic
{
    use ModelTrait;

    public static function NavList($where){
        $map['status'] = $where['status'];
    	$model = self::where($map)->field(['*']);
    	$model->page((int)$where['page'], (int)$where['limit']);
    	$data = ($data = $model->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $item['create_time']=time_format($item['create_time']);
            $item['update_time']=time_format($item['update_time']);
        }
    	$count = self::where($map)->count();
        return compact('count', 'data');
    }
}