<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/20
 */

namespace app\shopapi\model\shop;

use app\core\model\routine\RoutineTemplate;
use app\shopapi\model\user\User;
use app\shopapi\model\user\UserAddress;
use app\core\model\user\UserBill;
use app\shopapi\model\user\WechatUser;
use app\shopapi\model\shop\ShopOrderStatus;
use basic\ModelBasic;
use app\core\behavior\OrderBehavior;
use app\core\behavior\GoodsBehavior;
use app\core\behavior\UserBehavior;
use app\core\behavior\PaymentBehavior;
use service\HookService;
use app\core\util\MiniProgramService;
use app\core\util\SystemConfigService;
use app\core\util\WechatServiceShop;
use think\Cache;
use traits\ModelTrait;
use app\osapi\model\common\Support;
use app\admin\model\system\SystemConfig;
use service\WechatTemplateService;
use app\osapi\controller\Gong ;

class ShopOrder extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    protected static $payType = ['weixin'=>'微信支付','zfb'=>'支付宝支付'];

    protected static $deliveryType = ['send'=>'商家配送','express'=>'快递配送'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    protected function setCartIdAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    protected function getCartIdAttr($value)
    {
        return json_decode($value,true);
    }


    /**生成订单
     * @param $uid
     * @param $key
     * @param $addressId
     * @param $payType
     * @param bool $useIntegral
     * @param int $couponId
     * @param string $mark
     * @param int $combinationId
     * @param int $pinkId
     * @param int $seckill_id
     * @param int $bargain_id
     * @return bool|object
     */
    public static function cacheKeyCreateOrderNew($uid,$productInfo,$totalNum,$addressId,$mark = '')
    {
        self::beginTrans();
        $userInfo = User::getUserInfo($uid);
        if(!$userInfo) return  self::setErrorInfo('用户不存在!');;
        if(!$addressId) return self::setErrorInfo('请选择收货地址!');
        if(!UserAddress::be(['uid'=>$uid,'id'=>$addressId,'is_del'=>0]) || !($addressInfo = UserAddress::find($addressId))){
            return self::setErrorInfo('地址选择有误!');
        }
        if($productInfo['limit_num']>0){
            $user_nums=self::where('uid',$uid)->where('product_id',$productInfo['id'])->where('status','neq',0)->where('status','neq',-1)->sum('total_num');
            $now_user_nums=$user_nums+$totalNum;
            if($now_user_nums>$productInfo['limit_num']){
                return self::setErrorInfo('超过该物品限购数量!',true);
            }
        }
        $pay_cash=$productInfo['cash_price']*$totalNum;
        $payPrice=$pay_cash+$productInfo['postage'];
        $use_score_num=$productInfo['score_price']*$totalNum;
        $score_type=db('shop_score_type')->where('id',1)->value('flag');
        $user_score=User::where('uid',$uid)->value($score_type);
        $nums=$user_score-$use_score_num;
        if($nums<0){
            return self::setErrorInfo('积分不足!',true);
        }
        $orderInfo = [
            'uid'=>$uid,
            'order_id'=>self::getNewOrderId(),
            'real_name'=>$addressInfo['real_name'],
            'user_phone'=>$addressInfo['phone'],
            'user_address'=>$addressInfo['province'].' '.$addressInfo['city'].' '.$addressInfo['district'].' '.$addressInfo['detail'],
            'product_id'=>$productInfo['id'],
            'total_num'=>$totalNum,
            'pay_price'=>$payPrice,
            'pay_score'=>$use_score_num,
            'pay_postage'=>$productInfo['postage'],
            'pay_cash'=>$pay_cash,
            'add_time'=>time(),
            'status'=>0,
            'paid'=>0,
            'mark'=>htmlspecialchars($mark),
        ];
        $order = self::set($orderInfo,true);
        if(!$order->result)return self::setErrorInfo('订单生成失败!',true);
        $res5 = true;
        //减库存加销量
        $res5 = $res5 && ShopProduct::decProductStock($totalNum,$productInfo['id']);
        if(!$res5) return self::setErrorInfo('库存不足，订单生成失败!',true);
        try{
            HookService::listen('store_product_order_create',$order,compact('cartInfo','addressId'),false,GoodsBehavior::class);
        }catch (\Exception $e){
            return self::setErrorInfo($e->getMessage(),true);
        }
        $res_last=ShopOrderStatus::status($order['id'],'cache_key_create_order','订单生成');
        if(!$res_last) return self::setErrorInfo('订单生成失败!',true);
        self::commitTrans();
        return $order;
    }


    /*
     *订单确认收货
     */
    public static function receivingGoodsOrder($order_id)
    {
        $order=self::where('order_id',$order_id)->find();
        if(!$order) return self::setErrorInfo('没有查到此订单');
        ModelBasic::beginTrans();
        $order->status=2;
        $res2=$order->save();
        if($res2){
            self::commit();
            return $res2;
        }else{
            self::rollback();
            return false;
        }
    }

    public static function getNewOrderId()
    {
        $count = (int) self::where('add_time',['>=',strtotime(date("Y-m-d"))],['<',strtotime(date("Y-m-d",strtotime('+1 day')))])->count();
        return 'wx'.date('YmdHis',time()).(10000+$count+1);
    }

    public static function changeOrderId($orderId)
    {
        $ymd = substr($orderId,2,8);
        $key = substr($orderId,16);
        return 'wx'.$ymd.date('His').$key;
    }
    //TODO JS支付
    public static function jsPay($orderId,$field = 'order_id')
    {
        if(is_string($orderId))
            $orderInfo = self::where($field,$orderId)->find();
        else
            $orderInfo = $orderId;
        if(!$orderInfo || !isset($orderInfo['paid'])) exception('支付订单不存在!');
        if($orderInfo['paid']) exception('支付已支付!');
        if($orderInfo['pay_price'] == 0) exception('该支付无需支付!');
        $openid_eb = WechatUser::getOpenId($orderInfo['uid']);
        if(!$openid_eb){
            $openid_os=db('user_sync_login')->where('uid',$orderInfo['uid'])->value('open_id');
            if(!$openid_os){
                exception('该用户没有绑定微信!');
            }else{
                $openid=$openid_os;
            }
        }else{
            $openid=$openid_eb;
        }
        return WechatServiceShop::jsPay($openid,$orderInfo['order_id'],$orderInfo['pay_price'],'productr',SystemConfigService::get('website_name'));
    }
    //TODO 余额支付
    public static function yuePay($order_id,$uid,$formId = '',$bill_type='pay_product')
    {
        $orderInfo = self::where('uid',$uid)->where('order_id',$order_id)->find();
        if(!$orderInfo) return self::setErrorInfo('订单不存在!');
        if($orderInfo['paid']) return self::setErrorInfo('该订单已支付!');
//        if($orderInfo['pay_type'] != 'yue') return self::setErrorInfo('该订单不能使用余额支付!');
        $userInfo = User::getUserInfo($uid);
        /*if($userInfo['now_money'] < $orderInfo['pay_price'])
            return self::setErrorInfo(['status'=>'pay_deficiency','msg'=>'余额不足'.floatval($orderInfo['pay_price'])]);*/
        self::beginTrans();

        //$res1 = false !== User::bcDec($uid,'now_money',$orderInfo['pay_price'],'uid');

        $res3 = self::paySuccess($order_id,'yue',$formId);//余额支付成功
        try{
            HookService::listen('yue_pay_product',$userInfo,$orderInfo,false,PaymentBehavior::class);
        }catch (\Exception $e){
            self::rollbackTrans();
            return self::setErrorInfo($e->getMessage());
        }
        $res = $res3;
        self::checkTrans($res);
        return $res;
    }

    /**
     * 微信支付 为 0元时
     * @param $order_id
     * @param $uid
     * @return bool
     */
    public static function jsPayPrice($order_id,$uid,$formId = ''){
        $orderInfo = self::where('uid',$uid)->where('order_id',$order_id)->where('is_del',0)->find();
        if(!$orderInfo) return self::setErrorInfo('订单不存在!');
        if($orderInfo['paid']) return self::setErrorInfo('该订单已支付!');
        $userInfo = User::getUserInfo($uid);
        self::beginTrans();
        $res1 = UserBill::expend('购买商品',$uid,'now_money','pay_product',$orderInfo['pay_price'],$orderInfo['id'],$userInfo['now_money'],'微信支付'.floatval($orderInfo['pay_price']).'元购买商品');
        $res2 = self::paySuccess($order_id,'weixin',$formId);//微信支付为0时
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }


    /**
     * //TODO 支付成功后
     * @param $orderId
     * @param $paytype
     * @param $notify
     * @return bool
     */
    public static function paySuccess($orderId,$paytype='weixin',$formId = '')
    {
        $order = self::where('order_id',$orderId)->find();
        $res1 = self::where('order_id',$orderId)->update(['paid'=>1,'pay_type'=>$paytype,'status'=>1,'pay_time'=>time()]);//订单改为支付
        $score_type=db('shop_score_type')->where('id',1)->value('flag');
        $user_score=User::where('uid',$order['uid'])->value($score_type);
        $nums=$user_score-$order['pay_score'];
        $res2=User::where('uid',$order['uid'])->update([$score_type=>$nums]);
        $log=[];
        $log[$score_type]=$order['pay_score'];
        Support::jiafenlog($order['uid'],'积分商城购买物品',$log,0,'行为');

        $oid = self::where('order_id',$orderId)->value('id');
        $res3=ShopOrderStatus::status($oid,'pay_success','用户付款成功');
        WechatTemplateService::sendTemplate(WechatUser::uidToOpenid($order['uid']),WechatTemplateService::ORDER_PAY_SUCCESS, [
            'first'=>'亲，您购买的商品已支付成功',
            'keyword1'=>$orderId,
            'keyword2'=>date("Y-m-d H:i:s",$order['add_time']),
            'keyword3'=>$order['real_name'],
            'keyword4'=>$order['user_address'],
            'keyword5'=>$order['pay_price'],
            'remark'=>'可以去商城查看订单详情'
        ],'');
        $res = $res1 && $res2 && $res3;
        return false !== $res;
    }


    public static function getUserOrderDetail($uid,$key)
    {
        return self::where('order_id',$key)->where('uid',$uid)->find();
    }


    /**
     * TODO 订单发货
     * @param array $postageData 发货信息
     * @param string $oid orderID
     */
    public static function orderPostageAfter($postageData, $oid)
    {
        $order = self::where('id',$oid)->find();
        $url ='/pages/order_details/index?order_id='.$order['order_id'];
        $group = [
            'first'=>'亲,您的订单已发货,请注意查收',
            'remark'=>'点击查看订单详情'
        ];
        if($postageData['delivery_type'] == 'send'){//送货
            $goodsName = StoreOrderCartInfo::getProductNameList($order['id']);
            $group = array_merge($group,[
                'keyword1'=>$goodsName,
                'keyword2'=>$order['pay_type'] == 'offline' ? '线下支付' : date('Y/m/d H:i',$order['pay_time']),
                'keyword3'=>$order['user_address'],
                'keyword4'=>$postageData['delivery_name'],
                'keyword5'=>$postageData['delivery_id']
            ]);
            RoutineTemplate::sendOut('ORDER_DELIVER_SUCCESS',$order['uid'],$group,$url);
        }else if($postageData['delivery_type'] == 'express'){//发货
            $group = array_merge($group,[
                'keyword1'=>$order['order_id'],
                'keyword2'=>$postageData['delivery_name'],
                'keyword3'=>$postageData['delivery_id']
            ]);
            RoutineTemplate::sendOut('ORDER_POSTAGE_SUCCESS',$order['uid'],$group,$url);
        }
    }


    /**
     * //TODO 用户确认收货
     * @param $uni
     * @param $uid
     */
    public static function takeOrder($uni, $uid)
    {
        $order = self::getUserOrderDetail($uid,$uni);
        if(!$order) return self::setErrorInfo('订单不存在!');
        $order = self::tidyOrder($order);
        if($order['status'] != 2)  return self::setErrorInfo('订单状态错误!');
        self::beginTrans();
        $res1=self::edit(['status'=>3],$order['id'],'id');
        $res2=ShopOrderStatus::status($order['id'],'user_take_delivery','用户已收货');
        if(false !== $res1 && false !== $res2){
            self::commitTrans();
            return true;
        }else{
            self::rollbackTrans();
            return false;
        }
    }

    public static function tidyOrder($order,$detail = false,$isPic=false)
    {
        $status = [];
        if($order['paid']==1 && $order['status'] == 1){
            $status['_type'] = 1;
            $status['_title'] = '待发货';
            $status['_msg'] = '等待商家处理,请耐心等待';
            $status['_class'] = 'nobuy';
        }else if($order['paid']==1 && $order['status'] == 2){
            $status['_type'] = 2;
            $status['_title'] = '待收货';
            $status['_msg'] = '运送中,请耐心等待';
            $status['_class'] = 'nobuy';
        }else if($order['paid']==1 && $order['status'] == 3){
            $status['_type'] = 4;
            $status['_title'] = '兑换成功';
            $status['_msg'] = '交易完成,感谢您的支持';
            $status['_class'] = 'state-sqtk';
        }else if($order['paid']==1 && $order['status'] == -2){
            $status['_type'] = -2;
            $status['_title'] = '兑换失败';
            $status['_msg'] = '兑换失败，已为您退款';
            $status['_class'] = 'state-sqtk';
        }
        $order['_status'] = $status;
        $order['_pay_time']=isset($order['pay_time']) && $order['pay_time'] != null ? date('Y-m-d H:i:s',$order['pay_time']) : date('Y-m-d H:i:s',$order['add_time']);
        $order['_add_time']=isset($order['add_time']) ? (strstr($order['add_time'],'-')===false ? date('Y-m-d H:i:s',$order['add_time']) : $order['add_time'] ): '';
        $order['status_pic']='';
        switch($order['pay_type']){
            case 'weixin':
                $order['pay_type']='微信支付';
                break;
            case 'yue':
                $order['pay_type']='余额支付';
                break;
            case 'score':
                $order['pay_type']='积分支付';
                break;
        }
        $order['product_info']=ShopProduct::where('id',$order['product_id'])->find();
        //获取产品状态图片
        if($isPic){
            $order_details_images=\service\GroupDataService::getData('order_details_images') ? : [];
            foreach ($order_details_images as $image){
                if(isset($image['order_status']) && $image['order_status']==$order['_status']['_type']){
                    $order['status_pic']=$image['pic'];
                    break;
                }
            }
        }
        $order['order_sn']=$order['delivery_id'];
        switch ($order['delivery_name']){
            case '速通物流':
                $order['ShipperCode'] = 'ST';
                break;
            case '申通快递':
                $order['ShipperCode'] = 'STO';
                break;
            case '韵达快递':
                $order['ShipperCode'] = 'YD';
                break;
            case '圆通速递':
                $order['ShipperCode'] = 'YTO';
                break;
            case '宅急送':
                $order['ShipperCode'] = 'ZJS';
                break;
            case '众通快递':
                $order['ShipperCode'] = 'ZTE';
                break;
            case '中通速递':
                $order['ShipperCode'] = 'ZTO';
                break;
            case '亚马逊物流':
                $order['ShipperCode'] = 'AMAZON';
                break;
            default:
                $order['ShipperCode'] = '该物流公司不支持实时物流查询';
                break;
        }
        return $order;
    }


    public static function getUserOrderList($uid,$page = 0,$limit = 8)
    {
        $list = self::where('uid',$uid)->where('status','neq',0)->where('status','neq',-1)
            ->order('add_time DESC')->page((int)$page,(int)$limit)->select()->toArray();
        foreach ($list as $k=>$order){
            $list[$k] = self::tidyOrder($order,true);
        }

        return $list;
    }



    public static function getOrderStatusNum($uid)
    {
        $noBuy = self::where('uid',$uid)->where('paid',0)->where('is_del',0)->where('pay_type','<>','offline')->count();
        $noPostageNoPink = self::where('o.uid',$uid)->alias('o')->where('o.paid',1)->where('o.pink_id',0)->where('o.is_del',0)->where('o.status',0)->where('o.pay_type','<>','offline')->count();
        $noPostageYesPink = self::where('o.uid',$uid)->alias('o')->join('StorePink p','o.pink_id = p.id')->where('p.status',2)->where('o.paid',1)->where('o.is_del',0)->where('o.status',0)->where('o.pay_type','<>','offline')->count();
        $noPostage = bcadd($noPostageNoPink,$noPostageYesPink);
        $noTake = self::where('uid',$uid)->where('paid',1)->where('is_del',0)->where('status',1)->where('pay_type','<>','offline')->count();
        $noReply = self::where('uid',$uid)->where('paid',1)->where('is_del',0)->where('status',2)->count();
        $noPink = self::where('o.uid',$uid)->alias('o')->join('StorePink p','o.pink_id = p.id')->where('p.status',1)->where('o.paid',1)->where('o.is_del',0)->where('o.status',0)->where('o.pay_type','<>','offline')->count();
        $noRefund = self::where('uid',$uid)->where('paid',1)->where('is_del',0)->where('refund_status','IN','1,2')->count();
        return compact('noBuy','noPostage','noTake','noReply','noPink','noRefund');
    }



    public static function getUserPrice($uid =0){
        if(!$uid) return 0;
        $price = self::where('paid',1)->where('uid',$uid)->where('status',2)->where('refund_status',0)->column('pay_price','id');
        $count = 0;
        if($price){
            foreach ($price as $v){
                $count = bcadd($count,$v,2);
            }
        }
        return $count;
    }


    /*
     * 个人中心获取个人订单列表和订单搜索
     * @param int $uid 用户uid
     * @param int | string 查找订单类型
     * @param int $first 分页
     * @param int 每页显示多少条
     * @param string $search 订单号
     * @return array
     * */
    public static function getUserOrderSearchList($uid,$page,$limit)
    {
        $list = self::getUserOrderList($uid,$page,$limit);
        foreach ($list as $k=>&$order){
            $list[$k] = self::tidyOrder($order,true);
            $order['order_sn']=$order['delivery_id'];
            switch ($order['delivery_name']){
                case '速通物流':
                    $order['ShipperCode'] = 'ST';
                    break;
                case '申通快递':
                    $order['ShipperCode'] = 'STO';
                    break;
                case '韵达快递':
                    $order['ShipperCode'] = 'YD';
                    break;
                case '圆通速递':
                    $order['ShipperCode'] = 'YTO';
                    break;
                case '宅急送':
                    $order['ShipperCode'] = 'ZJS';
                    break;
                case '众通快递':
                    $order['ShipperCode'] = 'ZTE';
                    break;
                case '中通速递':
                    $order['ShipperCode'] = 'ZTO';
                    break;
                case '亚马逊物流':
                    $order['ShipperCode'] = 'AMAZON';
                    break;
                default:
                    $order['ShipperCode'] = '该物流公司不支持实时物流查询';
                    break;
            }
            $order['product_info']['image_150']=thumb_path($order['product_info']['image'],150,150);
            $order['product_info']['image_350']=thumb_path($order['product_info']['image'],350,350);
            $order['product_info']['image_750']=thumb_path($order['product_info']['image'],750,750);
        }
        return $list;
    }

    /**
     * 是否购买
     * [is_gm description]
     * @param  [type]  $uid [description]
     * @param  [type]  $gid [description]
     * @return boolean      [description]
     */
    public static function is_gm($uid,$gid)
    {
        $str = "cart_id like '[".$gid."]' or cart_id like '%,".$gid."]' or cart_id like '[".$gid.",%' or cart_id like '%,".$gid.",%'";
        return self::where(['uid'=>$uid,'paid'=>1,'is_zg'=>1])->where($str)->select();
    }

    public static function isOrder($uid)
    {
        return self::where(['uid'=>$uid,'paid'=>0,'is_zg'=>1])->select()->toArray();
    }
}