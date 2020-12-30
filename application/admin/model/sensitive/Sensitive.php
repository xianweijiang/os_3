<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\sensitive;


use app\admin\model\ump\StoreCouponUser;
use app\admin\model\wechat\WechatUser;
use app\admin\model\ump\StorePink;
use app\admin\model\order\StoreOrderCartInfo;
use app\admin\model\store\StoreProduct;
use app\admin\model\routine\RoutineFormId;
use app\core\model\routine\RoutineTemplate;
use service\ProgramTemplateService;
use service\PHPExcelService;
use traits\ModelTrait;
use basic\ModelBasic;
use service\WechatTemplateService;
use think\Url;
use think\Db;
use app\admin\model\user\User;
use app\admin\model\user\UserBill;
/**
 * 订单管理Model
 * Class StoreOrder
 * @package app\admin\model\store
 */
class Sensitive extends ModelBasic
{
    use ModelTrait;


    public static function editSensitive($data,$id){
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

    public static function sensitiveList($where)
    {
        $model = self::getModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('id desc')->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            switch($item['level']){
                case 1:
                    $item['level'] = '替换';
                    break;
                case 2:
                    $item['level'] = '删除';
                    break;
                case 3:
                    $item['level'] = '审核';
                    break;
            }
            $item['create_time']=time_format($item['create_time']);
        }
        //普通列表
        $count = self::getModelObject($where)->count();
        return compact('count', 'data');
    }

    public static function getModelObject($where = [])
    {
        $model = new self();
        if (!empty($where)) {
            if(isset($where['sensitive']) && $where['sensitive']!=''){
                $model->where('sensitive','like','%'.$where['sensitive'].'%');
            }
        }
        return $model;
    }

}