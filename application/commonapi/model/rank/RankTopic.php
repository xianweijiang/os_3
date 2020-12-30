<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\commonapi\model\rank;


use basic\ModelBasic;
use think\Db;
use think\Cache;
use app\commonapi\model\rank\Rank;
use app\osapi\model\com\ComTopic;

class RankTopic extends ModelBasic
{

    /**
     * qhy
     * 话题排行榜
     */
    public static function TopicRank(){
        $rank_del=RankDel::where('model','topic')->column('pid');
        $rankTopic=Rank::where('id',5)->find();
        $time=time()-$rankTopic['frequency']*3600;
        $time_update=time()-600;
        self::beginTrans();
        if($rankTopic['update_time']<$time){//根据频率更新
            $res=self::where('status',1)->delete();
            if($res===false){
                self::rollbackTrans();
                return false;
            }
            $thread=db('com_topic')->where('status',1)->where('id','not in',$rank_del)->field('id,view_count+post_count hot')->order('hot desc')->limit(50)->select();
            $map1=array();
            $data['status']=1;
            $i=1;
            foreach($thread as &$value){
                $data['oid']=$value['id'];
                $data['hot']=$value['hot'];
                $data['sort']=$i;
                $map1[]=$data;
                $i++;
            }
            unset($value);
            if($map1){
                $res1=self::insertAll($map1);
                if($res1===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            Rank::where('id',5)->update(['update_time'=>$time_update]);
        }
        self::commitTrans();
        Cache::rm('topic_rank_list');
        return true;
    }

    public static function getList(){
        $list=self::where('status',1)->order('sort asc,hot desc')->column('oid');
        $topic=ComTopic::where('id','in',$list)->select()->toArray();
        $topic=array_combine(array_column($topic,'id'),$topic);
        foreach($list as &$value){
            $value=$topic[$value];
        }
        return $list;
    }

}