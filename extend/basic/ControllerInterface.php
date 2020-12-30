<?php
/**
 * 该文件用于注释ControllerBasic中有哪些公用方法可供调用，以及其实现的主要功能
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/10/29
 * Time: 19:00
 */

namespace basic;


use think\Request;

interface ControllerInterface
{
    public function __construct(Request $request = null);//构造函数

}