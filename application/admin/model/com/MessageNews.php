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
class MessageNews extends ModelBasic
{
    use ModelTrait;

    public static function createMessageNews($data){
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
        $map['status']=1;
        $map['send_time']=time();
        $thread_id=self::where('id',$id)->update($map);
        if($thread_id){
            return 1;
        }else{
            return 0;
        }
    }

    /*
     * 获取营销消息列表
     * @param $where array
     * @return array
     *
     */
    public static function MessageNewsList($where)
    {
        if($where['status']==1){
            $data = ($data = self::where('status',$where['status'])->where('send_time','<',time())->order('create_time desc')->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        }else{
            $data = ($data = self::where('status',$where['status'])->order('create_time desc')->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
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
            $item['view']=db('com_thread')->where('id',$item['tid'])->value('view_count');
            $item['content'] =  mb_strcut(strip_tags(htmlspecialchars_decode(text($item['content']))),0,180,'utf-8');
            $item['send_time']=time_format($item['send_time']);
            $item['end_time']=time_format($item['end_time']);
            $item['create_time']=time_format($item['create_time']);
            $item['from_uid']='系统通知';
            $item['to_uid']='全体用户';
        }
        $count = self::where('status',$where['status'])->count();
        return compact('count', 'data');
    }



}