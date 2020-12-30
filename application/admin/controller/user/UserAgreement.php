<?php
namespace app\admin\controller\user;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use app\admin\model\user\UserAgreement as AgreementModel;
use think\Url;
use app\admin\model\system\SystemAttachment;
use app\admin\model\user\User as UserModel;
use app\admin\model\com\ComThread as ThreadModel;
use app\admin\model\com\ComThreadClass as ThreadClassModel;
use app\admin\model\com\ComForum as ForumModel;

/**
 * 版块控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class UserAgreement extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $map_common = ['status'=>1];
        $status=1;
        //已发布
        $common = AgreementModel::where($map_common)->count();
        $count = array(
            'orderCount'=>1
        );
        $this->assign(compact('status','common','count'));
        return $this->fetch();
    }


    /**
     * @return json
     */
    public function agreement_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['status',     1],
        ]);
        return JsonService::successlayui(AgreementModel::AgreementList($where));
    }


    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $Agreement = AgreementModel::getOne($id);
        if(!$Agreement) return Json::fail('数据不存在!');
        $this->assign('Agreement',$Agreement);
        return $this->fetch('edit');
    }

    public function edit_agreement(Request $request){
        $data = Util::postMore([
            'name',
            'id',
        ],$request);
        $data['content']=osx_input('post.content','','html');
        if(!$data['name']){
            JsonService::fail('协议名称不能为空');
        }
        if(mb_strlen($data['name'],'UTF-8')>8){
            JsonService::fail('协议名称最多只允许八个字。');
        }
        $result = AgreementModel::where('id',$data['id'])->update($data); //新增帖子内容到数据库，事务写法，过程中涉及很多数据库操作
        if ($result) {
            $res['info']='编辑成功';
            Json::successful($res);
        } else {
            JsonService::fail('编辑失败');
        }
    }

}
