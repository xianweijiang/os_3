<?php
namespace app\shopapi\controller;

use app\core\model\UserBill;
use app\core\model\SystemUserLevel;
use app\core\model\SystemUserTask;
use app\core\model\UserLevel;
use app\ebapi\model\store\StoreCategory;
use app\core\model\routine\RoutineFormId;//待完善
use app\ebapi\model\store\StoreCouponIssue;
use app\ebapi\model\store\StoreProduct;
use app\core\util\GroupDataService;
use app\ebapi\model\user\User;
use service\HttpService;
use service\JsonService;
use app\core\util\SystemConfigService;
use service\UploadService;
use service\UtilService;
use service\CacheService;
use app\admin\model\system\SystemConfig;
use think\Cache;

/**
 * 小程序公共接口
 * Class PublicApi
 * @package app\routine\controller
 *
 */
class PublicApi extends AuthController
{
    /*
     * 白名单不验证token 如果传入token执行验证获取信息，没有获取到用户信息
     * */
    public static function whiteList()
    {
        return [
            'index',
            'get_index_groom_list',
            'get_hot_product',
            'refresh_cache',
            'clear_cache',
            'get_logo_url',
            'get_my_naviga',
            'get_store_set',
        ];
    }


    /*
     * 根据经纬度获取当前地理位置
     * */
    public function getlocation(){
        $latitude=osx_input('latitude','');
        $longitude=osx_input('longitude','');
        $location=HttpService::getRequest('https://apis.map.qq.com/ws/geocoder/v1/',
            ['location'=>$latitude.','.$longitude,'key'=>'U65BZ-F2IHX-CGZ4I-73I7L-M6FZF-TEFCH']);
        $location=$location ? json_decode($location,true) : [];
        if($location && isset($location['result']['address'])){
            try{
                $address=$location['result']['address_component']['street'];
                return $this->successful(['address'=>$address]);
            }catch (\Exception $e){
                return $this->fail('获取位置信息失败!');
            }
        }else{
            return $this->fail('获取位置信息失败!');
        }
    }

    /*
     * 根据key来取系统的值
     * */
    public function get_system_value(){
        $key=osx_input('key','');
        $multi=osx_input('multi','');
        if($key=='' && $multi=='') return JsonService::fail('缺少参数');
        if($multi==1 && $key){
            $key=json_decode($key,true);
            return $this->successful(SystemConfigService::more($key));
        }
        $value=SystemConfigService::get($key);
        $value=is_array($value) ? $value[0] : $value;
        return $this->successful([$key=>$value]);
    }

    /*
     * 获取系统
     * */
    public function get_system_group_data_value(){
        $name=osx_input('name','');
        $multi=osx_input('multi',0,'intval');
        if($name=='') return $this->successful([$name=>[]]);
        if($multi==1){
            $name=json_decode($name,true);
            $value=[];
            foreach ($name as $item){
                $value[$item]=GroupDataService::getData($item)?:[];
            }
            return $this->successful($value);
        }else{
            $value= GroupDataService::getData($name)?:[];
            return $this->successful([$name=>$value]);
        }
    }
    /*
     * 删除指定资源
     *
     * */
    public function delete_image(){
        $post=UtilService::postMore([
            ['pic',''],
        ]);
        if($post['pic']=='') return $this->fail('缺少删除资源');
        $post['pic']=substr($post['pic'],1);
        if(file_exists($post['pic'])) unlink($post['pic']);
        if(strstr($post['pic'],'s_')!==false){
            $pic=str_replace(['s_'],'',$post['pic']);
            if(file_exists($pic)) unlink($pic);
        }
        return $this->successful('删除成功');
    }

    /**
     * 上传图片
     * @param string $filename
     * @return \think\response\Json
     */
    public function upload()
    {
        $dir=osx_input('dir','');
        $data = UtilService::postMore([
            ['filename',''],
        ],$this->request);
        $res = UploadService::image($data['filename'],$dir ? $dir: 'store/comment');
        if($res->status == 200)
            return $this->successful('图片上传成功!',['name'=>$res->fileInfo->getSaveName(),'url'=>UploadService::pathToUrl($res->dir)]);
        else
            return $this->fail($res->error);
    }



    /**
     * 刷新数据缓存
     */
    public function refresh_cache(){
        `php think optimize:schema`;
        `php think optimize:autoload`;
        `php think optimize:route`;
        `php think optimize:config`;
    }

    /*
    * 清除系统全部缓存
    * @return
    * */
    public function clear_cache()
    {
        \think\Cache::clear();
    }



}