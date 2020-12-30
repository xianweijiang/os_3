<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/24
 * Time: 16:04
 */

namespace app\osapi\model\user;

use app\commonapi\model\Gong;
use app\osapi\controller\weixin;
use app\osapi\model\BaseModel;
use app\admin\model\system\SystemConfig;
use app\core\util\TokenService;
use app\wap\model\user\WechatUser;
use service\WechatTemplateService;
use think\Cache;
class UserModel extends BaseModel
{

    /*
     * 说明：手机注册账号，账号和手机号相同
     *          todo 修改手机号时，账号要跟随变动
     *          todo 微信同步登陆时，账号要带微信标识“_weixin”,防止微信号和手机号相同而冲突
     *          todo 微信登录用户，绑定手机号时，账号要变成手机号
     */

    protected $table=DATABASE_PREFIX.'user';
    protected function initialize()
    {
        parent::initialize();

        $this->insert=[
            'status'=>1,
            'add_time'=>time(),
            'now_money'=>0,
            'integral'=>0,
            'sign_num'=>0
        ];
    }

    /**
     * 检测手机号是否已经注册过
     * @param $phone
     * @return array|false|\PDOStatement|string|\think\Model
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf\
     */
    public static function checkExist($phone)
    {
        return self::getUserByPhone($phone);
    }

    /**
     * 快捷登陆
     */
    public static function quickLogin($aAccount)
    {
        $uid=db('user')->where('phone',$aAccount)->where('status',1)->find();
        if(!$uid){
            return false;
        }else{
            $result = self::doQuickLogin($uid['uid']); //状态码是1时候登录成功
            if ($result['status'] == 1) {
                $user_token = $result['token'];
                $data['token'] = $user_token;
                $data['token_time'] = $result['token_time'];
                $data['user'] = $result['user'];
                return $data;
            } else {
                return false;
            }
        }
    }

    /**
     * 注册
     * @param $account
     * @param $nickname
     * @param $password
     * @param string $from
     * @param string $type
     * @return int|string
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function register($account, $nickname, $password, $from='osx', $type='phone',$is_password=0)
    {
        /*
         * 说明：手机注册账号，账号和手机号相同
         *          todo 修改手机号时，账号要跟随变动
         *          todo 微信同步登陆时，账号要带微信标识“_weixin”,防止微信号和手机号相同而冲突
         *          todo 微信登录用户，绑定手机号时，账号要变成手机号
         */
        if($nickname==''){
            $nickname=$account;
        }
        $avatar=SystemConfig::getValue('default_avatar');
        if($avatar){
            $avatar=get_root_path($avatar);
        }else{
            $avatar='';
        }
        $request_ip=request()->ip();
        $data = [
            'account' => $account,
            'nickname'=>$nickname,
            'pwd' => $password,
            'phone' => $account,
            'add_ip'=>$request_ip,
            'avatar'=>$avatar,
            'is_password'=>$is_password
        ];
        self::update(['account' => $account.'_del','phone'=>$account.'_del'],['phone'=>$account,'status'=>array('neq',1)]);//重置其他账号已删除的同手机号账户
        /* 添加用户 */
        $uid = self::add($data);
        $map['uid']=$uid;
        $map['nickname']=$nickname;
        $map['avatar']=$avatar;
        $map['fans']=0;
        $map['last_fans']=0;
        $map['rank']=$uid;
        $map['new_fans']=0;
        $map['week_rank']=$uid;
        $map['status']=1;
        db('rank_user')->insert($map);
        if($uid){
            self::registerMark($uid,$from,$type);
        }
        return $uid ? $uid : 0;
    }

    public static function addSyncData($info)
    {
        $data['account'] = create_rand(6);
        $data['password'] = create_rand(6);
        $data['type'] = 2;  // 视作用邮箱注册
        $uid = self::create($data);
        //去除特殊字符。
        //$data['nickname'] = preg_replace('/[^A-Za-z0-9_\x80-\xff\s\']/', '', $info['nick']);
        $data['nickname'] = $info['nick'];
        // 截取字数
        //$data['nickname'] = mb_substr($data['nickname'], 0, 6, 'utf-8');
        // 为空则随机生成
        if (empty($data['nickname'])) {
            $data['nickname'] = self::rand_nickname();
        } else {
            $is_have=SystemConfig::getValue('wx_nickname');
            if($is_have==1){
                if (self::where(array('nickname' => $data['nickname']))->count()) {
                    $data['nickname'] .= '_' . $uid['uid'];
                }
            }
        }
        $data['avatar']=$info['head'];
        $data['sex'] = $info['sex'];
        if(!$data['avatar']){
            $avatar=SystemConfig::getValue('default_avatar');
            if($avatar){
                $avatar=get_domain().$avatar;
            }else{
                $avatar='';
            }
            $data['avatar']=$avatar;
        }
        $map['uid']=$uid['uid'];
        $map['nickname']=$data['nickname'];
        $map['avatar']=$data['avatar'];
        $map['fans']=0;
        $map['last_fans']=0;
        $map['rank']=$uid['uid'];
        $map['new_fans']=0;
        $map['week_rank']=$uid['uid'];
        $map['status']=1;
        db('rank_user')->insert($map);
        self::where('uid',$uid['uid'])->update($data);
        return $uid;
    }

    /**
     * 第三方对接初始化用户
     * @param $userInfo
     * @return $this
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private static function _addWebsiteData($userInfo)
    {
        $data['account'] = create_rand(6);
        $data['pwd'] = create_rand(10);
        $data['user_type'] = 'website_connect';  // 视作用邮箱注册
        $uid = self::create($data);

        // 为空则随机生成
        if (!isset($userInfo['nickname'])) {
            $userInfo['nickname'] = self::rand_nickname();
        }
        //去除特殊字符。
        //$data['nickname'] = preg_replace('/[^A-Za-z0-9_\x80-\xff\s\']/', '', $userInfo['nickname']);
        $data['nickname'] = $userInfo['nickname'];
        // 截取字数
        //$data['nickname'] = mb_substr($data['nickname'], 0, 6, 'utf-8');

        isset($userInfo['sex'])&&$data['sex'] = $userInfo['sex'];
        if(!isset($userInfo['avatar'])){
            $avatar=SystemConfig::getValue('default_avatar');
            if($avatar){
                $avatar=get_domain().$avatar;
            }else{
                $avatar='';
            }
            $data['avatar']=$avatar;
        }else{
            $data['avatar']=$userInfo['avatar'];
        }
        $map['uid']=$uid['uid'];
        $map['nickname']=$data['nickname'];
        $map['avatar']=$data['avatar'];
        $map['fans']=0;
        $map['last_fans']=0;
        $map['rank']=$uid['uid'];
        $map['new_fans']=0;
        $map['week_rank']=$uid['uid'];
        $map['status']=1;
        db('rank_user')->insert($map);
        self::where('uid',$uid['uid'])->update($data);
        return $uid['uid'];
    }

    private static function rand_nickname()
    {
        $nickname = create_rand(4);
        if (self::where(array('nickname' => $nickname))->count()) {
            self::rand_nickname();
        } else {
            return $nickname;
        }
    }

    public static function login($uid, $remember = false)
    {
        /* 检测是否在当前应用注册 */
        $user = self::get($uid);
        if(!$user){
            return -1;
        }
        /* 登录用户 */
        $res=self::autoLogin($user['uid']);
        $site_session = SystemConfig::getValue('site_session');
        if($site_session==0||$site_session==''){
            $site_session=1000000000;
        }
        if($res){
            if($remember){
                //todo 记住登录，可以考虑token生成过程中带上remember标识，同时cache内容存数据库
            }
            $token=db('os_token')->where('uid',$uid)->value('token');
            if(!$token){
                $token=uuid();
                $data['token']=$token;
                $data['uid']=$uid;
                $data['create_time']=time();
                db('os_token')->insert($data);
            }
            $res_login=cache($token, $uid, $site_session);
            $userInfo['uid']=$user['uid'];
            $userInfo['nickname']=$user['nickname'];
            $userInfo['avatar']=$user['avatar'];
            $userInfo['fans']=$user['fans'];
            $userInfo['follow']=$user['follow'];
            $userInfo['sex']=$user['sex'];
            $userInfo['birthday']=$user['birthday'];
            $userInfo['signature']=$user['signature'];
            $userInfo['post_count']=$user['post_count'];
            $userInfo['collect']=$user['collect'];
            if($res_login){
                $result['token'] = $token;
                $result['token_time'] = time()+$site_session;
                $result['user']=$userInfo;
                $result['success']=1;
                $result['uid']=$uid;
                self::actionsystem($uid);
                WechatTemplateService::sendTemplate(WechatUser::uidToOpenid($uid),WechatTemplateService::USER_LOGIN, [
                    'first'=>'您好，您的帐号'.$user['nickname'].'被登录',
                    'time'=>date("Y-m-d H:i:s",time()),
                    'ip'=>request()->ip(),
                    'remark'=>'如果本次登录不是您本人所为，说明您的帐号已经被盗！请去联系网站管理员！'
                ],'');
            }else{
                $result['success']=0;
                $result['info']='缓存用户信息失败';
            }
            return $result;
        }else{
            $result['success']=0;
            $result['info']='修改用户最后登录信息失败';
        }
        return $result;
    }

    public static function actionsystem($uid)
    {
        //登录加积分

        $data = [
            'uid' => $uid,
            'create_time' => time()
        ] ;
        db('user_login_log')->insert($data) ;

        Gong::actionadd('meiridenglu','user_login_log','uid',$uid);
    }

    /**
     * 注册失败，注销账号
     * @param $uid
     * @param int $status
     * @return false|int
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function setRegFalse($uid,$status=-2)
    {
        return self::update(['status'=>$status],['uid'=>$uid]);
    }

    public static function doLogin($account,$pwd,$remember=false)
    {
        $check_phone = preg_match("/^(1[0-9])[0-9]{9}$/", $account, $match_phone);
        if(!$check_phone){
            $res['status'] = -3;
            $res['info']= '账号错误！';
        }else{
            $uid = self::checkPwd($account, $pwd);
            if (0 < $uid) { //账号密码验证成功
                /* 登录用户 */
                $result=self::login($uid, $remember);
                if ($result['success']==1) { //登录用户
                    $res['status']=1;
                    $res['uid'] = $uid;
                    $res['token']=$result['token'];
                    $res['token_time']=$result['token_time'];
                    $res['user']=$result['user'];
                } else {
                    $res['status'] = -4;
                    $res['info']=$result['info'];
                }
            } else { //登录失败
                switch ($uid) {
                    case -1:
                        $res['status'] = -1;
                        $res['info']= '用户不存在或被禁用！';
                        break; //系统级别禁用
                    case -2:
                        $res['status'] = -2;
                        $res['info']= '账号密码错误！';
                        break;
                    default:
                        $res['status'] = 0;
                        $res['info']= $uid;
                        break; // 0-接口参数错误（调试阶段使用）
                }
            }
        }
        return $res;
    }

    public static function doQuickLogin($uid,$remember=false)
    {
        if (0 < $uid) {
            /* 登录用户 */
            $result=self::login($uid, $remember);
            if ($result['success']==1) { //登录用户
                $res['status']=1;
                $res['uid'] = $uid;
                $res['token']=$result['token'];
                $res['token_time']=$result['token_time'];
                $res['user']=$result['user'];
            } else {
                $res['status'] = -4;
                $res['info']=$result['info'];
            }
        }
        return $res;
    }

    private static function checkPwd($account, $pwd)
    {
        $map['account'] = $account;
        $map['status'] = 1;
        /* 获取用户数据 */
        $model=self::getModel();

        $user=$model->visible(['uid','pwd','status'])->where($map)->find();
        if ($user && $user['status']) {
            /**
             * todo
             * 添加系统秘钥功能，秘钥存放在database.php中，每次安装随机一个新的
             */
            if (think_ucenter_md5($pwd) === $user['pwd']) {
                return $user['uid']; //登录成功，返回用户ID
            } else {
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    private static function registerMark($uid, $from='osx', $type='phone')
    {
        return db('user_register')->insert(array('uid' => $uid, 'from' => $from, 'type' => $type, 'status' => 1));
    }

    private static function autoLogin($uid)
    {
        /* 更新登录信息 */
        $data = [
            'last_time' => time(),
            'last_ip' => request()->ip()
        ];
        $res=self::update($data,['uid' => $uid]);
        self::where('uid',$uid)->setInc('login_time');
        return $res;
    }

    /**
     * 通过手机号获取用户
     * @param $phone
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getUserByPhone($phone)
    {
        $user=self::get(['phone'=> $phone,'status'=>1]);
        return $user;
    }

    /**
     * 修改用户头像路径
     * @param $path
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function setAvatar($path)
    {
        $uid=get_uid();
        $res=self::update(['avatar'=>$path],['uid'=>$uid]);
        if($res){
            return ['path'=>$path];
        }else{
            return false;
        }
    }

    /**
     * 根据用户uid获取用户信息
     * @param $uid
     * @return bool|null|static
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getUserInfo($uid,$fid='')
    {
        $user=Cache::get('user_info_'.$uid);
        if(!$user){
            $field='uid,nickname,avatar,sign_num,status,level,user_type,pay_count,fans,follow,sex,birthday,signature,total_sign,support_count,introduction,forum_count,post_count,exp,fly,gong,buy,one,two,three,four,five,is_collect,mark,icon,is_red,collect,cate_id';
            $user=self::where('uid',$uid)->where('status','>',0)->field($field)->find();
            if(!$user){
                return null;//用户不存在或被禁用、删除
            }else{
                if($user['avatar']){
                    $user['avatar']=get_root_path($user['avatar']);
                    $user['avatar_64']=thumb_path($user['avatar'],64,64);
                    $user['avatar_128']=thumb_path($user['avatar'],128,128);
                    $user['avatar_256']=thumb_path($user['avatar'],256,256);
                }
            }
            if($fid){
                $pid=db('com_forum')->where('id',$fid)->value('pid');
                $user['forum_admin_one']=db('com_forum_admin')->where('uid',$user['uid'])->where('status',1)->where('fid',$fid)->count();
                $user['forum_admin_two']=db('com_forum_admin')->where('uid',$user['uid'])->where('status',1)->where('fid',$pid)->count();
            }
            //认证图标
            $icon_field=is_icon('');
            if ($icon_field=='') {
                unset($user['icon']);
            }
            $user['rztx']='';
            if($user['cate_id']){
                $datum_data=db('certification_entity')->where('uid',$uid)->where('status',1)->where('cate_id',$user['cate_id'])->value('datum_data');
                $datum_data=unserialize($datum_data);
                if(!empty($datum_data['rztx'])){
                    $user['rztx']=$datum_data['rztx'];
                }
            }
            $user['grade'] = self::cacugrade($user['exp']) ;
            $tids=db('collect')->where(['uid'=>$uid,'status'=>1])->column('tid');
            $user['collect']=db('com_thread')->where(['id'=>['in',$tids],'status'=>1])->count();
            Cache::set('user_info_'.$uid,$user,60);
        }
        return $user;
    }

    /**
     * 获取最后注册用户信息
     * @return array|false|\PDOStatement|string|\think\Model
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getLastUser()
    {
        $user=self::where('status',1)->order('add_time desc')->field('uid,nickname')->find();
        return $user;
    }

    /**
     * 搜索用户
     */
    public static function searchUser($keyword,$page=1,$row=10){
        //认证图标
        $icon_field=is_icon('');
        $user=self::where('nickname','like','%'.$keyword.'%')->page($page, $row)->field('uid,nickname,avatar,sex,fans,follow,signature,'.$icon_field.'is_red')->order('add_time desc')->select();
//        $user['allCount']=self::where('nickname','like','%'.$keyword.'%')->count();
        return $user;
    }

    public static function cacugrade($exp){
        $experience = db('system_user_grade')->where('is_del',0)->order('experience','asc')->select();
        $count = count($experience) ;
        $exp = intval($exp) ;
        foreach ($experience as $key => $item) {

            if($exp < $item['experience'] && $key == 0){

                return [
                    'id'=>$experience[$key+1]['id'],
                    'name' => $experience[$key+1]['name'] ,
                    'grade' => 0   ,
                    'diff'  =>  $experience[$key]['experience'] - $exp,
                    'next' => $experience[$key]['experience'],
                    'icon' => $item['icon'],
                    'image' => $item['image'],
                ] ;
            }elseif($exp >= intval($item['experience']) && $count == $key + 1){
                //达到顶级了

                return [
                    'id'=>$experience[$key]['id'],
                    'name' => $experience[$key]['name'] ,
                    'grade' => $key   ,
                    'diff'  => 0,
                    'next' => $item['experience'],
                    'icon' => $item['icon'],
                    'image' => $item['image'],
                ];
            }elseif ($exp >= intval($item['experience']) && $exp < intval($experience[$key+1]['experience']) ) {
                if ($key+2 == $count) {
                    return [
                        'id'=>$experience[$key+1]['id'],
                        'name' => $experience[$key+1]['name'] ,
                        'grade' => $key   ,
                        'diff'  =>  $experience[$key+1]['experience'] - $exp,
                        'next' => $experience[$key+1]['experience'],
                        'icon' => $experience[$key+1]['icon'],
                        'image' => $experience[$key+1]['image'],
                        'now' => $experience[$key]['experience'],
                    ] ;
                } else {
                    return [
                        'id'=>$experience[$key+2]['id'],
                        'name' => $experience[$key+2]['name'] ,
                        'grade' => $key   ,
                        'diff'  =>  $experience[$key+1]['experience'] - $exp,
                        'next' => $experience[$key+1]['experience'],
                        'icon' => $experience[$key+1]['icon'],
                        'image' => $experience[$key+1]['image'],
                        'now' => $experience[$key]['experience'],
                    ] ;
                }
            }
        }
    }


    /**
     * 更新第三方接入用户的用户信息，无用户时创建用户
     * @param $data
     * @return int
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function updateWebsiteUserInfo($data)
    {
        $user_token=$data['user_token'];
        $has_user_uid=db('website_connect_token')->where('user_token',$user_token)->where('status',1)->value('uid');
        if(!$has_user_uid){//不存在用户
            $add_uid=self::_addWebsiteData($data['userInfo']);
            if($add_uid){
                $has_user_uid=$add_uid;

                $token_data['uid']=$add_uid;
                $token_data['user_token']=$user_token;
                $token_data['status']=1;
                $token_data['create_time']=time();
                $res=db('website_connect_token')->insertGetId($token_data);
                if(!$res){
                    self::where('uid',$add_uid)->update(['status'=>-1]);
                    db('rank_user')->where('uid',$add_uid)->delete();
                    return 0;
                }
            }else{
                return 0;
            }
        }
        $update_data=self::_buildeUpdateData($data['userInfo']);
        self::where('uid',$has_user_uid)->update($update_data);
        return $has_user_uid;
    }

    /**
     * 更新第三方接入用户的用户积分信息
     * @param $data
     * @return int
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function updateWebsiteUserScore($data)
    {
        $user_token=$data['user_token'];
        $has_user_uid=db('website_connect_token')->where('user_token',$user_token)->where('status',1)->value('uid');
        if(!$has_user_uid){
            return 0;
        }
        $update_data=self::_buildeUpdateDataScore($data['userInfo']);
        self::where('uid',$has_user_uid)->update($update_data);
        return $has_user_uid;
    }

    /**
     * 构造更新数据数组
     * @param $userInfo
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private static function _buildeUpdateDataScore($userInfo)
    {
        $userData=[];
        isset($userInfo['score1'])&&$userData['exp']=$userInfo['score1'];//exp，经验值。等级可由经验值换算，换算规则可在OSX短说后台配置
        isset($userInfo['score2'])&&$userData['fly']=$userInfo['score2'];//社区积分
        isset($userInfo['score3'])&&$userData['buy']=$userInfo['score3'];//购物积分
        isset($userInfo['score4'])&&$userData['gong']=$userInfo['score4'];//贡献值
        isset($userInfo['score5'])&&$userData['one']=$userInfo['score5'];//自定义积分类型1
        isset($userInfo['score6'])&&$userData['two']=$userInfo['score6'];//自定义积分类型2
        isset($userInfo['score7'])&&$userData['three']=$userInfo['score7'];//自定义积分类型3
        isset($userInfo['score8'])&&$userData['four']=$userInfo['score8'];//自定义积分类型4
        isset($userInfo['score9'])&&$userData['five']=$userInfo['score9'];//自定义积分类型5

        return $userData;
    }

    /**
     * 构造更新数据数组
     * @param $userInfo
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private static function _buildeUpdateData($userInfo)
    {
        $userData=[];
        isset($userInfo['nickname'])&&$userData['nickname']=$userInfo['nickname'];//用户昵称
        isset($userInfo['phone'])&&$userData['phone']=$userInfo['phone'];//用户手机号
        isset($userInfo['avatar'])&&$userData['avatar']=$userInfo['avatar'];//头像地址
        isset($userInfo['real_name'])&&$userData['real_name']=$userInfo['real_name'];//真实姓名
        isset($userInfo['sex'])&&$userData['sex']=$userInfo['sex'];//性别，0：保密，1：男，2：女
        isset($userInfo['birthday'])&&$userData['birthday']=$userInfo['birthday'];//生日，1970-01-01开始的时间戳，秒为单位
        isset($userInfo['qq'])&&$userData['qq']=$userInfo['qq'];//qq号
        isset($userInfo['signature'])&&$userData['signature']=$userInfo['signature'];//个性签名，个人心情、状态描述
        isset($userInfo['score1'])&&$userData['exp']=$userInfo['score1'];//exp，经验值。等级可由经验值换算，换算规则可在OSX短说后台配置
        isset($userInfo['score2'])&&$userData['fly']=$userInfo['score2'];//社区积分
        isset($userInfo['score3'])&&$userData['buy']=$userInfo['score3'];//购物积分
        isset($userInfo['score4'])&&$userData['gong']=$userInfo['score4'];//贡献值
        isset($userInfo['score5'])&&$userData['one']=$userInfo['score5'];//自定义积分类型1
        isset($userInfo['score6'])&&$userData['two']=$userInfo['score6'];//自定义积分类型2
        isset($userInfo['score7'])&&$userData['three']=$userInfo['score7'];//自定义积分类型3
        isset($userInfo['score8'])&&$userData['four']=$userInfo['score8'];//自定义积分类型4
        isset($userInfo['score9'])&&$userData['five']=$userInfo['score9'];//自定义积分类型5

        if(!isset($userData['nickname'])){
            $userData['nickname'] = self::rand_nickname();
        }
        //去除特殊字符。
        //$userData['nickname'] = preg_replace('/[^A-Za-z0-9_\x80-\xff\s\']/', '', $userData['nickname']);
        $userData['nickname'] = $userData['nickname'];
        // 截取字数
        //$userData['nickname'] = mb_substr($userData['nickname'], 0, 6, 'utf-8');
        if(!isset($userData['avatar'])||$userData['avatar']==''){
            $avatar=SystemConfig::getValue('default_avatar');
            if($avatar){
                $avatar=get_domain().$avatar;
            }else{
                $avatar='';
            }
            $userData['avatar']=$avatar;
        }

        return $userData;
    }



}