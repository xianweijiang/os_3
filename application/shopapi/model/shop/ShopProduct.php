<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/12
 */

namespace app\shopapi\model\shop;

use app\admin\model\store\StoreProductAttrValue as StoreProductAttrValuemodel;
use app\core\model\SystemUserLevel;
use app\core\model\UserLevel;
use basic\ModelBasic;
use service\SystemConfigService;
use traits\ModelTrait;
use think\Db;

class ShopProduct extends ModelBasic
{
    use  ModelTrait;

    protected function getSliderImageAttr($value)
    {
        return json_decode($value,true)?:[];
    }

    public static function getValidProduct($productId)
    {
        $res=self::where('status',1)->where('is_on',1)->where('id',$productId)->order('sort asc,add_time desc')->find();
        if($res['image']){
            $res['image_150']=thumb_path($res['image'],150,150);
            $res['image_350']=thumb_path($res['image'],350,350);
            $res['image_750']=thumb_path($res['image'],750,750);
        }
        $score_type=db('shop_score_type')->where('id',1)->value('flag');
        $res['score_name']=db('system_rule')->where('flag',$score_type)->value('name');
        return $res;
    }


    public static function isValidProduct($productId)
    {
        return self::be(['id'=>$productId,'is_del'=>0,'is_show'=>1]) > 0;
    }

    public static function getProductStock($productId,$uniqueId = '')
    {
        return self::where('id',$productId)->value('stock');
    }

    public static function decProductStock($num,$productId)
    {
        $res = self::where('id',$productId)->where('stock','>=',$num)->dec('stock',$num)->inc('sales',$num)->update();
        return $res;
    }


    public static function getPacketPrice($storeInfo,$productValue)
    {
        $store_brokerage_ratio=SystemConfigService::get('store_brokerage_ratio');
        $store_brokerage_ratio=bcdiv($store_brokerage_ratio,100,2);
        if(count($productValue)){
            $maxPrice=self::getArrayMax($productValue,'price');
            $minPrice=self::getArrayMin($productValue,'price');
            $maxPrice=bcmul($store_brokerage_ratio,$maxPrice,0);
            $minPrice=bcmul($store_brokerage_ratio,$minPrice,0);
            return $minPrice.'~'.$maxPrice;
        }else{
            return bcmul($store_brokerage_ratio,$storeInfo['price'],0);
        }
    }
    /*
     * 获取二维数组中最大的值
     * */
    public static function getArrayMax($arr,$field)
    {
        $temp=[];
        foreach ($arr as $k=>$v){
            $temp[]=$v[$field];
        }
        return max($temp);
    }
    /*
     * 获取二维数组中最小的值
     * */
    public static function getArrayMin($arr,$field)
    {
        $temp=[];
        foreach ($arr as $k=>$v){
            $temp[]=$v[$field];
        }
        return min($temp);
    }



}