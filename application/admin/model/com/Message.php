<?php
/**
 *
 * @author: cyx<cyx@ourstu.com>
 * @day: 2019/4/12
 */

namespace app\admin\model\com;

use service\PHPExcelService;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService;
use app\admin\model\user\User as UserModel;
/**
 * 版块 model
 * Class ComForum
 * @package app\admin\model\com
 */
class Message extends ModelBasic
{
    use ModelTrait;

    public static function createMessage($data){
        $data['content']=html($data['content']);
        $thread_id=self::insert($data);
        if($thread_id){
            return 1;
        }else{
            return 0;
        }
    }

    public static function delete_ann($id){
        $thread_id=self::where('id',$id)->update(['status' => -1]);
        if($thread_id){
            return 1;
        }else{
            return 0;
        }
    }

    public static function getOne($id){
        $res=self::where('id',$id)->find()->toArray();
        return $res;
    }

    public static function close($id){
        $thread_id=self::where('id',$id)->update(['status' => 0]);
        if($thread_id){
            return 1;
        }else{
            return 0;
        }
    }

    public static function open($id){
        $thread_id=self::where('id',$id)->update(['status' => 1,'send_time'=>time()]);
        if($thread_id){
            return 1;
        }else{
            return 0;
        }
    }

    /*
     * 获取通知列表
     * @param $where array
     * @return array
     *
     */
    public static function MessageList($where)
    {
        if($where['status']==1){
            $data = ($data = self::where('status',$where['status'])->where('send_time','<',time())->where('type_id',1)->where('type_now','>',0)->order('id desc')->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        }else{
            $data = ($data = self::where('status',$where['status'])->where('type_id',1)->where('type_now','>',0)->order('id desc')->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        }
        //普通列表
        foreach ($data as &$item){
            switch ($item['status']) {
                case 1:
                    $item['status']='已发送';
                    break;
                case 0:
                    $item['status']='已关闭';
                    break;
                default:
                    $item['status']='已删除';
                    break;
            }
            $item['from_uid']='系统通知';
            $item['send_time']=time_format($item['send_time']);
            $item['create_time']=time_format($item['create_time']);
            switch ($item['type_now']) {
                case 1:
                    $item['type_now']='系统通知';
                    break;
                case 2:
                    $item['type_now']='用户通知';
                    break;
                case 3:
                    $item['type_now']='活动通知';
                    break;
                default:
                    $item['type_now']='系统通知';
                    break;
            }
        }
        $count = self::where('status',$where['status'])->where('type_id',1)->where('type_now','>',0)->count();
        return compact('count', 'data');
    }



}