<?php
/**
 * Created by PhpStorm.
 * User: zxh
 * Date: 2020/3/31
 * Time: 9:20
 */
namespace app\commonapi\controller;


use app\admin\model\com\ForumPower;
use app\admin\model\com\VisitAudit;
use app\admin\model\group\Group;
use app\osapi\controller\Base;
use service\JsonService;
class Power extends Base
{

    /**
     * 判断用户是否有权限
     * @param $action
     * @param $uid
     * @param $pam
     * @return bool
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.31
     */
    public static function action_power($action,$uid,$pam){
        $uid=$uid?$uid:get_uid();
        //获取权限
        $power=Group::get_power_value($uid);

        //特殊处理化
        switch ($action){
            case 'send_thread_count':
                //发帖数量
                $count=db('com_thread')->where(['author_uid'=>$uid,'from'=>['neq','HouTai'],'create_time'=>['between',[time()-24*3600,time()]]])->count();
                if(array_key_exists($action, $power)){
                    if($power[$action]>$count){
                        return true;
                    }else{
                        return JsonService::success('error',['info'=>'24小时内允许发帖数量为'.$power[$action].',已达到上限制']);
                    }
                }
                ;break;
            case 'set_top':
                //设置置顶
                if(array_key_exists($action, $power)){
                    if($power[$action]>=$pam['set_top']){
                        return true;
                    }
                }
                $err_message=db('power')->where(['sign'=>$action])->cache('power_sign_'.$action)->value('error_message');
                return JsonService::success('error',['info'=>$err_message]);
                break;
            case 'audit':
                if(array_key_exists($action, $power)){
                    return $power[$action];
                }else{
                    return 1;
                }
                break;
            default:
                if(array_key_exists($action, $power)){
                    if($power[$action]==1){
                        return true;
                    }
                }
                $err_message=db('power')->where(['sign'=>$action])->cache('power_sign_'.$action)->value('error_message');
                return JsonService::success('error',['info'=>$err_message]);
        }
        return true;
    }

    /**
     * 判断用户是否有权限
     * @param $action
     * @param $uid
     * @param $fid
     * @return bool
     * @author zxh  zxh@ourstu.com
     *时间：2020.3.31
     */
    public static function forum_power($action,$uid,$fid){
        //审核自动通过
        VisitAudit::set_audit_show();

        $uid=$uid?$uid:get_uid();
        //获取权限
        $power=ForumPower::get_forum_user_power($uid,$fid);
        return $power[$action];
    }

    /**
     * 判断用户是否有版块权限
     * @param $action
     * @param $uid
     * @param $fid
     * @return bool|void
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.9
     */
    public static function forum_power_error($action,$uid,$fid){
        $val=['audit','visit','send_thread','send_comment','browse_power'];
        $power=self::forum_power($action,$uid,$fid);
        if(in_array($action,$val)){
            $error_message=[
                'audit'=>'没有审核通过',
                'visit'=>'没有访问权限',
                'send_thread'=>'没有发帖权限',
                'send_comment'=>'没有评论权限',
                'browse_power'=>'没有浏览权限'
            ];
            if($power==0){
                return JsonService::success('error',['info'=>$error_message[$action]]);
            }
        }
        return $power;
    }
}