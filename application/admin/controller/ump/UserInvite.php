<?php
/**
 * Created by PhpStorm.
 * User: zxh
 * Date: 2019/10/25
 * Time: 13:39
 */

namespace app\admin\controller\ump;


use app\admin\controller\AuthController;
use app\osapi\model\user\InviteCode;
use service\JsonService;

class UserInvite extends AuthController
{
    public function index(){
       return $this->fetch();
    }

    public function userInviteList(){
        $start_time=input('start_time','');
        $end_time=input('end_time','');
        $nickname=input('nickname','');
        $mav['uid|nickname']=['like','%'.$nickname.'%'];
        $uid=db('user')->where($mav)->field('uid')->select();
        $uid=array_column($uid, 'uid');
        if($nickname){
            $map['uid']=['in',$uid];
        }
        if($start_time&&$end_time){
            $map['create_time']=['between',[strtotime($start_time),strtotime($end_time)]];
        }
        $map['id']=['neq',0];

        $page=input('page',1);
        $limit=input('limit',10);
        $order='create_time desc';

        return JsonService::successlayui(InviteCode::getInviteList($map,$page,$limit,$order));
    }
}