<?php

namespace app\admin\controller\rank;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Db;
use think\Request;
use think\Url;
use think\Cache;
use service\FormBuilder as Form;
use app\admin\model\rank\Rank as RankModel;
use app\admin\model\rank\RankSearch as RankSearchModel;

/**
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class RankSearch extends AuthController
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

    /**
     * @return json
     */
    public function rank_search_list(){
        $where=Util::getMore([
            ['order',''],
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(RankSearchModel::rankSearchList($where));
    }

    public function clear(){
        Cache::rm('search_rank_list');
        Cache::rm('rank_list');
        return JsonService::successful('刷新成功');
    }

    public function create()
    {
        $field = [
            Form::input('keyword','关键词'),
            Form::date('end_time','有效期'),
            Form::number('sort','排序')->col(8),
            Form::radio('status','是否可用')->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]]),
        ];
        $form = Form::make_post_form('添加关键词',$field,Url::build('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function save(Request $request)
    {
        $data = Util::postMore([
            'keyword',
            'end_time',
            'sort',
            'status'
        ],$request);
        if(!$data['keyword']) return Json::fail('请输入关键词');
        $data['end_time']=strtotime($data['end_time']);
        $data['type']=2;
        $data['num']=1;
        $data['is_del']=0;
        RankSearchModel::insert($data);
        Cache::rm('search_rank_list');
        return Json::successful('成功');
    }

    public function edit($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $rank= RankSearchModel::get($id);
        if(!$rank) return Json::fail('数据不存在!');
        $field = [
            Form::input('keyword','关键词',$rank->getData('keyword')),
            Form::date('end_time','有效期',$rank->getData('end_time')),
            Form::number('sort','排序',$rank->getData('sort'))->col(8),
            Form::radio('status','是否可用',$rank->getData('status'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]]),
        ];
        $form = Form::make_post_form('编辑',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
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
            'keyword',
            'end_time',
            'sort',
            'status'
        ],$request);
        if(!$data['keyword']) return Json::fail('请输入关键词');
        $data['end_time']=strtotime($data['end_time']);
        $data['type']=2;
        RankSearchModel::where('id',$id)->update($data);
        Cache::rm('search_rank_list');
        return Json::successful('成功');
    }

    public function rank_del($id=''){
        if($id==''){
            JsonService::fail('缺少参数');
        }
        $res=RankSearchModel::where(['id'=>$id])->update(['is_del'=>1]);
        if($res!==false){
            Cache::rm('search_rank_list');
            return JsonService::successful('下榜成功');
        }else{
            return JsonService::fail('下榜失败');
        }
    }

    public function del_all(){
        $post = Util::postMore([
            ['ids', []]
        ]);
        if (empty($post['ids'])) {
            return JsonService::fail('请选择需要下榜的数据');
        }else{
            $res=RankSearchModel::where('id','in',$post['ids'])->update(['is_del'=>1]);
            if($res!==false){
                Cache::rm('search_rank_list');
                return JsonService::successful('下榜成功');
            }else{
                return JsonService::fail('下榜失败');
            }
        }
    }

}
