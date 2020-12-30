<?php
/**
 * Created by PhpStorm.
 * User: zzl-yf
 * Date: 2020/2/10
 * Time: 10:59
 */

namespace app\core\util;

use app\admin\model\system\SystemConfig;
use app\osapi\model\file\Picture;
use Qcloud\Cos\Client;

class TencentCosService
{
    private static function _tencentCOSConfig()
    {
        $tencent_COS_config=SystemConfig::getMore(['picture_store_tencent_secretId','picture_store_tencent_secretKey','picture_store_tencent_region','picture_store_tencent_bucket']);
        $config['secretId']=$tencent_COS_config['picture_store_tencent_secretId']; //"云 API 密钥 SecretId";
        $config['secretKey'] = $tencent_COS_config['picture_store_tencent_secretKey']; //"云 API 密钥 SecretKey";
        $config['region'] = $tencent_COS_config['picture_store_tencent_region']; //设置一个默认的存储桶地域
        $config['bucket'] = $tencent_COS_config['picture_store_tencent_bucket']; //存储桶名称 格式：BucketName-APPID
        return $config;
    }

    public static function tencentCOSUpload($tmp_info)
    {
        $cosClientConfig=self::_tencentCOSConfig();
        //初始化
        $secretId = $cosClientConfig['secretId'];//"云 API 密钥 SecretId";
        $secretKey = $cosClientConfig['secretKey']; //"云 API 密钥 SecretKey";
        $region = $cosClientConfig['region']; //设置一个默认的存储桶地域
        $bucket = $cosClientConfig['bucket']; //存储桶名称 格式：BucketName-APPID
        $cosClient = new Client(
            array(
                'region' => $region,
                'schema' => 'https', //协议头部，默认为http
                'credentials'=> array(
                    'secretId'  => $secretId ,
                    'secretKey' => $secretKey)));
        // 上传文件流
        try {
            $file_name=explode('.',$tmp_info['name']);
            $file_name = $file_name[0].time();
            $srcPath = $tmp_info['tmp_name'];//本地文件绝对路径
            $file = fopen($srcPath, "rb");
            if ($file) {
                $result = $cosClient->putObject(array(
                    'Bucket' => $bucket,
                    'Key' => $file_name,
                    'Body' => $file));
                if($result){
                    //对象存储SDK  cos-sdk-v5从【2.0.5】降配到【1.3.0】处理 start    因为云点播扩展包要依赖1.3.0版本才行
                    $key=explode('/',$result['ObjectURL']);
                    $result=[
                        'Location'=>$result['ObjectURL'],
                        'Bucket'=>$bucket,
                        'Key'=>$key[count($key)-1]
                    ];
                    $result['Location']=str_replace('http://','',$result['Location']);
                    $result['Location']=str_replace('https://','',$result['Location']);
                    //对象存储SDK  cos-sdk-v5从【2.0.5】降配到【1.3.0】处理 end   因为云点播扩展包要依赖1.3.0版本才行

                    // 成功上传后 获取上传信息
                    $tmp_info['path']='https://'.$result['Location'].'?upload_type/Tencent_COS';
                    $tmp_info['Bucket']=$result['Bucket'];
                    $tmp_info['Key']=$result['Key'];
                }else{
                    return ['result'=>false,'info'=>'上传到腾讯云失败'];
                }
            }
        } catch (\Exception $e) {
            return ['result'=>false,'info'=>'上传到腾讯云失败：'.$e];
        }
        return ['result'=>true,'info'=>$tmp_info];
    }

    public static function tencentCOSUploadBase64($base64_body)
    {
        $cosClientConfig=self::_tencentCOSConfig();
        //初始化
        $secretId = $cosClientConfig['secretId'];//"云 API 密钥 SecretId";
        $secretKey = $cosClientConfig['secretKey']; //"云 API 密钥 SecretKey";
        $region = $cosClientConfig['region']; //设置一个默认的存储桶地域
        $bucket = $cosClientConfig['bucket']; //存储桶名称 格式：BucketName-APPID
        $cosClient = new Client(
            array(
                'region' => $region,
                'schema' => 'https', //协议头部，默认为http
                'credentials'=> array(
                    'secretId'  => $secretId ,
                    'secretKey' => $secretKey)));
        // 上传文件流
        try {
            $file_name = 'base64_upload_'.rand(10000,99999).time();
            $result_cos = $cosClient->putObject(array(
                'Bucket' => $bucket,
                'Key' => $file_name,
                'Body' => base64_decode($base64_body)));
            if($result_cos){

                //对象存储SDK  cos-sdk-v5从【2.0.5】降配到【1.3.0】处理 start    因为云点播扩展包要依赖1.3.0版本才行
                $key=explode('/',$result_cos['ObjectURL']);
                $result_cos=[
                    'Location'=>$result_cos['ObjectURL'],
                    'Bucket'=>$bucket,
                    'Key'=>$key[count($key)-1]
                ];
                $result_cos['Location']=str_replace('http://','',$result_cos['Location']);
                $result_cos['Location']=str_replace('https://','',$result_cos['Location']);
                //对象存储SDK  cos-sdk-v5从【2.0.5】降配到【1.3.0】处理 end   因为云点播扩展包要依赖1.3.0版本才行

                // 成功上传后 获取上传信息
                $tmp_info['path']='https://'.$result_cos['Location'].'?upload_type/Tencent_COS';
                $tmp_info['Bucket']=$result_cos['Bucket'];
                $tmp_info['Key']=$result_cos['Key'];
                $tmp_info['file_content']=$base64_body;
            }else{
                return ['result'=>false,'info'=>'上传到腾讯云失败'];
            }
        } catch (\Exception $e) {
            return ['result'=>false,'info'=>'上传到腾讯云失败：'.$e];
        }
        return ['result'=>true,'info'=>$tmp_info];
    }
}