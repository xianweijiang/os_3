<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/24
 * Time: 17:32
 */

function modC($menu_name,  $default = ''){
    $tag = $menu_name.'_SystemConfig';
    $result = cache($tag);
    if($result === false){
        $config = \app\admin\model\system\SystemConfig::getValue($menu_name);
        if($config == null){
            $result = $default;
        }else{
            $result = $config;
            cache($tag,$result,600);
        }
    }
    return $result;
}

/**
 * 判断是否是post提交
 * @return mixed
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function is_post()
{
    return request()->post();
}

function phone_str($phone){
    $phone=substr_replace($phone,'xxxx',3,4);
    return $phone;
}

/**
 * 系统非常规MD5加密方法
 * @param $str
 * @param string $key
 * @return string
 * @author:wdx(wdx@ourstu.com)
 */
function think_ucenter_md5($str, $key = 'ThinkUCenter')
{
    return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key 加密密钥
 * @param int $expire 过期时间 (单位:秒)
 * @return string
 */
function think_ucenter_encrypt($data, $key, $expire = 0)
{
    $key = md5($key);
    $data = base64_encode($data);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time() : 0);
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return str_replace('=', '', base64_encode($str));
}

/**
 * 系统解密方法
 * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key 加密密钥
 * @return string
 */
function think_ucenter_decrypt($data, $key)
{
    $key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);
    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}


/**
 * 获取全球唯一标识
 * @return string
 */
function uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * 大数值展示处理
 * @param $num
 * @return int|string
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function big_num_show($num)
{
    $num=intval($num);
    if($num/10000>=1){
        $num_str=number_format($num*1.0/10000, 2).'W';
    }elseif ($num/1000>=1){
        $num_str=number_format($num*1.0/1000, 2).'K';
    }else{
        $num_str=$num;
    }
    return $num_str;
}


/**
 * 暂时不知道干嘛
 * @param $proArr
 * @return int|string
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function get_rand($proArr)
{
    $result = '';

    //概率数组的总概率精度
    $proSum = array_sum($proArr);
    //概率数组循环
    foreach ($proArr as $key => $proCur) {
        $randNum = mt_rand(1, $proSum);
        if ($randNum <= $proCur) {
            $result = $key;
            break;
        } else {
            $proSum -= $proCur;
        }
    }
    unset ($proArr);
    return $result;
}

/**
 * 添加行为日志
 * @param $uid
 * @param $action 最好传行为标识，方便后续统一管理行为类型
 * @param $content
 * @param string $model
 * @param string $row
 * @return bool|int|string
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function action_log($uid,$action,$content,$model='',$row='')
{
    $res=\app\osapi\model\common\ActionLog::addActionLog($uid,$action,$content,$model,$row);
    return $res;
}


function check_html_tags($content, $tags = array())
{
    $tags = is_array($tags) ? $tags : array($tags);
    if (empty($tags)) {
        $tags = array('script', '!DOCTYPE', 'meta', 'html', 'head', 'title', 'body', 'base', 'basefont', 'noscript', 'applet', 'object', 'param', 'style', 'frame', 'frameset', 'noframes', 'iframe');
    }
    foreach ($tags as $v) {
        $res = strpos($content, '<' . $v);
        if (!is_bool($res)) {
            return true;
        }
    }
    return false;
}

function filter_base64($content)
{
    preg_match_all("/data:.*?,(.*?)\"/", $content, $arr); //匹配base64编码
    if ($arr[1]) {
        foreach ($arr[1] as $v) {
            $base64_decode = base64_decode($v);
            $check = check_html_tags($base64_decode);
            if ($check) {
                $content = str_replace($v, '', $content);
            }
        }
    }
    return $content;
}

function curl_get_headers($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $f = curl_exec($ch);
    curl_close($ch);
    $h = explode("\n", $f);
    $r = array();
    foreach ($h as $t) {
        $rr = explode(":", $t, 2);
        if (count($rr) == 2) {
            $r[$rr[0]] = trim($rr[1]);
        }
    }
    return $r;
}

function check_image_src($file_path)
{
    if (!is_bool(strpos($file_path, 'http://'))) {
        $header = curl_get_headers($file_path);
        $res = strpos($header['Content-Type'], 'image/');
        return is_bool($res) ? false : true;
    } else {
        return true;
    }
}

function filter_image($content)
{
    preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/", $content, $arr); //匹配所有的图片
    if ($arr[1]) {
        foreach ($arr[1] as $v) {
            $check = check_image_src($v);
            if (!$check) {
                $content = str_replace($v, '', $content);
            }
        }
    }
    return $content;
}


