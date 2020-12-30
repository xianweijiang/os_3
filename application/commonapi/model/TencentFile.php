<?php
/**
 * Created by PhpStorm.
 * User: zzl-yf
 * Date: 2020/2/14
 * Time: 16:38
 */

namespace app\commonapi\model;


use app\admin\model\system\SystemConfig;
use basic\ModelBasic;
use traits\ModelTrait;

class TencentFile extends ModelBasic
{
    use ModelTrait;

    /**
     * 保存腾讯云点播文件（音频、视频）
     */
    public static function uploadTencentVOD($file_info)
    {
        $tencent_file = self::where(['file_id'=>$file_info['file_id'],'media_url'=>$file_info['media_url']])->field('file_id,media_url,cover_url,type')->find();
        if(!empty($tencent_file)){
            return $tencent_file;
        }
        $data['type'] = $file_info['type'];
        $data['file_id'] = $file_info['file_id'];
        $data['media_url'] = $file_info['media_url'];
        $data['cover_url'] = isset($file_info['cover_url'])?$file_info['cover_url']:'';
        $data['create_time']=time();
        $data['status'] = 1;
        $res = self::create($data);
        return $res;
    }


    public static function ifYunUpload(){
        $getTencentConfig = SystemConfig::getMore(['tencent_video_is_open','tencent_video_secret_id','tencent_video_secret_key','tencent_video_procedure','tencent_video_save_key','tencent_video_app_id']);

        $string['switch'] = $getTencentConfig['tencent_video_is_open'];
        $string['sid'] = $getTencentConfig['tencent_video_secret_id'];
        $string['skey'] = $getTencentConfig['tencent_video_secret_key'];
        $string['pkey'] = $getTencentConfig['tencent_video_save_key'];
        $string['appid'] = $getTencentConfig['tencent_video_app_id'];

        return $string;
    }

    /* 腾讯云点播防盗链修改
 	 * @param $type 期刊类型(1:图文;2:音频;3.视频)
	 * @param $infoPath 媒体上传保存原始路径
	 * @param $m_type 是否云点播
	 * 说明: 1. 如果不是视频,同时不是云点播就不进行防盗链了
	 *       2. 防盗链字串顺序: key+dir+t+us
	 *       3. 签名验证
	 *       4. 防盗链URL参数顺序: 原始url+过期时间+随机字串+签名
 	 */
    public static function yunKeyMediaUrl($infoPath,$getYunMediaKey)
    {
        if(!$getYunMediaKey){
            return $infoPath;
        }
        $getYunMediaDir = self::yunMediaDir($infoPath);
        $getTimeStamp = self::expireTime();
        $getRandom = rand(1,time());

        $queryString  = $getYunMediaKey.$getYunMediaDir.$getTimeStamp.$getRandom;
        //签名
        $mediaSign = md5($queryString);
        //生成防盗链地址
        $mediaURL = $infoPath.'?t='.$getTimeStamp.'&us='.$getRandom.'&sign='.$mediaSign;

        return $mediaURL;
    }

    /* 获取云点播视频原始播放地址并取出目录
     * @param $infoPath
     * @return string
     * */
    private static function yunMediaDir($infoPath)
    {
        $stringArray = parse_url($infoPath);
        $mediaDir = dirname($stringArray['path']).'/';

        return $mediaDir;
    }

    /* 获取云点播视频播放过期时间
    * @param
    * @return string
    * @说明: 设置6小时过期,必须为16进制
    * */
    public static function expireTime()
    {
        $rt = time() + 10800;
        $expireTime = dechex($rt);
        return $expireTime;
    }
}