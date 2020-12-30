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
class ComAnnounce extends ModelBasic
{
    use ModelTrait;

    public static function createAnnounce($data){
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
        $thread_id=self::where('id',$id)->update(['status' => 1]);
        if($thread_id){
            return 1;
        }else{
            return 0;
        }
    }

    /*
     * 获取公告列表
     * @param $where array
     * @return array
     *
     */
    public static function AnnounceList($where)
    {
        $data = ($data = self::where('status',$where['status'])->order('create_time desc')->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        //普通列表
        foreach ($data as &$item){
            switch ($item['status']) {
                case 1:
                    $item['status']='已发送';
                    break;
                case 0:
                    $item['status']='未发布';
                    break;
                default:
                    $item['status']='已删除';
                    break;
            }
            $item['uid']=db('user')->where('uid',$item['uid'])->value('nickname');
            $item['fid']=db('com_forum')->where('id',$item['fid'])->value('name');
            $item['view']=db('com_thread')->where('id',$item['tid'])->value('view_count');
            $item['start_time']=time_format($item['start_time']);
            $item['create_time']=time_format($item['create_time']);
        }
        $count = self::where('status',$where['status'])->count();
        return compact('count', 'data');
    }



}