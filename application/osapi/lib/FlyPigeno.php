<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/23
 * Time: 16:30
 */

namespace app\osapi\lib;


use app\admin\model\system\SystemConfig;
use app\osapi\model\user\UserVerify;

/**
 * 短信验证码
 * Class FlyPigeno
 * @package app\common\lib
 */
class FlyPigeno
{
    /**
     * 发送短信验证码
     * @param $mobile
     * @param $content
     * @param int $type_other
     * @return bool|string
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function sendSms($mobile, $content,$type_other = 1){
        $config = SystemConfig::getMore('sms_id,sms_password,sms_sign_id,sms_template_id,sms_back_password_template_id,sms_repost_time');

        $data['Account'] = $config['sms_id'];
        $data['Pwd'] 	 = $config['sms_password'];
        $data['Mobile']	 = $mobile;
        $data['SignId']	 = $config['sms_sign_id'];
        $verify = UserVerify::getValue($mobile);

        if($config['sms_template_id'] && $type_other == 1){
            $data['TemplateId'] = $config['sms_template_id'];
            $data['Content'] = $verify . '||' . $mobile;
            $url="http://api.feige.ee/SmsService/Template";
        }elseif($config['sms_back_password_template_id'] && $type_other == 2){
            $data['TemplateId'] = $config['sms_back_password_template_id'];
            $data['Content'] = $verify . '||' . $mobile;
            $url="http://api.feige.ee/SmsService/Template";
        }
        $res=self::post($url, $data);
        return $res;
    }

    private static function post($url, $data, $proxy = null, $timeout = 20){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); //在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        curl_setopt($curl, CURLOPT_POST, true); //发送一个常规的Post请求
        curl_setopt($curl,  CURLOPT_POSTFIELDS, $data);//Post提交的数据包
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数。
        $content = curl_exec($curl);
        curl_close($curl);
        unset($curl);
        $res = json_decode($content,true);
        if($res['Code'] == '0') {
            return true;
        } else {
            return "发送失败! 状态：" . $res['Code'] .' '. self::getCode($res['Code']);
        }
    }

    private static function getCode($code){
        switch($code){
            case 10001: return '账户为空';
            case 10002: return '密码为空';
            case 10004: return '短信内容为空';
            case 10005: return '短信号码为空';
            case 10006: return '短信号码有误';
            case 10007: return '短信号码个数有误';
            case 10008: return '短信模板为空';
            case 10009: return '未找到匹配的模板ID';
            case 10010: return '所传参数与模板不匹配';
            case 10011: return '暂未状态报告';
            case 10012: return '暂无上行';
            case 10013: return '短信ID不能为空';
            case 10014: return '短信ID无效';
            case 10015: return '短信ID错误';
            case 10016: return '手机号码不在此ID中';
            case 10017: return '没有签名ID';
            case 10018: return '签名ID有误';
            case 10019: return '错误的时间戳';
            case 20001: return '用户名或密码错误';
            case 20002: return '账号被停用，请联系飞鸽传书客服！';
            case 20003: return '账号被注销，请联系客服！';
            case 20004: return 'IP地址鉴权失败，请联系客服！';
            case 20005: return '账户余额不足，请联系客服！';
            case -99: return '通道屏蔽字';
            case -999: return 'IP黑名单';
            case -9999: return '系统错误';
            default : return '未知参数';
        }
    }
}