<?php
/**
 * Created by PhpStorm.
 * User: zxh
 * Date: 2019/9/29
 * Time: 10:07
 */
namespace app\admin\controller\prohibit;

use app\admin\controller\AuthController;
use app\osapi\model\com\Report;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Request;
use think\Url;
use app\admin\model\prohibit\ProhibitReason as ReasonModel;

class ProhibitReason extends AuthController
{

    public function index(){
        return $this->fetch();
    }

    public function reason_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(ReasonModel::ReasonList($where));
    }

    public function create()
    {
        $field = [
            Form::input('name', '禁言理由'),
            Form::input('sort', '排序'),
        ];
        $form = Form::make_post_form('添加理由', $field, Url::build('save'), 2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'id',
            'name',
            'sort',
        ],$request);
        if(!$data['name']) return Json::fail('请输入举报理由');
        if($data['sort']<0) return Json::fail('请输入排序');

        if($data['id']){
            $res=ReasonModel::where(['id'=>$data['id']])->update($data);
            $info='修改禁言理由';
        }else{
            $res=ReasonModel::insert($data);
            $info='创建禁言理由';
        }
        if($res!==false) {
            return Json::successful($info.'成功!');
        }else{
            return Json::fail($info.'失败!');
        }
    }

    /**编辑通知模板
     * @param $id
     * @return mixed|void
     */
    public function edit($id)
    {
        $data = ReasonModel::where(['id'=>$id])->find();
        if(!$data) return JsonService::fail('数据不存在!');

        $this->assign('info',$data);
        return $this->fetch('edit');
    }

    /**
     * 删除禁言理由
     * @param $id
     * @author qhy
     */
    public function delete($id){
        $res = ReasonModel::where(['id'=>$id])->update(['status'=>-1]);
        if($res) {
            return Json::successful('删除成功!');
        }else{
            return Json::fail('删除失败!');
        }
    }

}