<?php

namespace app\admin\controller\group;

use app\admin\controller\AuthController;
use app\admin\model\group\Group;
use app\admin\model\group\Power;
use Doctrine\Common\Cache\Cache;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use app\admin\model\invite\InviteCode as InviteCodeModel;
use think\Db;
use think\Url;

/**
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class GroupPower extends AuthController
{

    /**
     * 首页
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.25
     */
    public function index(){
        $pam=Util::getMore([
           ['group_type',0]
        ]);
        switch ($pam['group_type']){
            case 1:$name='管理组';break;
            case 2:$name='系统用户组';break;
            case 3:$name='晋级用户组';break;
            case 4:$name='会员用户组';break;
            case 5:$name='认证用户组';break;
            case 6:$name='自定义用户组';break;
            default:$name='';
        }
        //初始化 晋级系统用户
        $this->assign([
            'group_type'=>$pam['group_type'],
            'name'=>$name,
        ]);
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
        $res=Group::setStatus($map,$status);
        if($res){
            return Json::successful('修改成功!');
        }else{
            return Json::fail('修改失败!');
        }
    }
    /**
     * 获取族群
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.25
     */
    public function get_group_list(){
        $pam=Util::getMore([
            ['group_type',0],
            ['page',1],
            ['limit',10]
        ]);
        $map['type']=$pam['group_type'];
        $map['status']=1;
        return Json::successlayui(Group::get_group_list($map,$pam['page'],$pam['limit'],'id asc'));
    }

    /**
     *编辑/新增  用户组信息
     * @return mixed|void
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function edit(){
        $params = Util::getMore([
            ['id',0],
            ['name',''],
            ['remark',''],
            ['level',''],
            ['cate',''],
            ['type','自定义'],
            ['is_post',0],
        ],$this->request);
        if($params['is_post']==1){
            $name=$params['id']?'修改':'新增';
            $res=Group::editData($params);
            if($res){
                \think\Cache::clear('group_by_uid');
                return Json::successful($name.'成功!');
            }else{
                return Json::fail($name.'失败!');
            }
        }else{
            if($params['id']>0){
                $params=Group::getDate($params['id']);
            }
            $field = [
                Form::input('name','自定义用户组名称',$params['name']),
                Form::input('remark','角色描述',$params['remark']),
                Form::select('level','管理级别',(string)$params['level'])->options([
                    ['label'=>'一级','value'=>1],
                    ['label'=>'二级','value'=>2],
                    ['label'=>'三级','value'=>3],
                    ['label'=>'四级','value'=>4],
                    ['label'=>'五级','value'=>5],
                    ['label'=>'六级','value'=>6],
                ])->col(24),
                Form::input('cate','类型',$params['cate'])->disabled(1),
//                Form::select('cate','类型',$params['cate'])->options([['label'=>'自定义','value'=>'自定义'],['label'=>'内置','value'=>'内置']]),
                Form::hidden('id',$params['id']),
                Form::hidden('type',$params['type']),
                Form::hidden('is_post',1),
            ];
            $form = Form::make_post_form('新增/修改',$field,Url::build('edit'),2);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }

    }


    /**
     * 设置权限
     * @return mixed|void
     * @author zxh  zxh@ourstu.com
     *时间：202.3.26
     */
    public function edit_manage_power(){
        $params = Util::getMore([
            ['g_id',0],
            ['manage_type'],
            ['is_post'],
            ['uid',0]
        ],$this->request);

        //选择内容
        $html='_all';
        switch ($params['manage_type']){
            case 1:
                //管理权限
                $map['type']=3;
                $html='_manage';
                break;
            case 2:
                //社区权限
                $map['type']=['lt',3];
                $html='_base';
                break;
            default:$map['status']=1;
        }

        if($params['is_post']==1){
            $name='修改';
            $power_get=Power::get_power($map);
            $data = Util::getMore($power_get,$this->request);
            //删除传的值
            foreach ($data as $key=>$vo){
                if(empty($vo)&&$vo!=="0"){
                    unset($data[$key]);
                }
            }
            unset($key,$vo);
            $res=Power::bind_group_power($params['g_id'],$data);
            if($res){
                \think\Cache::clear('group_by_uid');
                return Json::successful($name.'成功!');
            }else{
                return Json::fail($name.'失败!');
            }
        }else{
            $power=Power::getAllPower($map,$params['g_id'],$params['uid']);
            $this->assign([
                'power'=>$power,
                'g_id'=>$params['g_id'],
                'manage_type'=>$params['manage_type']
            ]);
            return $this->fetch($html);
        }
    }
}
