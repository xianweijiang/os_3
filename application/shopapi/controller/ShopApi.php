<?php
namespace app\shopapi\controller;

use app\shopapi\model\shop\ShopProduct;
use app\core\util\GroupDataService;
use app\shopapi\model\shop\ShopColumn;
use app\shopapi\model\user\User as UserModel;
use service\JsonService;
use app\core\util\SystemConfigService;
use service\UtilService;
use app\core\util\MiniProgramService;

/**
 * 小程序产品和产品分类api接口
 * Class StoreApi
 * @package app\routine\controller
 *
 */
class ShopApi extends AuthController
{

    public static function whiteList()
    {
        return [
            'details',
            'shop_column_list',
        ];
    }

    /**
     * 商品详情页
     */
    public function details(){
        $id=osx_input('id',0,'intval');
        if(!$id || !($storeInfo = ShopProduct::getValidProduct($id))) return JsonService::fail('商品不存在或已下架');
        $data['storeInfo'] = $storeInfo;
        return JsonService::successful($data);
    }


    /**
     * 栏目列表
     */
    public function shop_column_list(){
        list($page,$limit)=UtilService::getMore([
            ['page',''],
            ['limit',''],
        ],$this->request,true);
        $list=ShopColumn::where('status',1)->order('sort asc')->select();
        foreach($list as &$val){
            $val['product_list']=ShopProduct::where('status',1)->where('is_on',1)->where('column_id',$val['id'])->page((int)$page,(int)$limit)->order('sort asc,add_time desc')->select();
            foreach($val['product_list'] as &$value){
                $value['image_150']=thumb_path($value['image'],150,150);
                $value['image_350']=thumb_path($value['image'],350,350);
                $value['image_750']=thumb_path($value['image'],750,750);
            }
            unset($value);
        }
        unset($val);
        return JsonService::successful($list);
    }

    /**
     * 用户积分
     */
    public function user_score(){
        $uid=$this->uid;
        $score_type=db('shop_score_type')->where('id',1)->value('flag');
        $user_score['name']=db('system_rule')->where('flag',$score_type)->value('name');
        $user_score['num']=UserModel::where('uid',$uid)->value($score_type);
        return JsonService::successful($user_score);
    }

}