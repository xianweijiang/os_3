<?php
namespace app\shopapi\controller;

use app\core\model\routine\RoutineFormId;//待完善
use app\core\model\UserLevel;
use app\shopapi\model\shop\ShopProduct;
use service\JsonService;
use app\core\util\SystemConfigService;
use service\UtilService;
use think\Request;
use app\osapi\model\common\Support;
use app\core\behavior\GoodsBehavior;//待完善
use app\shopapi\model\shop\ShopOrder;
use app\shopapi\model\user\User;
use app\admin\model\system\SystemConfig;
use service\WechatTemplateService;
use app\shopapi\model\user\WechatUser;
use app\core\util\WechatService;

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
        ];
    }

    /*
     * 获取订单支付状态
     * @param string ordre_id 订单id
     * @return json
     * */
    public function get_order_pay_info()
    {
        $order_id=osx_input('order_id','');
        if ($order_id == '') return JsonService::fail('缺少参数');
        return JsonService::successful(ShopOrder::tidyOrder(ShopOrder::where('order_id', $order_id)->find()));
    }
    /**
     * 订单页面
     * @param Request $request
     * @return \think\response\Json
     */
    public function confirm_order()
    {
        list($productId,$totalNum,,) = UtilService::postMore([
            'productId','totalNum',
        ], Request::instance(), true);
        $data['productInfo']=ShopProduct::where('id',$productId)->find();
        $data['totalNum']=$totalNum;
        $data['userInfo']=$this->userInfo;
        return JsonService::successful($data);
    }


    /**
     * 创建订单
     * @param string $key
     * @return \think\response\Json
     */
    public function create_order_new()
    {
        list($productId,$addressId, $totalNum, $mark,) = UtilService::postMore([
             'productId','addressId', 'totalNum', 'mark',
        ], Request::instance(), true);
        if (!$productId) return JsonService::fail('参数错误!');
        $productInfo=ShopProduct::where('id',$productId)->find();
        $totalNum=1;
        $order = ShopOrder::cacheKeyCreateOrderNew($this->userInfo['uid'], $productInfo,$totalNum ,$addressId,$mark);
        $orderId = $order['order_id'];
        if ($orderId) {
            return JsonService::status('success', '订单创建成功', $orderId);
        }else{
            return JsonService::fail(ShopOrder::getErrorInfo('订单生成失败!'));
        }
    }


    public function notify()
    {
        WechatService::handleNotify();
    }


    public function pay_order_new()
    {
        $uni=osx_input('uni','');
        $paytype=osx_input('paytype','weixin');
        $bill_type=osx_input('bill_type','pay_product');
        if (!$uni) return JsonService::fail('参数错误!');
        $order = ShopOrder::getUserOrderDetail($this->userInfo['uid'], $uni);
        if (!$order) return JsonService::fail('订单不存在!');
        if ($order['paid']) return JsonService::fail('该订单已支付!');
        $order['pay_type'] = $paytype;//重新支付选择支付方式
        if($order['pay_type']=='score' && $order['pay_price']==0){
            try {
                $score_type=db('shop_score_type')->where('id',1)->value('flag');
                $user_score=User::where('uid',$order['uid'])->value($score_type);
                $nums=$user_score-$order['pay_score'];
                if($nums<0){
                    return JsonService::fail('积分不足，购买失败');
                }
                $res1=User::where('uid',$order['uid'])->update([$score_type=>$nums]);
                if($res1==false){
                    return JsonService::fail('积分扣除失败');
                }
                $log=[];
                $log[$score_type]=$order['pay_score'];
                Support::jiafenlog($order['uid'],'积分商城购买物品',$log,0,'行为');
                $res2 = ShopOrder::where('order_id',$uni)->update(['paid'=>1,'pay_type'=>$paytype,'status'=>1,'pay_time'=>time()]);//订单改为支付
                if($res2==false){
                    return JsonService::fail('订单支付失败');
                }
            } catch (\Exception $e) {
                return JsonService::fail($e->getMessage());
            }
            return JsonService::successful('购买成功');
        }else{
            switch ($order['pay_type']) {
                case 'weixin':
                    $status=db('pay_set')->where('type','weixin')->value('status');
                    if($status==0){
                        return JsonService::fail('该支付未开启!');
                    }
                    try {
                        $jsConfig = ShopOrder::jsPay($order); //订单列表发起支付
                        if(isset($jsConfig['package']) && $jsConfig['package']){
                            $jsConfig['package']=str_replace('prepay_id=','',$jsConfig['package']);
                            for($i=0;$i<3;$i++){
                                RoutineFormId::SetFormId($jsConfig['package'], $this->uid);
                            }
                        }
                        $jsConfig['package']='prepay_id='.$jsConfig['package'];
                        ShopOrder::where('id', $order['id'])->update(['pay_type'=>'weixin']);
                    } catch (\Exception $e) {
                        return JsonService::fail($e->getMessage());
                    }
                    return JsonService::status('wechat_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                    break;
                case 'yue':
                    $status=db('pay_set')->where('type','yue')->value('status');
                    if($status==0){
                        return JsonService::fail('该支付未开启!');
                    }
                    if ($res = ShopOrder::yuePay($order['order_id'], $this->userInfo['uid'],'',$bill_type))
                        return JsonService::successful('余额支付成功');
                    else {
                        $error = ShopOrder::getErrorInfo();
                        return JsonService::fail(is_array($error) && isset($error['msg']) ? $error['msg'] : $error);
                    }
                    break;
            }
        }
    }

    const ReqURL = "http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx";

    /**
     * @param $ShipperCode 快递公司编号
     * @param $order_sn 运单号
     */
    public function getMessage(){
        $ShipperCode=osx_input('ShipperCode','');
        $order_sn=osx_input('order_sn','');
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

}
