<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\shopapi\model\shop;


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
class ShopColumn extends ModelBasic
{
    use ModelTrait;

    public static function getColumnList(){
        $column = self::Where('status',1)->select();
        return $column;
    }

    public static function editSet($data,$id){
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

    public static function SetList($where)
    {
        $data = ($data = self::where('status',$where['status'])->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            switch($item['type']){
                case 1:
                    $item['type'] = '商品列表';
                    break;
            }
        }
        //普通列表
        $count = self::where('status',$where['status'])->count();
        return compact('count', 'data');
    }

}