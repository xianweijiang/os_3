<?php
/**
 *
 * @author: cyx<cyx@ourstu.com>
 * @day: 2019/4/12
 */

namespace app\admin\model\store;

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
class StoreSet extends ModelBasic
{
    use ModelTrait;

    public static function editSet($data,$id){
        $data['content']=html($data['content']);
        $res=self::where('id',$id)->update($data);
        if($res){
            return 1;
        }else{
            return 0;
        }
    }

    public static function getOne($id){
        $res=self::where('id',$id)->find()->toArray();
        return $res;
    }

    /*
     * 获取通知列表
     * @param $where array
     * @return array
     *
     */
    public static function SetList($where)
    {
        $data = ($data = self::where('status',$where['status'])->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        //普通列表
        $count = self::where('status',$where['status'])->count();
        return compact('count', 'data');
    }



}