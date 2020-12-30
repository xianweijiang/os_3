<?php
namespace app\admin\controller\store;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Request;
use app\admin\model\store\StoreSet as SetModel;
use think\Url;


/**
 * 版块控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class StoreSet extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $map_common = ['status'=>1];
        $map_band = ['status'=>0];
        $map_recycle = ['status'=>-1];

        $status=$this->request->param('status');

        //已发布
        $common = SetModel::where($map_common)->count();
        //未发布
        $band = SetModel::where($map_band)->count();
        //已关闭
        $recycle =  SetModel::where($map_recycle)->count();
        $count = array(
            'orderCount'=>1
        );
        $this->assign(compact('status','common','band','recycle','count'));
        return $this->fetch();
    }


    /**
     * 栏目设置列表
     *
     * @return json
     */
    public function set_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['status',     1],
        ]);
        return JsonService::successlayui(SetModel::SetList($where));
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'title',
            'content',
        ],$request);
        SetModel::editSet($data,$id);
        return Json::successful('成功');
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
        $message = SetModel::getOne($id);
        if(!$message) return Json::fail('数据不存在!');
        $field = [
            Form::input('title','标题',$message['title'])->col(Form::col(24)),
            Form::input('content','内容',$message['content'])->type('textarea'),
        ];
        $form = Form::make_post_form('编辑栏目',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

}
