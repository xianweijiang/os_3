<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/30
 * Time: 14:52
 */

namespace app\osapi\model\com;


use app\osapi\model\BaseModel;


class MessageNews extends BaseModel
{

    /**
     * 获取运营消息
     */
    public static function getMessageNews($page,$row){
        $message_new=self::where(['status'=>1,'send_time'=>['lt',time()]])->page($page,$row)->order('send_time desc,create_time desc')->select();
        if($message_new){
            $message_new=$message_new->toArray();
            foreach($message_new as &$value){
                $value['content']=text($value['content']);
                $value['logo']=get_root_path($value['logo']);
                $value['logo_150']=thumb_path($value['logo'],150,150);
                $value['logo_350']=thumb_path($value['logo'],350,350);
                $value['logo_750']=thumb_path($value['logo'],750,750);
            }
            unset($value);
        }
        return $message_new;
    }
    
}