<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/28
 * Time: 16:21
 */

namespace app\commonapi\lib;


use app\commonapi\model\TencentFile;
use app\core\util\TencentVODService;
use think\Config;


class File
{

    protected static $error_info;

    /**
     * 音频上传-只支持腾讯云点播存储
     * @param $files 表单上传文件，在各个接口中获取传输。获取方式：request()->file('file')；其中‘file’可变，和前端对应
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function uploadAudio($files)
    {
        // 获取表单上传文件
        //$files = request()->file('file');//改为在各个接口中获取

        $file=$files;
        $tmp_info=$file->getInfo();

        $audio_upload=Config::get('TENCENT_VOD_UPLOAD');
        /*if(!$file->check($audio_upload)){
            $err=$file->getError();
            self::_setError('音频上传失败：'.$err);
            return false;
        }*/
        $info=$file->move($audio_upload['tmpPath']);
        //调用腾讯云上传
        $result=TencentVODService::tencentVODUpload($info->getPathname());
        unlink($info->getPathname());//删除临时文件
        if($result['result']==true){
            $result['info']['type']='audio';//该接口为音频上传接口
            $res = TencentFile::uploadTencentVOD($result['info']);
            if(!$res){
                // 写入数据库失败
                self::_setError('图片信息写入数据库失败');
                return false;
            }
        }else{
            self::_setError($result['info']);
            return false;
        }
        $result = [
            'code' => 0,
            'msg' => '上传成功',
            'id' => $res['id'],
            'file_id'=>$res['file_id'],
            'media_url'=>$res['media_url'],
            'type'=>$res['type']
        ];
        return $result;
    }

    public static function getError()
    {
        return self::$error_info;
    }

    private static function _setError($error='操作失败')
    {
        self::$error_info=$error;
        return true;
    }
}