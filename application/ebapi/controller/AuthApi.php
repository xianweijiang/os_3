<?php
namespace app\ebapi\controller;

use app\core\model\routine\RoutineFormId;//待完善
use app\core\model\UserLevel;
use service\JsonService;
use app\core\util\SystemConfigService;
use service\UtilService;
use think\Request;
use app\core\behavior\GoodsBehavior;//待完善
use app\ebapi\model\store\StoreCouponUser;
use app\ebapi\model\store\StoreOrder;
use app\ebapi\model\store\StoreProductAttrValue;
use app\ebapi\model\store\StoreCart;
use app\ebapi\model\user\User;
use app\ebapi\model\store\StorePink;
use app\ebapi\model\store\StoreBargainUser;
use app\ebapi\model\store\StoreBargainUserHelp;
use app\admin\model\system\SystemConfig;
use service\WechatTemplateService;
use app\ebapi\model\user\WechatUser;
use app\core\util\WechatService;
use app\ebapi\model\store\StoreOrderStatus;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;

/**
 * 小程序 购物车,新增订单等 api接口
 * Class AuthApi
 * @package app\routine\controller
 *
 */
class AuthApi extends AuthController
{

    public static function whiteList()
    {
        return [
            'time_out_order',
            'user_message_order',
            'website_paySuccess',
        ];
    }

    /**
     * 购物车
     * @return \think\response\Json
     */
    public function get_cart_list()
    {
        return JsonService::successful(StoreCart::getUserProductCartList($this->userInfo['uid']));
    }


    /*
     * 获取订单支付状态
     * @param string ordre_id 订单id
     * @return json
     * */
    public function get_order_pay_info()
    {
        $order_id=osx_input('order_id','','text');//订单id
        if ($order_id == '') return JsonService::fail('缺少参数');
        return JsonService::successful(StoreOrder::tidyOrder(StoreOrder::where('order_id', $order_id)->find()));
    }
    /**
     * 订单页面
     * @param Request $request
     * @return \think\response\Json
     */
    public function confirm_order(Request $request)
    {
        $data = UtilService::postMore(['cartId'], $request);
        $cartId = $data['cartId'];
        if (!is_string($cartId) || !$cartId) return JsonService::fail('请提交购买的商品!');
        $cartGroup = StoreCart::getUserProductCartList($this->userInfo['uid'], $cartId, 1);
        if (count($cartGroup['invalid'])) return JsonService::fail($cartGroup['invalid'][0]['productInfo']['store_name'] . '已失效!');
        if (!$cartGroup['valid']) return JsonService::fail('请提交购买的商品!!');
        $cartInfo = $cartGroup['valid'];
        $priceGroup = StoreOrder::getOrderPriceGroup($cartInfo);
        $other = [
            'offlinePostage' => SystemConfigService::get('offline_postage'),
            'integralRatio' => SystemConfigService::get('integral_ratio')
        ];
        $usableCoupon = StoreCouponUser::beUsableCoupon($this->userInfo['uid'], $priceGroup['totalPrice']);
        $cartIdA = explode(',', $cartId);
        if (count($cartIdA) > 1) $seckill_id = 0;
        else {
            $seckillinfo = StoreCart::where('id', $cartId)->find();
            if ((int)$seckillinfo['seckill_id'] > 0) $seckill_id = $seckillinfo['seckill_id'];
            else $seckill_id = 0;
        }
        $data['usableCoupon'] = $usableCoupon;
        $data['seckill_id'] = $seckill_id;
        $data['cartInfo'] = $cartInfo;
        $data['priceGroup'] = $priceGroup;
        $data['orderKey'] = StoreOrder::cacheOrderInfo($this->userInfo['uid'], $cartInfo, $priceGroup, $other);
        $data['offlinePostage'] = $other['offlinePostage'];
        $vipId=UserLevel::getUserLevel($this->uid);
        $this->userInfo['vip']=$vipId !==false ? true : false;
        if($this->userInfo['vip']){
            $this->userInfo['vip_id']=$vipId;
            $this->userInfo['discount']=UserLevel::getUserLevelInfo($vipId,'discount');
        }
        $data['userInfo']=$this->userInfo;
        $data['integralRatio'] = $other['integralRatio'];
        return JsonService::successful($data);
    }

    /**
     * [cacheorder description]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function cacheorder(Request $request)
    {
        $data = UtilService::postMore(['cartId'], $request);
        $cartId = $data['cartId'];
        if (!is_string($cartId) || !$cartId) return JsonService::fail('请提交购买的商品!');
        $cartGroup = StoreCart::getUserZgCartList($this->userInfo['uid'], $cartId, 1);
        if (count($cartGroup['invalid'])) return JsonService::fail($cartGroup['invalid'][0]['productInfo']['store_name'] . '已失效!');
        // return JsonService::fail([]);
        if (!$cartGroup['valid']) return JsonService::fail('请提交购买的商品!!');
        $cartInfo = $cartGroup['valid'];
        $priceGroup = StoreOrder::getOrderPriceGroup($cartInfo);
        $other = [
            'offlinePostage' => SystemConfigService::get('offline_postage'),
            'integralRatio' => SystemConfigService::get('integral_ratio')
        ];
        $usableCoupon = StoreCouponUser::beUsableCoupon($this->userInfo['uid'], $priceGroup['totalPrice']);
        $cartIdA = explode(',', $cartId);
        if (count($cartIdA) > 1) $seckill_id = 0;
        else {
            $seckillinfo = StoreCart::where('id', $cartId)->find();
            if ((int)$seckillinfo['seckill_id'] > 0) $seckill_id = $seckillinfo['seckill_id'];
            else $seckill_id = 0;
        }
        $data['usableCoupon'] = $usableCoupon;
        $data['seckill_id'] = $seckill_id;
        $data['cartInfo'] = $cartInfo;
        $data['priceGroup'] = $priceGroup;
        $data['orderKey'] = StoreOrder::cacheOrderInfo($this->userInfo['uid'], $cartInfo, $priceGroup, $other);
        $data['offlinePostage'] = $other['offlinePostage'];
        $vipId=UserLevel::getUserLevel($this->uid);
        $this->userInfo['vip']=$vipId !==false ? true : false;
        if($this->userInfo['vip']){
            $this->userInfo['vip_id']=$vipId;
            $this->userInfo['discount']=UserLevel::getUserLevelInfo($vipId,'discount');
        }
        $data['userInfo']=$this->userInfo;
        $data['integralRatio'] = $other['integralRatio'];
        return JsonService::successful($data);
    }

    /*
     * 获取小程序订单列表统计数据
     *
     * */
    public function get_order_data()
    {
        return JsonService::successful(StoreOrder::getOrderData($this->uid));
    }
    /**
     * 过度查$uniqueId
     * @param string $productId
     * @param int $cartNum
     * @param string $uniqueId
     * @return \think\response\Json
     */
    public function unique()
    {
        $productId = $_GET['productId'];
        if (!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $uniqueId = StoreProductAttrValue::where('product_id', $productId)->value('unique');
        $data = $this->set_cart($productId, $cartNum = 1, $uniqueId);
        if ($data == true) {
            return JsonService::successful('ok');
        }
    }
    /**
     * 加入到购物车
     * @return \think\response\Json
     */
    public function set_cart()
    {
        $productId=osx_input('productId','','text');
        $cartNum=osx_input('cartNum',1,'text');
        $uniqueId=osx_input('uniqueId','','text');
        $type=osx_input('type','product','text');
        if (!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $cart_limit=SystemConfig::getValue('cart_limit');
        if($cart_limit<$cartNum) return JsonService::fail('加入购物车数量最高限制为'.$cart_limit.'件');
        $res = StoreCart::setCart($this->userInfo['uid'], $productId, $cartNum, $uniqueId, $type,'','','','','add_to_cart');
        if (!$res->result) return JsonService::fail(StoreCart::getErrorInfo());
        else return JsonService::successful('ok', ['cartId' => $res->id]);
    }


    /**
     * 拼团 秒杀 砍价 加入到购物车
     * @return \think\response\Json
     */
    public function now_buy()
    {
        $productId=osx_input('productId','','text');
        $cartNum=osx_input('cartNum',1,'intval');
        $uniqueId=osx_input('uniqueId','','text');
        $combinationId=osx_input('combinationId',0,'intval');
        $secKillId=osx_input('secKillId',0,'intval');
        $bargainId=osx_input('bargainId',0,'text');
        if (!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        if ($bargainId && StoreBargainUserHelp::getSurplusPrice($bargainId, $this->userInfo['uid'])) return JsonService::fail('请先砍价');
        $res = StoreCart::setCart($this->userInfo['uid'], $productId, $cartNum, $uniqueId, 'product', 1, $combinationId, $secKillId, $bargainId,'now_buy');
        if (!$res->result) return JsonService::fail(StoreCart::getErrorInfo());
        else  return JsonService::successful('ok', ['cartId' => $res->id]);
    }
    /**
     * 拼团 秒杀 砍价 加入到购物车
     * @return \think\response\Json
     */
    public function now_buy_zg()
    {
        $productId=osx_input('productId','','text');
        $cartNum=osx_input('cartNum',1,'intval');
        $uniqueId=osx_input('uniqueId','','text');
        $combinationId=osx_input('combinationId',0,'intval');
        $secKillId=osx_input('secKillId',0,'intval');
        $bargainId=osx_input('bargainId',0,'text');
        if (!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        if ($bargainId && StoreBargainUserHelp::getSurplusPrice($bargainId, $this->userInfo['uid'])) return JsonService::fail('请先砍价');
        $res = StoreCart::setCart($this->userInfo['uid'], $productId, $cartNum, $uniqueId, 'is_zg', 1, $combinationId, $secKillId, $bargainId);
        if (!$res->result) return JsonService::fail(StoreCart::getErrorInfo());
        else  return JsonService::successful('ok', ['cartId' => $res->id]);
    }
    /**
     * 获取购物车数量
     * @return \think\response\Json
     */
    public function get_cart_num()
    {
        return JsonService::successful('ok', StoreCart::getUserCartNum($this->userInfo['uid'], 'product'));
    }
    
    /**
     * 修改购物车产品数量
     * @return \think\response\Json
     */
    public function change_cart_num()
    {
        $cartId=osx_input('cartId','','text');
        $cartNum=osx_input('cartNum','','text');
        if (!$cartId || !$cartNum || !is_numeric($cartId) || !is_numeric($cartNum)) return JsonService::fail('参数错误!');
        $cart_limit=SystemConfig::getValue('cart_limit');
        if($cart_limit<$cartNum) return JsonService::fail('加入购物车数量最高限制为'.$cart_limit.'件');
        $res = StoreCart::changeUserCartNum($cartId, $cartNum, $this->userInfo['uid']);
        if ($res)  return JsonService::successful();
        else return JsonService::fail(StoreCart::getErrorInfo('修改失败'));
    }

    /**
     * 删除购物车产品
     * @return \think\response\Json
     */
    public function remove_cart()
    {
        $ids=osx_input('ids','','text');
        if (!$ids) {
            return JsonService::fail('参数错误!');
        }
        $res=StoreCart::removeUserCart($this->userInfo['uid'], $ids);
        if($res){
            return JsonService::successful('删除成功');
        }
        return JsonService::fail('删除失败!');
    }
    /**
     * 创建订单
     * @return \think\response\Json
     */
    public function create_order()
    {
        $key=osx_input('key','','text');
        if (!$key) return JsonService::fail('参数错误!');
        if (StoreOrder::be(['order_id|unique' => $key, 'uid' => $this->userInfo['uid'], 'is_del' => 0]))
            return JsonService::status('extend_order', '订单已生成', ['orderId' => $key, 'key' => $key]);
        list($addressId, $couponId, $payType, $useIntegral, $mark, $combinationId, $pinkId, $seckill_id, $formId, $bargainId) = UtilService::postMore([
            'addressId', 'couponId', 'payType', 'useIntegral', 'mark', ['combinationId', 0], ['pinkId', 0], ['seckill_id', 0], ['formId', ''], ['bargainId', '']
        ], Request::instance(), true);
        $payType = strtolower($payType);
        if ($bargainId) StoreBargainUser::setBargainUserStatus($bargainId, $this->userInfo['uid']); //修改砍价状态
        if ($pinkId) if (StorePink::getIsPinkUid($pinkId, $this->userInfo['uid'])) return JsonService::status('ORDER_EXIST', '订单生成失败，你已经在该团内不能再参加了', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $this->userInfo['uid'])]);
        if ($pinkId) if (StoreOrder::getIsOrderPink($pinkId, $this->userInfo['uid'])) return JsonService::status('ORDER_EXIST', '订单生成失败，你已经参加该团了，请先支付订单', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $this->userInfo['uid'])]);
        $order = StoreOrder::cacheKeyCreateOrder($this->userInfo['uid'], $key, $addressId, $payType, $useIntegral, $couponId, $mark, $combinationId, $pinkId, $seckill_id, $bargainId);
        $orderId = $order['order_id'];
        $info = compact('orderId', 'key');
        if ($orderId) {
            switch ($payType) {
                case "weixin":
                    $orderInfo = StoreOrder::where('order_id', $orderId)->find();
                    if (!$orderInfo || !isset($orderInfo['paid'])) exception('支付订单不存在!');
                    if ($orderInfo['paid']) exception('支付已支付!');
                    //如果支付金额为0
                    if (bcsub((float)$orderInfo['pay_price'], 0, 2) <= 0) {
                        //创建订单jspay支付
                        if (StoreOrder::jsPayPrice($orderId, $this->userInfo['uid'], $formId))
                            return JsonService::status('success', '微信支付成功', $info);
                        else
                            return JsonService::status('pay_error', StoreOrder::getErrorInfo());
                    } else {
                        RoutineFormId::SetFormId($formId, $this->uid);
                        try {
                            $jsConfig = StoreOrder::jsPay($orderId); //创建订单jspay
                            if(isset($jsConfig['package']) && $jsConfig['package']){
                                $package=str_replace('prepay_id=','',$jsConfig['package']);
                                for($i=0;$i<3;$i++){
                                    RoutineFormId::SetFormId($package, $this->uid);
                                }
                            }
                        } catch (\Exception $e) {
                            return JsonService::status('pay_error', $e->getMessage(), $info);
                        }
                        $info['jsConfig'] = $jsConfig;
                        return JsonService::status('wechat_pay', '订单创建成功', $info);
                    }
                    break;
                case 'yue':
                    if (StoreOrder::yuePay($orderId, $this->userInfo['uid'], $formId))
                        return JsonService::status('success', '余额支付成功', $info);
                    else {
                        $errorinfo = StoreOrder::getErrorInfo();
                        if (is_array($errorinfo))
                            return JsonService::status($errorinfo['status'], $errorinfo['msg'], $info);
                        else
                            return JsonService::status('pay_error', $errorinfo);
                    }
                    break;
                case 'offline':
                    RoutineFormId::SetFormId($formId, $this->uid);
                    //                RoutineTemplate::sendOrderSuccess($formId,$orderId);//发送模板消息
                    return JsonService::status('success', '订单创建成功', $info);
                    break;
            }
        } else return JsonService::fail(StoreOrder::getErrorInfo('订单生成失败!'));
    }

    /**
     * 创建订单
     * @param string $key
     * @return \think\response\Json
     */
    public function create_order_new()
    {
        list($midkey,$addressId, $couponId, $useIntegral, $mark, $combinationId, $pinkId, $seckill_id, $formId, $bargainId,$is_zg,$score_num) = UtilService::postMore([
             'midkey','addressId', 'couponId', 'useIntegral', 'mark', ['combinationId', 0], ['pinkId', 0], ['seckill_id', 0], ['formId', ''], ['bargainId', ''], ['is_zg', '0'], ['score_num', '0']
        ], Request::instance(), true);
        if (!$midkey) return JsonService::fail('参数错误!');

        $iv = "1234567890123412";//16位 向量
        $key= '201707eggplant99';//16位 默认秘钥
        $midkey=trim(openssl_decrypt(base64_decode($midkey),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv));

        if (StoreOrder::be(['order_id|unique' => $midkey, 'uid' => $this->userInfo['uid'], 'is_del' => 0]))
            return JsonService::status('extend_order', '该订单已生成', ['orderId' => $midkey, 'key' => $midkey]);
        
        /**当前屏蔽砍价功能，所以这里用不到，所以不做事务考虑**/
        if ($bargainId) StoreBargainUser::setBargainUserStatus($bargainId, $this->userInfo['uid']); //修改砍价状态
        /**当前屏蔽砍价功能，所以这里用不到，所以不做事务考虑**/


        if ($pinkId) if (StorePink::getIsPinkUid($pinkId, $this->userInfo['uid'])) return JsonService::status('ORDER_EXIST', '订单生成失败，你已经在该团内不能再参加了', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $this->userInfo['uid'])]);
        if ($pinkId) if (StoreOrder::getIsOrderPink($pinkId, $this->userInfo['uid'])) return JsonService::status('ORDER_EXIST', '订单生成失败，你已经参加该团了，请先支付订单', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $this->userInfo['uid'])]);
        $order = StoreOrder::cacheKeyCreateOrderNew($this->userInfo['uid'], $midkey, $addressId, $useIntegral, $couponId, $mark, $combinationId, $pinkId, $seckill_id, $bargainId,$is_zg,$score_num);
        $orderId = $order['order_id'];
        $price= $order['pay_price'];
        $user_token=db('website_connect_token')->where('uid',$this->userInfo['uid'])->value('user_token');
        $info = compact('orderId', 'midkey','price','user_token');
        if ($orderId) {
            RoutineFormId::SetFormId($formId, $this->uid);
            //                RoutineTemplate::sendOrderSuccess($formId,$orderId);//发送模板消息
            return JsonService::status('success', '订单创建成功', $info);
        } else return JsonService::fail(StoreOrder::getErrorInfo('订单生成失败!'));
    }

    /**
     * 创建智果订单
     * @param string $key
     * @return \think\response\Json
     */
    public function create_zg_order()
    {
        list($midkey,$addressId, $couponId, $useIntegral, $mark, $combinationId, $pinkId, $seckill_id, $formId, $bargainId,$is_zg) = UtilService::postMore([
             'midkey','addressId', 'couponId', 'useIntegral', 'mark', ['combinationId', 0], ['pinkId', 0], ['seckill_id', 0], ['formId', ''], ['bargainId', ''], ['is_zg', '1']
        ], Request::instance(), true);
        if (!$midkey) return JsonService::fail('参数错误!');
        if (StoreOrder::be(['order_id|unique' => $midkey, 'uid' => $this->userInfo['uid'], 'is_del' => 0]))
            return JsonService::status('extend_order', '该订单已生成', ['orderId' => $midkey, 'key' => $midkey]);
        if ($bargainId) StoreBargainUser::setBargainUserStatus($bargainId, $this->userInfo['uid']); //修改砍价状态
        if ($pinkId) if (StorePink::getIsPinkUid($pinkId, $this->userInfo['uid'])) return JsonService::status('ORDER_EXIST', '订单生成失败，你已经在该团内不能再参加了', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $this->userInfo['uid'])]);
        if ($pinkId) if (StoreOrder::getIsOrderPink($pinkId, $this->userInfo['uid'])) return JsonService::status('ORDER_EXIST', '订单生成失败，你已经参加该团了，请先支付订单', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $this->userInfo['uid'])]);

        $order = StoreOrder::ZgCreateOrderNew($this->userInfo['uid'], $midkey, $addressId, $useIntegral, $couponId, $mark, $combinationId, $pinkId, $seckill_id, $bargainId,$is_zg);

        // return JsonService::fail(StoreOrder::getErrorInfo('********!'));
        $orderId = $order['order_id'];
        $info = compact('orderId', 'midkey');
        if ($orderId) {
            RoutineFormId::SetFormId($formId, $this->uid);
            //                RoutineTemplate::sendOrderSuccess($formId,$orderId);//发送模板消息
            return JsonService::status('success', '订单创建成功', $info);
        } else return JsonService::fail(StoreOrder::getErrorInfo('订单生成失败!'));
    }

    public function notify()
    {
        WechatService::handleNotify();
    }

    /**
     * 判断订单是否超时
     */
    public function time_out(){
        $uni=osx_input('uni','','text');
        if (!$uni) return JsonService::fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($this->userInfo['uid'], $uni);
        if (!$order) return JsonService::fail('订单不存在!');
        $time=time()-86400;
        if($order['paid']==0 && $order['add_time']<$time){
            StoreOrder::cancelOrder($order['order_id']);
            $data=0;
            JsonService::fail($data);
        }else{
            $data=1;
            return JsonService::successful($data);
        }
    }

    public function pay_order_new()
    {
        $uni=osx_input('uni','','text');
        $paytype=osx_input('paytype','weixin','text');
        $bill_type=osx_input('bill_type','pay_product','text');
        if (!$uni) return JsonService::fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($this->userInfo['uid'], $uni);
        if (!$order) return JsonService::fail('订单不存在!');
        if ($order['paid']) return JsonService::fail('该订单已支付!');
        if ($order['pink_id']) if (StorePink::isPinkStatus($order['pink_id'])) return JsonService::fail('该订单已失效!');
        if($order['pay_price']==0&&$order['is_zg']==1){
            $res = StoreOrder::yuePay($order['order_id'], $this->userInfo['uid'],'',$bill_type);
            if ($res){
                return JsonService::successful('购买成功');
            } else {
                return JsonService::fail('购买失败');
            }
        }else{
            $order['pay_type'] = $paytype; //重新支付选择支付方式
            switch ($order['pay_type']) {
                case 'weixin':
                    $status=db('pay_set')->where('type','weixin')->value('status');
                    if($status==0){
                        return JsonService::fail('该支付未开启!');
                    }
                    try {
                        $jsConfig = StoreOrder::jsPay($order); //订单列表发起支付
                        if(isset($jsConfig['package']) && $jsConfig['package']){
                            $jsConfig['package']=str_replace('prepay_id=','',$jsConfig['package']);
                            for($i=0;$i<3;$i++){
                                RoutineFormId::SetFormId($jsConfig['package'], $this->uid);
                            }
                        }
                        $jsConfig['package']='prepay_id='.$jsConfig['package'];
                        StoreOrder::where('id', $order['id'])->update(['pay_type'=>'weixin']);
                    } catch (\Exception $e) {
                        return JsonService::fail($e->getMessage());
                    }
                    return JsonService::status('wechat_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                    break;
                case 'routine':
                    $status=db('pay_set')->where('type','weixin')->value('status');
                    if($status==0){
                        return JsonService::fail('该支付未开启!');
                    }
                    try {
                        $jsConfig = StoreOrder::MiniProgramJsPay($order); //订单列表发起支付
                        if(isset($jsConfig['package']) && $jsConfig['package']){
                            $jsConfig['package']=str_replace('prepay_id=','',$jsConfig['package']);
                            for($i=0;$i<3;$i++){
                                RoutineFormId::SetFormId($jsConfig['package'], $this->uid);
                            }
                        }
                        $jsConfig['package']='prepay_id='.$jsConfig['package'];
                        StoreOrder::where('id', $order['id'])->update(['pay_type'=>'routine']);
                    } catch (\Exception $e) {
                        return JsonService::fail($e->getMessage());
                    }
                    return JsonService::status('wechat_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                    break;
                case 'weixin_app':
                    $status=db('pay_set')->where('type','weixin')->value('status');
                    if($status==0){
                        return JsonService::fail('该支付未开启!');
                    }
                    try {
                        $appConfig = StoreOrder::wechatAppPay($order); //订单列表发起支付
                        for($i=0;$i<3;$i++){
                            RoutineFormId::SetFormId($appConfig['prepayid'], $this->uid);//多个地方用到表单令牌
                        }
                        StoreOrder::where('id', $order['id'])->update(['pay_type'=>'weixin_app']);
                    } catch (\Exception $e) {
                        return JsonService::fail($e->getMessage());
                    }
                    return JsonService::status('wechat_app_pay', ['appConfig' => $appConfig, 'order_id' => $order['order_id']]);
                    break;
                case 'yue':
                    $status=db('pay_set')->where('type','yue')->value('status');
                    if($status==0){
                        return JsonService::fail('该支付未开启!');
                    }
                    if ($res = StoreOrder::yuePay($order['order_id'], $this->userInfo['uid'],'',$bill_type))
                        return JsonService::successful('余额支付成功');
                    else {
                        $error = StoreOrder::getErrorInfo();
                        return JsonService::fail(is_array($error) && isset($error['msg']) ? $error['msg'] : $error);
                    }
                    break;
            }
        }


    }

    //TODO 支付订单
    /**
     * 支付订单
     * @return \think\response\Json
     */
    public function pay_order()
    {
        $uni=osx_input('uni','','text');
        $paytype=osx_input('paytype','weixin','text');
        if (!$uni) return JsonService::fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($this->userInfo['uid'], $uni);
        if (!$order) return JsonService::fail('订单不存在!');
        if ($order['paid']) return JsonService::fail('该订单已支付!');
        if ($order['pink_id']) if (StorePink::isPinkStatus($order['pink_id'])) return JsonService::fail('该订单已失效!');
        $order['pay_type'] = $paytype; //重新支付选择支付方式
        switch ($order['pay_type']) {
            case 'weixin':
                try {
                    $jsConfig = StoreOrder::jsPay($order); //订单列表发起支付
                    if(isset($jsConfig['package']) && $jsConfig['package']){
                        $jsConfig['package']=str_replace('prepay_id=','',$jsConfig['package']);
                        for($i=0;$i<3;$i++){
                            RoutineFormId::SetFormId($jsConfig['package'], $this->uid);
                        }
                    }
                } catch (\Exception $e) {
                    return JsonService::fail($e->getMessage());
                }
                return JsonService::status('wechat_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                break;
            case 'yue':
                if ($res = StoreOrder::yuePay($order['order_id'], $this->userInfo['uid']))
                    return JsonService::successful('余额支付成功');
                else {
                    $error = StoreOrder::getErrorInfo();
                    return JsonService::fail(is_array($error) && isset($error['msg']) ? $error['msg'] : $error);
                }
                break;
            case 'offline':
                StoreOrder::createOrderTemplate($order);
                return JsonService::successful('订单创建成功');
                break;
        }
    }

    /*
     * 未支付的订单取消订单回退积分,回退优惠券,回退库存
     * @param string $order_id 订单id
     * */
    public function cancel_order()
    {
        $order_id=osx_input('order_id','','text');
        if (StoreOrder::cancelOrder($order_id))
            return JsonService::successful('取消订单成功');
        else
            return JsonService::fail(StoreOrder::getErrorInfo());
    }

    /**
     * 申请退款
     * @param string $uni
     * @param string $text
     * @return \think\response\Json
     */
    public function apply_order_refund(Request $request)
    {
        $data = UtilService::postMore([
            ['text', ''],
            ['refund_reason_wap_img', ''],
            ['refund_reason_wap_explain', ''],
            ['uni', '']
        ], $request);
        $uni = $data['uni'];
        unset($data['uni']);
        if ($data['refund_reason_wap_img']) $data['refund_reason_wap_img'] = explode(',', $data['refund_reason_wap_img']);
        if (!$uni || $data['text'] == '') return JsonService::fail('参数错误!');
        $res = StoreOrder::orderApplyRefund($uni, $this->userInfo['uid'], $data['text'], $data['refund_reason_wap_explain'], $data['refund_reason_wap_img']);
        if ($res){
            $order=StoreOrder::where('order_id',$uni)->find();
            WechatTemplateService::sendTemplate(WechatUser::uidToOpenid($order['uid']),WechatTemplateService::REFUND, [
                'first'=>'亲，您的订单已申请退款',
                'keyword1'=>$order['pay_price'],
                'keyword2'=>'3-7个工作日内',
                'remark'=>'可以去商城查看订单详情'
            ],'');
            return JsonService::successful();
        }
        else{
            return JsonService::fail(StoreOrder::getErrorInfo());
        }

    }


    /**
     * 再来一单
     * @param string $uni
     */
    public function order_details()
    {
        $uni=osx_input('uni','','text');
        if (!$uni) return JsonService::fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($this->userInfo['uid'], $uni);
        if (!$order) return JsonService::fail('订单不存在!');
        $order = StoreOrder::tidyOrder($order, true);
        $res = array();
        foreach ($order['cartInfo'] as $v) {
            if ($v['combination_id']) return JsonService::fail('拼团产品不能再来一单，请在拼团产品内自行下单!');
            else  $res[] = StoreCart::setCart($this->userInfo['uid'], $v['product_id'], $v['cart_num'], isset($v['productInfo']['attrInfo']['unique']) ? $v['productInfo']['attrInfo']['unique'] : '', 'product', 0, 0);
        }
        $cateId = [];
        foreach ($res as $v) {
            if (!$v->result) return JsonService::fail('再来一单失败，请重新下单!');
            $cateId[] = $v['id'];
        }
        return JsonService::successful('ok', implode(',', $cateId));
    }
    /**
     * 购物车库存修改
     */
    public function set_buy_cart_num()
    {
        $cartId=osx_input('cartId',0,'intval');
        $cartNum=osx_input('cartNum',0,'intval');
        if (!$cartId) return JsonService::fail('参数错误');
        $res = StoreCart::edit(['cart_num' => $cartNum], $cartId);
        if ($res) return JsonService::successful();
        else return JsonService::fail('修改失败');
    }

    const ReqURL = "http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx";


    public function getMessage(){
        $ShipperCode=osx_input('ShipperCode','');//快递公司编号
        $order_sn=osx_input('order_sn','');//运单号
        $requestData= "{'OrderCode':'','ShipperCode':'".$ShipperCode."','LogisticCode':'".$order_sn."'}";
        $config = SystemConfig::getMore('kdn_id,kdn_my');
        $datas = array(
            'EBusinessID' => $config['kdn_id'],
            'RequestType' => '1002',//接口指令1002，固定
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2', //数据返回格式 2 json
        );
        //把$requestData进行加密处理
        $datas['DataSign'] = $this -> encrypt($requestData,$config['kdn_my']);
        $result = $this -> sendPost( self::ReqURL, $datas);
        if(!is_array($result)){
            $result=json_decode($result,true);
        }
        return JsonService::successful($result);
    }

    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    private function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(empty($url_info['port']))
        {
            $url_info['port']=80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    /*
     * 进行加密
     */
    private function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }

    /**
     * //TODO 第三方支付
     * @param $orderId
     * @param $notify
     * @return bool
     */
    public function website_paySuccess()
    {
        $appKey=osx_input('post.appKey','');
        $timestamp=osx_input('post.endtimestamp','');
        $user_token=osx_input('post.user_token','');
        $sign=osx_input('post.sign','');
        $order_id=osx_input('post.order_id','');
        $status=osx_input('post.status','');
        $price=osx_input('post.price','');
        $type=osx_input('post.type','');
        $args=[
            'endtimestamp' => $timestamp,
            'user_token' => $user_token,
            'appKey' =>$appKey,
            'order_id' =>$order_id,
            'status' =>$status,
            'price' =>$price,
            'type' =>$type,
        ];
        self::_checkSign($args,$sign);

        if($status==1){
            switch($type){
                case 1:
                    $type='score';
                    break;
                case 2:
                    $type='zfb';
                    break;
                case 3:
                    $type='yhk';
                    break;
                case 4:
                    $type='yue';
                    break;
                case 5:
                    $type='score_zfb';
                    break;
                case 6:
                    $type='score_yue';
                    break;
                case 7:
                    $type='score_yhk';
                    break;
            }
            $order = StoreOrder::where('order_id',$order_id)->find();
            if($order['paid']==1){
                self::apiError('该订单已支付');
            }
            if($order['is_del']==1){
                self::apiError('该订单已关闭');
            }
            $resPink = true;
            User::bcInc($order['uid'],'pay_count',1,'uid');
            $res1 = StoreOrder::where('order_id',$order_id)->update(['paid'=>1,'pay_time'=>time(),'pay_price'=>$price,'pay_type'=>$type]);
            if($order->combination_id && $res1 && !$order->refund_status) $resPink = StorePink::createPink($order);//创建拼团
            $oid = StoreOrder::where('order_id',$order_id)->value('id');
            StoreOrderStatus::status($oid,'pay_success','用户付款成功');
            WechatTemplateService::sendTemplate(WechatUser::uidToOpenid($order['uid']),WechatTemplateService::ORDER_PAY_SUCCESS, [
                'first'=>'亲，您购买的商品已支付成功',
                'keyword1'=>$order_id,
                'keyword2'=>date("Y-m-d H:i:s",$order['add_time']),
                'keyword3'=>$order['real_name'],
                'keyword4'=>$order['user_address'],
                'keyword5'=>$order['pay_price'],
                'remark'=>'可以去商城查看订单详情'
            ],'');
            $res = $res1 && $resPink;
            self::apiSuccess($res);
        }else{
            self::apiError('未支付成功');
        }
    }

    private function _checkSign($args,$sign)
    {
        $website_connect=SystemConfig::getMore('website_connect_app_key,website_connect_app_secret');
        $default_appKey=$website_connect['website_connect_app_key'];
        $default_appSecret=$website_connect['website_connect_app_secret'];

        $args['appSecret'] = $default_appSecret;

        $endtimestamp=intval($args['endtimestamp']);
        if($endtimestamp<time()){
            $this->apiError('请求超时');//请求超时，不做处理
        }
        if($args['appKey']!=$default_appKey){
            $this->apiError('参数错误，秘钥key不正确');//参数错误，秘钥key不正确
        }
        ksort($args);//$Map按 键 升序排列
        $before_md5_sign=implode('',$args);//拼接$Map为字符串
        $after_md5_sign=md5($before_md5_sign);//MD5加密
        if($after_md5_sign!=$sign){
            $this->apiError('签名验证不通过');//签名验证不通过
        }
        return true;
    }

}
