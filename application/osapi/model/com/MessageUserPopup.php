<?php
/**
 *
 * @author: cyx<cyx@ourstu.com>
 * @day: 2019/4/12
 */

namespace app\osapi\model\com;

use app\osapi\model\BaseModel;
use app\osapi\model\user\UserModel;
/**
 * 版块 model
 * Class ComForum
 * @package app\admin\model\com
 */
class MessageUserPopup extends BaseModel
{
    /**
     * 用户设置弹窗
     */
    public static function setUserPopup($uid,$status){
       $popup=self::where('uid',$uid)->find();
        if(!$popup){
            $data['uid']=$uid;
            $data['status']=$status;
            $res=self::insert($data);
        }else{
            $data['status']=$status;
            $res=self::where('uid',$uid)->update($data);
        }
        return $res;
    }

    /**
     * 获取用户弹窗设置
     */
    public static function getUserPopup($uid){
        $popup=self::where('uid',$uid)->where('status',0)->find();
       if($popup){
           return 0;
       }else{
           return 1;
       }
    }

}