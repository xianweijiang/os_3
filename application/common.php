<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

require_once(APP_PATH . 'thumb.php');//图片裁剪功能
require_once(APP_PATH . 'user.php');//用户相关，如当前登录用户获取
require_once(APP_PATH . 'filter.php');//用户相关，如当前登录用户获取
/**
 * 敏感词过滤
 * @param  string
 * @return string
 */
function sensitive_words_filter($str)
{
    if (!$str) return '';
    $file = ROOT_PATH. PUBILC_PATH.'/static/plug/censorwords/CensorWords';
    $words = file($file);
    foreach($words as $word)
    {
        $word = str_replace(array("\r\n","\r","\n","/","<",">","="," "), '', $word);
        if (!$word) continue;

        $ret = preg_match("/$word/", $str, $match);
        if ($ret) {
            return $match[0];
        }
    }
    return '';
}

/**
 * 上传路径转化,默认路径 UPLOAD_PATH
 * $type 类型
 */
function makePathToUrl($path,$type = 2)
{
    $path =  DS.ltrim(rtrim($path));
    switch ($type){
        case 1:
            $path .= DS.date('Y');
            break;
        case 2:
            $path .=  DS.date('Y').DS.date('m');
            break;
        case 3:
            $path .=  DS.date('Y').DS.date('m').DS.date('d');
            break;
    }
    if (is_dir(ROOT_PATH.UPLOAD_PATH.$path) == true || mkdir(ROOT_PATH.UPLOAD_PATH.$path, 0777, true) == true) {
        return trim(str_replace(DS, '/',UPLOAD_PATH.$path),'.');
    }else return '';

}

// 过滤掉emoji表情
function filterEmoji($str)
{
    $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);
    return $str;
}

//可逆加密
function encrypt($data, $key) {
    $prep_code = serialize($data);
    $block = mcrypt_get_block_size('des', 'ecb');
    if (($pad = $block - (strlen($prep_code) % $block)) < $block) {
        $prep_code .= str_repeat(chr($pad), $pad);
    }
    $encrypt = mcrypt_encrypt(MCRYPT_DES, $key, $prep_code, MCRYPT_MODE_ECB);
    return base64_encode($encrypt);
}

//可逆解密
function decrypt($str, $key) {
    $str = base64_decode($str);
    $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = ord($str[($len = strlen($str)) - 1]);
    if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) {
        $str = substr($str, 0, strlen($str) - $pad);
    }
    return unserialize($str);
}
//替换一部分字符
/**
 * @param $string 需要替换的字符串
 * @param $start 开始的保留几位
 * @param $end 最后保留几位
 * @return string
 */
function strReplace($string,$start,$end)
{
    $strlen = mb_strlen($string, 'UTF-8');//获取字符串长度
    $firstStr = mb_substr($string, 0, $start,'UTF-8');//获取第一位
    $lastStr = mb_substr($string, -1, $end, 'UTF-8');//获取最后一位
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($string, 'utf-8') -1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;

}

/**
 * 创建随机数
 * @param int $length
 * @param string $type
 * @return string
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function create_rand($length = 8, $type = 'all')
{
    $num = '0123456789';
    $letter = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ($type == 'num') {
        $chars = $num;
    } elseif ($type == 'letter') {
        $chars = $letter;
    } else {
        $chars = $letter . $num;
    }

    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $str;
}

/**
 * 获取帖子类型标识对应的中文描述
 * @param $index
 * @return mixed
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function get_thread_type($index){
    $index_array=[
        1=>'普通版面',
        2=>'微博',
        3=>'朋友圈',
        4=>'资讯',
        5=>'活动',
        6=>'视频横版（PGC为主）',
        7=>'视频竖版（UGC为主）',
        8=>'公告'
    ];
    if(!in_array($index,array_keys($index_array))){
        return false;
    }
    return $index_array[$index];
}


// 获取客户端IP地址
function get_client_ip() {
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos)
            unset($arr[$pos]);
        $ip = trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
    return $ip;
}

//对emoji表情转义
function emoji_encode($str){
    $strEncode = '';
    $length = mb_strlen($str,'utf-8');
    for ($i=0; $i < $length; $i++) {
        $_tmpStr = mb_substr($str,$i,1,'utf-8');
        if(strlen($_tmpStr) >= 4){
            $strEncode .= '[[emjoin:'.rawurlencode($_tmpStr).']]';
        }else{
            $strEncode .= $_tmpStr;
        }
    }
    return $strEncode;
}
//对emoji表情反转义
function emoji_decode($str){
    $strDecode = preg_replace_callback('|\[\[emjoin:(.*?)\]\]|', function($matches){
        return rawurldecode($matches[1]);
    }, $str);

    return $strDecode;
}

/**
 * 是否是分销商
 * @param $uid
 * @return int|mixed
 * @author 郑钟良(zzl@ourstu.com)
 * @date 2019-7
 */
function is_seller($uid){
    if($uid==0){
        return 0;
    }
    $tag='IS_SELLER_'.$uid;
    $is_seller=cache($tag);
    if($is_seller===false){
        $seller_info=db('sell')->where(['uid'=>$uid,'status'=>1])->find();
        if($seller_info){
            $is_seller=1;
        }else{
            $is_seller=0;
        }
        cache($tag,$is_seller);
    }
    return $is_seller;
}

/**
 * 获取一级分销返利金额
 * @param $strip_num
 * @param null $father1
 * @return bool|float|int|mixed
 * @author 郑钟良(zzl@ourstu.com)
 * @date 2019-7
 */
function get_seller_back_num($strip_num,$father1=null){
    if($strip_num==0){
        return 0;
    }
    if($father1){
        $uid=$father1;
    }else{
        $uid=get_uid();
    }
    if(!$uid){
        return 0;
    }
    $is_seller=is_seller($uid);
    if($is_seller){
        $first_back=\app\admin\model\system\SystemConfig::getValue('agent_yongjin_config');
        $first_back=(floatval($first_back)*$strip_num)/100;
        return $first_back;
    }
    return 0;
}

/**
 * 时间转成前台显示时间
 * @param $time
 * @return false|string
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function time_to_show($time)
{
    if(intval($time)!=$time||intval($time)==0){
        return $time==0?'':$time;
    }
    $time=intval($time);
    $now_time=time();
    if($time>$now_time){//未来时间
        return time_format($time,'Y-m-d H:i');
    }
    if($now_time-$time<60){
        return '刚刚';
    }
    if($now_time-$time<3600){
        $long=$now_time-$time;
        $minute=intval($long/60);
        return $minute.'分钟前';
    }
    if($now_time-$time<24*3600){
        $long=$now_time-$time;
        $minute=intval($long/3600);
        return $minute.'小时前';
    }
    if($time>strtotime("yesterday")){
        return '昨天'.date('H:i',$time);
    }
    if($time>strtotime("1/1 this year")){
        return date('m-d H:i',$time);
    }
    return date('Y-m-d H:i',$time);
}

/**
 * 格式化时间戳
 * @param $time
 * @param string $format
 * @return false|string
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function time_format($time,$format='Y-m-d H:i'){
    return date($format,$time);
}

/**
 * 是否显示认证图标
 * @param string prefix 是否有前缀
 * @return string 要调取的icon字段
 */
function is_icon($prefix)
{
    $is_certification_icon = \app\admin\model\system\SystemConfig::getValue('is_certification_icon');
    $result='icon,';
    if (!$is_certification_icon) {
        $result='';
    }else{
        if ($prefix) {
            $result=$prefix.$result;
        }
    }
    return $result;
}

/**
 * 链接选择器选出的url转换成前台使用的url
 * @param $link_url 转换前的格式为： 说明||链接选择器选择地址（社区-社区首页||/pages/index/index）
 * @return array
 * @author 郑钟良(zzl@ourstu.com)
 * @date 2019-7
 */
function link_select_url($link_url)
{
    $link_url=explode('||',$link_url);
    !isset($link_url[1])&&$link_url[1]=$link_url[0];
    !isset($link_url[2])&&$link_url[2]='';
    return ['title'=>$link_url[0],'url'=>$link_url[1],'target'=>$link_url[2]];
}

/**
 * 链接选择器选出的url转换成前台使用的url
 * @param $link_url 转换前的格式为： 说明||链接选择器选择地址（社区-社区首页||/pages/index/index）
 * @return array
 * @author 郑钟良(zzl@ourstu.com)
 * @date 2019-7
 * @author jiangxw
 */
function link_select_url_nyb($link_url)
{
    $domain = config('front_url');
    $link_url=explode('||',$link_url);
    $url = '';
    isset($link_url[1])&&($link_url[1]!=""&&$url=$domain.$link_url[1]);
    return $url;
}

function census($action,$num){
    \app\osapi\model\com\CommunityCount::census($action,$num);
}


/**
 * 判断用户组是否有权限
 * @param $action
 * @param $uid
 * @param $pam
 * @author zxh  zxh@ourstu.com
 *时间：2020.3.31
 */
function action_power($action,$uid,$pam=''){
    return \app\commonapi\controller\Power::action_power($action,$uid,$pam);
}

/**
 * 判断版块是否有权限
 * @param $action
 * @param $uid
 * @param $pam
 * @author zxh  zxh@ourstu.com
 *时间：2020.3.31
 */
function forum_power($action,$uid,$pam=''){
    return \app\commonapi\controller\Power::forum_power($action,$uid,$pam);
}

/**
 * 判断版块是否有权限（返回报错）
 * @param $action
 * @param $uid
 * @param $pam
 * @author zxh  zxh@ourstu.com
 *时间：2020.3.31
 */
function forum_power_error($action,$uid,$pam=''){
    return \app\commonapi\controller\Power::forum_power_error($action,$uid,$pam);
}


/**
 * 发起用户行为第三方事件通知。
 * 新增加时间通知钩子时，请补充文档
 * http://oa.xiangtian.ren/index.php?mod=corpus&op=list&cid=58#fid_614
 * @param $uid 操作用户id
 * @param $to_id 操作对象id，如帖子id
 * @param $to_uid 操作对象所属uid
 * @param $action 行为标识
 * @return bool
 * @author 郑钟良(zzl@ourstu.com)
 * @date 2019-7
 */
function website_connect_notify($uid,$to_id,$to_uid,$action)
{
    return \app\commonapi\model\WebsiteConnect::userActionNotify($uid,$to_id,$to_uid,$action);
}


/**
 * 权限限制,一段时间内同一用户触发次数
 * @param $uid
 * @param $action
 * @return bool|void
 * @author zxh  zxh@ourstu.com
 *时间：2020.4.26
 */
function action_limit($action,$uid){
    return \app\commonapi\model\action\ActionLimit::action_limit($uid,$action);
}

/**
 * 权限限制,写入次数一次
 * @param $uid
 * @param $action
 * @return bool|void
 * @author zxh  zxh@ourstu.com
 *时间：2020.4.26
 */
function write_action_limit($action,$uid){
    return \app\commonapi\model\action\ActionLimit::write_action_limit($uid,$action);
}

/**
 * 编辑器内容过滤
 * @param $content
 * @return mixed|string
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function edit_filter($content)
{
    $content = html($content);
    $content = preg_replace('/\<embed[^>]*?src=\"[^>]*?\.swf[^>]*?\>/', '', $content);;
    $content = filter_base64($content);
    //检测图片src是否为图片并进行过滤
    $content = filter_image($content);
    $content = htmlspecialchars($content);
    return $content;
}

/**
 * 数据库取出来的内容转化为前台展示的内容
 * @param $content
 * @return string
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function content_show($content){
    return html(htmlspecialchars_decode(text($content)));
}

/**
 * 数据库取出来的简介转化为前台展示的简介
 * @param $summary
 * @return string
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function summary_show($summary){
    $summary = htmlspecialchars_decode(text($summary));  //帖子不获取内容，只获取摘要，将编码转化为标签
    $summary = strip_tags($summary, '<p></p><br><span></span>'); //只保留部分标签
    return $summary;
}