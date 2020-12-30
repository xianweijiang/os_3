<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\prohibit;

use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use app\admin\model\user\User as UserModel;

/**
 * 产品管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class Prohibit extends ModelBasic
{
    use ModelTrait;

    /*
     * 获取产品列表
     * @param $where array
     * @return array
     *
     */
    public static function ProhibitList($where){
        $model = new self;
        $model = self::getModelObject($where,$model);
        $data=($data=$model->order('id desc')->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $userInfo=UserModel::where('uid',$item['uid'])->field('uid,nickname,avatar')->find();
            $item['avatar']=$userInfo['avatar'];
            $item['nickname']=$userInfo['nickname'];
            $item['forum_name']=db('com_forum')->where('id',$item['fid'])->value('name');
            if($item['operation_identity']==1){
                $item['operation_nickname']=db('system_admin')->where('id',$item['operation_uid'])->value('real_name');
            }else{
                $item['operation_nickname']=UserModel::where('uid',$item['operation_uid'])->value('nickname');
            }
            switch($item['operation_identity']){
                case 1:
                    $item['operation_identity']='后台管理员';
                    break;
                case 2:
                    $item['operation_identity']='超级版主';
                    break;
                case 3:
                    $item['operation_identity']='版主';
                    break;
                case 4:
                    $item['operation_identity']='前台管理员';
                    break;
            }
            if($item['relieve_identity']==1){
                $item['relieve_nickname']=db('system_admin')->where('id',$item['relieve_uid'])->value('real_name');
            }else{
                $item['relieve_nickname']=UserModel::where('uid',$item['relieve_uid'])->value('nickname');
            }
            switch($item['relieve_identity']){
                case 1:
                    $item['relieve_identity']='后台管理员';
                    break;
                case 2:
                    $item['relieve_identity']='超级版主';
                    break;
                case 3:
                    $item['relieve_identity']='版主';
                    break;
                case 4:
                    $item['relieve_identity']='前台管理员';
                    break;
            }
            if($item['prohibit_reason']>0){
                $item['reason']=db('prohibit_reason')->where('id',$item['prohibit_reason'])->value('name');
            }else{
                $item['reason']=$item['other_reason'];
            }
            $time_type=db('report_prohibit')->where('id',$item['prohibit_time'])->find();
            switch($time_type['time_type']){
                case 1:
                    $time_type['time_type']='小时';
                    break;
                case 2:
                    $time_type['time_type']='天';
                    break;
            }
            $item['time']=$time_type['num'].$time_type['time_type'];
            $item['count']=self::where('uid',$item['uid'])->count();
            switch($item['status']){
                case 1:
                    $item['status_type']='禁言中';
                    break;
                case 0:
                    $item['status_type']='禁言失效';
                    break;
                case 2:
                    $item['status_type']='已解禁';
                    break;
            }
            if($item['status']==1 && $item['end_time']<time()){
                $item['status_type']='已解禁';
            }
            $item['create_time']=time_format($item['create_time']);
        }
        $count=self::getModelObject($where,$model)->count();
        return compact('count','data');
    }

    public static function ProhibitAllList($where){
        $data=($data=db('report')->where('status',1)->where('prohibit_time','>',time())->order('id desc')->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $userInfo=UserModel::where('uid',$item['to_uid'])->field('uid,nickname,avatar')->find();
            $item['avatar']=$userInfo['avatar'];
            $item['nickname']=$userInfo['nickname'];
            if($item['operation_identity']==1){
                $item['operation_nickname']=db('system_admin')->where('id',$item['operation_uid'])->value('real_name');
            }else{
                $item['operation_nickname']=UserModel::where('uid',$item['operation_uid'])->value('nickname');
            }
            switch($item['operation_identity']){
                case 1:
                    $item['operation_identity']='后台管理员';
                    break;
                case 2:
                    $item['operation_identity']='超级版主';
                    break;
                case 3:
                    $item['operation_identity']='版主';
                    break;
            }
            $item['reason']=db('report_reason')->where('id',$item['prohibit_reason'])->value('name');
            $time_type=db('report_prohibit')->where('id',$item['prohibit_time'])->find();
            switch($time_type['time_type']){
                case 1:
                    $time_type['time_type']='小时';
                    break;
                case 2:
                    $time_type['time_type']='天';
                    break;
            }
            $item['time']=$time_type['num'].$time_type['time_type'];
            $item['status_type']='禁言中';
            $item['create_time']=time_format($item['create_time']);
        }
        $count=db('report')->where('status',1)->where('prohibit_time','>',time())->count();
        return compact('count','data');
    }

    public static function getModelObject($where,$model){
        if (!empty($where)) {
            if($where['identity'] != ''){
                $model->where('operation_identity', $where['identity']);
            }
            if($where['time'] != ''){
                $model->where('prohibit_time', $where['time']);
            }
            if($where['reason'] != ''){
                $model->where('prohibit_reason', $where['reason']);
            }
            if($where['status'] != ''){
                $model->where('status',$where['status']);
            }
        }
        return $model;
    }

}