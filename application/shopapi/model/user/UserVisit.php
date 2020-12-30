<?php
/**
 * Created by PhpStorm.
 * User: zxh
 * Date: 2019/9/19
 * Time: 10:09
 */

namespace app\shopapi\model\user;


use basic\ModelBasic;
use GuzzleHttp\Psr7\Request;
use traits\ModelTrait;

class UserVisit extends ModelBasic
{

    use ModelTrait;
    /**
     * 添加查看信息
     * @param $uid
     * @param $visit_uid
     * @return bool
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.19
     */
    public static function addVisitUser($uid,$visit_uid){
        if($uid==$visit_uid){
            return false;
        }else{
            $is_visit=self::where(['uid'=>$uid,'visit_uid'=>$visit_uid])->find();
            if($is_visit){
                self::where(['uid'=>$uid,'visit_uid'=>$visit_uid])->update(['create_time'=>time()]);
            }else{
                self::set(['uid'=>$uid,'visit_uid'=>$visit_uid,'create_time'=>time()]);
            }
        }
    }

    /**
     * 获取列表
     * @param $map
     * @param $page
     * @param $limit
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.19
     */
    public static  function getUserVisitList($map,$page,$limit){
        $user=self::where($map)->page($page,$limit)->select();
        $data=[];
        foreach ($user as $key=>$vo){
            $data[$key]=User::getUserInfo($vo['visit_uid'],'uid,nickname,avatar');
        }
        unset($vo,$key);
        $count=self::where($map)->count();
        return ['data'=>$data,'count'=>$count];
    }

    /**
     * 卡片点赞
     * @param $uid
     * @param $visit_uid
     * @return bool
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.19
     */
    public static  function addSupportCount($uid,$visit_uid){
        if($uid==$visit_uid) {
            return false;
        }
        $model=db('user_support_card');
        $is_support=$model->where(['uid'=>$visit_uid,'to_uid'=>$uid])->find();
        if(!$is_support){
            return $model->insert(['uid'=>$visit_uid,'to_uid'=>$uid,'create_time'=>time()]);
        }else{
            return false;
        }
    }

    /**
     * 获取点赞数量
     * @param $uid
     * @return int|string
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.19
     */
    public static  function getSupportCount($uid){
        $model=db('user_support_card');
        return $model->where(['to_uid'=>$uid])->count();
    }

    /**
     * @param $uid
     * @param $picture
     * @return bool
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.19
     */
    public static  function addPicture($uid,$picture){
        $model=db('user_picture');
        $is_picture=$model->where(['uid'=>$uid])->find();
        if(!$is_picture){
            return $model->insert(['uid'=>$uid,'picture'=>$picture,'create_time'=>time()]);
        }else{
            return $model->where(['uid'=>$uid])->update(['picture'=>$picture,'create_time'=>time()]);
        }
    }

    /**
     * 获取图片
     * @param $uid
     * @return bool
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.19
     */
    public static  function getPicture($uid){
        $model=db('user_picture');
        $is_picture=$model->where(['uid'=>$uid])->find();
        $pic=explode(',',$is_picture['picture']);
        $data=[];
        foreach ($pic as $key=>$vo){
            $path=db('picture')->where(array('id'=>$vo))->field('id,path')->find();
            $data[$key]=$path;
        }
        unset($key,$vo);
        return $data;
    }
}