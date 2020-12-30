<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */
namespace app\admin\controller\shop;

use Api\Express;
use app\admin\controller\AuthController;
use app\admin\model\shop\ShopProduct;
use service\FormBuilder as Form;
use app\admin\model\shop\ShopOrderStatus;
use app\admin\model\ump\StorePink;
use app\admin\model\user\User;
use app\admin\model\user\UserBill;
use basic\ModelBasic;
use behavior\admin\OrderBehavior;
use behavior\wechat\PaymentBehavior;
use EasyWeChat\Core\Exception;
use service\CacheService;
use service\HookService;
use service\JsonService;
use service\SystemConfigService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Db;
use think\Request;
use think\Url;
use app\osapi\model\common\Support;
use app\wap\model\user\WechatUser;
use service\WechatTemplateService;
use app\admin\model\shop\ShopOrder as ShopOrderModel;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;
use app\admin\model\system\SystemConfig;
/**
 * 订单管理控制器 同一个订单表放在一个控制器
 * Class StoreOrder
 * @package app\admin\controller\store
 */
class ShopOrder extends AuthController
{
    /**
     * @return mixed
     */
    public function index()
    {
        $config = SystemConfigService::more(['pay_routine_appid','pay_routine_appsecret','pay_routine_mchid','pay_routine_key','pay_routine_client_cert','pay_routine_client_key']);
        $this->assign([
            'year'=>getMonth('y'),
            'real_name'=>$this->request->get('real_name',''),
            'orderCount'=>ShopOrderModel::orderCount(),
        ]);
        return $this->fetch();
    }
    /**
     * 获取头部订单金额等信息
     * return json
     *
     */
    public function getBadge(){
        $where = Util::postMore([
            ['status',''],
            ['real_name',''],
            ['data',''],
            ['order',''],
        ]);
        return JsonService::successful(ShopOrderModel::getBadge($where));
    }
    /**
     * 获取订单列表
     * return json
     */
    public function order_list(){
        $where = Util::getMore([
            ['status',''],
            ['real_name',$this->request->param('real_name','')],
            ['data',''],
            ['order',''],
            ['page',1],
            ['limit',20],
            ['excel',0],
        ]);
        return JsonService::successlayui(ShopOrderModel::OrderList($where));
    }
    public function orderchart(){
        $where = Util::getMore([
            ['status',''],
            ['real_name',''],
            ['is_del',0],
            ['data',''],
            ['combination_id',''],
            ['export',0],
            ['order','id desc']
        ],$this->request);
        $limitTimeList = [
            'today'=>implode(' - ',[date('Y/m/d'),date('Y/m/d',strtotime('+1 day'))]),
            'week'=>implode(' - ',[
                date('Y/m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),
                date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600))
            ]),
            'month'=>implode(' - ',[date('Y/m').'/01',date('Y/m').'/'.date('t')]),
            'quarter'=>implode(' - ',[
                date('Y').'/'.(ceil((date('n'))/3)*3-3+1).'/01',
                date('Y').'/'.(ceil((date('n'))/3)*3).'/'.date('t',mktime(0,0,0,(ceil((date('n'))/3)*3),1,date('Y')))
            ]),
            'year'=>implode(' - ',[
                date('Y').'/01/01',date('Y/m/d',strtotime(date('Y').'/01/01 + 1year -1 day'))
            ])
        ];
        if($where['data'] == '') $where['data'] = $limitTimeList['today'];
        $orderCount = [
            urlencode('未支付')=>ShopOrderModel::getOrderWhere($where,ShopOrderModel::statusByWhere(0))->count(),
            urlencode('未发货')=>ShopOrderModel::getOrderWhere($where,ShopOrderModel::statusByWhere(1))->count(),
            urlencode('待收货')=>ShopOrderModel::getOrderWhere($where,ShopOrderModel::statusByWhere(2))->count(),
            urlencode('待评价')=>ShopOrderModel::getOrderWhere($where,ShopOrderModel::statusByWhere(3))->count(),
            urlencode('交易完成')=>ShopOrderModel::getOrderWhere($where,ShopOrderModel::statusByWhere(4))->count(),
            urlencode('退款中')=>ShopOrderModel::getOrderWhere($where,ShopOrderModel::statusByWhere(-1))->count(),
            urlencode('已退款')=>ShopOrderModel::getOrderWhere($where,ShopOrderModel::statusByWhere(-2))->count()
        ];
        $model = ShopOrderModel::getOrderWhere($where,new ShopOrderModel())->field('sum(total_num) total_num,count(*) count,sum(total_price) total_price,sum(refund_price) refund_price,from_unixtime(add_time,\'%Y-%m-%d\') add_time')
            ->group('from_unixtime(add_time,\'%Y-%m-%d\')');
        $orderPrice = $model->select()->toArray();
        $orderDays = [];
        $orderCategory = [
            ['name'=>'商品数','type'=>'line','data'=>[]],
            ['name'=>'订单数','type'=>'line','data'=>[]],
            ['name'=>'订单金额','type'=>'line','data'=>[]],
            ['name'=>'退款金额','type'=>'line','data'=>[]]
        ];
        foreach ($orderPrice as $price){
            $orderDays[] = $price['add_time'];
            $orderCategory[0]['data'][] = $price['total_num'];
            $orderCategory[1]['data'][] = $price['count'];
            $orderCategory[2]['data'][] = $price['total_price'];
            $orderCategory[3]['data'][] = $price['refund_price'];
        }
        $this->assign(ShopOrderModel::systemPage($where,$this->adminId));
        $this->assign('price',ShopOrderModel::getOrderPrice($where));
        $this->assign(compact('limitTimeList','where','orderCount','orderPrice','orderDays','orderCategory'));
        return $this->fetch();
    }

    /**
     * 发货
     * @param $id
     *  express
     */
    public function deliver_goods($id){
        if(!$id) return $this->failed('数据不存在');
        $product = ShopOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['paid'] == 1 && $product['status'] == 1){
            $f = array();
            $f[] = Form::radio('no_delivery','无需物流',0)->options([['label'=>'否','value'=>0],['label'  => '是','value'=>1]]);
            $f[] = Form::select('delivery_name','快递公司')->setOptions(function(){
                        $list =  Db::name('express')->where('is_show',1)->order('sort DESC')->column('id,name');
                        $menus = [];
                        foreach ($list as $k=>$v){
                            $menus[] = ['value'=>$v,'label'=>$v];
                        }
                        return $menus;
                    })->filterable(1);
            $f[] = Form::input('delivery_id','快递单号');
            $form = Form::make_post_form('修改订单',$f,Url::build('updateDeliveryGoods',array('id'=>$id)),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        } else {
            return $this->failedNotice('订单状态错误');
        }
    }

    /**发货保存
     * @param Request $request
     * @param $id
     */
    public function updateDeliveryGoods(Request $request, $id){
        $data = Util::postMore([
            'delivery_name',
            'delivery_id',
            'no_delivery',
        ],$request);
        $data['delivery_type'] = 'express';
        if($data['no_delivery']==0){
            if(!$data['delivery_name']) return Json::fail('请选择快递公司');
            if(!$data['delivery_id']) return Json::fail('请输入快递单号');
        }
        $data['status'] = 2;
        ShopOrderModel::edit($data,$id);
        $order = ShopOrderModel::where('id',$id)->find();
        ShopOrderStatus::setStatus($id,'delivery_goods','已发货 快递公司：'.$data['delivery_name'].' 快递单号：'.$data['delivery_id']);
        $set=MessageTemplate::getMessageSet(20);
        if($set['status']==1){
            $message_id=Message::sendMessage($order['uid'],0,$set['template'],1,$set['title'],1,'','shop_order',$order['order_id']);
            $read_id=MessageRead::createMessageRead($order['uid'],$message_id,$set['popup'],1);
        }
        if($set['sms']==1&&$set['status']==1){
            $account=User::where('uid',$order['uid'])->value('phone');
            $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
            $set['template']='【'.$config['cl_sms_sign'].'】'.$set['template'];
            $sms=ChuanglanSmsApi::sendSMS($account,$set['template']); //发送短信
            $sms=json_decode($sms,true);
            if ($sms['code']==0) {
                $read_data['is_sms']=1;
                $read_data['sms_time']=time();
                MessageRead::where('id',$read_id)->update($read_data);
            }
        }
        WechatTemplateService::sendTemplate(WechatUser::uidToOpenid($order['uid']),WechatTemplateService::ORDER_POSTAGE_SUCCESS, [
            'first'=>'亲，您购买的商品已发货',
            'keyword1'=>$order['real_name'],
            'keyword2'=>$order['user_phone'],
            'keyword3'=>$order['delivery_name'],
            'keyword4'=>$order['delivery_id'],
            'keyword5'=>$order['order_id'],
            'remark'=>'可以去商城查看订单详情'
        ],'');
        return Json::successful('修改成功!');
    }
    /**
     * 修改状态为已收货
     * @param $id
     * @return \think\response\Json|void
     */
    public function take_delivery($id){
        if(!$id) return $this->failed('数据不存在');
        $order = ShopOrderModel::get($id);
        if(!$order) return Json::fail('数据不存在!');
        if($order['status'] == 3) return Json::fail('不能重复收货!');
        if($order['paid'] == 1 && $order['status'] == 2) $data['status'] = 3;
        else return Json::fail('请先发货或者送货!');
        if(!ShopOrderModel::edit($data,$id))
            return Json::fail(ShopOrderModel::getErrorInfo('收货失败,请稍候再试!'));
        else{
            ShopOrderStatus::setStatus($id,'take_delivery','已收货');
            return Json::successful('收货成功!');
        }
    }
    /**
     * 修改退款状态
     * @param $id
     * @return \think\response\Json|void
     */
    public function refund_y($id){
        if(!$id) return $this->failed('数据不存在');
        $product = ShopOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['paid'] == 1){
            $f = array();
            $f[] = Form::input('order_id','退款单号',$product->getData('order_id'))->disabled(1);
            $f[] = Form::number('refund_price','退款金额',$product->getData('pay_price'))->precision(2)->min(0.01);
            $f[] = Form::number('refund_score','退款积分',$product->getData('pay_score'));
            $form = Form::make_post_form('退款处理',$f,Url::build('updateRefundY',array('id'=>$id)),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }
        else return Json::fail('数据不存在!');
    }

    /**退款处理
     * @param Request $request
     * @param $id
     */
    public function updateRefundY(Request $request, $id){
        $data = Util::postMore([
            'refund_price',
            'refund_score',
        ],$request);
        if(!$id) return $this->failed('数据不存在');
        $product = ShopOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $refund_data['pay_price'] = $product['pay_price'];
        $refund_data['refund_price'] = $data['refund_price'];
        $refund_data['refund_id']=$product['order_id'];
        if($product['pay_type'] == 'weixin'){
            try{
                if(!$data['refund_price']) return Json::fail('请输入退款金额');
                $score_type=db('shop_score_type')->where('id',1)->value('flag');
                $user_score=User::where('uid',$product['uid'])->value($score_type);
                $nums=$user_score+$data['refund_score'];
                $res3=User::where('uid',$product['uid'])->update([$score_type=>$nums]);
                if($res3===false) return $this->failed('积分恢复失败!');
                $log=[];
                $log[$score_type]=$data['refund_score'];
                Support::jiafenlog($product['uid'],'积分商城退款',$log,1,'行为');
                WechatTemplateService::sendTemplate(WechatUser::uidToOpenid($product['uid']),WechatTemplateService::REFUND_SUCCESS, [
                    'first'=>'亲，您的订单退款已到账',
                    'keyword1'=>$product['order_id'],
                    'keyword2'=>$product['pay_price'],
                    'remark'=>'可以去商城查看订单详情'
                ],'');
                HookService::listen('wechat_pay_order_refund',$id,$refund_data,true,PaymentBehavior::class);
                $map['status']=-2;
                $res4=ShopOrderModel::where('id',$id)->update($map);
                if($res4===false) return $this->failed('退款失败!');
            }catch(\Exception $e){
                return Json::fail($e->getMessage());
            }
//            if($product['is_channel']){//小程序
//                try{
//                    $user_score=User::where('uid',$product['uid'])->value('buy');
//                    $nums=$user_score+$product['score_num'];
//                    $res3=User::where('uid',$product['uid'])->update(['buy'=>$nums]);
//                    if($res3===false) return $this->failed('积分恢复失败!');
//                    HookService::listen('routine_pay_order_refund',$product['order_id'],$refund_data,true,PaymentBehavior::class);
//                }catch(\Exception $e){
//                    return Json::fail($e->getMessage());
//                }
//            }else{
//                try{
//                    $user_score=User::where('uid',$product['uid'])->value('buy');
//                    $nums=$user_score+$product['score_num'];
//                    $res3=User::where('uid',$product['uid'])->update(['buy'=>$nums]);
//                    if($res3===false) return $this->failed('积分恢复失败!');
//                    HookService::listen('wechat_pay_order_refund',$product['order_id'],$refund_data,true,PaymentBehavior::class);
//                }catch(\Exception $e){
//                    return Json::fail($e->getMessage());
//                }
        }else{
            ModelBasic::beginTrans();
            $usermoney = User::where('uid',$product['uid'])->value('now_money');
            $res1 = User::bcInc($product['uid'],'now_money',$data['refund_price'],'uid');
            $score_type=db('shop_score_type')->where('id',1)->value('flag');
            $user_score=User::where('uid',$product['uid'])->value($score_type);
            $nums=$user_score+$data['refund_score'];
            $res3=User::where('uid',$product['uid'])->update([$score_type=>$nums]);
            if($res3===false) return $this->failed('积分恢复失败!');
            $log=[];
            $log[$score_type]=$data['refund_score'];
            Support::jiafenlog($product['uid'],'积分商城退款',$log,1,'行为');
            WechatTemplateService::sendTemplate(WechatUser::uidToOpenid($product['uid']),WechatTemplateService::REFUND_SUCCESS, [
                'first'=>'亲，您的订单退款已到账',
                'keyword1'=>$product['order_id'],
                'keyword2'=>$product['pay_price'],
                'remark'=>'可以去商城查看订单详情'
            ],'');
            try{
                //HookService::listen('store_order_yue_refund',$product,$refund_data,false,OrderBehavior::class);
                $map['status']=-2;
                $res4=ShopOrderModel::where('id',$id)->update($map);
                if($res4===false) return $this->failed('退款失败!');
            }catch (\Exception $e){
                ModelBasic::rollbackTrans();
                return Json::fail($e->getMessage());
            }
            $res = $res1;
            ModelBasic::checkTrans($res);
            if(!$res) return Json::fail('余额退款失败!');
        }
        $resEdit = ShopOrderModel::edit($data,$id);
        if($resEdit){
            HookService::afterListen('shop_product_order_refund_y',$data,$id,false,OrderBehavior::class);
            ShopOrderStatus::setStatus($id,'refund_price','退款给用户'. $data['refund_price'].'元，'.$data['refund_score'].'积分');
            return Json::successful('修改成功!');
        }else{
            ShopOrderStatus::setStatus($id,'refund_price','退款给用户'. $data['refund_price'].'元，'.$data['refund_score'].'积分失败');
            return Json::successful('修改失败!');
        }
    }
    public function order_info($oid = '')
    {
        if(!$oid || !($orderInfo = ShopOrderModel::get($oid)))
            return $this->failed('订单不存在!');
        $userInfo = User::getUserInfos($orderInfo['uid']);
        $this->assign(compact('orderInfo','userInfo'));
        return $this->fetch();
    }
    public function express($oid = '')
    {
        if(!$oid || !($order = ShopOrderModel::get($oid)))
            return $this->failed('订单不存在!');
        if($order['delivery_type'] != 'express' || !$order['delivery_id']) return $this->failed('该订单不存在快递单号!');
        $cacheName = $order['order_id'].$order['delivery_id'];
        $result = CacheService::get($cacheName,null);
        if($result === null || 1==1){
            $result = Express::query($order['delivery_id']);
            if(is_array($result) &&
                isset($result['result']) &&
                isset($result['result']['deliverystatus']) &&
                $result['result']['deliverystatus'] >= 3)
                $cacheTime = 0;
            else
                $cacheTime = 1800;
            CacheService::set($cacheName,$result,$cacheTime);
        }
        $this->assign([
            'order'=>$order,
            'express'=>$result
        ]);
        return $this->fetch();
    }
    /**
     * 修改配送信息
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function distribution($id){
        if(!$id) return $this->failed('数据不存在');
        $product = ShopOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('order_id','物流订单',$product->getData('order_id'))->disabled(1);
        if($product['delivery_type'] == 'send'){
            $f[] = Form::input('delivery_name','送货人姓名',$product->getData('delivery_name'));
            $f[] = Form::input('delivery_id','送货人电话',$product->getData('delivery_id'));
        }else if($product['delivery_type'] == 'express'){
            $f[] = Form::select('delivery_name','快递公司',$product->getData('delivery_name'))->setOptions(function (){
                $list =  Db::name('express')->where('is_show',1)->column('id,name');
                $menus = [];
                foreach ($list as $k=>$v){
                    $menus[] = ['value'=>$v,'label'=>$v];
                }
                return $menus;
            });
            $f[] = Form::input('delivery_id','快递单号',$product->getData('delivery_id'));
        }
        $form = Form::make_post_form('配送信息',$f,Url::build('updateDistribution',array('id'=>$id)),5);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**修改配送信息
     * @param Request $request
     * @param $id
     */
    public function updateDistribution(Request $request, $id){
        $data = Util::postMore([
            'delivery_name',
            'delivery_id',
        ],$request);
        if(!$id) return $this->failed('数据不存在');
        $product = ShopOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['delivery_type'] == 'send'){
            if(!$data['delivery_name']) return Json::fail('请输入送货人姓名');
            if(!(int)$data['delivery_id']) return Json::fail('请输入送货人电话号码');
            else if(!preg_match("/^1[3456789]{1}\d{9}$/",$data['delivery_id']))  return Json::fail('请输入正确的送货人电话号码');
        }else if($product['delivery_type'] == 'express'){
            if(!$data['delivery_name']) return Json::fail('请选择快递公司');
            if(!$data['delivery_id']) return Json::fail('请输入快递单号');
        }
        ShopOrderModel::edit($data,$id);
        HookService::afterListen('store_product_order_distribution',$data,$id,false,OrderBehavior::class);
        ShopOrderStatus::setStatus($id,'distribution','修改发货信息为'.$data['delivery_name'].'号'.$data['delivery_id']);
        return Json::successful('修改成功!');
    }
    /**
     * 修改退款状态
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function refund_n($id){
        if(!$id) return $this->failed('数据不存在');
        $product = ShopOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $f[] = Form::input('order_id','退款单号',$product->getData('order_id'))->disabled(1);
        $f[] = Form::input('refund_reason','拒绝退款原因')->type('textarea');
        $form = Form::make_post_form('退款',$f,Url::build('updateRefundN',array('id'=>$id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**拒绝拒绝退款原因
     * @param Request $request
     * @param $id
     */
    public function updateRefundN(Request $request, $id){
        $data = Util::postMore([
            'refund_reason',
        ],$request);
        if(!$id) return $this->failed('数据不存在');
        $product = ShopOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if(!$data['refund_reason']) return Json::fail('请输入拒绝退款原因');
        $data['refund_status'] = 0;
        ShopOrderModel::edit($data,$id);
        HookService::afterListen('store_product_order_refund_n',$data['refund_reason'],$id,false,OrderBehavior::class);
        ShopOrderStatus::setStatus($id,'refund_n','拒绝拒绝退款原因:'.$data['refund_reason']);
        return Json::successful('修改成功!');
    }

    public function remark(Request $request){
        $data = Util::postMore(['id','remark'],$request);
        if(!$data['id']) return Json::fail('参数错误!');
        if($data['remark'] == '')  return Json::fail('请输入要备注的内容!');
        $id = $data['id'];
        unset($data['id']);
        ShopOrderModel::edit($data,$id);
        return Json::successful('备注成功!');
    }
    public function order_status($oid){
       if(!$oid) return $this->failed('数据不存在');
       $this->assign(ShopOrderStatus::systemPage($oid));
       return $this->fetch();
    }

    public function backStock($id){
        if(!$id) return $this->failed('数据不存在');
        $product = ShopOrderModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $res1 = ShopProduct::where('id',$product['product_id'])->inc('stock',$product['total_num'])->dec('sales',$product['total_num'])->update();
        $res2 = ShopOrderModel::where('id',$product['id'])->update(['status' => -1]);
        if($res1===false && $res2===false){
            return Json::fail('库存回退失败!');
        }else{
            return Json::successful('库存回退成功!');
        }
    }
}
