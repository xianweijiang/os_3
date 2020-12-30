<?php
/**
 * Created by PhpStorm.
 * User: zxh
 * Date: 2019/9/26
 * Time: 9:11
 */

namespace app\osapi\model\com;


use app\admin\model\system\SystemConfig;
use app\osapi\lib\ChuanglanSmsApi;
use app\osapi\model\BaseModel;
use app\osapi\model\user\UserModel;
use think\Cache;

class Report extends BaseModel
{

    /**
     * 获取投诉原因
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.26
     */
    public static function getReportReason(){
        $reason=db('report_reason')->select();
        $data=[];
        foreach ($reason as $key=>$vo){
            $data[$vo['id']]=$vo['name'];
        }
        unset($vo);
        return $data;
    }


    /**
     * 获取投诉原因
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.26
     */
    public static function getReportReasonIds(){
        $reason=db('report_reason')->select();
        $data=[];
        foreach ($reason as $key=>$vo){
            $data[]=$vo['id'];
        }
        unset($vo);
        return $data;
    }

    /**
     * 举报帖子
     * @param $data
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.26
     */
    public static function addForumReport($data){
        return self::insert($data);
    }

    /**
     * 获取举报帖子列表
     * @param $map
     * @param $page
     * @param int $limit
     * @param string $order
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.26
     */
    public static function getForumReportList($map,$page,$limit=10,$order='create_time desc'){
        $list=self::where($map)->page($page,$limit)->order($order)->select()->toArray();
        $reason= Report::getReportReason();
        $deal_type=self::getDealType();
        $report_type=self::getReportType();
        $reason_ids= self::getReportReasonIds();
        foreach ($list as &$vo){
            $vo['nickname']=db('user')->where(['uid'=>$vo['uid']])->value('nickname');
            $vo['to_nickname']=db('user')->where(['uid'=>$vo['to_uid']])->value('nickname');
            if($vo['type']!==4){
                if($vo['type']==3){
                    $vo['content_show']=db('com_post')->where(['id'=>$vo['content']])->value('content');
                    $vo['content_show']= mb_strcut(strip_tags(htmlspecialchars_decode(text($vo['content_show']))),0,180,'utf-8');
                }else{
                    $vo['content_show']=db('com_thread')->where(['id'=>$vo['content']])->value('content');
                    $vo['content_show']=json_decode($vo['content_show']);
                    $vo['content_show']= mb_strcut(strip_tags(htmlspecialchars_decode(text($vo['content_show']))),0,180,'utf-8');
                }
            }else{
                $vo['content_show']='';
            }
            $plate=db('com_forum')->where(['id'=>$vo['plate']])->value('name');
            $cate=db('com_thread_class')->where(['id'=>$vo['cate']])->value('name');
            $vo['plate_cate']=$plate.'</br>'.$cate;
            $vo['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
            $vo['is_deal']=$vo['is_deal']?'已处理':'未处理';
            $vo['type_name']=$vo['type']==1?'评论':'帖子';
            $vo['report_type']= $vo['type']?$report_type[$vo['type']]['name']:'';
            $vo['deal_type']=$vo['deal_type']?$deal_type[$vo['deal_type']]['name']:'';
            if($vo['prohibit']>0){
                $prohibit=db('report_prohibit')->where(['id'=>$vo['prohibit']])->find();
                $prohibit['time_type']=$prohibit['time_type']==1?'小时':'天';
                $vo['deal_type'].='</br>禁言'.$prohibit['num'].$prohibit['time_type'];
            }
            if(in_array($vo['reason'],$reason_ids)){
                $vo['reason_show']=$vo['reason']==0?$vo['other_reason']:$reason[$vo['reason']];
            }else{
                $vo['reason_show']=$vo['other_reason'];
            }
            if($vo['operation_identity']==1){
                $vo['operation_nickname']=db('system_admin')->where('id',$vo['operation_uid'])->value('real_name');
            }else{
                $vo['operation_nickname']=UserModel::where('uid',$vo['operation_uid'])->value('nickname');
            }
            switch($vo['operation_identity']){
                case 1:
                    $vo['operation_identity']='后台管理员';
                    break;
                case 2:
                    $vo['operation_identity']='超级版主';
                    break;
                case 3:
                    $vo['operation_identity']='版主';
                    break;
                case 4:
                    $vo['operation_identity']='前台管理员';
                    break;
            }
        }
        unset($vo);
        $count=self::where($map)->count();
        return ['count'=>$count,'data'=>$list];
    }

    /**
     * 举报用户
     * @param $data
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.26
     */
    public static function addUserReport($data){
        return self::insert($data);
    }
    /**
     * 获取举报用户列表
     * @param $map
     * @param $page
     * @param int $limit
     * @param string $order
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.26
     */
    public static function getUserReportList($map,$page,$limit=10,$order='create_timde desc'){
        $table=db('user_report');
        $list=$table->where($map)->page($page,$limit)->order($order)->select();
        $reason= Report::getReportReason();
        foreach ($list as &$vo){
            $vo['nickname']=db('user')->where(['uid'=>$vo['uid']])->value('nickname');
            $vo['to_nickname']=db('user')->where(['uid'=>$vo['to_uid']])->value('nickname');
            $vo['reason_show']=$reason[$vo['reason']];
            $vo['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
            $vo['is_deal']=$vo['is_deal']?'已处理':'未处理';
        }
        $count=$table->where($map)->count();
        return ['count'=>$count,'data'=>$list];
    }

    /**
     * 举报帖子
     * @param $data
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.26
     */
    public static function addCommentReport($data){
        return self::insert($data);
    }
    /**
     * 获取举报帖子列表
     * @param $map
     * @param $page
     * @param int $limit
     * @param string $order
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.26
     */
    public static function getCommentReportList($map,$page,$limit=10,$order='create_time desc'){
        $table=db('comment_report');
        $list=$table->where($map)->page($page,$limit)->order($order)->select();
        $count=$table->where($map)->count();
        return array($list,$count);
    }

    /**
     * 累计投诉更新
     * @param $uid
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.26
     */
    public static function reportCount($uid){
        $map['to_uid']=$uid;
        $count1=db('forum_report')->where($map)->count();
        $count2=db('user_report')->where($map)->count();
        $count3=db('comment_report')->where($map)->count();
        $count=$count1+$count2+$count3;
        db('user_report')->where($map)->update(['total_count'=>$count]);
    }

    /**
     * 获取举报原因
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.30
     */
    public static function getReportReasonList(){
        $list=db('report_reason')->where(['status'=>1])->order('sort asc')->select();
        $count=db('report_reason')->where(['status'=>1])->count();
        return ['count'=>$count,'data'=>$list];
    }

    /**
     * @return array
     * 处理类型
     */
    public static function getDealType(){
        $data=[
            1=>['id'=>1,'name'=>'无效举报','sort'=>1],
            2=>['id'=>2,'name'=>'禁言处理','sort'=>2],
            3=>['id'=>3,'name'=>'删除帖子','sort'=>3],
            4=>['id'=>4,'name'=>'禁用处理','sort'=>4],
        ];
        return $data;
    }

    public static function getReportType(){
        //主题类型,1.普通版面,2.微博,3.朋友圈,4.资讯,5.活动,6.视频横版,7.视频竖版,8.公告
        $data=[
            1=>['id'=>1,'name'=>'内容举报-帖子','sort'=>1],
            2=>['id'=>2,'name'=>'内容举报-视频','sort'=>2],
            3=>['id'=>3,'name'=>'评论举报','sort'=>3],
            4=>['id'=>4,'name'=>'用户举报','sort'=>4],
            5=>['id'=>5,'name'=>'内容举报-动态','sort'=>5],
            6=>['id'=>6,'name'=>'内容举报-话题','sort'=>6],
        ];
        return $data;
    }

    /**
     * 删除帖子
     * @param $map
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function delete_content($map,$info){
        self::beginTrans();
        //数据更改
        $data['is_deal']=1;
        $data['deal_type']=3;
        $data['deal_time']=time();
        $data['status']=1;
        $data['operation_uid']=$info['operation_uid'];
        $data['operation_identity']=$info['operation_identity'];
        $is_deal=self::where($map)->update($data);
        if(!$is_deal)  { self::rollbackTrans();return false;};
        //删除帖子
        $content=self::where($map)->select()->toArray();
        $ids=[];
        $ids_thread=[];
        foreach ($content as $vo){
            if($vo['type']==3){
                $ids[]=$vo['content'];
            }else{
                $ids_thread[]=$vo['content'];
            }
        }
        if($ids){
            $res=db('com_post')->where(['id'=>['in',$ids]])->update(['status'=>-1]);
        }else{
            $res=true;
        }
        if($ids_thread){
            $res2 = db('com_thread')->where(['id'=>['in',$ids_thread]])->update(['status'=>-1]);
        }else{
            $res2=true;
        }
        $reportList=self::where($map)->select();
        $reason= Report::getReportReason();
        $reason_ids= self::getReportReasonIds();
        foreach ($reportList as &$vo){
            if(in_array($vo['reason'],$reason_ids)){
                $vo['reason_show']=$vo['reason']==0?$vo['other_reason']:$reason[$vo['reason']];
            }else{
                $vo['reason_show']=$vo['other_reason'];
            }
            self::delete_forum_message($vo);
        }
        unset($reportList);
        if($res&&$res2){
            Cache::clear('thread_list_cache');
            self::commitTrans();
            return true;
        }
        self::rollbackTrans();
        return false;
    }

    /**
     * 用户禁言
     * @param $map
     * @param $prohibit
     * @return $this
     */
    public static function user_prohibit($map,$prohibit,$where){
        //数据更改
        $data['is_deal']=1;
        $data['deal_type']=2;
        $data['deal_time']=time();
        $data['status']=1;
        $data['prohibit']=$prohibit;
        $data['operation_uid']=$where['operation_uid'];
        $data['operation_identity']=$where['operation_identity'];
        $prohibit=db('report_prohibit')->where(['id'=>$prohibit])->find();
        $time=$prohibit['time_type']==1?3600*$prohibit['num']:24*3600*$prohibit['num'];
        $info=$prohibit['time_type']==1?$prohibit['num'].'小时':$prohibit['num'].'天';
        $data['prohibit_time']=time()+$time;
        $report=self::where($map)->field('uid,to_uid')->find();
        self::deal_user_message($report['to_uid'],$report['uid'],'禁言'.$info);
        return  self::where($map)->update($data);
    }

    /*
     * 用户禁用
     */
    public static function user_delete($map,$info){
        //数据更改
        $data['is_deal']=1;
        $data['deal_type']=4;
        $data['deal_time']=time();
        $data['status']=1;
        $data['operation_uid']=$info['operation_uid'];
        $data['operation_identity']=$info['operation_identity'];
        //用户禁用
        $to_uid=self::where($map)->value('to_uid');
        db('user')->where(['uid'=>$to_uid])->update(['status'=>0]);
        $uid=self::where($map)->value('uid');
        self::deal_user_message($to_uid,$uid,'禁用');
        return  self::where($map)->update($data);
    }

    public static function no_deal($map,$info){
        $data['is_deal']=1;
        $data['deal_type']=1;
        $data['deal_time']=time();
        $data['status']=1;
        $data['prohibit']='';
        $data['status']=1;
        $data['prohibit_time']='';
        $data['operation_uid']=$info['operation_uid'];
        $data['operation_identity']=$info['operation_identity'];
        return self::where($map)->update($data);
    }
    /**
     * 获取举报原因
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.30
     */
    public static function getProhibitList(){
        $list=db('report_prohibit')->where(['status'=>1])->select();
        foreach ($list as &$v){
            $v['time_type']=$v['time_type']==1?'小时':'天';
            $v['type']='禁言';
        }
        $count=db('report_prohibit')->where(['status'=>1])->count();
        return ['count'=>$count,'data'=>$list];
    }

    /**
     * 判断是否处于禁言中
     * @param $uid
     * @return bool
     * 2020.2.19
     */
    public static function is_prohibit($uid){
        $map['to_uid']=$uid;
        $map['status']=1;
        $map['prohibit_time']=['gt',time()];
        $res=db('report')->where($map)->find();
        if($res) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * 举报处理通知
     * @param $v
     * @param $to_uid
     * @param $action
     */
    public static function is_deal_message($v,$to_uid,$action=''){
        $now_uid=get_uid();
        //发送消息
        $set=MessageTemplate::getMessageSet(45);
        $template=str_replace('{年月日时分}', date('Y-m-d H:i',time()), $set['template']);
        $template=str_replace('{XX}', $action, $template);
        if($set['status']==1){
            $message_id=Message::sendMessage($to_uid,$now_uid,$template,1,$set['title'],1,'','','');
            $read_id=MessageRead::createMessageRead($to_uid,$message_id,$set['popup'],1);
        }
        if($set['sms']==1&&$set['status']==1){
            $account=UserModel::where('uid',$to_uid)->value('phone');
            $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
            $template='【'.$config['cl_sms_sign'].'】'.$template;
            $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
            $sms=json_decode($sms,true);
            if ($sms['code']==0) {
                $read_data['is_sms']=1;
                $read_data['sms_time']=time();
                MessageRead::where('id',$read_id)->update($read_data);
            }
        }
    }

    /**
     * @param $v
     * @param $to_uid
     * @param $uid
     * @param string $action
     */
    public static function deal_user_message($to_uid,$uid,$action=''){
        self::is_deal_message($v='',$uid);
        $now_uid=get_uid();
        $set=MessageTemplate::getMessageSet(46);
        $template=str_replace('{XX}', $action, $set['template']);
        if($set['status']==1){
            $message_id=Message::sendMessage($to_uid,$now_uid,$template,1,$set['title'],1,'','','');
            $read_id=MessageRead::createMessageRead($to_uid,$message_id,$set['popup'],1);
        }
        if($set['sms']==1&&$set['status']==1){
            $account=UserModel::where('uid',$to_uid)->value('phone');
            $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
            $template='【'.$config['cl_sms_sign'].'】'.$template;
            $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
            $sms=json_decode($sms,true);
            if ($sms['code']==0) {
                $read_data['is_sms']=1;
                $read_data['sms_time']=time();
                MessageRead::where('id',$read_id)->update($read_data);
            }
        }
    }

    /**
     * 删帖操作发送消息
     * @param $v
     * @param $t_uid
     * @param $uid
     * @param string $remarks
     */
    public static function delete_forum_message($report){

        if($report['type']==3){
            $thread=db('com_post')->where(['id'=>$report['content']])->find();
            $set=MessageTemplate::getMessageSet(48);
            //发送举报人消息
            $forum_name=db('com_thread')->where(['id'=>$thread['tid']])->value('title');
            $thread['title']=$thread['title']?$thread['title']:mb_substr($thread['content'],0,10).'...';
            //发送消息
            $template=str_replace('{年月日时分}', date('Y-m-d H:i',time()), $set['template']);
            $template=str_replace('{帖子标题}', $forum_name, $template);
            $template=str_replace('{评论标题}', $thread['title'], $template);
            $template=str_replace('{删帖类型}', $report['reason_show'], $template);
            $thread_id=$report['content'];

        }else{
            $thread=db('com_thread')->where(['id'=>$report['content']])->find();
            //发送举报人消息
            $forum_name=db('com_forum')->where(['id'=>$thread['fid']])->value('name');
            $thread['title']=$thread['title']?$thread['title']:mb_substr($thread['content'],0,10).'...';
            $thread_id=$thread['post_id'];
            $set=MessageTemplate::getMessageSet(47);
            //发送消息
            $template=str_replace('{年月日时分}', date('Y-m-d H:i',time()), $set['template']);
            $template=str_replace('{版块名称}', $forum_name, $template);
            $template=str_replace('{帖子标题}', $thread['title'], $template);
            $template=str_replace('{删帖类型}', $report['reason_show'], $template);
            //缓存版块缓存
            $tag='forum_other_info_fid_'.$thread['fid'];
            Cache::set($tag,null);
        }
        self::is_deal_message($thread_id,$report['uid']);
        $now_uid=get_uid();

        if($set['status']==1){
            $message_id=Message::sendMessage($thread['author_uid'],$now_uid,$template,1,$set['title'],1,'','','');
            $read_id=MessageRead::createMessageRead($thread['author_uid'],$message_id,$set['popup'],1);
        }
        if($set['sms']==1&&$set['status']==1){
            $account=UserModel::where('uid',$thread['author_uid'])->value('phone');
            $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
            $template='【'.$config['cl_sms_sign'].'】'.$template;
            $sms=ChuanglanSmsApi::sendSMS($account,$template); //发送短信
            $sms=json_decode($sms,true);
            if ($sms['code']==0) {
                $read_data['is_sms']=1;
                $read_data['sms_time']=time();
                MessageRead::where('id',$read_id)->update($read_data);
            }
        }

    }
}