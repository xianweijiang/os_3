<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/2/5
 * Time: 10:15
 */

namespace app\admin\controller\system;


use app\admin\controller\AuthController;
use app\admin\model\system\AppVersion;
use service\UploadService as Upload;
use service\UtilService as Util;
use service\JsonService as Json;
use app\admin\model\system\SystemConfig as ConfigModel;
use app\core\util\TencentCosService;
use app\osapi\model\file\Picture;
use think\Config;

class SystemVersion extends AuthController
{
    public function index(){
        return $this->fetch();
    }

    /**
     *修改状态
     * @param $id
     * @param $status
     */
    public function del_version($id,$status)
    {
        $map['id']=$id;
        $res=AppVersion::setStatus($map,$status);
        if($res){
            return Json::successful('修改成功!');
        }else{
            return Json::fail('修改失败!');
        }
    }

    /**
     *编辑/新增 授权信息
     * @return mixed|void
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function edit(){
        $params = Util::getMore([
            ['id',0],
            ['version',''],
            ['url',[]],
            ['remark',''],
            ['is_post',0],
        ],$this->request);
        if($params['is_post']){
            $name=$params['id']?'修改':'新增';
            if(empty($params['url'][0])){
                return Json::fail('请上传app!');
            }
            if(empty($params['version'])){
                return Json::fail('请填写版本号!');
            }
            if(empty($params['remark'])){
                return Json::fail('请填写更新说明!');
            }

            if(strpos($params['url'][0],$_SERVER['SERVER_NAME']) === false){
                $params['url']= 'http://'.$_SERVER['SERVER_NAME'].$params['url'][0];
            }
            $res=AppVersion::editData($params);
            if($res){
                return Json::successful($name.'成功!');
            }else{
                return Json::fail($name.'失败!');
            }
        }else{
            $type = 1;
            $tab_id = 4;
            if(!$tab_id) $tab_id = 1;
            $this->assign('tab_id',$tab_id);
            $config_tab = ConfigModel::getConfigTabAll($type);
            if($params['id']){
                $data=AppVersion::getDate($params['id']);
            }else{
                $data['id']=$data['version']=$data['url']=$data['remark']='';
            }
            $this->assign('id',$params['id']);
            $list=[
                1=>[
                    'id'=>1,
                    "menu_name"=>'version',
                    "type"=>'text',
                    'status'=>1,
                    'config_tab_id'=>4,
                    'info'=>'版本号',
                    'value'=>$data['version'],
                    'required'=>'',
                    'width'=>100,
                    'high'=>0,
                    'upload_type'=>0,
                    'desc'=>'版本号'
                ],
                2=>[
                    'id'=>1,
                    "menu_name"=>'url',
                    "type"=>'upload',
                    'status'=>1,
                    'config_tab_id'=>4,
                    'info'=>'app安装包',
                    'value'=>[$data['url']],
                    'required'=>'',
                    'width'=>100,
                    'high'=>0,
                    'upload_type'=>3,
                    'desc'=>'app安装包'
                ],
                3=>[
                    'id'=>1,
                    "menu_name"=>'remark',
                    "type"=>'textarea',
                    'status'=>1,
                    'config_tab_id'=>4,
                    'info'=>'更新说明',
                    'value'=>$data['remark'],
                    'required'=>'',
                    'width'=>100,
                    'high'=>5,
                    'upload_type'=>0,
                    'desc'=>'更新说明'
                ],
            ];
            $this->assign('config_tab',$config_tab);
            $this->assign('list',$list);
            return $this->fetch();
        }

    }

    public function get_version_list(){
        $where = Util::getMore([
            ['order','create_time desc'],
            ['page',1],
            ['limit',20],
        ]);
        $map['status']=1;
        return Json::successlayui(AppVersion::get_version_list($map,$where['page'],$where['limit'],$where['order']));
    }

    public function view_upload(){
        if($_POST['type'] == 3){
            $res = Upload::file($_POST['file'],'config/file');
            if(!$res->status) return Json::fail($res->error);
            return Json::successful('上传成功!',['url'=>$res->dir]);
        }else{
            $file = request()->file($_POST['file']);
            $tmp_info=$file->getInfo();
            $isExist=Picture::checkExist($tmp_info['tmp_name']);
            if($isExist){
                return Json::successful('上传成功!', ['url' => $isExist['path']]);
            }else {
                $upload_type = ConfigModel::getValue('picture_store_place');
                switch ($upload_type) {
                    case 'Tencent_COS':
                        $picture_upload=Config::get('TENCENT_COS_PICTURE_UPLOAD');
                        if(!$file->check($picture_upload)){
                            $err=$file->getError();
                            return Json::fail('图片上传失败：'.$err);
                        }
                        //调用腾讯云上传
                        $result = TencentCosService::tencentCOSUpload($tmp_info);
                        if($result['result']==true){
                            Picture::uploadTencentCOS($result['info']);
                            return Json::successful('上传成功!', ['url' => $result['info']['path']]);
                        }else{
                            return Json::fail($result['info']);
                        }
                        break;
                    case 'local':
                    default:
                        $picture_upload=Config::get('PICTURE_UPLOAD');
                        $info = $file->validate($picture_upload)->rule($picture_upload['nameBuilder'])->move($picture_upload['rootPath']);
                        if ($info) {
                            // 成功上传后 获取上传信息
                            Picture::upload($info);
                            return Json::successful('上传成功!', ['url' => $picture_upload['db_rootPath'].'/'.$info->getSaveName()]);
                        }else{
                            return Json::fail($file->getError());
                        }
                }
            }
        }
    }

    /**
     * token值展示
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.13
     */
    public function token_show(){
        $data=self::get_access_token();
        $this->assign([
            'data'=>$data,
        ]);
        return $this->fetch();
    }
}