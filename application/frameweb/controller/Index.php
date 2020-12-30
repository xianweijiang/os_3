<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/22
 * Time: 9:39
 */

namespace app\frameweb\controller;

use basic\ControllerBasic;
use think\Config;

class Index extends ControllerBasic
{

    public function frameweb(){
        $url=input('url','','text');
        $url1=input('url1','','text');
        $url=$url.'#'.$url1;
        $info=db('pc_set')->where('id',1)->find();
        $this->assign('url',$url);
        $this->assign('info',$info);
        return $this->fetch();
    }

}