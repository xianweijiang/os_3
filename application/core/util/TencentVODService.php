<?php
/**
 * Created by PhpStorm.
 * User: zzl-yf
 * Date: 2020/2/10
 * Time: 10:59
 */

namespace app\core\util;

use app\admin\model\system\SystemConfig;
use Qcloud\Cos\Client;
use Vod\Model\VodUploadRequest;
use Vod\VodUploadClient;

class TencentVODService
{
    private static function _tencentVODConfig()
    {
        // 确定 App 的云 API 密钥 -后续做成后台配置项
        $tencent_video_config=SystemConfig::getMore(['tencent_video_is_open','tencent_video_secret_id','tencent_video_secret_key','tencent_video_procedure']);

        $config['is_open']=$tencent_video_config['tencent_video_is_open'];
        $config['secret_id'] = $tencent_video_config['tencent_video_secret_id'];//"AKIDFVdXjfpnytl0ylIb7PREqCWaTAbmJRuJ";//todo 做成后台配置项
        $config['secret_key'] = $tencent_video_config['tencent_video_secret_key'];//"LwLUPcbKo38B5xZwe7ML7e8OyMZbP2Vs";//todo 做成后台配置项
        $config['procedure'] = $tencent_video_config['tencent_video_procedure'];//"LwLUPcbKo38B5xZwe7ML7e8OyMZbP2Vs";//todo 做成后台配置项
        return $config;
    }

    /**
     * 腾讯云点播上传
     */
    public static function tencentVODUpload($local_path)
    {
        $tencent_video_config=self::_tencentVODConfig();
        if(!$tencent_video_config['is_open']){
            return ['result'=>false,'info'=>'未开启腾讯云点播'];
        }
        //初始化
        $secretId = $tencent_video_config['secret_id'];//"云 API 密钥 SecretId";
        $secretKey = $tencent_video_config['secret_key']; //"云 API 密钥 SecretKey";

        $vodClient = new VodUploadClient($secretId, $secretKey);
        $req = new VodUploadRequest();
        $req->MediaFilePath = realpath($local_path);//本地文件绝对路径
        //$req->SubAppId = 101;//TODO 子应用上传
        //$req->Procedure = "Your Procedure Name";//TODO 指定任务流
        //$req->CoverFilePath = "/data/videos/Wildlife-Cover.png";//TODO 携带封面
        //$req->StorageRegion = "ap-chongqing";//TODO 指定存储地域
        try {
            $rsp = $vodClient->upload("ap-guangzhou", $req);
            return ['result'=>true,'info'=>[
                'file_id'=>$rsp->FileId,
                'media_url'=> $rsp->MediaUrl
            ]];
        } catch (Exception $e) {
            // 处理上传异常
            return ['result'=>false,'info'=>'上传到腾讯云失败：'.$e];
        }
    }


    /**
     * 根据资源id获得资源基础信息
     */
    public static function getTencentVideoUrl($video_ids,$time=1)
    {
        $tencent_video_config=self::_tencentVODConfig();
        if(!$tencent_video_config['is_open']){
            return false;
        }
        /**
         * 签名生成
         */
        $secretId = $tencent_video_config['secret_id'];
        $secretKey = $tencent_video_config['secret_key'];
        $param["Nonce"] = rand();
        $param["Timestamp"] = time();
        $param["Region"] = "ap-guangzhou";//存储地域
        $param["SecretId"] = $secretId;
        $param["Version"] = "2018-07-17";//版本
        $param["Action"] = "DescribeMediaInfos";//请求
        $param["Filters.0"] = "basicInfo";//获取信息范围“基础信息”
        $i=0;
        foreach ($video_ids as $vid) {
            $param["FileIds.".$i] = $vid;
            $i++;
        }
        unset($vid);

        ksort($param);

        $signStr = "GETvod.tencentcloudapi.com/?";
        $url='https://vod.tencentcloudapi.com/?';
        foreach ( $param as $key => $value ) {
            $signStr = $signStr . $key . "=" . $value . "&";
            $url = $url . $key . "=" . $value . "&";
        }
        unset($key,$value);
        $signStr = substr($signStr, 0, -1);

        $signature = base64_encode(hash_hmac("sha1", $signStr, $secretKey, true));
        /**
         * 签名生成end
         */
        $url=$url.'Signature='.$signature;
        $header = array(
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
        );
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 超时设置,以秒为单位
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        // 超时设置，以毫秒为单位
        // curl_setopt($curl, CURLOPT_TIMEOUT_MS, 500);

        // 设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //执行命令
        $data = curl_exec($curl);

        // 显示错误信息
        if (curl_error($curl)) {
            echo '获取腾讯云点播资源信息失败：'. curl_error($curl);
            exit;
        } else {
            // 打印返回的内容
            curl_close($curl);
            $data=json_decode($data,true);
            if(isset($data['Response']['Error'])){
                sleep(1);
                if($time<3){//循环发起三次请求，经测试请求可能失败，所以多次请求
                    $data=self::getTencentVideoUrl($video_ids,++$time);
                    return $data;
                }else{
                    echo '获取腾讯云点播资源信息失败：\n' ;
                    dump($data['Response']['Error']);
                    exit;
                }
            }else{
                $data=$data['Response'];

                $new_data=[];
                foreach ($data['MediaInfoSet'] as $val){
                    $new_data[$val['FileId']]=[
                        'FileId'=>$val['FileId'],
                        'MediaUrl'=>$val['BasicInfo']['MediaUrl'],
                        'CoverUrl'=>$val['BasicInfo']['CoverUrl'],
                    ];
                }
                $data=[
                    'has_list'=>$new_data,
                    'not_has_list'=>$data['NotExistFileIdSet']
                ];
                unset($val,$new_data);
                return $data;
            }
        }
    }
}