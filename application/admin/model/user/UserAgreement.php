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
class UserAgreement extends ModelBasic
{
    use ModelTrait;


    public static function getOne($id){
        $res=self::where('id',$id)->find()->toArray();
        return $res;
    }

    public static function AgreementList($where)
    {
        $data = ($data = self::where('status',1)->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$vo){
            $vo['content']=text($vo['content']);
            $count=mb_strlen($vo['content'],'UTF-8');
            if($count>100){
                $vo['content']=mb_substr($vo['content'],0,100,'UTF-8');
                $vo['content'].='....';
            }
        }
        unset($vo);
        //普通列表
        $count = self::where('status',1)->count();
        return compact('count', 'data');
    }



}