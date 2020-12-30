<?php
/**
 * Created by PhpStorm.
 * User: zxh
 * Date: 2020/1/14
 * Time: 14:33
 */

namespace app\osapi\model\com;


use app\admin\controller\com\ComThread;
use app\osapi\model\BaseModel;
use app\osapi\model\user\UserModel;
use think\Db;

class CommunityCount extends BaseModel
{

    /**
     * 统计
     * @param $action
     * @param int $num
     * @author zxh  zxh@ourstu.com
     *时间：2020.1.14
     */
    public static function census($action,$num=1){
        $time = strtotime(date("Y-m-d"),time());
        $map['time']=$time;
        if(self::where($map)->count()){
            self::where($map)->setInc($action,$num);
        }else{
            $map[$action]=$num;
            self::insert($map);
        }
    }

    /**
     * 获取社区列表
     * @author zxh  zxh@ourstu.com
     *时间：2020.1.14
     */
    public static function getCensusList(){
        //今日统计
        $time = strtotime(date("Y-m-d"),time());
        $map['time']=$time;
        $today=self::where($map)->find();
        if(!$today){
            self::insert($map);
            $today=$map;
            $today['forum']= $today['comment']= $today['support']= $today['share']= $today['reward']=0;
        }
        $map['time']=$time-24*3600;
        $data=cache('census_list_'.$time);
        if(!$data){
            $yesterday=self::where($map)->find();
            $map['time']=['elt',$time-24*3600];
            $count=self::where($map)->count();
            $value=['forum','comment','support','share','reward'];
            $average=[];
            $max=[];
            foreach ($value as $vo){
                $sum=self::where($map)->sum($vo);
                $average[$vo]=intval($sum/$count);
                $max[$vo]=self::where($map)->max($vo);
            }
            $data=['yesterday'=>$yesterday,'average'=>$average,'max'=>$max];
            cache('census_list_'.$time,$data,24*3600);
        }
        unset($vo);
        $data['today']=$today;
        return $data;
    }

    /**
     * 获取列表值
     * @param $limit
     * @param $field
     * @return false|\PDOStatement|string|\think\Collection
     * @author zxh  zxh@ourstu.com
     *时间：
     */
    public static function getCensusLimit($limit=7,$field='forum'){
        $time = strtotime(date("Y-m-d"),time());
        $map['time']=['elt',$time];
        $census=self::where($map)->cache('census_list_time_'.$time.'_limit_'.$limit.'_field_',24*3600)->order('time desc')->limit($limit)->select();
        $legend='社区统计';
        $series=[];
        $xAxis=[];
        $yAxis=self::where($map)->cache('census_list_time_'.$time.'_max_'.$limit.'_field_',24*3600)->order('time desc')->limit($limit)->max($field);
        foreach ($census as $vo){
            $series[]=$vo[$field];
            $xAxis[]=date('m-d',$vo['time']);
        }
        unset($vo);
        return ['legend'=>$legend,'series'=>$series,'xAxis'=>$xAxis,'yAxis'=>$yAxis];
    }

    /**
     * 获取列表信息
     * @param string $order
     * @return array|mixed
     * @author zxh  zxh@ourstu.com
     *时间：2020.1.15
     */
    public static function censusList($order='one'){
        $time = strtotime(date("Y-m-d"),time());
//        $data=  cache('census_list_time_'.$time.'_order_'.$order);
//        if ($data){
            $map=['time'=>$time];
            $thread_census=db('thread_census')->where($map)->order($order.' desc')->limit(10)->select();
            $thread=[];
            foreach ($thread_census as &$vo){
                $value['data']=$vo[$order];
                $value['uid']=$vo['uid'];
                $value['user']=db('user')->where(['uid'=>$vo['uid']])->value('nickname');
                $thread[]=$value;
            }
            unset($vo,$value);
            $comment_census=db('comment_census')->where($map)->order($order.' desc')->limit(10)->select();
            $comment=[];
            foreach ($comment_census as &$vo){
                $value['data']=$vo[$order];
                $value['uid']=$vo['uid'];
                $value['user']=db('user')->where(['uid'=>$vo['uid']])->value('nickname');
                $comment[]=$value;
            }
            unset($vo,$value);
            $hot_census=db('hot_census')->where($map)->order($order.' desc')->limit(10)->select();
            $hot=[];
            foreach ($hot_census as &$vo){
                $value['data']=$vo[$order];
                $value['forum']=db('com_thread')->where(['id'=>$vo['tid']])->value('title');
                $uid=db('com_thread')->where(['id'=>$vo['tid']])->value('author_uid');
                $value['uid']=$uid;
                $value['user']=db('user')->where(['uid'=>$uid])->value('nickname');
                $hot[]=$value;
            }
            unset($vo,$value);

            $forum_census=db('forum_census')->where($map)->order($order.' desc')->limit(10)->select();
            $forum=[];
            foreach ($forum_census as &$vo){
                $value['data']=$vo[$order];
                $value['data_comment']=$vo[$order.'_comment'];
                $value['data_view']=$vo[$order.'_view'];
                $value['data_member']=$vo[$order.'_member'];
                $value['forum']=db('com_forum')->where(['id'=>$vo['fid']])->value('name');
                $forum[]=$value;
            }
            unset($vo,$value);
            $data=['thread_census'=>$thread,'comment_census'=>$comment,'hot_census'=>$hot,'forum_census'=>$forum];
            cache('census_list_time_'.$time.'_order_'.$order,$data,24*3600);
//        }
        return $data;
    }
}