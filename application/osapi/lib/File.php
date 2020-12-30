<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/28
 * Time: 16:21
 */

namespace app\osapi\lib;


use app\admin\model\system\SystemConfig;
use app\core\util\TencentCosService;
use app\osapi\model\file\Picture;
use app\osapi\model\user\UserModel;
use Complex\Exception;
use think\Config;


class File
{

    protected static $error_info;

    /*
     * 当前先兼容原版的，后续再进行改进、升级
     * 2020-02-07增加腾讯云对象存储COS方案
     *  todo 优化上传方案
     *  todo 兼容相关配置项
     *  todo 兼容七牛云
     */
    /**
     * 单图片上传
     * @param $files 表单上传文件，在各个接口中获取传输。获取方式：request()->file('file')；其中‘file’可变，和前端对应
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function uploadPicture($files)
    {
        // 获取表单上传文件
        //$files = request()->file('file');//改为在各个接口中获取
        //todo 多图支持，直接对file进行foreach就行；
        $file=$files;

        $tmp_info=$file->getInfo();
//        $tmp_info= self::check_need_rotate($tmp_info);
        $isExist=Picture::checkExist($tmp_info['tmp_name']);
        if($isExist){
            $res=$isExist;
        }else{
            $upload_type=SystemConfig::getValue('picture_store_place');
            if(($file->getMime())=='image/webp'){
                $upload_type='local';//webp格式图片只支持本地上传
            }
            switch ($upload_type) {
                case 'Tencent_COS'://腾讯云COS
                    $picture_upload=Config::get('TENCENT_COS_PICTURE_UPLOAD');
                    if(!$file->check($picture_upload)){
                        $err=$file->getError();
                        self::_setError('图片上传失败：'.$err);
                        return false;
                    }

                    //调用腾讯云上传
                    $result=TencentCosService::tencentCOSUpload($tmp_info);
                    if($result['result']==true){
                        $res = Picture::uploadTencentCOS($result['info']);
                        if(!$res){
                            // 写入数据库失败
                            self::_setError('图片信息写入数据库失败');
                            return false;
                        }
                    }else{
                        self::_setError($result['info']);
                        return false;
                    }
                    break;
                case 'local':
                default:
                    $picture_upload=Config::get('PICTURE_UPLOAD');
                    $info = $file->validate($picture_upload)->rule($picture_upload['nameBuilder'])->move($picture_upload['rootPath']);
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $res = Picture::upload($info);
                        if(!$res){
                            // 写入数据库失败
                            self::_setError('图片信息写入数据库失败');
                            return false;
                        }
                    } else {
                        // 上传失败获取错误信息
                        self::_setError($file->getError());
                        return false;
                    }
                    break;
            }
        }
        $path=$res['type']=='local'?get_domain() . $res['path']:$res['path'];
        $result = [
            'code' => 0,
            'msg' => '上传成功',
            'id' => $res['id'],
            'path' => $path,
        ];
        return $result;
    }

    public static function check_need_rotate($file)
    {

        $name=date('YmdHis').rand(1000,9999).'.jpg';
        $savePathFile = '/upload/temps/';
        $targetName = __DIR__.$savePathFile.$name;
        $res=self::isAnimatedGif($file['tmp_name'].'/'.$file['name']);
        if($res==1){
            return $file;
        }
        $image = imagecreatefromstring(file_get_contents($file['tmp_name']));
        try{
            $exif = exif_read_data($file['tmp_name']);
            if(!empty($exif['Orientation'])) {
                switch($exif['Orientation']) {
                    case 8:
                        $image = imagerotate($image,90,0);
                        break;
                    case 3:
                        $image = imagerotate($image,180,0);
                        break;
                    case 6:
                        $image = imagerotate($image,-90,0);
                        break;
                }
                imagejpeg($image,$targetName);
                $file['name']=$name;
                $file['type']="image/jpeg";
                $file['tmp_name']=$savePathFile;
            }

        }catch(Exception $e){

         }
        return $file;
    }

    public static function  isAnimatedGif($filename) {
        $fp = fopen($filename, 'rb');
        $filecontent = fread($fp, filesize($filename));
        fclose($fp);
        return strpos($filecontent, chr(0x21) . chr(0xff) . chr(0x0b) . 'NETSCAPE2.0') === FALSE ? 0 : 1;
    }
    /**
     * 多图上传
     * @param $files 表单上传文件，在各个接口中获取传输。获取方式：request()->file('mulitfile')；其中‘mulitfile’可变,接口中确定，和前端对应
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function uploadMulitPicture($files)
    {
        // 获取表单上传文件
        //$files = request()->file('mulitfile');//改为在各个接口中获取
        $success=[];

        foreach ($files as $k=>$file){
            $tmp_info=$file->getInfo();
            $isExist=Picture::checkExist($tmp_info['tmp_name']);
            if($isExist){
                $success[$k]=[
                    'num'=>$k,
                    'info'=>'已存在',
                    'id'=>$isExist['id'],
                    'path'=>$isExist['type']=='local'?get_domain() . $isExist['path']:$isExist['path']
                ];
            }else{
                $upload_type=SystemConfig::getValue('picture_store_place');
                switch ($upload_type){
                    case 'Tencent_COS'://腾讯云COS
                        $picture_upload=Config::get('TENCENT_COS_PICTURE_UPLOAD');
                        if(!$file->check($picture_upload)){
                            $err=$file->getError();
                            // 写入数据库失败
                            $success[$k]=[
                                'num'=>$k,
                                'info'=>'图片上传失败：'.$err
                            ];
                            continue;
                        }

                        //调用腾讯云上传
                        $result=TencentCosService::tencentCOSUpload($tmp_info);
                        if($result['result']==true){
                            $res = Picture::uploadTencentCOS($result['info']);
                            if(!$res){
                                // 写入数据库失败
                                $success[$k]=[
                                    'num'=>$k,
                                    'info'=>'图片信息写入数据库失败'
                                ];
                                continue;
                            }
                            $success[$k]=[
                                'num'=>$k,
                                'info'=>'上传成功',
                                'id'=>$res['id'],
                                'path'=>$res['path']
                            ];
                        }else{
                            // 写入数据库失败
                            $success[$k]=[
                                'num'=>$k,
                                'info'=>$result['info']
                            ];
                            continue;
                        }
                        break;
                    case 'local':
                    default:
                        $picture_upload=Config::get('PICTURE_UPLOAD');

                        $info = $file->validate($picture_upload)->rule($picture_upload['nameBuilder'])->move($picture_upload['rootPath']);
                        if ($info) {
                            // 成功上传后 获取上传信息
                            $res = Picture::upload($info);
                            if(!$res){
                                // 写入数据库失败
                                $success[$k]=[
                                    'num'=>$k,
                                    'info'=>'图片信息写入数据库失败'
                                ];
                                continue;
                            }
                        } else {
                            // 上传失败获取错误信息
                            $success[$k]=[
                                'num'=>$k,
                                'info'=>$file->getError()
                            ];
                            continue;
                        }
                        $success[$k]=[
                            'num'=>$k,
                            'info'=>'上传成功',
                            'id'=>$res['id'],
                            'path'=>get_domain() . $res['path']
                        ];
                        break;
                }
            }
        }
        $result = [
            'code' => 0,
            'msg' => '批量上传执行完成，具体执行结果请查看result字段',
            'result' => $success
        ];
        return $result;
    }

    /**
     * 上传base64位图片-上传头像专用
     * @param $fileData
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function uploadAvatar($fileData)
    {
        if ($fileData == '' || $fileData == 'undefined') {
            self::_setError('参数错误');
            return false;
        }
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $fileData, $file_info)) {
            $base64_body = substr(strstr($fileData, ','), 1);
            empty($aExt) && $aExt = $file_info[2];
        } else {
            $base64_body = $fileData;
        }
        $picture_upload_base64=Config::get('PICTURE_UPLOAD_BASE64');
        if (!in_array($aExt, $picture_upload_base64['ext'])) {
            self::_setError('非法操作,上传照片格式不符');
            return false;
        }
        $hasPhp = base64_decode($base64_body);
        if (strpos($hasPhp, '<?php') !== false) {
            self::_setError('非法操作');
            return false;
        }
        $driver =SystemConfig::getValue('picture_store_place');

        switch ($driver){
            case 'local'://本地上传
                $uid=get_uid();
                $root_path=$picture_upload_base64['avatarPath'] . '/' .$uid ;
                $file_name=md5(microtime(true)) . '.' . $aExt;
                $path = $root_path. '/' . $file_name;
                if(!file_exists($root_path)){
                    mkdir($root_path, 0777, true);
                }
                $data = base64_decode($base64_body);
                $rs = file_put_contents($path, $data);
                if($rs){
                    // 成功上传后 获取上传信息
                    $save_path=$picture_upload_base64['db_avatarPath'] . '/' .$uid;
                    $result = [
                        'code' => 0,
                        'msg' => '上传成功',
                        'path' => get_domain().$save_path.'/'.$file_name,
                    ];
                }else{
                    // 上传失败获取错误信息
                    self::_setError('图片上传失败');
                    return false;
                }
                break;
            case 'Tencent_COS'://腾讯云COS
                $isExist=Picture::checkExist(null,$base64_body);
                if($isExist){
                    return $isExist;
                }

                $res=TencentCosService::tencentCOSUploadBase64($base64_body);
                if($res['result']==true){
                    $res_db = Picture::uploadBase64TencentCOS($res['info']);
                    if(!$res_db){
                        // 写入数据库失败
                        self::_setError('图片信息写入数据库失败');
                        return false;
                    }else{
                        $result = [
                            'code' => 0,
                            'msg' => '上传成功',
                            'path' => $res['info']['path'],
                        ];
                    }
                }else{
                    self::_setError($res['info']);
                    return false;
                }
            default:
        }
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