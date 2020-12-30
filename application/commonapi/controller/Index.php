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


use app\admin\model\system\AppVersion;
use app\admin\model\system\SystemConfig;
use app\core\util\TencentCosService;
use app\osapi\model\file\Picture;
use basic\ControllerBasic;
use Complex\Exception;
use service\UtilService;
use think\Config;

class Index extends ControllerBasic
{
    /**
     * 腾讯云视频点播签名生成
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function createSignature()
    {
        // 确定 App 的云 API 密钥 -后续做成后台配置项
        $tencent_video_config=SystemConfig::getMore(['tencent_video_is_open','tencent_video_secret_id','tencent_video_secret_key','tencent_video_procedure']);
        if(!$tencent_video_config['tencent_video_is_open']){
            $this->apiError('未开启腾讯云点播');
        }
        $secret_id = $tencent_video_config['tencent_video_secret_id'];//"AKIDFVdXjfpnytl0ylIb7PREqCWaTAbmJRuJ";//todo 做成后台配置项
        $secret_key = $tencent_video_config['tencent_video_secret_key'];//"LwLUPcbKo38B5xZwe7ML7e8OyMZbP2Vs";//todo 做成后台配置项

        // 确定签名的当前时间和失效时间
        $current = time();
        $expired = $current + 86400;  // 签名有效期：1天

        // 向参数列表填入参数
        $arg_list = array(
            "secretId" => $secret_id,
            "currentTimeStamp" => $current,
            "expireTime" => $expired,
            "random" => rand(1,10000),
            "procedure"=>$tencent_video_config['tencent_video_procedure'],//'osx',//todo 任务流，后面改成后台配置项
        );

        // 计算签名
        $original = http_build_query($arg_list);
        $signature = base64_encode(hash_hmac('SHA1', $original, $secret_key, true).$original);
        $this->apiSuccess(['signature'=>$signature]);
    }

    /**
     * 是否开启云点播
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function openTencentVideo()
    {
        $tencent_video_is_open=SystemConfig::getValue('tencent_video_is_open');
        $this->apiSuccess(['is_open_tenvent_video'=>intval($tencent_video_is_open)==1?true:false]);
    }

	/**
	 * 云点播防盗秘钥
	 * @author
	 * @date
	 */
		public function getTencentVideoSaveKey()
		{
			$tencent_video_save_key = SystemConfig::getValue('tencent_video_save_key');

			$this->apiSuccess(['tencent_video_save_key'=>$tencent_video_save_key]);
		}

    /**
     * 获取视频播放openid
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function getTencentVideoAppId()
    {
        $tencent_video_app_id=SystemConfig::getValue('tencent_video_app_id');
        /**加密 start**/
        $iv = "1234567890123412";//16位 向量
        $key= '201707eggplant99';//16位 默认秘钥
        $tencent_video_app_id=urlencode(base64_encode(openssl_encrypt($tencent_video_app_id,"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv)));
        /**加密 end**/

        $this->apiSuccess(['tencent_video_app_id'=>$tencent_video_app_id]);
    }

    /**
     * 图片地址转成base64位内容
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function imageToBase64()
    {
        $image_src=osx_input('post.image_src','');
        $img_src=urldecode($image_src);
        if($img_src==null||$img_src==''){
            $this->apiError('图片地址不能为空');
        }
        try{
            $imageInfo = @getimagesize($img_src);
            $base64_data='data:' . $imageInfo['mime'] . ';base64,' . chunk_split(base64_encode(file_get_contents($img_src)));
            $this->apiSuccess($base64_data);
        }catch (Exception $e){
            $this->apiError('获取图片信息失败:'.$e->getMessage());
        }
    }

    /**
     * 获取客户端缓存标识，确认是否需要清空缓存
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function getLocalStorageVersion()
    {
        $cache_version=osx_input('get.cache_version','');
        $now_version=SystemConfig::getValue('client_local_storage_version');
        if($cache_version!=$now_version){
            $return['new_version']=$now_version;
            $return['clear_storage']=true;
        }else{
            $return['new_version']=['cache_version'=>$cache_version];
            $return['clear_storage']=false;
        }
        $this->apiSuccess($return);
    }

    /**
     * 获取客户端功能点开放情况列表
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function getExtendsOpenList()
    {
        $open_list=$this->_getClientOpenList();//开放情况获取
        $this->apiSuccess($open_list);
    }

    public function getWeixinSet(){
        $data['login']=SystemConfig::getValue('reg_switch');
        $this->apiSuccess($data);
    }

    /**
     * 我的积分
     */
    public function myScore(){
        $uid=get_uid();
        $type=db('system_rule')->where('status',1)->where('is_del',0)->order('id asc')->select();
        $score=db('user')->where('uid',$uid)->field('uid,exp,fly,gong,buy,one,two,three,four,five')->find();
        $data=array();
        foreach ($type as $key=>$value){
            $data[$key]['name']=$value['name'];
            $data[$key]['value']=$score[$value['flag']];
        }
        unset($value);
        unset($key);
        $this->apiSuccess($data);
    }

    /**
     * 我的积分记录
     */
    public function myScoreLog(){
        $type=osx_input('type','all','text');
        $page = osx_input('page',1,'intval');
        $row = osx_input('row', 10,'intval');
        $uid=get_uid();
        switch ($type){
            case 'all':
                $log=db('renwu_jiafen_log')->where('uid',$uid)->page($page,$row)->order('create_time desc')->select();
                $type=db('system_rule')->where('status',1)->where('is_del',0)->order('id asc')->select();
                foreach ($log as &$val){
                    foreach ($type as $key=>$value){
                        $val['score'][$key]['name']=$value['name'];
                        if($val['type']==1){
                            $val['score'][$key]['value']=+$val[$value['flag']];
                        }else{
                            $val['score'][$key]['value']=-$val[$value['flag']];
                        }
                    }
                }
                unset($val);
                unset($value);
                unset($key);
                break;
            case 'plus':
                $log=db('renwu_jiafen_log')->where('uid',$uid)->where('type',1)->page($page,$row)->order('create_time desc')->select();
                $type=db('system_rule')->where('status',1)->where('is_del',0)->order('id asc')->select();
                foreach ($log as &$val){
                    foreach ($type as $key=>$value){
                        $val['score'][$key]['name']=$value['name'];
                        $val['score'][$key]['value']=+$val[$value['flag']];
                    }
                }
                unset($val);
                unset($value);
                unset($key);
                break;
            case 'reduce':
                $log=db('renwu_jiafen_log')->where('uid',$uid)->where('type',0)->page($page,$row)->order('create_time desc')->select();
                $type=db('system_rule')->where('status',1)->where('is_del',0)->order('id asc')->select();
                foreach ($log as &$val){
                    foreach ($type as $key=>$value){
                        $val['score'][$key]['name']=$value['name'];
                        $val['score'][$key]['value']=-$val[$value['flag']];
                    }
                }
                unset($val);
                unset($value);
                unset($key);
                break;
        }
        $this->apiSuccess($log);
    }

    public function toEndImg()
    {
        $localhost_img=osx_input('post.localhost_img','');
        $image_name=osx_input('post.image_name','');

        $file=ROOT.$localhost_img;
        $tmp_info['name']=$image_name;
        $tmp_info['tmp_name']=$file;
        if(!is_file($file)){
            $this->apiSuccess(['result'=>false]);
        }
        $isExist=Picture::checkExist($file);
        if($isExist){
            $res=$isExist;
            @unlink($file);
            $this->apiSuccess(['result' => true, 'end_path' => get_root_path($res['path'])]);
        }else {
            $upload_type=SystemConfig::getValue('picture_store_place');
            switch ($upload_type) {
                case 'Tencent_COS'://腾讯云COS
                    //调用腾讯云上传
                    $result = TencentCosService::tencentCOSUpload($tmp_info);
                    if ($result['result'] == true) {
                        $tencent_file_info=$result['info'];
                        Picture::uploadTencentCOS($tencent_file_info);
                        @unlink($file);
                        $this->apiSuccess(['result' => true,'end_path' => $tencent_file_info['path']]);
                    } else {
                        $this->apiSuccess(['result' => false]);
                    }
                    break;
                case 'local':
                default:
                    $this->apiSuccess(['result' => false]);
            }
        }
    }

    /**
     * 获取最新的app
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_app_version(){
        $app=AppVersion::where(['status'=>1])->order('create_time desc')->find();
        $this->apiSuccess($app);
    }

    /**
     * qhy
     * 获取pc跳转设置信息
     */
    public function get_pc_set(){
        $data=db('pc_set')->where('id',1)->find();
        $this->apiSuccess($data);
    }

    public function cs(){
        action_limit('cs',1);
        write_action_limit('cs',1);
    }
}