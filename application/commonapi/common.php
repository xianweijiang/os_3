<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-28 15:38:14
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-12 08:56:50
 */

//获取认证条件满足情况
function certification_condition($uid,$name,$condition_value){
    $result=['status'=>false];
    switch ($name) {
        case 'rztj1'://清晰头像
            $avatar = db('user')->where('uid',$uid)->where('is_avatar',1)->value('avatar');
            if ($avatar) {
                $result['status']=true;
            }
            $result['value']=$avatar;
            break;
        case 'rztj2'://绑定手机
            $phone = db('user')->where('uid',$uid)->value('phone');
            if ($phone) {
                $result['status']=true;
            }
            $result['value']=$phone;
            break;
        case 'rztj3'://关注数≥
            $follow = db('user')->where('uid',$uid)->value('follow');
            if ($condition_value<$follow+1) {
                $result['status']=true;
            }
            $result['value']=$follow;
            break;
        case 'rztj4'://粉丝数≥
            $fans = db('user')->where('uid',$uid)->value('fans');
            if ($condition_value<$fans+1) {
                $result['status']=true;
            }
            $result['value']=$fans;
            break;
        case 'rztj5'://评论数≥
            //$post_count = db('user')->where('uid',$uid)->value('post_count');
            //$posts = db('com_post')->where('author_uid',$uid)->count();
            //$comments=$post_count-$posts;
            $where="author_uid=".$uid." and to_reply_uid!=0";
            $comments = db('com_post')->where($where)->count();
            if ($condition_value<$comments+1) {
                $result['status']=true;
            }
            $result['value']=$comments;
            break;
        case 'rztj6'://近30天发帖数≥
            $startime = time() - 3600 * 24 * 30;
            $endtime = time();
            $where= "`create_time` between ".$startime." and ".$endtime." and author_uid=".$uid." and to_reply_uid=0";
            //$where='DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(create_time) and author_uid='.$uid;
            $posts = db('com_post')->where($where)->count();
            if ($condition_value<$posts+1) {
                $result['status']=true;
            }
            $result['value']=$posts;
            break;
        default:
            # code...
            break;
    }
    return $result;
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