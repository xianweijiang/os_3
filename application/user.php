<?php
/**
 * OSX
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/19
 * Time: 10:43
 */



/**
 * 获取当前登录的用户uid
 * @return int|mixed
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function get_uid()
{
    $token = request()->server('HTTP_ACCESS_TOKEN');
    if($token == '' || $token == null){
        $token = request()->post('token');
    }
    if($token==''){
        return 0;
    }
    $login_uid=cache($token);
    if(!$login_uid){//数据库判断token用户是否存在
        $login_uid=db('os_token')->where('token',$token)->value('uid');
        if(!$login_uid){
            return 0;
        }
        $site_session = \app\admin\model\system\SystemConfig::getValue('site_session');
        if($site_session==0||$site_session==''){
            $site_session=1000000000;
        }
        cache($token, $login_uid, $site_session);
    }
    return $login_uid?$login_uid:0;
}