<?php
namespace app\shopapi\controller;

use Api\Express;
use app\admin\model\system\SystemConfig;
use app\core\model\routine\RoutineFormId;//待完善
use app\shopapi\model\user\User;
use app\shopapi\model\shop\ShopOrder;
use app\shopapi\model\user\UserAddress;
use app\osapi\model\com\ComThread;
use app\osapi\model\com\Report;
use app\osapi\model\user\UserFollow;
use service\CacheService;
use service\JsonService;
use service\UtilService;
use think\Request;
use think\Cache;


/**
 * 小程序个人中心api接口
 * Class UserApi
 * @package app\routine\controller
 *
 */
class UserApi extends AuthController
{

    public static function whiteList()
    {
        return [
            'userCard',
        ];
    }


    /**
     * 获取一条用户地址
     * @param string $addressId 地址id
     * @return \think\response\Json
     */
    public function get_user_address(){
        $addressId=osx_input('addressId',0,'intval');
        $addressInfo = [];
        if($addressId && is_numeric($addressId) && UserAddress::be(['is_del'=>0,'id'=>$addressId,'uid'=>$this->userInfo['uid']])){
            $addressInfo = UserAddress::find($addressId);
        }
        return JsonService::successful($addressInfo);
    }

    /**
     * 获取默认地址
     * @return \think\response\Json
     */
    public function user_default_address()
    {
        $defaultAddress = UserAddress::getUserDefaultAddress($this->userInfo['uid'],'id,real_name,phone,province,city,district,detail,is_default');
        if($defaultAddress) return JsonService::successful('ok',$defaultAddress);
        else return JsonService::successful('empty',[]);
    }

    /**
     * 删除地址
     * @param string $addressId 地址id
     * @return \think\response\Json
     */
    public function remove_user_address()
    {
        $addressId=osx_input('addressId',0,'intval');
        if(!$addressId || !is_numeric($addressId)) return JsonService::fail('参数错误!');
        if(!UserAddress::be(['is_del'=>0,'id'=>$addressId,'uid'=>$this->userInfo['uid']]))
            return JsonService::fail('地址不存在!');
        if(UserAddress::edit(['is_del'=>'1'],$addressId,'id'))
            return JsonService::successful();
        else
            return JsonService::fail('删除地址失败!');
    }

    /**
     * 个人中心 获取订单列表
     * @param string $type
     * @param int $first
     * @param int $limit
     * @param string $search
     * @return \think\response\Json
     */
    public function get_user_order_list()
    {
        list($page,$limit)=UtilService::getMore([
            ['page',''],
            ['limit',''],
        ],$this->request,true);
        return JsonService::successful(ShopOrder::getUserOrderSearchList($this->uid,$page,$limit));
    }

    /**
     * 个人中心 订单详情页
     * @return \think\response\Json
     */
    public function get_order(){
        $uni=osx_input('uni','');
        if($uni == '') return JsonService::fail('参数错误');
        $order = ShopOrder::getUserOrderDetail($this->userInfo['uid'],$uni);
        $order = $order->toArray();
        $order['add_time_y'] = date('Y-m-d',$order['add_time']);
        $order['add_time_h'] = date('H:i:s',$order['add_time']);
        if(!$order) return JsonService::fail('订单不存在');
        return JsonService::successful(ShopOrder::tidyOrder($order,true,true));
    }

    /**
     * 获取用户手机号码
     * @param Request $request
     * @return \think\response\Json
     */
    public function bind_mobile(Request $request){
        list($iv,$cache_key,$encryptedData) = UtilService::postMore([
            ['iv',''],
            ['cache_key',''],
            ['encryptedData',''],
        ],$request,true);
        $iv  = urldecode(urlencode($iv));
        try{
            if(!Cache::has('eb_api_code_'.$cache_key)) return JsonService::fail('获取手机号失败');
            $session_key=Cache::get('eb_api_code_'.$cache_key);
            $userInfo = \service\MiniProgramService::encryptor($session_key,$iv,$encryptedData);
            if(!empty($userInfo['purePhoneNumber'])){
                if(User::edit(['phone'=>$userInfo['purePhoneNumber']],$this->userInfo['uid']))
                    return JsonService::successful('绑定成功',['phone'=>$userInfo['purePhoneNumber']]);
                else
                    return JsonService::fail('绑定失败');
            }else
                return JsonService::fail('获取手机号失败');
        }catch (\Exception $e){
            return JsonService::fail('error',$e->getMessage());
        }
    }
    /**
     * 个人中心 用户确认收货
     * @return \think\response\Json
     */
    public function user_take_order()
    {
        $uni=osx_input('uni','');
        if(!$uni) return JsonService::fail('参数错误!');

        $res = ShopOrder::takeOrder($uni,$this->userInfo['uid']);
        if($res)
            return JsonService::successful();
        else
            return JsonService::fail(ShopOrder::getErrorInfo());
    }

    /**
     * 获取用户所有地址
     * @return \think\response\Json
     */
    public function user_address_list()
    {
        $page=osx_input('page',1,'intval');
        $limit=osx_input('limit',8,'intval');
        $list = UserAddress::getUserValidAddressList($this->userInfo['uid'],$page,$limit,'id,real_name,phone,province,city,district,detail,is_default');
        return JsonService::successful($list);
    }

    /**
     * 修改收货地址
     * @return \think\response\Json
     */
    public function edit_user_address()
    {
        $request = Request::instance();
        if(!$request->isPost()) return JsonService::fail('参数错误!');
        $addressInfo = UtilService::postMore([
            ['city',''],
            ['district',''],
            ['province',''],
            ['is_default',false],
            ['real_name',''],
            ['post_code',''],
            ['phone',''],
            ['detail',''],
            ['id',0]
        ],$request);
        $addressInfo['is_default'] = $addressInfo['is_default'] == true ? 1 : 0;
        $addressInfo['uid'] = $this->userInfo['uid'];

        if($addressInfo['id'] && UserAddress::be(['id'=>$addressInfo['id'],'uid'=>$this->userInfo['uid'],'is_del'=>0])){
            $id = $addressInfo['id'];
            unset($addressInfo['id']);
            if(UserAddress::edit($addressInfo,$id,'id')){
                if($addressInfo['is_default'])
                    UserAddress::setDefaultAddress($id,$this->userInfo['uid']);
                return JsonService::successful();
            }else
                return JsonService::fail('编辑收货地址失败!');
        }else{
            if($address = UserAddress::set($addressInfo,true)){
                if($addressInfo['is_default'])
                    UserAddress::setDefaultAddress($address->id,$this->userInfo['uid']);
                return JsonService::successful(['id'=>$address->id]);
            }else
                return JsonService::fail('添加收货地址失败!');
        }
    }



    /**
     * 设置为默认地址
     * @return \think\response\Json
     */
    public function set_user_default_address()
    {
        $addressId=osx_input('addressId',0,'intval');
        if(!$addressId || !is_numeric($addressId)) return JsonService::fail('参数错误!');
        if(!UserAddress::be(['is_del'=>0,'id'=>$addressId,'uid'=>$this->userInfo['uid']]))
            return JsonService::fail('地址不存在!');
        $res = UserAddress::setDefaultAddress($addressId,$this->userInfo['uid']);
        if(!$res)
            return JsonService::fail('地址不存在!');
        else
            return JsonService::successful();
    }


}