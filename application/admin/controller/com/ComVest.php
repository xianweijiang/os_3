<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/2/5
 * Time: 10:15
 */

namespace app\admin\controller\com;


use app\admin\controller\AuthController;
use app\admin\model\com\Vest;
use Doctrine\Common\Cache\Cache;
use service\UtilService as Util;
use service\JsonService as Json;
use service\FormBuilder as Form;
use think\Url;

class ComVest extends AuthController
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
        $status=osx_input('status',0,'intval');
        $map['id']=$id;
        $res=Vest::setStatus($map,$status);
        if($res){
            return Json::successful('修改成功!');
        }else{
            return Json::fail('修改失败!');
        }
    }
    /**
     *批量注销
     */
    public function del_vest()
    {
        $id=osx_input('id',0,'text');
        $status=-1;
        $id=explode(',',$id);
        $map['id']=['in',$id];
        $res=Vest::setStatus($map,$status);
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
            ['bind_uid',''],
            ['nickname',''],
            ['avatar',''],
            ['phone',''],
            ['sex',0],
            ['signature',''],
            ['mark',''],
            ['is_post',0],
        ],$this->request);
        if($params['is_post']==1){
            $name=$params['id']?'修改':'新增';
            if($params['phone']!==''){
                preg_match("/^1[3456789][0-9]{9}$/", $params['phone'], $match_phone);
                if (!$match_phone) {
                    return Json::fail('请输入正确的手机号码');
                }
                $bind_uid=db('vest')->where(['id'=>$params['id']])->value('bind_uid');
                $is_exit=db('user')->where(['uid'=>['neq',$bind_uid],'phone'=>$params['phone']])->count();
                if($is_exit>0){
                    return Json::fail('手机号码已经注册过了');
                }
            }
            $res=Vest::editData($params);
            if($res){
                return Json::successful($name.'成功!');
            }else{
                return Json::fail($name.'失败!');
            }
        }else{
            if($params['id']>0){
                $version=Vest::getDate($params['id']);
            }else{
                $version['bind_uid']=$version['nickname']=$version['avatar']=$version['phone']=$version['sex']=$version['signature']= $version['remark']='';
            }
            $field = [
                Form::input('bind_uid','用户编号',$version['bind_uid'])->disabled(1),
                Form::input('nickname','用户昵称',$version['nickname']),
                Form::frameImageOne('avatar','用户头像',Url::build('admin/widget.images/index',array('fodder'=>'avatar')),$version['avatar'])->icon('image')->width('100%')->height('500px'),
                Form::input('phone','手机号',$version['phone']),
                Form::radio('sex','性别',$version['sex'])->options([['label'=>'保密','value'=>0],['label'=>'男','value'=>1],['label'=>'女','value'=>2]]),
                Form::textarea('signature','个人简介',$version['signature']),
                Form::textarea('mark','备注',$version['mark']),
                Form::hidden('id',$params['id']),
                Form::hidden('is_post',1),
            ];
            $form = Form::make_post_form('新增/修改',$field,Url::build('edit'),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }

    }

    public function get_vest_list(){
        $where = Util::getMore([
            ['order','id desc'],
            ['page',1],
            ['limit',20],
            ['real_name',''],
        ]);
        trace($where);
        $map['status']=['egt',0];
        if($where['real_name']){
            $map['version|remark']=['LIKE','%'.$where['real_name'].'%'];
        }
        return Json::successlayui(Vest::get_vest_list($map,$where['page'],$where['limit'],$where['order']));
    }

    /**
     * 批量添加马甲
     * @return mixed|void
     */
    public function add_vest(){
        set_time_limit(0);
        $data = Util::getMore([
            ['number',0],
            ['min_number',0],
            ['max_number',0],
            ['is_post',0],
        ]);
        if($data['is_post']==1){
            if($data['number']<=0){
                return Json::fail('请填写正确的数量!');
            }
            if($data['max_number']<$data['min_number']){
                return Json::fail('取值范围错误!');
            }
            $res=Vest::add_vest($data['number'],$data['max_number'],$data['min_number']);
            if($res){
                return Json::successful('添加成功!');
            }else{
                return Json::fail('添加失败!');
            }
        }else{
            return $this->fetch();
        }
    }

    public function cs(){
        $time=  cache('time_value');
        dump($time);
    }
}