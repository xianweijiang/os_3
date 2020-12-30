<?php

namespace app\admin\controller\shop;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\JsonService as Json;
use think\Request;
use think\Url;
use service\UtilService;


/**
 * 商品管理
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class ShopType extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->fetch();
    }

    public function edit_score_type()
    {
        $type=db('shop_score_type')->where('id',1)->find();
        $field[]=Form::select('flag','积分商城类型',$type['flag'])->setOptions(function(){
            $list = db('system_rule')->where('is_del',0)->where('status',1)->select();
            $menus=[];
            foreach ($list as $menu){
                $menus[] = ['value'=>$menu['flag'],'label'=>$menu['name']];
            }
            return $menus;
        })->filterable(1);
        $form = Form::make_post_form('积分商城类型',$field,Url::build('save_score_type'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function save_score_type(Request $request)
    {
        $data = UtilService::postMore([
            'flag',
        ],$request);
        if(!$data['flag']) return Json::fail('积分商城类型不能为空');
        $res=db('shop_score_type')->where('id',1)->update($data);
        if($res===false){
            return JsonService::fail('积分商城类型修改失败');
        }else{
            return JsonService::successful('积分商城类型修改成功');
        }
    }

}
