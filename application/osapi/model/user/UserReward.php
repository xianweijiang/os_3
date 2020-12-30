<?php
/**
 * Created by PhpStorm.
 * User: ylf
 * Date: 2019/1/10
 * Time: 11:28
 */
namespace app\osapi\model\user;

use app\osapi\model\BaseModel;
class UserReward extends BaseModel{

    /**
     * 打赏帖子
     * @param array $data
     * @return bool
     * @author 姚林锋 ylf@ourstu.com
     * @date 2019/2/27 16:25
     */
    public function rewardPost($data = array()){
        if($data['type']==1){
            $val='integral';
        }elseif($data['type']==2){
            $val='now_money';
        }else{
            return false;
        }
        $score = db('user')->where('uid',$data['uid'])->value($val);
        //获取用户的该类型积分值
        if($score < $data['amount'] || $data['author_uid'] == $data['uid']){  //若积分不够或者作者时自己时直接返回
            return false;
        }
        $this->save($data);  //添加打赏记录
        $this->db('user')->where('uid',$data['author_uid'])->setInc($val, $data['amount']);  //增加作者的积分值
        $this->db('user')->where('uid',$data['uid'])->setDec($val, $data['amount']);  //扣去当前用户的积分值

    }

    /**
     * 获取打赏用户列表
     * @param $pid
     * @param bool $all
     * @param int $page
     * @param int $row
     * @return array
     * @author 姚林锋 ylf@ourstu.com
     * @date 2019/2/27 16:27
     */
    public function getRewardUser($pid, $all = true, $page = 1, $row = 10){
        $user = [];
        if(!$all){  //只获取前四个打赏用户
            $uids = $this->where('pid',$pid)->limit(4)->column('uid');
            foreach ($uids as $k) {
                $user[] = $this->userInfo->getUserInfo($k);
            }
        }else{  //获取打赏用户列表
            $rewards = $this->where('pid',$pid)->page($page,$row)->order('create_time desc')->select()->toArray();
            foreach ($rewards as $k => &$v){
                $v['score_name'] = $this->scoreType->_toScoreName($v['type']);  //获取打赏类型名称
            }
            unset($v);
            $uids = array_column($rewards,'uid');
            foreach ($uids as $k) {
                $user[] = $this->userInfo->getUserInfo($k);  //获取用户信息
            }
            foreach ($user as $k => &$v){  //拼接打赏的积分类型名称和值
                foreach ($rewards as $key => &$val){
                    if($v['uid'] == $val['uid']){
                        $v['score_type'] = $val['type'];
                        $v['score_name'] = $val['score_name'];
                        $v['score_amount'] = $val['amount'];
                        $v['reward_time'] = $val['create_time'];
                    }
                }
                unset($val);
            }
            unset($v);
        }
        return $user;
    }

    /**
     * 获取打赏积分的每个类型的总分
     * @param $pid
     * @return array
     * @author 姚林锋 ylf@ourstu.com
     * @date 2019/2/27 16:29
     */
    public function getRewardScore($pid){
        $total = [];
        $allType = $this->getRewardType($pid);  //获取所有积分类型
        foreach ($allType as $k => &$v){
            $count['count'] = $this->where('pid',$pid)->where('type',$k)->sum('amount');  //获取对应类型的打赏积分总数
            $count['name'] = $v;
            array_push($total,$count);
        }
        return $total;
    }

    /**
     * 获取所有打赏过的积分类型和值
     * @param $pid
     * @return mixed
     * @author 姚林锋 ylf@ourstu.com
     * @date 2019/2/27 16:30
     */
    public function getRewardType($pid){
        $allType = $this->scoreType->getTypeList();  //获取积分列表
        $type = $this->distinct(true)->where('pid',$pid)->column('type');  //获取已被打赏过的积分类型
        foreach ($allType as $k => &$v){  //从总的积分类型中筛选出已被打打赏过的积分类型
            if(!in_array($k,$type)){
                unset($allType[$k]);
            }
        }
        unset($v);
        return $allType;
    }
}