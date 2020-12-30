<?php
/**
 * 后续鉴权及加密文件，一些公用的依赖方法放到这里，让整个系统脱离不掉该文件
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/10/29
 * Time: 18:51
 */

namespace basic;

use app\admin\model\system\SystemConfig;
use behavior\wechat\UserBehavior;
use service\HookService;
use service\JsonService;
use service\UtilService;
use service\WechatService;
use think\Cache;
use think\Controller;
use think\Cookie;
use think\Request;
use think\Session;

class ControllerBasic extends Controller implements ControllerInterface
{
    protected $user_token;//标识登录用户
    protected $access;//标识哪一端
    protected $official_auth_list='OFFICIAL_AUTH_DATA_LIST_INFO';
    /**
     * 构造方法
     * @access public
     * @param Request $request Request 对象
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_checkOfficialAuth();//官方鉴权
        $this->check_platform();
        //$this->_check_extend_auth();
    }

    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        if(request()->method() == 'OPTIONS'){
            die;
        }
        parent::_initialize();

        // token 该处只是获取一下，实际获取使用是在get_uid()中
        $token = request()->server('HTTP_ACCESS_TOKEN');
        if($token == '' || $token == null){
            $token = request()->post('token');
        }
        $this->user_token = $token;
    }

    /**
     * 需要登录后才能操作
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    protected function _needLogin()
    {
        $uid = get_uid();
        if (!$uid) {
            $return['need_login']=1;
            $return['info']='请重新登录';
            $this->apiError($return);
        }
        return $uid;
    }

    /**
     * 请求失败,公用返回方法
     * @param string $msg
     * @param array $data
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    protected function apiFailed($data=[],$msg = 'fail')
    {
        return JsonService::fail($msg,$data);
    }

    /**
     * 执行报错，请求成功,公用返回方法
     * @param string $msg
     * @param array $data
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    protected function apiError($data=[],$msg = 'error')
    {
        if(false == is_string($msg)){
            $data['info'] = $msg;
            $msg = 'error';
        }
        return JsonService::success($msg,$data);
    }

    /**
     * 请求成功,公用返回方法
     * @param string $msg
     * @param array $data
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    protected function apiSuccess($data=[],$msg = 'ok')
    {
        return JsonService::success($msg,$data);
    }

    /**
     * 微信用户自动登陆
     * @return string $openid
     */
    protected function oauth()
    {
        $openid = Session::get('loginOpenid','wap');
        if($openid) return $openid;
        //if(!UtilService::isWechatBrowser()) exit($this->failed('请在微信客户端打开链接'));
        if($this->request->isAjax()) exit($this->failed('请登陆!'));
        $errorNum = (int)Cookie::get('_oen');
        if($errorNum && $errorNum > 3) exit($this->failed('微信用户信息获取失败!!'));
        try{
            $wechatInfo = WechatService::oauthService()->user()->getOriginal();
        }catch (\Exception $e){
            Cookie::set('_oen',++$errorNum,900);
            exit(WechatService::oauthService()->scopes(['snsapi_base'])
                ->redirect($this->request->url(true))->send());
        }
        if(!isset($wechatInfo['nickname'])){
            $wechatInfo = WechatService::getUserInfo($wechatInfo['openid']);
            if(!$wechatInfo['subscribe'] && !isset($wechatInfo['nickname']))
                exit(WechatService::oauthService()->scopes(['snsapi_userinfo'])
                    ->redirect($this->request->url(true))->send());
            if(isset($wechatInfo['tagid_list']))
                $wechatInfo['tagid_list'] = implode(',',$wechatInfo['tagid_list']);
        }else{
            if(isset($wechatInfo['privilege'])) unset($wechatInfo['privilege']);
            $wechatInfo['subscribe'] = 0;
        }
        Cookie::delete('_oen');
        $openid = $wechatInfo['openid'];
        HookService::afterListen('wechat_oauth',$openid,$wechatInfo,false,UserBehavior::class);
        Session::set('loginOpenid',$openid,'wap');
        Cookie::set('is_login',1);
        return $openid;
    }

    /**
     * 官方授权校验
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private function _checkOfficialAuth()
    {
        $module_name=$this->request->module();
        $controller_name=$module_name.'/'.$this->request->controller();
        $action_name=$controller_name.'/'.$this->request->action();
        if($module_name=='auth'){
            return true;//授权官方模块相关内容请求地址持续有效
        }
        if($action_name=='osapi/Base/giveauth'){
            return true;//任何时候保证授权请求地址有效
        }
        $tip_content='本系统由想天软件提供，为确保您能正常使用，请先联系 想天软件 获得官方授权  联系电话： 400-0573-080 ';
        /**获得授权文件内容 start**/
        $auth_all_list=Cache::get($this->official_auth_list);
        if(!$auth_all_list){
            $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
            if(is_file($auth_file_path)){
                $content=file_get_contents($auth_file_path);
                $auth_all_list=$this->_dealAuthContent($content);
                Cache::set($this->official_auth_list,$auth_all_list,600);
            }else{
                return JsonService::fail($tip_content);
            }
        }
        $auth_all_list=json_decode($auth_all_list,true);
        /**获得授权文件内容 end**/

        if($auth_all_list['valid_end']<time()) {
            $content=$this->giveAuth(1);
            if($content){
                $auth_all_list=$this->_dealAuthContent($content);
                $auth_all_list=json_decode($auth_all_list,true);
                if($auth_all_list['valid_end']<time()) {
                    return JsonService::fail($tip_content);
                }
            }else{
                return JsonService::fail($tip_content);
            }
        }

        if($auth_all_list['valid_end']>time()&&$auth_all_list['valid_end']<time()-3600){//最后一小时内访问，触发重新获取校验内容
            $tag='OFFICIAL_AUTH_CHECK_SEND';
            if(!Cache::get($tag)) {
                $this->giveAuth(1);
                Cache::set($tag,1,600);
            }
        }

        if(in_array($module_name,$auth_all_list['forbidden_list'])||in_array($controller_name,$auth_all_list['forbidden_list'])||in_array($action_name,$auth_all_list['forbidden_list'])) {
            return JsonService::fail($tip_content);
        }

        $auth_list=$auth_all_list['open_list'];
        //判断是否是官方模块
        $official_module=self::get_official_module();
        if(!in_array($module_name,$official_module)){
            return true;
        }
        if(isset($auth_list[$module_name])&&$auth_list[$module_name]['auth']&&($auth_list[$module_name]['end_time']=='forever'||$auth_list[$module_name]['end_time']>time())){
            //对模块授权
            return true;
        }
        if(isset($auth_list[$controller_name])&&$auth_list[$controller_name]['auth']&&($auth_list[$controller_name]['end_time']=='forever'||$auth_list[$controller_name]['end_time']>time())){
            //对控制器授权
            return true;
        }
        if(isset($auth_list[$action_name])&&$auth_list[$action_name]['auth']&&($auth_list[$action_name]['end_time']=='forever'||$auth_list[$action_name]['end_time']>time())){
            //对方法授权
            return true;
        }
        return JsonService::fail($tip_content);
    }

    /**
     * 官方请求该地址，向各客户端提供授权
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function giveAuth($no_out=0)
    {
        if (function_exists('curl_init')) {
            $data = UtilService::getMore([['auth_get_code','']]);
            $code=$data['auth_get_code'];
            if($code==''){
                $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
                if(!is_file($auth_file_path)){
                    if($no_out){
                        return false;
                    }
                    header("content-type:text/html;charset=utf-8");
                    exit("权限校验失败-file not exit");
                }
                $content=file_get_contents($auth_file_path);
                $content=base64_decode($content);
                $string_content=substr($content,16,strlen($content)-32);
                $iv_r=substr($content,0,16);
                $key_r=substr($content,strlen($content)-16,16);
                $auth_all_list=json_decode(openssl_decrypt(base64_decode($string_content),"AES-128-CBC",$key_r,OPENSSL_RAW_DATA,$iv_r),true);
                if(!isset($auth_all_list['code'])){
                    if($no_out){
                        return false;
                    }
                    header("content-type:text/html;charset=utf-8");
                    exit("权限校验失败- code false");
                }
                $code=$auth_all_list['code'];
            }
            if($code==''){
                if($no_out){
                    return false;
                }
                header("content-type:text/html;charset=utf-8");
                exit("权限校验失败- no code");
            }

            $url = "https://osxbe.demo.opensns.cn/auth/Index/getAuthCode";

            $iv = "".mt_rand(10000000,99999999).mt_rand(10000000,99999999);//生成16位的 向量
            $strs="ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz";
            $key=substr(str_shuffle($strs),mt_rand(0,strlen($strs)-17),16);//生成16位的 默认秘钥
            $code_str=openssl_encrypt($code.time(),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv);
            $code_str=base64_encode($iv.$code_str.$key);
            $a=db('user')->where(['status'=>1])->count();
            $version_file=ROOT.'/version.php';
            if(!is_file($version_file)){
                $version='no-found';
            }else{
                $version=file_get_contents($version_file);
            }
            $params = http_build_query(array("code"=>$code_str,'a'=>$a,'b'=>base64_encode(get_domain()),'v'=>base64_encode($version)));

            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ( $ch, CURLOPT_POST, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );

            $result = curl_exec ( $ch );
            $result=json_decode($result,true);
            curl_close ( $ch );
        } else {
            if($no_out){
                return false;
            }
            header("content-type:text/html;charset=utf-8");
            echo('汗！貌似您的服务器尚未开启curl扩展，无法连接想天软件进行授权校验，请联系您的主机商开启，本地调试请无视');
            exit;
        }
        if($result['code']==200){
            /**
             * 请求端start   获得input_content给请求端，写入文件中
             */
            $content=$result['data']['auth_code'];
            $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
            $res=file_put_contents($auth_file_path,$content);
            //dump($this->_dealAuthContent($content));
            if($res){
                Cache::rm($this->official_auth_list);
                SystemConfig::setValue('client_local_storage_version',time_format(time(),'Y-m-d-H-i'));
            }
            /**
             * 请求端end   获得input_content给请求端，写入文件中完成，然后清除相关缓存。
             */

            if($no_out){
                return $content;
            }
            header("content-type:text/html;charset=utf-8");
            exit("已获得来自 想天软件 的权限校验");
        }else{
            if($no_out){
                return false;
            }
            header("content-type:text/html;charset=utf-8");
            exit("权限校验失败-end  ".$result['msg']);
        }
    }

    /**
     * 获取隐藏菜单列表
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    protected function _getHideAdminMenu()
    {
        $auth_all_list=Cache::get($this->official_auth_list);
        if(!$auth_all_list){
            $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
            $content=file_get_contents($auth_file_path);
            $auth_all_list=$this->_dealAuthContent($content);
            Cache::set($this->official_auth_list,$auth_all_list,600);
        }
        $auth_all_list=json_decode($auth_all_list,true);
        return isset($auth_all_list['hide_menu'])?$auth_all_list['hide_menu']:[];
    }
    /**
     * 获取权限是否显示红色部分权限提醒
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    protected function _getShowTipPageList()
    {
        $auth_all_list=Cache::get($this->official_auth_list);
        if(!$auth_all_list){
            $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
            $content=file_get_contents($auth_file_path);
            $auth_all_list=$this->_dealAuthContent($content);
            Cache::set($this->official_auth_list,$auth_all_list,600);
        }
        $auth_all_list=json_decode($auth_all_list,true);
        return isset($auth_all_list['show_tip_page_list'])?$auth_all_list['show_tip_page_list']:[];
    }
    /**
     * 获取权限是否显示红色部分权限到期的提醒
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    protected function _getEndShowTipPageList()
    {
        $auth_all_list=Cache::get($this->official_auth_list);
        if(!$auth_all_list){
            $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
            $content=file_get_contents($auth_file_path);
            $auth_all_list=$this->_dealAuthContent($content);
            Cache::set($this->official_auth_list,$auth_all_list,600);
        }
        $end_show_tip_page_list=[];
        $auth_all_list=json_decode($auth_all_list,true);
        if(array_key_exists('end_show_tip_page_list',$auth_all_list)){
            foreach ($auth_all_list['end_show_tip_page_list'] as &$v){
                if($v['end_time']<time()){
                    $end_show_tip_page_list[]=$v['menus'];
                }
            }
            unset($v);
        }
        return $end_show_tip_page_list;
    }
    /**
     * 获取是否提供用户信息
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    protected function _getIsRegister()
    {
        $auth_all_list=Cache::get($this->official_auth_list);
        if(!$auth_all_list){
            $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
            $content=file_get_contents($auth_file_path);
            $auth_all_list=$this->_dealAuthContent($content);
            Cache::set($this->official_auth_list,$auth_all_list,600);
        }
        $auth_all_list=json_decode($auth_all_list,true);
        return isset($auth_all_list['is_register'])?$auth_all_list['is_register']:[];
    }
    /**
     * 获取网站信息
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    protected function _getSitOrder()
    {
        $auth_all_list=Cache::get($this->official_auth_list);
        if(!$auth_all_list){
            $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
            $content=file_get_contents($auth_file_path);
            $auth_all_list=$this->_dealAuthContent($content);
            Cache::set($this->official_auth_list,$auth_all_list,600);
        }
        $auth_all_list=json_decode($auth_all_list,true);
        return isset($auth_all_list['site_order'])?$auth_all_list['site_order']:'';
    }
    /**
     * 获取网站平台权限
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    protected function _getPlatformOrder()
    {
        $auth_all_list=Cache::get($this->official_auth_list);
        if(!$auth_all_list){
            $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
            $content=file_get_contents($auth_file_path);
            $auth_all_list=$this->_dealAuthContent($content);
            Cache::set($this->official_auth_list,$auth_all_list,600);
        }
        $auth_all_list=json_decode($auth_all_list,true);
        return isset($auth_all_list['platform'])?explode(',',$auth_all_list['platform']):[];
    }
    /**
     * 获取客户端模块、拓展开放列表
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    protected function _getClientOpenList()
    {
        $client_open_list=[
//            'certification',//认证
//            'shop',//积分商城
//            'tencent_video',//腾讯视频
        ];

        $auth_all_list=Cache::get($this->official_auth_list);
        if(!$auth_all_list){
            $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
            $content=file_get_contents($auth_file_path);
            $auth_all_list=$this->_dealAuthContent($content);
            Cache::set($this->official_auth_list,$auth_all_list,600);
        }
        $auth_all_list=json_decode($auth_all_list,true);
        if(array_key_exists('client_open_list',$auth_all_list)) {
            foreach ($auth_all_list['client_open_list'] as $key => $v) {
                if(is_array($v)){
                    if ($v['end_time'] > time()) {
                        $client_open_list[] = $key;
                    }
                }
            }
        }
        unset($v);
        return $client_open_list;
    }

    /**
     * 获取授权中链接选择器授权列表
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    protected function _getSelectTabOpenList(){
        //链接选择器授权列表默认值，授权码中没该字段时显示该值
        $select_tab_open_list=[
            'os',//社区
            'eb',//商城
            'zg',//知识商城
            'my_page',//我的页面
            'message',//消息中心
            'renzheng',//认证中心
            'renwu',//任务中心
            'tuiguang',//推广中心(分销)
            'shop',//积分商城
            'defined',//装修页面
            'other',//站外链接
        ];

        $auth_all_list=Cache::get($this->official_auth_list);
        if(!$auth_all_list){
            $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
            $content=file_get_contents($auth_file_path);
            $auth_all_list=$this->_dealAuthContent($content);
            Cache::set($this->official_auth_list,$auth_all_list,600);
        }
        $auth_all_list=json_decode($auth_all_list,true);
        return isset($auth_all_list['select_tab_open_list'])?$auth_all_list['select_tab_open_list']:$select_tab_open_list;
    }

    /**
     * 处理加密文件内容
     * @param $content
     * @return string
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private function _dealAuthContent($content)
    {
        $content=base64_decode($content);
        $string_content=substr($content,16,strlen($content)-32);
        $iv_r=substr($content,0,16);
        $key_r=substr($content,strlen($content)-16,16);
        $auth_all_list=openssl_decrypt(base64_decode($string_content),"AES-128-CBC",$key_r,OPENSSL_RAW_DATA,$iv_r);
        return $auth_all_list;
    }

    protected  function _getCode($no_out=0){
        $auth_file_path=UPLOAD_PATH.'/'.md5('give_auth').'.md5';
        if(!is_file($auth_file_path)){
            if($no_out){
                return false;
            }
            header("content-type:text/html;charset=utf-8");
            exit("权限校验失败-file not exit");
        }
        $content=file_get_contents($auth_file_path);
        $content=base64_decode($content);
        $string_content=substr($content,16,strlen($content)-32);
        $iv_r=substr($content,0,16);
        $key_r=substr($content,strlen($content)-16,16);
        $auth_all_list=json_decode(openssl_decrypt(base64_decode($string_content),"AES-128-CBC",$key_r,OPENSSL_RAW_DATA,$iv_r),true);
        if(!isset($auth_all_list['code'])){
            if($no_out){
                return false;
            }
            header("content-type:text/html;charset=utf-8");
            exit("权限校验失败- code false");
        }
        $code=$auth_all_list['code'];
        return $code;
    }

    /**
     * 获取access_token
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.13
     */
    public function get_access_token(){
        //域名
        $url=get_domain();
//        $appid='xiangtian';
//        $appselect='xiangtian2020';
        $plate=$this->_getPlatformOrder();
        $data=[];
        $iv = "1234567890123412";//16位 向量
        $key= '201707eggplant99';//16位 默认秘钥
        foreach ($plate as $v){
            $code=$url.'|'.$v;
            $data[$v]=trim(base64_encode(openssl_encrypt(base64_encode($code),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv)));
        }
        unset($v);
        return $data;
    }

    /**
     * 获取access_token是否有效
     * @param $token
     * @return bool
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.13
     */
    public function decrypt_access_token($token='qxgt+WgUsARnawZqp83JX6LFuwDmDLKG2ciSCaLePayiBbBbIfCVl4usV+xJz6QV1r5EJgki/dLzgS03adgvpcY8ocIzpZC/LkfvtMix/LE='){
        $plate=$this->_getPlatformOrder();
        $iv = "1234567890123412";//16位 向量
        $key= '201707eggplant99';//16位 默认秘钥
        if(empty($token)) return false;
        $access_token=trim(base64_decode(openssl_decrypt(base64_decode($token),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv)));
        $access=explode('|',$access_token);
        $this->access = $access;
        if($access[0]==get_domain()&&in_array($access[1],$plate)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 判断平台的token值
     * @return bool
     * @author zxh  zxh@ourstu.com
     */
    public function check_platform(){
        //特殊的接口不需要判断
        $module_name=$this->request->module();
        $controller_name=$module_name.'/'.$this->request->controller();
        $action_name=$controller_name.'/'.$this->request->action();
        //特殊接口集合
        $all_special=[
            'admin',
            'auth',
            'osapi/weixin',
            'commonapi/user',
            'osapi/base/giveauth',
            'ebapi/auth_api/time_out_order',
            'ebapi/authapi/time_out_order',
            'shareapi/order/giveorderback',
            'ebapi/auth_api/user_message_order',
            'ebapi/authapi/user_message_order',
            'shareapi/order/sendmessage',
            'commonapi/index/toendimg',
            'commonapi/system/countdata',
            'commonapi/script/new_thread',
            'commonapi/rank/threadrank',
            'commonapi/rank/topicrank',
            'commonapi/rank/userrank',
            'commonapi/script/set_hot_topic',
            'commonapi/index/createsignature',
            'frameweb/index/frameweb',
            'commonapi/user/renotify',
            'ebapi/auth_api/notify',
            'ebapi/authapi/notify',
            'ebapi/authapi/website_paysuccess',
            //脚本
            'commonapi/script'
        ];

        if(in_array(strtolower($module_name),$all_special)||in_array(strtolower($controller_name),$all_special)||in_array(strtolower($action_name),$all_special)){
            return true;
        }
        //特殊end
        // 判断平台
        $token = self::getAllHeaders();
        if(array_key_exists('Platform-Token',$token)){
            if(!self::decrypt_access_token($token['Platform-Token'])){
                $this->error('Platform-Token错误！');
            }
        }else{
            $this->error('Platform-Token错误！');
        }
        return true;
    }

    /**
     * 判断商业扩展插件是否有权限（部分自有后端部分的通过该方式判断，正常前端做判断）
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private function _check_extend_auth()
    {
        $module_name=$this->request->module();
        $controller_name=$module_name.'/'.$this->request->controller();

        //校验第三方平台接入是否授权
        if(strtolower($controller_name)=='commomapi/user'){
            $clientOpenList=$this->_getClientOpenList();
            if(!in_array('website_connect',$clientOpenList)){
                $this->error('该功能为商业付费扩展，请联系官方客户经理开通！');
            }
        }
        //校验第三方平台接入是否授权 end

        return true;
    }

    function getAllHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * 获取官方模块
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.21
     */
    public function get_official_module(){
        $model=[
            'admin',
            'osapi',
            'commonapi',
            'ebapi',
            'shopapi',
            'shareapi'
        ];
        return $model;
    }
}