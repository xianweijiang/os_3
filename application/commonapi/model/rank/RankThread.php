<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\commonapi\model\rank;


use app\admin\model\com\ForumPower;
use basic\ModelBasic;
use app\commonapi\model\rank\RankThreadTime;
use app\commonapi\model\rank\RankDel;
use think\Db;
use think\Cache;
use app\osapi\model\com\ComThread;

class RankThread extends ModelBasic
{

    public static function getList($type,$time_type){
        $list=self::where('status',1)->where('type',$type)->where('tid','not in',ForumPower::get_private_id_com())->where('time_type',$time_type)->order('sort asc,hot desc')->column('tid');
        $thread=ComThread::where('id','in',$list)->select()->toArray();
        $thread=array_combine(array_column($thread,'id'),$thread);
        foreach($list as &$value){
            $value=$thread[$value];
        }
        $list=ComThread::threadListHandle($list);
        return $list;
    }

    /**
     * qhy
     * 热评排行榜
     */
    public static function ThreadRank(){
        $rank_del=RankDel::where('model','thread')->column('pid');
        $update_time1=RankThreadTime::where('time_type',1)->value('update_time');
        $update_time2=RankThreadTime::where('time_type',2)->value('update_time');
        $update_time3=RankThreadTime::where('time_type',3)->value('update_time');
        $update_time4=RankThreadTime::where('time_type',4)->value('update_time');
        $time1=time()-86400;
        $time2=time()-604800;
        $time3=time()-2592000;
        $time=time()-600;
        self::beginTrans();
        //版块权限排除私密帖子
        $mav['status']=1;
        $mav['id']=['not in',ForumPower::get_private_id()];
        $forum_ids=db('com_forum')->where($mav)->column('id');
        if($update_time1<$time1){//日榜更新
            //帖子更新
            $thread=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',1)->where('is_weibo',0)->where('create_time','>',$time1)->where('id','not in',$rank_del)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $res=self::where('time_type',1)->where('status',1)->delete();
            if($res===false){
                self::rollbackTrans();
                return false;
            }
            $map1=array();
            $data1['type']=1;
            $data1['time_type']=1;
            $data1['status']=1;
            $i=1;
            foreach($thread as &$value){
                $data1['tid']=$value['id'];
                $data1['hot']=$value['hot'];
                $data1['sort']=$i;
                $map1[]=$data1;
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
            //视频更新
            $video=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time1)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map2=array();
            $data2['type']=2;
            $data2['time_type']=1;
            $data2['status']=1;
            $i=1;
            foreach($video as &$value){
                $data2['tid']=$value['id'];
                $data2['hot']=$value['hot'];
                $data2['sort']=$i;
                $map2[]=$data2;
                $i++;
            }
            unset($value);
            if($map2){
                $res2=self::insertAll($map2);
                if($res2===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            //动态更新
            $weibo=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->where('create_time','>',$time1)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map3=array();
            $data3['type']=3;
            $data3['time_type']=1;
            $data3['status']=1;
            $i=1;
            foreach($weibo as &$value){
                $data3['tid']=$value['id'];
                $data3['hot']=$value['hot'];
                $data3['sort']=$i;
                $map3[]=$data3;
                $i++;
            }
            unset($value);
            if($map3){
                $res3=self::insertAll($map3);
                if($res3===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            //资讯更新
            $news=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time1)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map4=array();
            $data4['type']=4;
            $data4['time_type']=1;
            $data4['status']=1;
            $i=1;
            foreach($news as &$value){
                $data4['tid']=$value['id'];
                $data4['hot']=$value['hot'];
                $data4['sort']=$i;
                $map4[]=$data4;
                $i++;
            }
            unset($value);
            if($map4){
                $res4=self::insertAll($map4);
                if($res4===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            RankThreadTime::where('time_type',1)->update(['update_time' => $time]);
        }
        if($update_time2<$time2){//周榜更新
            //帖子更新
            $thread=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',1)->where('is_weibo',0)->where('create_time','>',$time2)->where('id','not in',$rank_del)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $res=self::where('time_type',2)->where('status',1)->delete();
            if($res===false){
                self::rollbackTrans();
                return false;
            }
            $map1=array();
            $data1['type']=1;
            $data1['time_type']=2;
            $data1['status']=1;
            $i=1;
            foreach($thread as &$value){
                $data1['tid']=$value['id'];
                $data1['hot']=$value['hot'];
                $data1['sort']=$i;
                $map1[]=$data1;
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
            //视频更新
            $video=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time2)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map2=array();
            $data2['type']=2;
            $data2['time_type']=2;
            $data2['status']=1;
            $i=1;
            foreach($video as &$value){
                $data2['tid']=$value['id'];
                $data2['hot']=$value['hot'];
                $data2['sort']=$i;
                $map2[]=$data2;
                $i++;
            }
            unset($value);
            if($map2){
                $res2=self::insertAll($map2);
                if($res2===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            //动态更新
            $weibo=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->where('create_time','>',$time2)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map3=array();
            $data3['type']=3;
            $data3['time_type']=2;
            $data3['status']=1;
            $i=1;
            foreach($weibo as &$value){
                $data3['tid']=$value['id'];
                $data3['hot']=$value['hot'];
                $data3['sort']=$i;
                $map3[]=$data3;
                $i++;
            }
            unset($value);
            if($map3){
                $res3=self::insertAll($map3);
                if($res3===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            //资讯更新
            $news=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time2)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map4=array();
            $data4['type']=4;
            $data4['time_type']=2;
            $data4['status']=1;
            $i=1;
            foreach($news as &$value){
                $data4['tid']=$value['id'];
                $data4['hot']=$value['hot'];
                $data4['sort']=$i;
                $map4[]=$data4;
                $i++;
            }
            unset($value);
            if($map4){
                $res4=self::insertAll($map4);
                if($res4===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            RankThreadTime::where('time_type',2)->update(['update_time' => $time]);
        }

        if($update_time3<$time3){//月榜更新
            //帖子更新
            $thread=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',1)->where('is_weibo',0)->where('create_time','>',$time3)->where('id','not in',$rank_del)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $res=self::where('time_type',3)->where('status',1)->delete();
            if($res===false){
                self::rollbackTrans();
                return false;
            }
            $map1=array();
            $data1['type']=1;
            $data1['time_type']=3;
            $data1['status']=1;
            $i=1;
            foreach($thread as &$value){
                $data1['tid']=$value['id'];
                $data1['hot']=$value['hot'];
                $data1['sort']=$i;
                $map1[]=$data1;
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
            //视频更新
            $video=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time3)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map2=array();
            $data2['type']=2;
            $data2['time_type']=3;
            $data2['status']=1;
            $i=1;
            foreach($video as &$value){
                $data2['tid']=$value['id'];
                $data2['hot']=$value['hot'];
                $data2['sort']=$i;
                $map2[]=$data2;
                $i++;
            }
            unset($value);
            if($map2){
                $res2=self::insertAll($map2);
                if($res2===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            //动态更新
            $weibo=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->where('create_time','>',$time3)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map3=array();
            $data3['type']=3;
            $data3['time_type']=3;
            $data3['status']=1;
            $i=1;
            foreach($weibo as &$value){
                $data3['tid']=$value['id'];
                $data3['hot']=$value['hot'];
                $data3['sort']=$i;
                $map3[]=$data3;
                $i++;
            }
            unset($value);
            if($map3){
                $res3=self::insertAll($map3);
                if($res3===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            //资讯更新
            $news=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->where('create_time','>',$time3)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map4=array();
            $data4['type']=4;
            $data4['time_type']=1;
            $data4['status']=1;
            $i=1;
            foreach($news as &$value){
                $data4['tid']=$value['id'];
                $data4['hot']=$value['hot'];
                $data4['sort']=$i;
                $map4[]=$data4;
                $i++;
            }
            unset($value);
            if($map4){
                $res4=self::insertAll($map4);
                if($res4===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            RankThreadTime::where('time_type',3)->update(['update_time' => $time]);
        }

        if($update_time4<$time1){//总榜更新
            //帖子更新
            $thread=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',1)->where('is_weibo',0)->where('id','not in',$rank_del)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $res=self::where('time_type',4)->where('status',1)->delete();
            if($res===false){
                self::rollbackTrans();
                return false;
            }
            $map1=array();
            $data1['type']=1;
            $data1['time_type']=4;
            $data1['status']=1;
            $i=1;
            foreach($thread as &$value){
                $data1['tid']=$value['id'];
                $data1['hot']=$value['hot'];
                $data1['sort']=$i;
                $map1[]=$data1;
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
            //视频更新
            $video=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',6)->where('is_weibo',0)->where('id','not in',$rank_del)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map2=array();
            $data2['type']=2;
            $data2['time_type']=4;
            $data2['status']=1;
            $i=1;
            foreach($video as &$value){
                $data2['tid']=$value['id'];
                $data2['hot']=$value['hot'];
                $data2['sort']=$i;
                $map2[]=$data2;
                $i++;
            }
            unset($value);
            if($map2){
                $res2=self::insertAll($map2);
                if($res2===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            //动态更新
            $weibo=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('is_weibo',1)->where('id','not in',$rank_del)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map3=array();
            $data3['type']=3;
            $data3['time_type']=4;
            $data3['status']=1;
            $i=1;
            foreach($weibo as &$value){
                $data3['tid']=$value['id'];
                $data3['hot']=$value['hot'];
                $data3['sort']=$i;
                $map3[]=$data3;
                $i++;
            }
            unset($value);
            if($map3){
                $res3=self::insertAll($map3);
                if($res3===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            //资讯更新
            $news=db('com_thread')->where('fid','in',$forum_ids)->where('status',1)->where('type',4)->where('is_weibo',0)->where('id','not in',$rank_del)->field('id,reply_count+support_count hot')->order('hot desc')->limit(100)->select();
            $map4=array();
            $data4['type']=4;
            $data4['time_type']=4;
            $data4['status']=1;
            $i=1;
            foreach($news as &$value){
                $data4['tid']=$value['id'];
                $data4['hot']=$value['hot'];
                $data4['sort']=$i;
                $map4[]=$data4;
                $i++;
            }
            unset($value);
            if($map4){
                $res4=self::insertAll($map4);
                if($res4===false){
                    self::rollbackTrans();
                    return false;
                }
            }
            RankThreadTime::where('time_type',4)->update(['update_time' => $time]);
        }
        self::commitTrans();
        Cache::clear('thread_rank_list');
        return true;
    }

}