<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/22
 * Time: 9:39
 */

namespace app\commonapi\controller;


use app\admin\model\system\SystemConfig;
use app\commonapi\model\WebsiteConnect;
use app\osapi\model\user\UserModel;
use basic\ControllerBasic;
use service\UtilService;

class User extends ControllerBasic
{

    protected function _initialize(){
        parent::_initialize();
        $website_connect_open=SystemConfig::getValue('website_connect_open');
        if($website_connect_open!='1'){
            $this->apiError('系统未开通第三方对接');
            return false;
        }
    }

    /**
     * 第三方用户信息变更通知OSX服务端接口，第三方服务端调用
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function userInfoChanged()
    {
        $appKey=osx_input('get.appKey','');
        $timestamp=osx_input('get.endtimestamp','');
        $user_token=osx_input('get.user_token','');
        $sign=osx_input('get.sign','');
        $args=[
            'appKey' =>$appKey,
            'endtimestamp' => $timestamp,
            'user_token' => $user_token
        ];
        $this->_checkSign($args,$sign);

        $res=$this->_initWebsiteUser($user_token,'not_login');
        if($res>0){
            $this->apiSuccess('用户信息更新成功');
        }else{
            $this->apiError('用户信息更新失败');
        }
    }

    /**
     * 第三方用户信息变更通知OSX服务端接口，第三方服务端调用
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function getUserTokenByUid()
    {
        $appKey=osx_input('get.appKey','');
        $timestamp=osx_input('get.endtimestamp','');
        $uid=osx_input('get.uid','');
        $sign=osx_input('get.sign','');
        $args=[
            'appKey' =>$appKey,
            'endtimestamp' => $timestamp,
            'uid' => $uid
        ];
        $this->_checkSign($args,$sign);

        $user_token=db('website_connect_token')->where('uid',$uid)->where('status',1)->value('user_token');

        if(!$user_token){
            $this->apiSuccess(['user_token'=>$user_token,'uid'=>$uid]);
        }else{
            $this->apiError('不存在用户token');
        }
    }

    /**
     * 用户第三方对接自动登录。OSX客户端调用
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function userWebsiteAutoLogin()
    {
        $appKey=osx_input('post.appKey','');
        $timestamp=osx_input('post.endtimestamp','');
        $user_token=osx_input('post.user_token','');
        $token=osx_input('post.token','');
        $sign=osx_input('post.sign','');
        $args=[
            'appKey' =>$appKey,
            'endtimestamp' => $timestamp,
            'user_token' => $user_token,
            'token' => $token
        ];
        $this->_checkSign($args,$sign);

        $res=$this->_initWebsiteUser($user_token,$token);
        if($res>0){
            $result = UserModel::doQuickLogin($res); //状态码是1时候登录成功
            if ($result['status'] == 1) {
                $this->user_token = $result['token'];
                $data['token'] = $this->user_token;
                $data['token_time'] = $result['token_time'];
                $data['user'] = $result['user'];
                $this->apiSuccess($data);
            } else {
                $this->apiError($result);
            }
        }else{
            $this->apiError('用户信息更新失败');
        }
    }


    /**
     * 重新发送通知
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function reNotify()
    {
        $res=WebsiteConnect::reNotify();
        if($res){
            echo '重发通知成功';
            exit(true);
        }else{
            echo '重发通知失败';
            exit(false);
        }
    }

    public function websiteConnect()
    {
        $website_connect_open=SystemConfig::getMore('website_connect_open,website_connect_login_page');
        $this->apiSuccess(['is_website_connect_open'=>intval($website_connect_open['website_connect_open'])==1?true:false,'website_connect_login_page'=>$website_connect_open['website_connect_login_page']]);
    }


    /**
     * 更新用户信息，无用户时初始化用户
     * @param $user_token
     * @param $token
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private function _initWebsiteUser($user_token,$token)
    {
        $website_connect_userInfo_api=SystemConfig::getValue('website_connect_userInfo_api');
        $args=[
            'user_token'=>$user_token,
            'token'=>$token
        ];
        $url=WebsiteConnect::buildSignUrl($args,$website_connect_userInfo_api);

        $ch = curl_init();// 初始化一个新会话
        curl_setopt($ch, CURLOPT_URL, $url);// 设置要求请的url
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if($token=='not_login'){
            $tip_info='用户信息更新';
        }else{
            $tip_info='登录';
        }

        try {
            // 执行CURL请求
            $output = curl_exec($ch);
            // 关闭CURL资源
            curl_close($ch);
            $output=json_decode($output,true);
            if($output['msg']=='ok'){
                if($output['data']['user_token']!=$user_token){
                    $this->apiError($tip_info.'失败：用户唯一标识异常');//用户信息更新失败，失败原因：$output['data']
                    return false;
                }
                $res=UserModel::updateWebsiteUserInfo($output['data']);
                if($res<=0){
                    $this->apiError($tip_info.'失败：用户信息添加失败');//用户信息更新失败，失败原因：$output['data']
                    return false;
                }
                return $res;//用户信息更新成功
            }else{
                $this->apiError($tip_info.'失败：'.$output['data']);//用户信息更新失败，失败原因：$output['data']
            }
        } catch (\Exception $e) {
            $this->apiError($tip_info.'失败：'.$e->getMessage());
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




    /********************************************************后面是测试回调地址，管理后台可配置该地址用于临时测试流程***********************************************************/

    /**
     * 测试用户信息获取接口，纯粹测试使用。默认测试地址。后端可将用户信息获取接口配置为该地址测试
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function testApi()
    {
        $get_data=UtilService::getMore([
            'user_token','token','endtimestamp','appKey','sign'
        ]);
        $args=[
            'appKey' =>$get_data['appKey'],
            'endtimestamp' => $get_data['endtimestamp'],
            'user_token' => $get_data['user_token'],
            'token' => $get_data['token']
        ];
        $this->_checkSign($args,$get_data['sign']);
        $data['user_token']=$get_data['user_token'];
        $data['userInfo']=[
            'nickname'=>create_rand(6),
            'score1'=>10000,
            'score2'=>10000
        ];
        $this->apiSuccess($data);
    }

    /**
     * 测试事件通知,直接调用
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function testNotifytest()
    {
        $this->apiSuccess(website_connect_notify(15,10,16,'osapi_common_doSupport_thread'));
    }


    /**
     * 测试事件通知接口，管理后台可配置该地址
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function testNotify()
    {
        $get_data=UtilService::getMore([
            'user_token','to_id','to_user_token','action','action_token','endtimestamp','appKey','sign'
        ]);
        $args=[
            'appKey' =>$get_data['appKey'],
            'endtimestamp' => $get_data['endtimestamp'],
            'user_token' => $get_data['user_token'],
            'to_id' =>$get_data['to_id'],
            'action' => $get_data['action'],
            'action_token' => $get_data['action_token'],
            'to_user_token' => $get_data['to_user_token']
        ];
        $this->_checkSign($args,$get_data['sign']);
        $data['action_token']=$get_data['action_token'];
        $data['user_info']=[
            'user_token'=>$get_data['user_token'],
            'score1'=>89156,
            'score2'=>10000,
            'score8'=>18000,
        ];
        $data['to_user_info']=[
            'to_user_token'=>$get_data['to_user_token'],
            'score1'=>10001,
            'score5'=>10000,
        ];
        $this->apiSuccess($data);
    }
}