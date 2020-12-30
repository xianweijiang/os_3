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
class MessageTemplate extends BaseModel
{
    /**
     * 获取消息模版配置
     */
    public static function getMessageSet($id){
       $template=self::where('id',$id)->find();
        return $template;
    }


}