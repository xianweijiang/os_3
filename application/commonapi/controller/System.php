<?php
/**
 * Created by PhpStorm.
 * User: zzl-yf
 * Date: 2020/2/14
 * Time: 15:47
 */

namespace app\commonapi\controller;


use app\commonapi\model\SystemCountLogToShow;
use basic\ControllerBasic;
use service\UtilService;
use app\admin\model\system\SystemConfig;
use think\Cache;
use think\Request;

class System extends ControllerBasic
{

    /**
     * 用户首次访问
     */
    public function firstUser()
    {
        $count_args['platform']=osx_input('post.platform',0);
        $count_args['user_type']=osx_input('post.user_type','');
        $platform=$count_args['platform'];
        if(!in_array($platform,SystemCountLogToShow::$platform_list)){
            $this->apiError('请传入使用平台');
        }
        if($count_args['user_type']!='new'&&$count_args['user_type']!='active'){
            $this->apiError('请传入用户类型');
        }
        $data=[
            'place'=>$platform,
            'type'=>$count_args['user_type'],
            'create_time'=>time()
        ];
        $res=db('system_count_log_user')->insertGetId($data);
        if(!$res){
            $this->apiError('插入记录失败');
        }
        $this->apiSuccess('记录成功');
    }

    /**
     * 用户访问次数
     */
    public function viewCount()
    {
        $count_args['platform']=osx_input('post.platform',0);
        $count_args['num']=osx_input('post.num',0);
        $platform=$count_args['platform'];
        if(!in_array($platform,SystemCountLogToShow::$platform_list)){
            $this->apiError('请传入使用平台');
        }
        if(intval($count_args['num'])<=0){
            $this->apiError('请传入访问次数');
        }
        $data=[
            'place'=>$platform,
            'num'=>intval($count_args['num']),
            'create_time'=>time()
        ];
        $res=db('system_count_log_view')->insertGetId($data);
        if(!$res){
            $this->apiError('插入记录失败');
        }
        $this->apiSuccess('记录成功');
    }

    /**
     * 用户分享次数
     */
    public function shareCount()
    {
        $platform=osx_input('post.platform',0);
        if(!in_array($platform,SystemCountLogToShow::$platform_list)){
            $this->apiError('请传入使用平台');
        }
        $request = Request::instance();
        $tag='share_count_census_'. $request->ip();
        $is_count=Cache::get($tag);
        if(!$is_count){
            Cache::set($tag,'yes',5);
            $data=[
                'place'=>$platform,
                'create_time'=>time()
            ];
            $res=db('system_count_log_share')->insertGetId($data);
            if(!$res){
                $this->apiError('插入记录失败');
            }
        }
        Cache::clear($tag);
        $this->apiSuccess('记录成功');
    }


    /**
     * 获取协议列表
     * @author zxh  zxh@ourstu.com
     */
    public function get_agreement(){
        $data=db('user_agreement')->where(['status'=>1])->select();
        $this->apiSuccess($data);
    }

    /**
     * 获取证件相关内容
     * @author zxh  zxh@ourstu.com
     */
    public function get_company(){
        $company_name=SystemConfig::getValue('company_name');
        $related_information=SystemConfig::getValue('related_information');
        $this->apiSuccess(['company_name' => $company_name, 'related_information' =>$related_information]);
    }

    /**
     * 获取协议的详情内容
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.10
     */
    public function get_agreement_one(){
        $pam_id=osx_input('post.id',0,'intval');;
        if($pam_id<=0){
            $this->apiError('请传入正确的id');
        }
        $data=db('user_agreement')->where(['id'=>$pam_id])->find();
        $this->apiSuccess($data);
    }
}