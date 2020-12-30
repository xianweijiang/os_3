<?php
/**----------------------------------------------------------------------
 * OpenCenter V3
 * Copyright 2014-2018 http://www.ocenter.cn All rights reserved.
 * ----------------------------------------------------------------------
 * Author: lin(lt@ourstu.com)
 * Date: 2018/12/25
 * Time: 11:40
 * ----------------------------------------------------------------------
 */

namespace app\osapi\controller;

use app\commonapi\model\Gong;
use app\osapi\model\user\InviteCode;
use app\wechat\sdk\WechatAuth;
use app\osapi\model\user\UserModel;
use app\admin\model\system\SystemConfig;
use app\osapi\lib\FlyPigeno;
use app\osapi\model\user\UserVerify;
use app\osapi\lib\ChuanglanSmsApi;
use think\Db;
use app\wechat\sdk\JSSDK;
use think\Cache;
use app\osapi\model\common\Support;

class weixin extends Base
{
    protected $appId;
    protected $appSecret;
    protected $User;
    protected $UserInfo;
    protected $Token;
    protected $sync;

    public function __construct(\think\Request $request = null)
    {
        parent::__construct($request);
    }


    /**
     * 微信登录url
     * @author:lin(lt@ourstu.com)
     */
    public function weChatLogin()
    {
        $redirect_uri = input('post.url', '', 'text');
        cache('redirect_uri', $redirect_uri);
        $appId = SystemConfig::getValue('wechat_appid');
        if (!empty($appId)) {
            $redirect = urlencode(url('osapi/Weixin/callback', '', true, true));
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appId . '&redirect_uri=' . $redirect . '&response_type=code&scope=snsapi_userinfo&state=opensns#wechat_redirect';
            $this->apiSuccess($url);
        } else {
            $this->apiError('缺少微信公众平台的相关配置！');
        }
    }

    public function callback()
    {
        $code = input('get.code', '', 'text');
        $url = cache('redirect_uri') . '?code=' . $code;
        header('location:' . $url);
        exit;
    }

    /**
     * 微信授权登录
     */
    public function weChatOauth()
    {
        $code = input('post.code', '', 'text');
        $platform = input('post.platform', '','text');
        $invite_code = input('post.invite_code','','text');
        $appId = SystemConfig::getValue('wechat_appid');
        $appSecret = SystemConfig::getValue('wechat_appsecret');
        $wechat = new WechatAuth($appId, $appSecret);
        /* 获取请求信息 */
        $token = $wechat->getAccessToken('code', $code);
        if (isset($token['errcode'])) {
            $this->apiError('errcode:' . $token['errcode'] . ',errmsg:' . $token['errmsg']);
        }
        $userinfo = $wechat->getUserInfo($token);
        if (isset($userinfo['errcode'])) {
            $this->apiError('errcode:' . $userinfo['errcode'] . ',errmsg:' . $userinfo['errmsg']);
        }
        //判断是否绑定了微信开放平台 xsh
        if(!empty($userinfo['unionid'])){
            $is_unionid = db('user_sync_login')->where('type_uid',$userinfo['unionid'])->find();//用unionid查找数据 qhy
            if (!$is_unionid){
                $is_openid = db('user_sync_login')->where('open_id',$userinfo['openid'])->find();//如果unionid没查到数据，则用openid查找 qhy
                if($is_openid){
                    if ($is_openid['unionid']!=$userinfo['unionid']) {//如果unionid不同，则更新unionid qhy
                        $data['type_uid'] = $userinfo['unionid'];
                        $data['oauth_token_secret'] = $userinfo['unionid'];
                        $data['open_id'] = $userinfo['openid'];
                        db('user_sync_login')->where('open_id',$userinfo['openid'])->update($data);
                    }
                    if($is_unionid['is_update']==0){//老数据更新信息
                        $data['type_uid'] = $userinfo['unionid'];
                        $data['oauth_token_secret'] = $userinfo['unionid'];
                        $data['open_id'] = $userinfo['openid'];
                        $data['is_update'] = 1;
                        db('user_sync_login')->where('open_id',$userinfo['openid'])->update($data);
                    }
                    $uid=$is_openid['uid'];
                }else{
                    $uid=null;
                }
            }else{
                if(!$is_unionid['open_id']){
                    $data['open_id'] = $userinfo['openid'];
                    db('user_sync_login')->where('type_uid',$userinfo['unionid'])->update($data);
                }
                if($is_unionid['is_update']==0){//老数据更新信息
                    $data['type_uid'] = $userinfo['unionid'];
                    $data['oauth_token_secret'] = $userinfo['unionid'];
                    $data['open_id'] = $userinfo['openid'];
                    $data['is_update'] = 1;
                    db('user_sync_login')->where('type_uid',$userinfo['unionid'])->update($data);
                }
                $uid=$is_unionid['uid'];
            }
        }else{
            $uid = db('user_sync_login')->where('open_id',$userinfo['openid'])->value('uid');
        }
        $userinfo['unionid'] = !empty($userinfo['unionid']) ? $userinfo['unionid'] : '';
        cache('wechat_token', array('access_token' => $token['access_token'], 'openid' => $userinfo['unionid'], 'openid_public' => $userinfo['openid']), 600);
        if (!$uid) {
            $user_info = $this->wechat($userinfo);
            $uid = $this->addData($user_info);
            $fids=db('com_forum')->where('default_follow',1)->where('status',1)->column('id');
            foreach($fids as &$val){
                $data['uid']=$uid;
                $data['status']=1;
                $data['create_time']=time();
                $data['fid']=$val;
                db('com_forum_member')->insert($data);
            }
            unset($val);
            //新增微信授权层级 2019.10.25 zxh
            InviteCode::addInviteLog($invite_code,$uid);
        }
        $data = [
            'uid' => $uid,
            'platform'=>$platform,
            'reg_time' => time(),
        ];
        db('stat_reg_info')->insert($data);
        $res = UserModel::doQuickLogin($uid); //登陆
        if ($res) {
            $this->apiSuccess('微信登录成功', $res);
        } else {
            $this->apiError('微信登录失败');
        }

    }

    /**
     * 微信绑定
     */
    public function weChatOauthBind()
    {
        $uid=$this->_needLogin();
        $code = input('post.code', '', 'text');
        $appId = SystemConfig::getValue('wechat_appid');
        $appSecret = SystemConfig::getValue('wechat_appsecret');
        $wechat = new WechatAuth($appId, $appSecret);
        /* 获取请求信息 */
        $token = $wechat->getAccessToken('code', $code);
        if (isset($token['errcode'])) {
            $this->apiError('errcode:' . $token['errcode'] . ',errmsg:' . $token['errmsg']);
        }
        $userinfo = $wechat->getUserInfo($token);
        if (isset($userinfo['errcode'])) {
            $this->apiError('errcode:' . $userinfo['errcode'] . ',errmsg:' . $userinfo['errmsg']);
        }
        //判断是否绑定了微信开放平台 xsh
        if(!empty($userinfo['unionid'])){
            $is_unionid = db('user_sync_login')->where('type_uid',$userinfo['unionid'])->find();//用unionid查找数据 qhy
            if (!$is_unionid){
                $is_openid = db('user_sync_login')->where('open_id',$userinfo['openid'])->find();//如果unionid没查到数据，则用openid查找 qhy
                if($is_openid){
                    if ($is_openid['unionid']!=$userinfo['unionid']) {//如果unionid不同，则更新unionid qhy
                        $data['type_uid'] = $userinfo['unionid'];
                        $data['oauth_token_secret'] = $userinfo['unionid'];
                        $data['open_id'] = $userinfo['openid'];
                        db('user_sync_login')->where('open_id',$userinfo['openid'])->update($data);
                    }
                    $old_uid=$is_openid['uid'];
                }else{
                    $old_uid=null;
                }
            }else{
                if(!$is_unionid['open_id']){
                    $data['open_id'] = $userinfo['openid'];
                    db('user_sync_login')->where('type_uid',$userinfo['unionid'])->update($data);
                }
                $old_uid=$is_unionid['uid'];
            }
        }else{
            $old_uid = db('user_sync_login')->where('open_id',$userinfo['openid'])->value('uid');
        }
        $userinfo['unionid'] = !empty($userinfo['unionid']) ? $userinfo['unionid'] : '';
        cache('wechat_token', array('access_token' => $token['access_token'], 'openid' => $userinfo['unionid'], 'openid_public' => $userinfo['openid']), 600);
        if (!$old_uid) {
            $res = $this->bindData($uid);
            if ($res) {
                $this->apiSuccess('微信绑定成功');
            } else {
                $this->apiError('微信绑定失败');
            }
        }else{
            $this->apiError('该微信已绑定其他账号');
        }
    }

    /**
     * 解绑微信
     */
    public function weChatBindDel(){
        $uid=$this->_needLogin();
        $res=db('user_sync_login')->where('uid',$uid)->delete();
        if($res){
            Cache::rm('user_weixin_nickname'.$uid);
            $this->apiSuccess('微信解绑成功');
        }else{
            $this->apiError('微信解绑失败');
        }
    }

    protected $config = array(
        'url' => "https://api.weixin.qq.com/sns/jscode2session", //微信获取session_key接口url
        'appid' => '', // APPId
        'secret' => '', // 秘钥
        'grant_type' => 'authorization_code', // grant_type，一般情况下固定的
    );
    /**
     * 微信小程序登录
     * @author:qhy(qhy@ourstu.com)
     */
    public function MiniProgram()
    {
        $code = input('post.code', '', 'text');
        $nickname = input('post.nickname', '', 'text');
        $avatar = input('post.avatar', '', 'text');
        $sex = input('post.sex', '', 'text');
        $platform = input('post.platform', '4','text');
        $appId = SystemConfig::getValue('routine_appId');
        $appSecret = SystemConfig::getValue('routine_appsecret');
        $params = array(
            'appid' => $appId,
            'secret' => $appSecret,
            'js_code' => $code,
            'grant_type' => $this->config['grant_type']
        );
        /* 获取请求信息 */
        $info = $this->checkLogin($params);
        $info=json_decode($info,true);
        //如果小程序openid存在open_id，调整回去
        $is_old_openid=db('user_sync_login')->where('open_id',$info['openid'])->find();
        if($is_old_openid){
            $map['mini_open_id']=$info['openid'];
            $map['open_id']='';
            db('user_sync_login')->where('open_id',$info['openid'])->update($map);
        }
        if(!empty($info['unionid'])){
            $is_unionid = db('user_sync_login')->where('type_uid',$info['unionid'])->find();//用unionid查找数据 qhy
            if (!$is_unionid){
                $is_openid = db('user_sync_login')->where('mini_open_id',$info['openid'])->find();//如果unionid没查到数据，则用openid查找 qhy
                if($is_openid){
                    if ($is_openid['unionid']!=$info['unionid']) {//如果unionid不同，则更新unionid qhy
                        $data['type_uid'] = $info['unionid'];
                        $data['oauth_token_secret'] = $info['unionid'];
                        $data['mini_open_id'] = $info['openid'];
                        db('user_sync_login')->where('mini_open_id',$info['openid'])->update($data);
                    }
                    if($is_openid['is_update']==0){//老数据更新信息
                        $data['type_uid'] = $info['unionid'];
                        $data['oauth_token_secret'] = $info['unionid'];
                        $data['mini_open_id'] = $info['openid'];
                        $data['is_update'] = 1;
                        db('user_sync_login')->where('mini_open_id',$info['openid'])->update($data);
                    }
                    $uid=$is_openid['uid'];
                }else{
                    $uid=null;
                }
            }else{
                if(!$is_unionid['mini_open_id']){
                    $data['mini_open_id'] = $info['openid'];
                    db('user_sync_login')->where('type_uid',$info['unionid'])->update($data);
                }
                if($is_unionid['is_update']==0){//老数据更新信息
                    $data['type_uid'] = $info['unionid'];
                    $data['oauth_token_secret'] = $info['unionid'];
                    $data['mini_open_id'] = $info['openid'];
                    $data['is_update'] = 1;
                    db('user_sync_login')->where('type_uid',$info['unionid'])->update($data);
                }
                $uid=$is_unionid['uid'];
            }
        }else{
            $uid = db('user_sync_login')->where('mini_open_id',$info['openid'])->value('uid');
        }
        $info['unionid'] = !empty($info['unionid']) ? $info['unionid'] : '';
        if (!$uid) {
            $user_info['name'] = $nickname;
            $user_info['nick'] = $nickname;
            $user_info['head'] = $avatar;
            $user_info['sex'] = $sex;
            $uid = $this->addDataProgram($user_info,$info);
        }
        $data = [
            'uid' => $uid,
            'platform'=>$platform,
            'reg_time' => time(),
        ];
        db('stat_reg_info')->insert($data);
        $res = UserModel::doQuickLogin($uid); //登陆
        if ($res) {
            $this->apiSuccess('微信登录成功', $res);
        } else {
            $this->apiError('微信登录失败');
        }

    }

    /**
     * 获取openid 参数准备
     * @param $code
     * @return mixed
     */
    private function checkLogin($params) {
        /**
         * 这是一个 HTTP 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。其中 session_key 是对用户数据进行加密签名的密钥。
         * 为了自身应用安全，session_key 不应该在网络上传输。
         * 接口地址："https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code"
         */
        $res = $this->_requestPost($this->config['url'], $params);
        return $res;
    }

    //post 提交
    private function _requestPost($url, $data, $ssl = true) {
        //curl完成
        $curl = curl_init();
        //设置curl选项
        curl_setopt($curl, CURLOPT_URL, $url);//URL
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

        //SSL相关
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。
        }
        // 处理post相关选项
        curl_setopt($curl, CURLOPT_POST, true);// 是否为POST请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// 处理请求数据
        // 处理响应结果
        curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果

        // 发出请求
        $response = curl_exec($curl);
        if (false === $response) {
            echo '<br>', curl_error($curl), '<br>';
            return false;
        }
        curl_close($curl);
        return $response;
    }

    /**
     * 微信用户信息
     * @param $data
     * @return mixed
     * @author:lin(lt@ourstu.com)
     */
    private function wechat($data)
    {
        if (!isset($data['ret'])) {
            $userInfo['type'] = 'WEIXIN';
            $userInfo['name'] = $data['nickname'];
            $userInfo['nick'] = $data['nickname'];
            $userInfo['head'] = $data['headimgurl'];
            $userInfo['sex'] = $data['sex'] == '1' ? 0 : 1;
            return $userInfo;
        } else {
            $this->apiError("获取微信用户信息失败：{$data['errmsg']}");
        }
    }

    /**
     * 新增用户数据
     * @param $user_info
     * @return mixed
     * @author:lin(lt@ourstu.com)
     */
    private function addData($user_info)
    {
        $res = UserModel::addSyncData($user_info);
        // 记录数据到sync_login表中
        $this->addSyncLoginData($res['uid']);
        return $res['uid'];
    }


    /**
     * 绑定微信信息
     */
    private function bindData($uid)
    {
        // 记录数据到sync_login表中
        $res=$this->addSyncLoginData($uid);
        return $res;
    }

    private function addDataProgram($user_info,$info)
    {
        $res = UserModel::addSyncData($user_info);
        // 记录数据到sync_login表中
        $this->addSyncLoginDataProgram($res['uid'],$info);
        return $res['uid'];
    }

    /**
     * 记录sync_login表数据
     * @param $uid
     * @author:lin(lt@ourstu.com)
     */
    private static function addSyncLoginData($uid)
    {
        $cache = cache('wechat_token');
        $data['uid'] = $uid;
        $data['type_uid'] = $cache['openid'];
        $data['oauth_token'] = $cache['access_token'];
        $data['oauth_token_secret'] = $cache['openid'];
        $data['open_id'] = $cache['openid_public'];
        $data['type'] = 'weixin';
        $data['is_sync'] = 1;
        $data['is_update'] = 1;
        $syncModel = db('user_sync_login');
        $map['uid'] = $uid;
        if (!$syncModel->where($map)->count()) {
            $res=$syncModel->insert($data);
            return $res;
        } else {
            $res=$syncModel->where($map)->update($data);
            return $res;
        }
    }

    private static function addSyncLoginDataProgram($uid,$info)
    {
        $data['uid'] = $uid;
        $data['type_uid'] = $info['unionid'];
        $data['oauth_token'] = $info['session_key'];
        $data['oauth_token_secret'] = $info['openid'];
        $data['mini_open_id'] = $info['openid'];
        $data['type'] = 'weixinProgram';
        $data['is_sync'] = 1;
        $data['is_update'] = 1;
        $syncModel = db('user_sync_login');
        $map['uid'] = $uid;
        if (!$syncModel->where($map)->count()) {
            $syncModel->insert($data);
        } else {
            $syncModel->where($map)->data($data)->update();
        }
    }

    /**
     * 微信分享
     */
    public function share(){
        $appId = SystemConfig::getValue('wechat_appid');
        $appSecret = SystemConfig::getValue('wechat_appsecret');
        $url=input('url','','');
        $jssdk = new JSSDK($appId,$appSecret,$url);
        $data['signPackage'] = $jssdk->GetSignPackage();
        $this->apiSuccess($data);
    }

    /**
     * 微信绑定手机号
     */
    public function phone(){
        $now_uid=$this->_needLogin();
        $aAccount = input('post.phone', '', 'text');
        $inviteCode = input('post.invite_code', '','text');
        $type = input('post.type/d',1);
        /**解密 start**/
        $iv = "1234567890123412";//16位 向量
        $key= '201707eggplant99';//16位 默认秘钥
        $aAccount=trim(openssl_decrypt(base64_decode($aAccount),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv));
        /**解密 end**/

        $uid=db('user')->where('phone',$aAccount)->find();
        if(!$uid){
            $res=db('user')->where('uid',$now_uid)->update(['phone' => $aAccount]);
            InviteCode::addInviteLog($inviteCode,$now_uid);
            if($res){
                //绑定手机号加分
                Gong::bindfirst('bangdingshouji',1) ;

                $this->apiSuccess('微信绑定手机号成功');
            }else {
                $this->apiError('微信绑定手机号失败');
            }
        } else {
            $is_bind=db('user_sync_login')->where('uid',$uid['uid'])->find();
            if($is_bind){
                if($type==1){
                    if($is_bind['open_id']){
                        $this->apiError('该手机已经绑定过微信');
                    }
                    $now_openid=db('user_sync_login')->where('uid',$now_uid)->value('open_id');
                    $res=db('user_sync_login')->where('uid',$is_bind['uid'])->update(['open_id' => $now_openid]);
                }else{
                    if($is_bind['mini_open_id']){
                        $this->apiError('该手机已经绑定过小程序');
                    }
                    $now_openid=db('user_sync_login')->where('uid',$now_uid)->value('mini_open_id');
                    $res=db('user_sync_login')->where('uid',$is_bind['uid'])->update(['mini_open_id' => $now_openid]);
                }
            }else{
                $res=db('user_sync_login')->where('uid',$now_uid)->update(['uid' => $uid['uid']]);
            }
            if($res!==false){
                db('user')->where('uid',$now_uid)->delete();
                db('invite_level')->where('uid',$now_uid)->delete();
                $is_invite=db('invite_level')->where('uid',$now_uid)->find();
                if(!$is_invite){
                    InviteCode::addInviteLog($inviteCode,$uid['uid']);
                }
                $token = UserModel::doQuickLogin($uid['uid']);
                $token['message']='微信绑定手机号成功';
                //绑定手机号加分
                Gong::bindfirst('bangdingshouji',1) ;
                $this->apiSuccess($token);
            }else {
                $this->apiError('微信绑定手机号失败');
            }
        }

    }

    /**
     * 微信绑定生成手机验证码并发送
     */
    public function Verify()
    {
        if (is_post()) {
            $now_uid=$this->_needLogin();
            $account = input('post.phone', '', 'text');
            /**解密 start**/
            $iv = "1234567890123412";//16位 向量
            $key= '201707eggplant99';//16位 默认秘钥
            $account=trim(openssl_decrypt(base64_decode($account),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv));
            /**解密 end**/
            if (!isset($account)) {
                $this->apiError('请填写手机号');
            }
            $uid=UserModel::where('phone',$account)->value('uid');
            if($now_uid==$uid){
                $this->apiError('你已绑定该手机号！');
            }
            if($uid){
                $wx_uid=db('user_sync_login')->where('uid',$uid)->value('uid');
                if($wx_uid){
                    $this->apiError('该手机号已绑定微信账号！');
                }
            }
            $resend_time = modC('sms_resend_time', 60);
            if (time() <= Cache::get('verify_time_'.get_client_ip()) + $resend_time) {
                $this->apiError('请勿重复获取验证码');
            }
            Cache::set('verify_time_'.get_client_ip(), time());
            $aVerify = UserVerify::addData($account); //生成验证码
            if ($aVerify) {
                $sms_type = SystemConfig::getValue('sms_type');
                if($sms_type=='fg'){
                    $content = modC('sms_content');
                    $content = str_replace('{$verify}', $aVerify, $content); //根据短信模板添加验证码
                    $content = str_replace('{$account}', $account, $content); //根据短信模板添加手机号
                    $res = FlyPigeno::sendSMS($account, $content); //发送短信
                    if ($res===true) {
                        $this->apiSuccess('发送验证码成功');
                    } else {
                        Cache::rm('verify_time_'.get_client_ip());
                        $this->apiError($res);
                    }
                }else{
                    $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                    $content = str_replace('{s6}', $aVerify, $config['cl_sms_template']); //根据短信模板添加验证码
                    $content='【'.$config['cl_sms_sign'].'】'.$content;
                    $res = ChuanglanSmsApi::sendSMS($account,$content); //发送短信
                    $res=json_decode($res,true);
                    if ($res['code']==0) {
                        $this->apiSuccess('发送验证码成功');
                    } else {
                        Cache::rm('verify_time_'.get_client_ip());
                        $this->apiError($res['errorMsg']);
                    }
                }
            } else {
                Cache::rm('verify_time_'.get_client_ip());
                $this->apiError('生成验证码失败');
            }
        }
    }

    /**
     * 验证短信验证码
     */
    public function CheckVerify(){
        $aAccount = input('post.phone', '', 'text');
        $aRegVerify = input('post.quick_verify', '','text');
        /**解密 start**/
        $iv = "1234567890123412";//16位 向量
        $key= '201707eggplant99';//16位 默认秘钥
        $aAccount=trim(openssl_decrypt(base64_decode($aAccount),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv));
        /**解密 end**/
        //是否用户已经存在
        $is_exit=db('user')->where(['phone'=>$aAccount])->count();

        if (empty($aRegVerify)) {
            $this->apiError('请输入验证码');
        }
        $code=UserVerify::checkVerify($aAccount,'mobile', $aRegVerify);
        switch($code){
            case 1:
                $is_invite= db('system_config')->where(['menu_name'=>'invite_code'])->find();
                $need_invite= db('system_config')->where(['menu_name'=>'invite_code_need'])->find();
                $data['is_invite']=$is_exit?0:json_decode($is_invite['value']);
                $data['need_invite']=$is_exit?0:json_decode($need_invite['value']);
                $this->apiSuccess($data);
                break;
            case -1:
                $data['is_exit']=-1;
                $data['info']='短信验证码错误';
                $this->apiError($data);
                break;
            case -2:
                $data['is_exit']=-2;
                $data['info']='短信验证码已过期';
                $this->apiError($data);
                break;
        }
    }

    /**
     * 微信支付
     */
    public function wxPay(){
        require_once("../wxpay/WxPay.Api.php");
        $body=input('post.body', '', '通过微信在线支付');
        $order_sn=input('post.order_id', '', '');
        $order=db('store_order')->where('order_id',$order_sn)->find();
        $total_fee = $order['pay_price']*100;
        $notify_url = 'http://'.$_SERVER['HTTP_HOST'].'/osapi/Weixin/notify';
        $WxPayApi = new \WxPayApi;
        $input = new \WxPayUnifiedOrder;
        $input->SetBody($body);
        $input->SetOut_trade_no($order_sn);
        $input->SetTotal_fee($total_fee);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 60*10));
        $input->SetNotify_url($notify_url);
        $input->SetTrade_type("APP");
        $data = $WxPayApi::unifiedOrder($input);
        $order_data = $WxPayApi->GetAppParameters($data);
        $this->apiSuccess($order_data);
    }

    public function notify()
    {
        require_once("../wxpay/WxPay.Data.php");
        $WxPay = new \WxPayResults();

        header('Content-type: text/xml');

        $returnResult = $GLOBALS['HTTP_RAW_POST_DATA'];

        //$res = $WxPay->FromXml($returnResult);
        $res = $WxPay::Init($returnResult);

        //支付成功
        if ($res['result_code'] == 'SUCCESS') {
            $data['paid']=1;
            $data['pay_type']='weixin';
            $data['pay_time']=time();
            $data['is_channel']=2;
            if(db('store_order')->where('order_id',$res['out_trade_no'])->count()){
                db('store_order')->where('order_id',$res['out_trade_no'])->update($data);
                if(db('sell_order')->where('order_id',$res['out_trade_no'])->where('order_status',4)->count()){
                    db('sell_order')->where('order_id',$res['out_trade_no'])->where('order_status',4)->update(['order_status'=>0]);//修改分销订单为已支付
                }
            }
            $success = array('return_code' => 'SUCCESS', 'return_msg' => 'OK');
            exit($this->ToXml($success));
        } else{
            // todo 返回错误信息记录表
        }
    }

    private function ToXml($data)
    {
        $xml = "<xml>";
        foreach ($data as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    /*
     * 微信退款
     */
    private function refundWeixin($order){
        require_once("../wxpay/WxPay.Api.php");
        $pay = new \WxPayApi;
        $amount = $order['pay_price']*100;
        $desc = '退款';
        $number = time().create_rand(16,'num');
        $openid = Db::name('user_sync_login')->where(array('uid'=>$order['uid']))->field('open_id')->find();
        if(!$openid){
            return false;
        }
        $params = array(
            'partner_trade_no' => $number,
            'openid' => $openid['open_id'],
            'check_name' => 'NO_CHECK',
            'amount' => $amount,
            'desc' => $desc,
        );
        $toPay = $pay::payToUser($params);
        if($toPay["return_code"]=="SUCCESS"&&$toPay["result_code"]=="SUCCESS"){
            return true;
        }else{
            return false;
        }
    }
}