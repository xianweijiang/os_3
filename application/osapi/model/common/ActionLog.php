<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/6/4
 * Time: 13:55
 */

namespace app\osapi\model\common;


use app\osapi\model\BaseModel;

class ActionLog extends BaseModel
{

    /**
     * 添加行为日志
     * @param $uid
     * @param $action
     * @param $content
     * @param string $model
     * @param string $row
     * @return bool|int|string
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function addActionLog($uid,$action,$content,$model='',$row='')
    {
        if($uid==0){
            return false;
        }
        $data=[
            'uid'=>$uid,
            'action'=>self::_getActionStr($action),
            'content'=>$content,
            'create_time'=>time(),
            'model'=>$model,
            'row'=>$row
        ];
        $res=self::add($data);
        return $res;
    }

    /**
     * action标识转换为字符串标识。归类action，方便后续整理
     * @param $action_key
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _getActionStr($action_key)
    {
        $actionList=[
            1=>'add_to_forum',//加入版块
            2=>'out_forum',//退出版块
            3=>'add_thread',//添加主题帖
            4=>'add_post',//添加帖子（这里只对应评论）
            5=>'edit_thread',//编辑主题帖子
            6=>'follow_user',//关注用户
            7=>'unfollow_user',//取消关注用户
            8=>'support_thread',//点赞主题
            9=>'support_ucard',//点赞用户小名片
            10=>'support_forum',//点赞版块
            11=>'un_support_thread',//点赞主题
            12=>'un_support_ucard',//点赞用户小名片
            13=>'un_support_forum',//点赞版块
        ];
        if(!in_array($action_key,array_keys($actionList))){
            return $action_key;
        }
        return $actionList[$action_key];
    }

}