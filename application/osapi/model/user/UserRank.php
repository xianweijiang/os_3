<?php
/**
 * Created by PhpStorm.
 * User: ylf
 * Date: 2019/1/7
 * Time: 11:38
 */
namespace app\osapi\model\user;


use app\osapi\model\BaseModel;
class UserRank extends BaseModel{
    /**
     * 用户签到操作
     * @param $uid
     * @return bool
     * @author 姚林锋 ylf@ourstu.com
     * @date 2019/2/27 16:16
     */
    public function memberCheckIn($uid){
        $user = $this->where('uid',$uid)->find();  //查看是否签到过
        if($user){
            $data['total_check'] = $user['total_check'] += 1;  //累签数加一
            if(substr($user['update_time'],0,10) == date("Y-m-d",strtotime("-1 day"))){
                //如果上次签到时昨天，则连签数加一
                $data['con_check'] = $user['con_check'] += 1;
            }elseif(substr($user['update_time'],0,10) == date('Y-m-d',time())){
                //如果今天签到过，则返回
                return true;
            }else{  //此外连签数置为一
                $data['con_check'] = 1;
            }
            $this->isUpdate(true)->save($data,['id' => $user['id']]);  //更新签到记录
        }else{  //添加签到记录
            $data = [
                'uid' => $uid,
                'total_check' => 1,
                'con_check' => 1,
            ];
            $this->save($data);
        }
        $this->userInfo->save($data,['uid' => $uid]);  //用户信息更新
        return true;
    }

    /**
     * 获取用户排名和签到数、累签数和更新时间
     * @param $uid
     * @author 姚林锋 ylf@ourstu.com
     * @date 2019/2/23 17:03
     */
    public function memberCheck($uid){
        $count = $this->where('uid',$uid)->field('con_check,total_check,update_time')->find();  //查看用户签到记录
        if($count == null){  //无记录
            $count['total_check'] = 0;
            $count['con_check'] = 0;
            $count['rank'] = NULL;
            $count['time'] = NULL;
        }else{
            if( date('Y-m-d',strtotime($count['update_time'])) == date('Y-m-d',time())){  //若今日签到过，则计算排名和时间
                $allCount = $this->whereTime('update_time','d')->where('update_time','<',strtotime($count['update_time']))->count();
                $count['rank'] = $allCount + 1;
                $count['time'] = date('H:i',strtotime($count['update_time']));
            }else{
                $count['rank'] = NULL;
                $count['time'] = NULL;
            }
        }
        unset($count['update_time']);
        $count = json_decode(json_encode($count),true);  //转化为数组
        return $count;
    }

    /**
     * 获取今日排名列表和累签列表
     * @param int $type
     * @param int $page
     * @param int $row
     * @return array
     * @author 姚林锋 ylf@ourstu.com
     * @date 2019/2/27 16:22
     */
    public function getCheckList($type = 1,$page = 1,$row = 10){
        if($type == 2){  //获取累签列表
            $list = $this->order('total_check')->page($page,$row)->select()->toArray();
        }else{  //获取今日签到列表
            $list = $this->whereTime('update_time','d')->order('update_time desc')->page($page,$row)->select()->toArray();
        }
        foreach ($list as $k => &$v){
            $v['user'] = $this->userInfo->getUserInfo($v['uid']);  //获取用户信息
            $v['time'] = date('H:i',strtotime($v['update_time']));
        }
        unset($v);
        return $list;
    }
}