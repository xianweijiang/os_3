<?php

/** 不兼容sae 只兼容本地 --駿濤
 * @param        $filename
 * @param int $width
 * @param string $height
 * @param int $type
 * @param bool $replace
 * @return mixed|string
 * @auth 陈一枭
 */
function getThumbImage($filename, $width = 100, $height = 'auto', $type = 0, $replace = false)
{
    if($filename==''||$filename=='0'){
        $info['src']='';
        return $info;
    }
    $old_file=$filename;
    $filename=str_ireplace('https://','http://',$filename);
    $UPLOAD_URL = 'http://'.request()->host();
    $UPLOAD_PATH = '';
    $filename = str_ireplace($UPLOAD_URL, '', $filename); //将URL转化为本地地址

    if(strpos($filename,'http://')===0){//仍然存在http：//   说明是第三方地址
        $old_file=getThumbImageUNLocal($old_file,$width,$height,$type);
        $info['src']=$old_file;
        return $info;//远程第三方地址，调用第三方地址处理方案并返回地址
    }

    $info = pathinfo($filename);

    $oldFile = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . $info['extension'];
    $thumbFile = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '_' . $width . '_' . $height . '.' . $info['extension'];
    $oldFile = str_replace('\\', '/', $oldFile);
    $thumbFile = str_replace('\\', '/', $thumbFile);

    $filename = ltrim($filename, '/');
    $oldFile = ltrim($oldFile, '/');
    $thumbFile = ltrim($thumbFile, '/');
    if (!file_exists($UPLOAD_PATH . $oldFile)) {
        //原图不存在直接返回
        @unlink($UPLOAD_PATH . $thumbFile);
        $info['src'] = $oldFile;
        $info['width'] = intval($width);
        $info['height'] = intval($height);
        return $info;
    } elseif (file_exists($UPLOAD_PATH . $thumbFile) && !$replace) {
        //缩图已存在并且  replace替换为false
        $imageinfo = getimagesize($UPLOAD_PATH . $thumbFile);
        $info['src'] = $thumbFile;
        $info['width'] = intval($imageinfo[0]);
        $info['height'] = intval($imageinfo[1]);
        return $info;
    } else {
        //执行缩图操作
        $oldimageinfo = getimagesize($UPLOAD_PATH . $oldFile);
        if($oldimageinfo['mime']=='image/webp'){//webp格式图片直接返回原图，不做裁剪
            $info['src'] = $oldFile;
            $info['width'] = intval($oldimageinfo[0]);
            $info['height'] = intval($oldimageinfo[1]);
            return $info;
        }
        $old_image_width = intval($oldimageinfo[0]);
        $old_image_height = intval($oldimageinfo[1]);
        if ($old_image_width <= $width && $old_image_height <= $height) {
            @unlink($UPLOAD_PATH . $thumbFile);
            @copy($UPLOAD_PATH . $oldFile, $UPLOAD_PATH . $thumbFile);
            $info['src'] = $thumbFile;
            $info['width'] = $old_image_width;
            $info['height'] = $old_image_height;
            return $info;
        } else {
            if($old_image_width>8000||$old_image_height>8000||filesize($UPLOAD_PATH . $oldFile)>7*1024*1024){
                if($old_image_width>10000||$old_image_height>10000||filesize($UPLOAD_PATH . $oldFile)>10*1024*1024){
                    $info['src'] = $oldFile;
                    $info['width'] = intval($oldimageinfo[0]);
                    $info['height'] = intval($oldimageinfo[1]);
                    return $info;
                }
                ini_set("memory_limit","500M");
            }
            if ($height == "auto") $height = $old_image_height * $width / $old_image_width;
            if ($width == "auto") $width = $old_image_width * $width / $old_image_height;
            if (intval($height) == 0 || intval($width) == 0) {
                return 0;
            }
            require_once('./vendor/phpthumb/PhpThumbFactory.class.php');
            $thumb = PhpThumbFactory::create($UPLOAD_PATH . $filename);
            if ($type == 0) {
                $thumb->adaptiveResize($width, $height);
            } else {
                $thumb->resize($width, $height);
            }
            $res = $thumb->save($UPLOAD_PATH . $thumbFile);
            $info['src'] = $UPLOAD_PATH . $thumbFile;
            $info['width'] = $old_image_width;
            $info['height'] = $old_image_height;
            return $info;

        }
    }
}

/**
 * 非本地图片处理，当前支持腾讯云对象存储COS图片
 * @param $filename
 * @param int $width
 * @param string $height
 * @param int $type，裁剪类型，0居中裁剪，更多操作如水印等，参考腾讯云文档-》数据万象-》Api-》基础图片处理
 * @return mixed|string
 */
function getThumbImageUNLocal($filename, $width = 100, $height = 'auto', $type = 0){
    if($filename==''||$filename=='0'){
        return '';
    }
    if(stripos($filename,'?upload_type/Tencent_COS')>0){//腾讯云COS图片
        if(intval($height) == 0 || intval($width) == 0){
            if (intval($height) == 0 && intval($width) == 0) {
                return $filename;
            }
            $picture = SessionInstance()::get('picture_' . $filename);
            if (empty($picture)) {
                $picture = db('Picture')->where(array('status' => 1))->where('path',$filename)->find();
                SessionInstance()::set('picture_' . $filename, $picture);
            }
            if (empty($picture)) {
                $image_size=getimagesize($filename);
                $old_image_width=$image_size['0'];
                $old_image_height=$image_size['1'];
            }else{
                $old_image_width=$picture['width'];
                $old_image_height=$picture['height'];
            }
            if ($height == "auto") $height = $old_image_height * $width / $old_image_width;
            if ($width == "auto") $width = $old_image_width * $width / $old_image_height;
            if (intval($height) == 0 || intval($width) == 0) {
                return $filename;
            }
        }
        $del_str='crop/'.$width.'x'.$height;
        if($type==0){//居中裁剪
            $del_str=$del_str.'/gravity/center';
        }
        $filename= str_ireplace('?upload_type/Tencent_COS','?imageMogr2/',$filename);
        $filename=$filename.$del_str;
    }
    //其它第三方图片暂时不做处理
    return $filename;
}

function SessionInstance(){
    return new think\Session();
}


/**
 * 通过ID获取到图片的缩略图
 * @param        $cover_id 图片的ID
 * @param int $width 需要取得的宽
 * @param string $height 需要取得的高
 * @param int $type 图片的类型，Tencent_COS 腾讯云对象存储COS，local 本地, sae SAE
 * @param bool $replace 是否强制替换
 * @return mixed
 * @author 郑钟良(zzl@ourstu.com)
 * @date 2019-7
 */
function getThumbImageById($cover_id, $width = 100, $height = 'auto', $type = 0, $replace = false)
{
    if($cover_id==''||$cover_id==0){
        return '';
    }
    $picture = SessionInstance()::get('picture_' . $cover_id);
    if (empty($picture)) {
        $picture = db('Picture')->where(array('status' => 1))->getById($cover_id);
        SessionInstance()::set('picture_' . $cover_id, $picture);
    }
    if (empty($picture)) {
        return null;
    }

    if ($picture['type'] == 'local') {
        $attach = getThumbImage($picture['path'], $width, $height, $type, $replace);
        return get_root_path($attach['src']);
    } else {
        $path = getThumbImageUNLocal($picture['path'], $width, $height, $type);
        return $path;
    }

}

/**简写函数，等同于getThumbImageById（）
 * @param $cover_id 图片id
 * @param int $width 宽度
 * @param string $height 高度
 * @param int $type 裁剪类型，0居中裁剪
 * @param bool $replace 裁剪
 * @return string
 */
function thumb($cover_id, $width = 100, $height = 'auto', $type = 0, $replace = false)
{
    if($cover_id==''||$cover_id==0){
        return '';
    }
    return getThumbImageById($cover_id, $width, $height, $type, $replace);
}

/**简写函数，通过地址获取图片缩略图
 * @param $path 图片地址
 * @param int $width 宽度
 * @param string $height 高度
 * @param int $type 裁剪类型，0居中裁剪
 * @param bool $replace 裁剪
 * @return string
 */
function thumb_path($path, $width = 100, $height = 'auto', $type = 0, $replace = false)
{
    if($path==''||$path=='0'){
        return '';
    }
    $magnification=\app\admin\model\system\SystemConfig::getValue('picture_magnification');
    $width=$width*$magnification;
    if($height!='auto'){
        $height=$height*$magnification;
    }
    $path=str_replace(get_domain(),'',$path);
    //不存在http://
    $not_http_remote = (strpos($path, 'http://') === false);
    //不存在https://
    $not_https_remote = (strpos($path, 'https://') === false);
    if ($not_http_remote && $not_https_remote) {
        $attach = getThumbImage($path, $width, $height, $type, $replace);
        return get_root_path($attach['src']);
    } else {
        $path = getThumbImageUNLocal($path, $width, $height, $type);
        return $path;
    }
}

/**
 * 获取请求域名
 * @return string
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function get_domain()
{
    return request()->domain();
}

/**
 * 渲染图片链接
 * @param $path
 * @param bool $domain 是否带域名
 * @return mixed
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function get_root_path($path,$domain=true)
{
    if($path==''||$path=='0'){
        return '';
    }
    //不存在http://
    $not_http_remote = (strpos($path, 'http://') === false);
    //不存在https://
    $not_https_remote = (strpos($path, 'https://') === false);
    if ($not_http_remote && $not_https_remote) {
        //本地url
        $path='/'.$path;
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);
        return $domain==true?get_domain().$path:$path;
    } else {
        //远端url
        return $path;
    }
}

/**
 * 获取图片信息
 * @param $image_id
 * @param null $field 可以获取某个字段
 * @return array|bool|false|mixed|PDOStatement|string|\think\Model
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function get_img_info($image_id, $field = null)
{
    if (empty($image_id)||$image_id==''||$image_id==0) {
        return '';
    }

    $tag = 'picture_' . $image_id;
    $picture = SessionInstance()::get($tag);
    if ($picture === false) {
        $picture = db('picture')->where(array('status' => 1))->getById($image_id);
        SessionInstance()::set($tag, $picture);
    }

    return empty($field) ? $picture : $picture[$field];
}

/**
 * 获取图片地址
 * @param $image_id
 * @param bool $domain 是否包含域名
 * @return array|bool|false|mixed|PDOStatement|string|\think\Model
 * @author 郑钟良(zzl@ourstu.com)
 * @date slf
 */
function get_img_path($image_id,$domain=true)
{
    if($image_id==''||$image_id==0){
        return '';
    }
    $path=get_img_info($image_id,'path');
    if(!$path){
        return false;
    }
    return get_root_path($path,$domain);
}

