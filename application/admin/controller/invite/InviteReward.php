<?php

namespace app\admin\controller\invite;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use app\admin\model\invite\InviteReward as InviteRewardModel;
use think\Db;
use think\Request;

/**
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class InviteReward extends AuthController
{

    /**
     * 显示首页列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 邀请奖励列表
     *
     * @return json
     */
    public function invite_reward_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(InviteRewardModel::rewardList($where));
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function create_reward()
    {
        $level=InviteRewardModel::where('status',1)->column('level');
        $i=0;
        do{
            $i++;
        }while(in_array($i,$level));
        $score=db('system_rule')->where('status',1)->where('is_del',0)->select();
        $this->assign('score',$score);
        $this->assign('level',$i);
        $this->assign('style','create');
        return $this->fetch();
    }

    public function add_reward(Request $request){
        $post  = $request->post();
        $data = Util::postMore([
            'type',
            'num',
            'reward_type',
            'level',
        ],$request);
        $data['reward']=$_POST['reward'];
        $data['status']=1;
        $data['reward']=json_encode($data['reward']);
        if($data['level']>1){
            $level=$data['level']-1;
            $num=InviteRewardModel::where('level',$level)->value('num');
            if($num>=$data['num']){
                JsonService::fail('人数必须比上一等级高');
            }
        }
        $result = InviteRewardModel::create($data);
        if ($result) {
            Json::successful('创建成功');
        } else {
            JsonService::fail('创建失败');
        }
    }

    public function edit(Request $request){
        $post  = $request->post();
        $data = Util::postMore([
            'id',
            'type',
            'num',
            'reward_type',
        ],$request);
        $data['reward']=$_POST['reward'];
        $data['reward']=json_encode($data['reward']);
        $result = InviteRewardModel::where('id',$data['id'])->update($data);
        if ($result!==false) {
            Json::successful('编辑成功');
        } else {
            JsonService::fail('编辑失败');
        }
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function edit_reward($id)
    {
        $reward=InviteRewardModel::get($id);
        $score=db('system_rule')->where('status',1)->where('is_del',0)->select();
        $reward['reward']=json_decode($reward['reward'],true);
        if($reward['reward_type']=='积分奖励'){
            if($reward['reward']){
                $re=$reward['reward'];
                foreach ($re as &$value){
                    $value['name']=db('system_rule')->where('flag',$value['flag'])->value('name');
                }
                unset($value);
                $reward['reward']=$re;
            }
        }
        $this->assign('reward',$reward);
        $this->assign('score',$score);
        $this->assign('level',$reward['level']);
        $this->assign('style','edit');
        return $this->fetch('create_reward');
    }

    public function del($id=''){
        if($id==''){
            JsonService::fail('缺少参数');
        }
        $level=InviteRewardModel::where('id',$id)->value('level');
        $res=InviteRewardModel::where('level','>=',$level)->delete();
        if($res!==false){
            return JsonService::successful('删除成功');
        }else{
            return JsonService::fail('删除失败');
        }
    }


    public function reward_remarks(){
        $data['is_post'] = osx_input('post.is_post',0,'intval');
        $data['remarks'] = osx_input('post.remarks',0,'html');
        if($data['is_post']==1){
            $res=db('system_config')->where(['menu_name'=>'invite_reward_remark'])->update(['value'=>$data['remarks']]);
            if($res!==false){
                return JsonService::successful('修改成功');
            }else{
                return JsonService::fail('修改失败');
            }
        }else{
            $remarks=db('system_config')->where(['menu_name'=>'invite_reward_remark'])->value('value');
            $this->assign('remarks', $remarks);
            return $this->fetch();
        }
    }
}
