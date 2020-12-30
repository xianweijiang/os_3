<?php

namespace app\admin\model\user;

use service\PHPExcelService;
use app\admin\model\User;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService;

/**
 * 广告位 model
 * Class ComForum
 * @package app\admin\model\com
 */
class UserRecommend extends ModelBasic
{
	use ModelTrait;

	// 自动写入时间戳
	/*protected $autoWriteTimestamp = 'datetime';
	protected $dateFormat         = 'Y-m-d H:i:s';*/

	public static function UserList($where){
		$model = self::where('status',1)->field(['*']);
		$model->page((int)$where['page'], (int)$where['limit']);
        $model->order('create_time desc');
		$data = ($data = $model->select()) && count($data) ? $data->toArray() : [];
        foreach($data as &$val){
            $val['create_time']=time_format($val['create_time']);
            $val['nickname']=db('user')->where('uid',$val['uid'])->value('nickname');
        }
		$count = self::where('status',1)->count();
		return compact('count', 'data');
	}
}