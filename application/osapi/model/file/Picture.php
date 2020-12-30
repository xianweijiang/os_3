<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/29
 * Time: 9:01
 */

namespace app\osapi\model\file;


use app\osapi\model\BaseModel;
use think\Config;

class Picture extends BaseModel
{
    protected $autoWriteTimestamp = true;

    /**
     * @param $info
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author:lin(lt@ourstu.com)
     */
    public static function upload($info)
    {
        $md5 = $info->md5();
        $sha1 = $info->sha1();
        $img = self::where(['md5'=>$md5,'sha1'=>$sha1])->field('path,type,id')->find();
        if(!empty($img)){
            return $img;
        }
        $picture_upload=Config::get('PICTURE_UPLOAD');
        $data['type'] = 'local';
        $data['path'] = $picture_upload['db_rootPath'].'/'.$info->getSaveName();
        $data['md5'] = $md5;
        $data['sha1'] = $sha1;
        $size = getimagesize($info->getPathname());
        $data['width'] = $size['0'];
        $data['height'] = $size['1'];
        $data['status'] = 1;
        $res = self::create($data);
        return $res;
    }

    /**
     * @param $info
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author:lin(lt@ourstu.com)
     */
    public static function uploadTencentCOS($tmp_info)
    {
        $md5 = md5_file($tmp_info['tmp_name']);
        $sha1 = sha1_file($tmp_info['tmp_name']);;
        $img = self::where(['md5'=>$md5,'sha1'=>$sha1])->field('path,type,id')->find();
        if(!empty($img)){
            return $img;
        }
        $data['type'] = 'Tencent_COS';
        $data['path'] = $tmp_info['path'];
        $data['md5'] = $md5;
        $data['sha1'] = $sha1;
        $data['url'] = $tmp_info['Bucket'].'|||'.$tmp_info['Key'];
        $size = getimagesize($data['path']);
        $data['width'] = $size['0'];
        $data['height'] = $size['1'];
        $data['status'] = 1;
        $res = self::create($data);
        return $res;
    }



    /**
     * 上传base64后的处理
     * @param $file_name
     * @param $file_path
     * @return $this|array|false|\PDOStatement|string|\think\Model
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function uploadBase64($file_name,$file_path)
    {
        $md5 = md5_file($file_path);
        $sha1 =sha1_file($file_path);
        $img = self::where(['md5'=>$md5,'sha1'=>$sha1])->field('path,type,id')->find();
        if(!empty($img)){
            return $img;
        }
        $picture_upload_base64=Config::get('PICTURE_UPLOAD_BASE64');
        $data['type'] = 'local';
        $data['path'] = $picture_upload_base64['db_rootPath'].'/'.$file_name;
        $data['md5'] = $md5;
        $data['sha1'] = $sha1;
        $size = getimagesize($file_path);
        $data['width'] = $size['0'];
        $data['height'] = $size['1'];
        $data['status'] = 1;
        $res = self::create($data);
        return $res;
    }

    /**
     * 上传base64后的处理
     * @param $file_name
     * @param $file_path
     * @return $this|array|false|\PDOStatement|string|\think\Model
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function uploadBase64TencentCOS($tmp_info)
    {
        $md5 = md5($tmp_info['file_content']);
        $sha1 =sha1($tmp_info['file_content']);
        $img = self::where(['md5'=>$md5,'sha1'=>$sha1])->field('path,type,id')->find();
        if(!empty($img)){
            return $img;
        }
        $data['type'] = 'Tencent_COS';
        $data['path'] = $tmp_info['path'];
        $data['url'] = $tmp_info['Bucket'].'|||'.$tmp_info['Key'];
        $data['md5'] = $md5;
        $data['sha1'] = $sha1;
        $size = getimagesize($data['path']);
        $data['width'] = $size['0'];
        $data['height'] = $size['1'];
        $data['status'] = 1;
        $res = self::create($data);
        return $res;
    }


    /**
     * 检测文件是否已经存在
     * @param $file_name 填写时，通过文件名获取hash值
     * @param null $file_str  $file_name未填写时，通过base64位内容获取文件hash值
     * @return array|bool|false|\PDOStatement|string|\think\Model
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function checkExist($file_name,$file_str=null)
    {
        if($file_name!=null){
            $md5=md5_file($file_name);
            $sha1=sha1_file($file_name);
        }else{
            $md5=md5($file_str);
            $sha1=sha1($file_str);
        }
        $img = self::where(['md5'=>$md5,'sha1'=>$sha1])->field('path,type,id')->find();
        return !empty($img)?$img:false;
    }
}