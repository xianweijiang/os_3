<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/24
 * Time: 17:11
 */

namespace app\osapi\controller;

use app\osapi\model\com\ComAdv;
use think\Cache;
class Adv extends Base
{

    /**
     * 广告位
     */
    public function adv(){
        $type = input('post.type', 0);
        $list=Cache::get('adv'.$type);
        if(!$list){
            $list=ComAdv::getAdv($type);
            Cache::set('adv'.$type,$list,600);
        }
        $this->apiSuccess($list);
    }
    /**
     * 广告位 nyb  6 首页第一个banner 8 首页第二个banner
     * @author jiangxw
     */
    public function adv_nyb(){
        $type = input('post.type', 0);
        $list = Cache::get('adv'.$type);
        if(!$list){
            $list=ComAdv::getAdvNyb($type);
            Cache::set('adv'.$type,$list,600);
        }
        $this->apiSuccess($list);
    }


}