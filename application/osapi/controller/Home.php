<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/24
 * Time: 17:10
 */

namespace app\osapi\controller;


use app\osapi\model\com\ComAnnounce;
use app\osapi\model\com\ComPost;
use app\osapi\model\user\UserModel;

class Home extends Base
{
    /**
     * 社区页面的顶部数据信息和公告内容
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function header()
    {
        $uid = get_uid(); //判断是否登录
        if ($uid) { //登录后才会添加弹出公告
            $announce = ComAnnounce::getOneAnnounce($uid); //获取一条用户未读过的最新（1个月内）公告
            if ($announce != null) {
                $data['login_announce'] = $announce;
            }
        }
        $data['user'] = UserModel::getLastUser(); //获取最新注册用户的nickname
        $data['count'] = ComPost::getPostCount(); //获取今日帖子数
        $data['last_announce'] = ComAnnounce::getLastAnnounce(); //获取最新公告
        $this->apiSuccess($data);
    }
}