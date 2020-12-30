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


use app\osapi\model\com\ComForumMember;
use app\osapi\model\com\ComThread;
use app\osapi\model\user\UserFollow;
use app\admin\model\system\SystemConfig;

class Ucard extends Base
{
    /**
     * 关注用户列表
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function followList()
    {
        $uid=input('uid/d',0);
        !$uid&&$uid=$this->_needLogin();
        $page=input('page/d',1);
        $row=input('row/d',10);
        $list  = UserFollow::getFollowList($uid, $page, $row,'create_time desc'); //用户的关注用户列表
        $this->apiSuccess($list);
    }

    /**
     * 粉丝列表
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function fansList()
    {
        $uid=input('uid/d',0);
        !$uid&&$uid=$this->_needLogin();
        $page=input('page/d',1);
        $row=input('row/d',10);
        $list  = UserFollow::getFansList($uid, $page, $row,'create_time desc'); //用户的关注用户列表
        $this->apiSuccess($list);
    }

    /**
     * 版块列表
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function forumList()
    {
        $uid=input('uid/d',0);
        !$uid&&$uid=$this->_needLogin();
        $page=input('page/d',1);
        $row=input('row/d',10);
        $access=$this->access;
        $video_is_on=SystemConfig::getValue('xcx_video');
        $list  = ComForumMember::getUserForumList($uid, $page, $row,'create_time desc',$access,$video_is_on); //用户的关注用户列表
        $this->apiSuccess($list);
    }

    /**
     * 主题帖列表
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function threadList()
    {
        $uid=input('uid/d',0);
        !$uid&&$uid=$this->_needLogin();
        $page=input('page/d',1);
        $row=input('row/d',10);
        $list  = ComThread::getUserThreadList($uid, $page, $row,$type=1,'create_time desc'); //用户的关注用户列表
        $this->apiSuccess($list);
    }
}