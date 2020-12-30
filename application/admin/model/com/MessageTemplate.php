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
class MessageTemplate extends ModelBasic
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
    public static function MessageReminderList($where)
    {
        $data = ($data = self::page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        //普通列表
        foreach ($data as &$item){
            switch ($item['forum']) {
                case 1:
                    $item['forum']='社区消息';
                    break;
                case 2:
                    $item['forum']='商城消息';
                    break;
                case 3:
                    $item['forum']='分销消息';
                    break;
                case 4:
                    $item['forum']='积分商城消息';
                    break;
                case 5:
                    $item['forum']='认证消息';
                    break;
                case 6:
                    $item['forum']='管理后台';
                    break;
                case 7:
                    $item['forum']='知识付费';
                    break;
                default:
                    $item['forum']='其他消息';
                    break;
            }
        }
        $count = self::count();
        return compact('count', 'data');
    }

}