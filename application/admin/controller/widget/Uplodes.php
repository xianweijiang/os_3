<?php

namespace app\admin\controller\widget;

use think\Url;
use think\Request;
use think\Controller;
use service\UploadService as Upload;
use service\JsonService as Json;
use service\UtilService as Util;
use service\FormBuilder as Form;

class Uplodes extends Controller
{
    /**
     * 图片管理上传图片
     * @return \think\response\Json
     */
    public function upload()
    {
        $path = input('post.path');
        $res = Upload::file('file',$path.DS.date('Y').DS.date('m').DS.date('d'));
        if(strpos(PUBILC_PATH,'public') == false){
            $res->dir = str_replace('public/','',$res->dir);
        }
        $info = array(
            'code' =>200,
            'msg'  =>'上传失败',
            'src'  =>$res->dir
        );
        if (file_exists(ROOT.$res->dir))
            $info['msg'] = '上传成功';
        echo json_encode($info);
    }
}
