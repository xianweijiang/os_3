<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/2/5
 * Time: 10:15
 */

namespace app\admin\controller\com;


use app\admin\controller\AuthController;
use app\admin\model\com\CommentTemplate;
use service\UtilService as Util;
use service\JsonService as Json;
use service\FormBuilder as Form;
use think\Url;

class ComTemplate extends AuthController
{
    public function index(){
       $this->assign([
            'year'=>getMonth('y'),
            'real_name'=>$this->request->get('real_name',''),
        ]);
        return $this->fetch();
    }

    /**
     *修改状态
     */
    public function del_version()
    {
        $id=osx_input('id',0,'intval');
        $status=osx_input('status','','text');
        $map['id']=$id;
        $res=CommentTemplate::setStatus($map,$status);
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
            ['content',''],
            ['is_post',0],
        ],$this->request);
        if($params['is_post']==1){
            $name=$params['id']?'修改':'新增';
            $res=CommentTemplate::editData($params);
            if($res){
                return Json::successful($name.'成功!');
            }else{
                return Json::fail($name.'失败!');
            }
        }else{
            if($params['id']>0){
                $version=CommentTemplate::getDate($params['id']);
            }else{
                $version['content']='';
            }
            $field = [
                Form::textarea('content',' 内容',$version['content']),
                Form::hidden('id',$params['id']),
                Form::hidden('is_post',1),
            ];
            $form = Form::make_post_form('新增/修改',$field,Url::build('edit'),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }

    }

    public function get_template_list(){
        $where = Util::getMore([
            ['order','create_time desc'],
            ['page',1],
            ['limit',20],
            ['real_name',''],
        ]);
        trace($where);
        $map['status']=['egt',0];
        if($where['real_name']){
            $map['version|remark']=['LIKE','%'.$where['real_name'].'%'];
        }
        return Json::successlayui(CommentTemplate::get_template_list($map,$where['page'],$where['limit'],$where['order']));
    }

 
}