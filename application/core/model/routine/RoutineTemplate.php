<?php
namespace  app\core\model\routine;

use app\core\util\Template;
use app\ebapi\model\store\StoreOrder;
use app\ebapi\model\user\WechatUser;


/**
 * 小程序模板消息
 * Class RoutineTemplate
 * @package app\routine\model\routine
 */
class RoutineTemplate
{
    /**
     * 退款成功发送消息
     * @param array $order
     */
    public static function sendOrderRefundSuccess($order = array()){

        $data['keyword1'] =  $order['order_id'];
        $data['keyword2'] =  date('Y-m-d H:i:s',time());
        $data['keyword3'] =  $order['pay_price'];
        if($order['pay_type'] == 'yue') $data['keyword4'] =  '余额支付';
        else if($order['pay_type'] == 'weixin') $data['keyword4'] =  '微信支付';
        else if($order['pay_type'] == 'offline') $data['keyword4'] =  '线下支付';
        $data['keyword5']['value'] = '已成功退款';
        return self::sendOut('ORDER_REFUND_SUCCESS',$order['uid'],$data);
    }
    /**
     * 用户申请退款给管理员发送消息
     * @param array $order
     * @param string $refundReasonWap
     * @param array $adminList
     */
    public static function sendOrderRefundStatus($order = array(),$refundReasonWap = '',$adminList = array()){
        $data['keyword1'] =  $order['order_id'];
        $data['keyword2'] =  $refundReasonWap;
        $data['keyword3'] =  date('Y-m-d H:i:s',time());
        $data['keyword4'] =  $order['pay_price'];
        $data['keyword5'] =  '原路返回';
        foreach ($adminList as $uid){
            self::sendOut('ORDER_REFUND_STATUS',$uid,$data);
        }
    }
    /**
     * 砍价成功通知
     * @param array $bargain
     * @param array $bargainUser
     * @param int $bargainUserId
     */
    public static function sendBargainSuccess($bargain = array(),$bargainUser  = array(),$bargainUserId = 0){
        $data['keyword1'] =  $bargain['title'];
        $data['keyword2'] =  $bargainUser['bargain_price'];
        $data['keyword3'] =  $bargainUser['bargain_price_min'];
        $data['keyword4'] =  $bargainUser['price'];
        $data['keyword5'] =  $bargainUser['bargain_price_min'];
        $data['keyword6'] =  '恭喜您，已经砍到最低价了';
        return self::sendOut('BARGAIN_SUCCESS',$bargainUser['uid'],$data);
    }
    /**
     * 订单支付成功发送模板消息
     * @param string $formId
     * @param string $orderId
     */
    public static function sendOrderSuccess($formId = '',$orderId = ''){
        if($orderId == '') return ;
        $order = StoreOrder::where('order_id',$orderId)->find();
        $data['keyword1'] =  $orderId;
        $data['keyword2'] =  date('Y-m-d H:i:s',time());
        $data['keyword3'] =  '已支付';
        $data['keyword4'] =  $order['pay_price'];
        if($order['pay_type'] == 'yue') $data['keyword5'] =  '余额支付';
        else if($order['pay_type'] == 'weixin') $data['keyword5'] =  '微信支付';
        return self::sendOut('ORDER_PAY_SUCCESS',$order['uid'],$data,$formId,'/pages/order_details/index?order_id='.$orderId);
    }

    /*
     * 发送模板消息
     * @param string  $TempCode 模板消息常量名称
     * @param int $uid 用户uid
     * @param array $data 模板内容
     * @param string $formId formId
     * @param string $link 跳转链接
     * */
    public static function sendOut($TempCode,$uid=null,$data=null,$formId = '',$link='')
    {
        try{
            $openid=WechatUser::getOpenId($uid);
            if(!$formId){
                $form= RoutineFormId::getFormIdOne($uid,true);
                RoutineFormId::del($form['id']);
            }
            return Template::instance()->routine_two->sendTemplate($TempCode,$openid,$data,$form['form_id'],$link);
        }catch (\Exception $e){
            return false;
        }
    }
}