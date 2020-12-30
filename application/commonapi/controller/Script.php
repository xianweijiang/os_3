<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * 脚本控制器
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/22
 * Time: 9:39
 */

namespace app\commonapi\controller;


use app\admin\controller\com\ComThread;
use app\admin\model\group\Group;
use app\admin\model\system\SystemConfig;
use app\commonapi\model\TencentFile;
use app\core\util\TencentVODService;
use app\osapi\model\user\UserModel;
use basic\ControllerBasic;
use app\ebapi\model\store\StorePink;
use app\ebapi\model\store\StoreOrder;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;
use app\shareapi\model\SellOrder;
use tests\thinkphp\library\think\dbTest;
use think\Cache;
use app\commonapi\model\SystemCountLogToShow;
use app\commonapi\model\rank\RankUser;
use app\commonapi\model\rank\RankThread;
use app\commonapi\model\rank\RankTopic;

class Script extends ControllerBasic
{
    /**
     * 把帖子content转换成json
     */
    public function thread_content_json(){
        $thread=db('com_thread')->select();
        foreach($thread as &$value){
            $data['content']=json_encode($value['content']);
            db('com_thread')->where('id',$value['id'])->update($data);
        }
        unset($value);
        echo '成功';
    }

    /**
     * 把帖子的summary的内容清空
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function thread_summary_null(){
        $sql="update `".config('database.prefix')."com_thread` set summary='' where 1;";
        db()->execute($sql);
        echo '成功';
    }



    public function cs(){
        $community=db('community_count');
        $time=strtotime(date("Y-m-d"),time());
        for($i=0;$i<90;$i++){
            $time=$time-24*3600;
            $data['time']=$time;
            $map['create_time']=['between',[$time-24*3600,$time]];
            //发帖数
            $data['forum']=db('com_thread')->where($map)->count();
            //评论数量
            $map['level']=['gt',0];
            $data['comment']=db('com_post')->where($map)->count();
            unset($map['level']);
            //点赞数
            $data['support']=db('support')->where($map)->count();
            //每日整点进行总统计
//            $time = strtotime(date("Y-m-d"),time())-24*3600;
            $community->insert($data);
        }
        echo '成功';
    }

    /**
     * 积分日志处理
     */
    public function jifen_log(){
        $map['explain']  = ['like','%【减分】%'];
        $jifeng=db('renwu_jiafen_log')->where($map)->column('id');
        $data['type']=0;
        db('renwu_jiafen_log')->where('id','in',$jifeng)->update($data);
        echo '成功';
    }

    /**
     * 积分日志处理
     */
    public function jifen_log2(){
        $map['explain']  = ['like','%【行为】%'];
        $jifeng=db('renwu_jiafen_log')->where($map)->column('id');
        $data['model']='行为';
        db('renwu_jiafen_log')->where('id','in',$jifeng)->update($data);

        $map2['explain']  = ['like','%【签到】%'];
        $jifeng2=db('renwu_jiafen_log')->where($map2)->column('id');
        $data2['model']='签到';
        db('renwu_jiafen_log')->where('id','in',$jifeng2)->update($data2);

        $map3['explain']  = ['like','%【任务】%'];
        $jifeng3=db('renwu_jiafen_log')->where($map3)->column('id');
        $data3['model']='任务';
        db('renwu_jiafen_log')->where('id','in',$jifeng3)->update($data3);
        echo '成功';
    }

    /**
     * 积分日志处理
     */
    public function jifen_log3(){
        $jifeng=db('renwu_jiafen_log')->field('id,explain')->select();
        foreach ($jifeng as $value){
            $str=$value['explain'];
            $str=str_replace('【加分】','',$str);
            $str=str_replace('【减分】','',$str);
            $str=str_replace('【签到】','',$str);
            $str=str_replace('【任务】','',$str);
            $str=str_replace('【行为】','',$str);
            $data['explain']=$str;
            db('renwu_jiafen_log')->where('id',$value['id'])->update($data);
        }
        echo '成功';
    }

    /**
     * 版主处理
     */
    public function forum_admin(){
        $forum=db('com_forum')->where('status',1)->field('id,admin_uid,status')->select();
        foreach ($forum as $value){
            if($value['admin_uid']){
                $data['uid']=$value['admin_uid'];
                $data['fid']=$value['id'];
                $data['level']=1;
                $data['admin']=1;
                $data['create_time']=time();
                $data['status']=1;
                db('com_forum_admin')->insert($data);
            }
        }
        echo '成功';
    }

    /**
     * 历史腾讯云视频处理
     */
    public function old_tencent_video(){
        header("Content-type: text/html; charset=utf-8");
        $tencent_video_config=SystemConfig::getMore(['tencent_video_is_open']);
        if(!$tencent_video_config['tencent_video_is_open']){
            echo '未开启腾讯云点播';
            exit;
        }
        dump('-------------开始处理：-----------------');
        $num=0;
        do{
            $video_ids=db('com_thread')->where('video_id','neq','')->where('video_url','')->limit(20)->column('video_id');
            $ids=array_unique($video_ids);
            if(count($ids)>0){
                $video_info=TencentVODService::getTencentVideoUrl($ids,1);
                if(count($video_info['not_has_list'])>0){
                    foreach ($video_info['not_has_list'] as $not_has_id){
                        dump('删除不纯在的视频：--------'.$not_has_id.'-----------------');
                    }
                    db('com_thread')->where('video_id','in',$video_info['not_has_list'])->update(['video_id'=>'']);
                    $num=$num+count($video_info['not_has_list']);
                }
                $add_data=[];
                foreach ($video_info['has_list'] as $val){
                    dump('处理视频成功：'.$val['FileId'].'-----------------');
                    db('com_thread')->where('video_id',$val['FileId'])->update(['video_url'=>$val['MediaUrl'],'video_cover'=>$val['CoverUrl']]);
                    $add_data[]=[
                        'type'=>'video',
                        'file_id'=>$val['FileId'],
                        'media_url'=>$val['MediaUrl'],
                        'cover_url'=>$val['CoverUrl'],
                        'create_time'=>time(),
                        'status'=>1
                    ];
                    $num++;
                }
                TencentFile::setAll($add_data);
            }
        }while(count($video_ids)==20);
        dump('-------------总共处理了：'.$num.'条视频数据-----------------');
        exit;
    }


    /**
     * 脚本对用户组进行整理
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.1
     */
    public function start_create(){
        Group::start_create();
        echo '成功';
    }


    /**
     * 计划任务每十分钟执行一次集合
     */
    public function Minute(){
        //检测超时订单自动取消订单 qhy
        $out_time=SystemConfig::getValue('close_order_time');
        $out_time=$out_time*3600;
        $time=time()-$out_time;
        $order_id = StoreOrder::where('add_time','<',$time)->where('paid',0)->column('order_id');
        foreach($order_id as &$value){
            StoreOrder::cancelOrder($value);
        }
        unset($value);
        $pinkBool = 0;
        $pink_id=StorePink::where('status',1)->where('k_id',0)->where('stop_time','<',time())->column('id');
        foreach($pink_id as &$val){
            $pink =  StorePink::getPinkUserOne($val);
            list($pinkAll,$pinkT,$count,$idAll,$uidAll)=StorePink::getPinkMemberAndPinkK($pink);
            if($count>0){
                StorePink::PinkFail($pinkAll,$pinkT,$pinkBool);
            }
        }
        unset($val);
        $data['name']='超时订单自动取消';
        $data['type']=1;
        $data['status']=1;
        $data['create_time']=time();
        db('script')->insert($data);

        //超时自动收货 qhy
        $receiving_time=SystemConfig::getValue('receiving_goods_time');
        $receiving_time=$receiving_time*24*3600;
        $receiving_time=time()-$receiving_time;
        $order_id = StoreOrder::where('delivery_time','<',$receiving_time)->where('paid',1)->where('status',1)->column('order_id');
        foreach($order_id as &$value){
            StoreOrder::receivingGoodsOrder($value);
        }
        unset($value);
        $data['name']='超时自动收货';
        $data['type']=1;
        $data['status']=1;
        $data['create_time']=time();
        db('script')->insert($data);

        //检测用户未付款订单并提醒用户 qhy
        $time=time()-600;
        $order_id = StoreOrder::where('add_time','<',$time)->where('is_message',0)->where('paid',0)->column('id');
        foreach($order_id as &$value){
            $order=StoreOrder::get($value);
            $set=MessageTemplate::getMessageSet(13);
            if($set['status']==1){
                $message_id=Message::sendMessage($order['uid'],0,$set['template'],1,$set['title'],1,'','store_order',$order['order_id']);
                $read_id=MessageRead::createMessageRead($order['uid'],$message_id,$set['popup'],1);
            }
            if($set['sms']==1&&$set['status']==1){
                $account=UserModel::where('uid',$order['uid'])->value('phone');
                $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                $set['template']='【'.$config['cl_sms_sign'].'】'.$set['template'];
                $sms=ChuanglanSmsApi::sendSMS($account,$set['template']); //发送短信
                $sms=json_decode($sms,true);
                if ($sms['code']==0) {
                    $read_data['is_sms']=1;
                    $read_data['sms_time']=time();
                    MessageRead::where('id',$read_id)->update($read_data);
                }
            }
            $map['is_message']=1;
            StoreOrder::where('id',$value)->update($map);
        }
        unset($value);
        $data['name']='用户未付款订单并提醒用户';
        $data['type']=1;
        $data['status']=1;
        $data['create_time']=time();
        db('script')->insert($data);

        //禁言到期自动解禁 qhy
        $res=db('prohibit')->where('status',1)->where('end_time','<',time())->update(['status'=>2]);
        if($res===false){
            $data['status']=0;
        }else{
            $data['status']=1;
        }
        $data['name']='禁言到期自动解禁';
        $data['type']=1;
        $data['create_time']=time();
        db('script')->insert($data);


        echo '完成';
        exit(true);
    }

    /**
     * 计划任务每小时执行一次合集
     */
    public function Hour(){
        //话题榜更新 qhy
        $res=RankTopic::TopicRank();
        if($res===false){
            $data['status']=0;
        }else{
            $data['status']=1;
        }
        $data['name']='话题榜更新';
        $data['type']=2;
        $data['create_time']=time();
        db('script')->insert($data);

        //推荐到期还原 qhy
        $res=db('com_thread')->where('is_recommend',1)->where('recommend_end_time','<',time())->update(['is_recommend'=>0]);
        if($res===false){
            $data['status']=0;
        }else{
            $data['status']=1;
        }
        $data['name']='推荐到期还原';
        $data['type']=2;
        $data['create_time']=time();
        db('script')->insert($data);

        //置顶到期还原 qhy
        $res=db('com_thread')->where('is_top',1)->where('top_end_time','<',time())->update(['is_top'=>0]);
        if($res===false){
            $data['status']=0;
        }else{
            $data['status']=1;
        }
        $data['name']='置顶到期还原';
        $data['type']=2;
        $data['create_time']=time();
        db('script')->insert($data);

        //首页置顶到期还原 qhy
        $res=db('com_thread')->where('index_top',1)->where('index_top_end_time','<',time())->update(['index_top'=>0]);
        if($res===false){
            $data['status']=0;
        }else{
            $data['status']=1;
        }
        $data['name']='首页置顶到期还原';
        $data['type']=2;
        $data['create_time']=time();
        db('script')->insert($data);

        //详情置顶到期还原 qhy
        $res=db('com_thread')->where('detail_top',1)->where('detail_top_end_time','<',time())->update(['detail_top'=>0]);
        if($res===false){
            $data['status']=0;
        }else{
            $data['status']=1;
        }
        $data['name']='详情置顶到期还原';
        $data['type']=2;
        $data['create_time']=time();
        db('script')->insert($data);

        echo '完成';
        exit(true);
    }


    /**
     * 计划任务每天凌晨1点执行集合
     */
    public function DayOne(){
        //订单返利任务 zzl
        $map['give_back_time']=array('elt',time());
        $map['back_status']=2;
        $map['order_status']=array('in',[2,3]);
        //分销收益结算消息提醒start
        $set=MessageTemplate::getMessageSet(24);
        $father1_list=SellOrder::where($map)->column('father1');

        $father2_list=SellOrder::where($map)->column('father2');
        $uid_list=array_merge($father1_list,$father2_list);
        $uid_list=array_filter(array_unique($uid_list));
        if($set['status']==1){
            $message=array();
            $data['from_uid']=0;
            $data['content']=$set['template'];
            $data['type_id']=1;
            $data['title']=$set['title'];
            $data['from_type']=1;
            $data['route']='promotion_center';
            $data['create_time']=time();
            $data['send_time']=time();
            $map1=$data;
            foreach($uid_list as &$value){
                $data['to_uid']=$value;
                $message[]=$data;
            }
            unset($value);
            Message::insertAll($message);
            $message_list=Message::where($map1)->select()->toArray();
            $data2['is_read']=0;
            if($set['popup']==1){
                $data2['is_popup']=0;
            }else{
                $data2['is_popup']=1;
                $data2['popup_time']=time();
            }
            $data2['is_sms']=0;
            $data2['type']=1;
            $data2['create_time']=time();
            $data2['is_share']=0;
            $map2=$data2;
            Cache::set('giveOrderBack',$map2,72000);
            $message_read=array();
            foreach($message_list as &$item){
                $data2['uid']=$item['to_uid'];
                $data2['message_id']=$item['id'];
                $message_read[]=$data2;
            }
            MessageRead::insertAll($message_read);
        }
        //分销收益结算消息提醒end

        $limit=50;
        do{
            $order_list=SellOrder::where($map)->limit($limit)->select()->toArray();
            foreach ($order_list as $val){
                SellOrder::giveOrderBack($val);//执行返利操作
            }
            unset($val);
        }while(count($order_list)==$limit);

        $data['name']='订单返利';
        $data['type']=3;
        $data['status']=1;
        $data['create_time']=time();
        db('script')->insert($data);

        //每日社区数据统计 zxh
        unset($map);
        $community=db('community_count');
        $time=strtotime(date("Y-m-d"),time());
        $map['create_time']=['between',[$time-24*3600,$time]];
        //发帖数
        $data['forum']=db('com_thread')->where($map)->count();
        //评论数量
        $map['level']=['gt',0];
        $data['comment']=db('com_post')->where($map)->count();
        unset($map['level']);
        //点赞数
        $data['support']=db('support')->where($map)->count();
        //每日整点进行总统计
        $time = strtotime(date("Y-m-d"),time())-24*3600;
        if($community->where(['time'=>$time])->find()){
            $community->where(['time'=>$time])->update($data);
        }else{
            $data['time']=$time;
            $community->insert($data);
        }
        //统计日常发帖 昨天 7天 30天 90天
        $time_today=strtotime(date("Y-m-d"),time());
        $uids=db('com_thread')->group('author_uid')->where(['create_time'=>['gt',$time_today-24*3600*90]])->field('author_uid')->select();
        $time_list=[1=>'one',7=>'seven',30=>'thirty',90=>'ninety'];
        $data_forum_all=[];
        $map['status']=1;
        foreach ($uids as $key=>$vo){
            $data_forum['uid']=$map['author_uid']=$vo['author_uid'];
            foreach ($time_list as $k=>$v){
                $map['create_time']=['egt',$time_today-$k*24*3600];
                $data_forum[$v]=db('com_thread')->where($map)->count();
            }
            $data_forum['time']=$time_today;
            $data_forum_all[]=$data_forum;
        }
        db('thread_census')->insertAll($data_forum_all);
        unset($vo,$v,$k,$key,$map,$uids);

        $data_comment_all=[];
        $map['status']=1;
        $map['level']=['egt',1];
        $uids=db('com_post')->group('author_uid')->where(['create_time'=>['gt',$time_today-24*3600*90,'level'=>['egt',1]]])->field('author_uid')->select();
        foreach ($uids as $key=>$vo){
            $data_comment['uid']=$map['author_uid']=$vo['author_uid'];
            foreach ($time_list as $k=>$v){
                $map['create_time']=['egt',$time_today-$k*24*3600];
                $data_comment[$v]=db('com_post')->where($map)->count();
            }
            $data_comment['time']=$time_today;
            $data_comment_all[]=$data_comment;
        }
        db('comment_census')->insertAll($data_comment_all);
        unset($vo,$v,$k,$key,$map,$uids);

        $data_hot_all=[];
        $map['level']=['egt',1];
        $map['status']=1;
        $uids=db('com_post')->group('tid')->where(['create_time'=>['gt',$time_today-24*3600*90,'level'=>['egt',1]]])->field('tid')->select();
        foreach ($uids as $key=>$vo){
            $data_hot['tid']=$map['tid']=$vo['tid'];
            foreach ($time_list as $k=>$v){
                $map['create_time']=['egt',$time_today-$k*24*3600];
                $data_hot[$v]=db('com_post')->where($map)->count();
            }
            $data_hot['time']=$time_today;
            $data_hot_all[]=$data_hot;
        }
        db('hot_census')->insertAll($data_hot_all);
        unset($vo,$v,$k,$key,$map,$uids);

        $data_forum_all=[];
        $map['status']=1;
        $uids=db('com_thread')->group('fid')->where(['create_time'=>['gt',$time_today-24*3600*90]])->field('id,fid')->select();
        foreach ($uids as $key=>$vo){
            $data_forum['fid']=$map['fid']=$vo['fid'];
            foreach ($time_list as $k=>$v){
                $map['create_time']=['egt',$time_today-$k*24*3600];
                $data_forum[$v]=db('com_thread')->where($map)->count();
                $mav=$map;
                $mav['level']=['egt',1];
                $data_forum[$v.'_comment']=db('com_post')->where($mav)->count();
                $data_forum[$v.'_member']=db('com_forum_member')->where($map)->count();
                $data_forum[$v.'_view']=db('com_thread')->where($map)->sum('view_count');
            }
            $data_forum['time']=$time_today;
            $data_forum_all[]=$data_forum;
        }
        db('forum_census')->insertAll($data_forum_all);
        unset($vo,$v,$k,$key,$map,$uids);
        $data['name']='每日社区数据统计';
        $data['type']=3;
        $data['status']=1;
        $data['create_time']=time();
        db('script')->insert($data);

        //数据统计 zzl
        $res1=SystemCountLogToShow::countData();
        if($res1===false){
            $data['status']=0;
        }else{
            $data['status']=1;
        }
        $data['name']='数据统计';
        $data['type']=3;
        $data['create_time']=time();
        db('script')->insert($data);

        //新帖复原 qhy
        $time=time()-86400;
        $res2=db('com_thread')->where('is_new',1)->where('create_time','<',$time)->update(['is_new'=>0]);
        if($res2===false){
            $data['status']=0;
        }else{
            $data['status']=1;
        }
        $data['name']='新帖复原';
        $data['type']=3;
        $data['create_time']=time();
        db('script')->insert($data);

        //过期热门话题复原
        $res3=db('com_topic')->where('is_hot',1)->where('hot_end_time','<',time())->update(['is_hot'=>0]);
        if($res3===false){
            $data['status']=0;
        }else{
            $data['status']=1;
        }
        $data['name']='过期热门话题复原';
        $data['type']=3;
        $data['create_time']=time();
        db('script')->insert($data);

        //热评榜更新 qhy
        $res4=RankThread::ThreadRank();
        if($res4===false){
            $data['status']=0;
        }else{
            $data['status']=1;
        }
        $data['name']='热评榜更新';
        $data['type']=3;
        $data['create_time']=time();
        db('script')->insert($data);


        echo '完成';
        exit(true);
    }

    /**
     * 计划任务每天早上8点执行集合
     */
    public function DayEight(){
        //分销结算发送短信 qhy
        $set=MessageTemplate::getMessageSet(24);
        if($set['sms']==1&&$set['status']==1){
            $limit=3000;
            do{
                $user=MessageRead::where('is_share',0)->limit($limit)->select()->toArray();
                $uids=array_column($user,'uid');
                $account=db('user')->where('uid','in',$uids)->column('phone');
                $account=implode(',',$account);
                $read_data['is_sms']=1;
                $read_data['sms_time']=time();
                $read_data['is_share']=1;
                $ids=array_column($user,'id');
                MessageRead::where('id','in',$ids)->update($read_data);
                $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                $set['template']='【'.$config['cl_sms_sign'].'】'.$set['template'];
                ChuanglanSmsApi::sendSMS($account,$set['template']); //发送短信
            }while(count($user)==$limit);
        }
        $data['name']='分销结算发送短信';
        $data['type']=4;
        $data['status']=1;
        $data['create_time']=time();
        db('script')->insert($data);


        echo '完成';
        exit(true);
    }

    /**
     * 计划任务每周执行集合(目前只有人气榜，如果有其他的计划任务，需要把人气榜拆分出去)
     */
    public function Week(){
        ignore_user_abort();
        set_time_limit(0);
        //人气榜更新 qhy
        $time=time()-600000;
        $user_time=db('rank_user_time')->where('id',1)->find();
        if($user_time['type']=='end'&&$user_time['update_time']<$time){
            $res=RankUser::UserRank();
            if($res===false){
                $data['status']=0;
                $data['name']='人气榜更新';
                $data['type']=5;
                $data['create_time']=time();
                db('script')->insert($data);
            }else{
                $res2=RankUser::updateUserRank($user_time['page'],$user_time['page2']);
                if($res2===false){
                    $data['status']=0;
                    $data['name']='人气榜更新';
                    $data['type']=5;
                    $data['create_time']=time();
                    db('script')->insert($data);
                }else{
                    $url = get_domain().'/commonapi/script/week.html';
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                    curl_exec($ch);
                }
            }
        }else{
            if($user_time['type']=='start'){
                $res=RankUser::updateUserRank($user_time['page'],$user_time['page2']);
                if($res===false){
                    $data['status']=0;
                    $data['name']='人气榜更新';
                    $data['type']=5;
                    $data['create_time']=time();
                    db('script')->insert($data);
                }else{
                    $url = get_domain().'/commonapi/script/week.html';
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                    curl_exec($ch);
                }
            }else{
                $data['status']=1;
                $data['name']='人气榜更新';
                $data['type']=5;
                $data['create_time']=time();
                db('script')->insert($data);
            }
            Cache::clear('user_rank_list');
        }


        echo '完成';
        exit(true);
    }

}

