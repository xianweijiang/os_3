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
use app\osapi\model\com\ComForumMember;

class ComAdv extends BaseModel
{

    /**
     * 获取广告
     */
    public static function getAdv($type){
        $map=[
            'type'=>$type,
            'status'=>1,
        ];
        $list=self::where($map)->order('sort asc')->select()->toArray();
        if(in_array($type,array('1','3','6','10','12','13'))){
            foreach($list as &$value){
                $value['pic']=thumb_path($value['pic'],700,350);
                $value['link_url']=link_select_url($value['url']);
            }
            unset($value);
        }else{
            foreach($list as &$value){
                $value['pic']=thumb_path($value['pic'],700,200);
                $value['link_url']=link_select_url($value['url']);
            }
            unset($value);
        }
        return $list;
    }
    /**
     * 获取广告 nyb
     * @author jiangxw
     */
    public static function getAdvNyb($type){
        $map=[
            'type'=>$type,
            'status'=>1,
        ];
        $field = 'name,pic,url,jump_id';
        $list=self::where($map)->field($field)->order('sort asc')->select()->toArray();
        if(in_array($type,array('1','3','6','10','12','13'))){
            foreach($list as &$value){
                //$value['pic']=thumb_path($value['pic'],700,350);
                $value['pic'] = $value['pic'];
                $value['link_url']=link_select_url_nyb($value['url']);
            }
            unset($value);
        }else{
            foreach($list as &$value){
                //$value['pic']=thumb_path($value['pic'],700,200);
                $value['pic'] = $value['pic'];
                $value['link_url']=link_select_url_nyb($value['url']);
            }
            unset($value);
        }
        return $list;
    }

}