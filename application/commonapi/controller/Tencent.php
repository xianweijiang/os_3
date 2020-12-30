<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/22
 * Time: 9:39
 */

namespace app\commonapi\controller;

use app\commonapi\lib\File;
use basic\ControllerBasic;

class Tencent extends ControllerBasic
{
    /**
     * 音频、视频上传到腾讯云接口（当前视频不过后端，主要用于音频）
     * @author zzl(zzl@ourstu.com)
     * @date slf
     */
    public function uploadAudio()
    {
        $files = request()->file('file');
        $res=File::uploadAudio($files);
        if($res==false){
            $this->apiError(File::getError());
        }else{
            $this->apiSuccess($res);
        }
    }
}
