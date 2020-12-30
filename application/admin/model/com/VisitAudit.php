<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/2/5
 * Time: 10:23
 */
namespace app\admin\model\com;
use app\admin\model\system\SystemConfig;
use app\osapi\lib\ChuanglanSmsApi;
use app\osapi\model\com\ComForum;
use app\osapi\model\user\UserModel;
use app\osapi\model\com\Message;
use think\Cache;
use traits\ModelTrait;
use basic\ModelBasic;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use think\Db;

class VisitAudit extends ModelBasic
{
    use ModelTrait;
    protected $name='com_forum_member';

    public static function addDate($data){
        $count=self::where(['fid'=>$data['fid'],'uid'=>$data['uid']])->count();
        $data['count']=$count+1;
        return self::set($data);
    }

    public static function editData($data){
        if(self::where(['id'=>$data['id']])->count()){
            Cache::rm('_visit_audit_'.$data['id']);
            return self::where(['id'=>$data['id']])->update($data);
        }else{
            return self::addDate($data);
        }
    }
    public static function getDate($id){
        $map['id']=$id;
        $data=self::where($map)->cache('_visit_audit_'.$id)->find();;
        return $data;
    }
    public static function setStatus($map,$status){
        return self::where($map)->update(['status'=>$status]);
    }

    /**
     * 获取审核列表
     * @param $map
     * @param $page
     * @param $limit
     * @param $order
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.8
     */
    public static function get_audit_list($map,$page,$limit,$order){
        $data=self::where($map)->page($page,$limit)->order($order)->select()->toArray();
        foreach ($data as &$v){
            if($v['status']==1){
                if($v['audit_time']==0){
                    $v['audit_time']=$v['create_time'];
                }
            }
            $v['audit_time']=$v['audit_time']?date('Y-m-d H:i',$v['audit_time']):'';
            $v['create_time']=date('Y-m-d H:i',$v['create_time']);
            $v['time']='申请时间:'.$v['create_time'].'</br>审核时间:'.$v['audit_time'];
            $user=UserModel::getUserInfo($v['uid'],'uid,nickname,avatar');
            $v['nickname']=$user['nickname'];
            $v['avatar']=$user['avatar'];
            $v['f_name']=ComForum::where(['id'=>$v['fid']])->value('name');
            if($v['status']==1){
                $v['status']='已审核';
            }elseif($v['status']==2){
                $v['status']='待审核';
            }else{
                $v['status']='驳回';
            }
            if($v['is_admin']==1){
                $v['audit_name']=db('system_admin')->where(['id'=>$v['audit_uid']])->value('real_name');
            }else{
                $user=UserModel::getUserInfo($v['audit_uid'],'nickname');
                $v['audit_name']=$user['nickname'];
            }
        }
        $count=self::where($map)->count();
        return compact('count', 'data');
    }

    /**
     * 需要审核的长时间不审核会自动审核
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.9
     */
    public static function set_audit_show(){
        $audit_visit_limit=SystemConfig::getValue('forum_audit_visit_limit');

        $map['create_time']=['lt',time()-24*3600*$audit_visit_limit];
        $map['status']=2;
        $ids=self::where($map)->field('id')->select()->toArray();
        if($ids){
            $status=SystemConfig::getValue('forum_audit_visit');
            $mav['id']=['in',array_column($ids,'id')];
            if($status==0){
                $reason='系统自动判定未通过';
            }else{
                //清除权限缓存
                ForumPower::clear_cache();
                $reason='';
            }
            self::where($map)->update(['reject_reason'=>$reason,'status'=>$status,'audit_uid'=>0,'audit_time'=>time()]);
            //发送短信
            if($status==1){
                $tem=49;
            }else{
                $tem=50;
            }
            self::send_message($mav,$tem,$reason);
        }
        $audit_forum_limit=SystemConfig::getValue('forum_audit_forum_limit');
        $map['create_time']=['lt',time()-24*3600*$audit_forum_limit];
        $status=SystemConfig::getValue('forum_audit_forum');
        ComThread::where($map)->update(['status'=>$status]);
    }

    /**
     * 发送消息
     * @param $map
     * @param $message_type
     * @param string $reason
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.8
     */
    public static function send_message($map,$message_type,$reason=''){
        $audit=self::where($map)->select()->toArray();
        foreach ($audit as $v){
            //发送举报人消息
            $forum_name=db('com_forum')->where(['id'=>$v['fid']])->value('name');
            $set=MessageTemplate::getMessageSet($message_type);
            //发送消息
            $template=str_replace('{版块名称}', $forum_name, $set['template']);;
            $template=str_replace('{删除原因}', $reason, $template);
            $template=str_replace('{移除原因}', $reason, $template);

            $now_uid=get_uid();
            if($set['status']==1){
                $message_id=Message::sendMessage($v['uid'],$now_uid,$template,1,$set['title'],1,'','','');
                $read_id=MessageRead::createMessageRead($v['uid'],$message_id,$set['popup'],1);
            }
            if($set['sms']==1&&$set['status']==1){
                $account=UserModel::where('uid',$v['uid'])->value('phone');
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
    /**
     * 时间选择
     * @param $op
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2020.4.15
     */
    public static function timeRange($op){
        if (is_array($op)) {
            $range = $op;
        } else {
            // 使用日期表达式
            switch (strtolower($op)) {
                case 'today':
                case 'd':
                    $range = [ strtotime('today'),  strtotime('tomorrow')];
                    break;
                case 'week':
                case 'w':
                    $range = [ strtotime('this week 00:00:00'),  strtotime('next week 00:00:00')];
                    break;
                case 'month':
                case 'm':
                    $range = [strtotime('first Day of this month 00:00:00'), strtotime('first Day of next month 00:00:00')];
                    break;
                case 'year':
                case 'y':
                    $range = [ strtotime('this year 1/1'),  strtotime('next year 1/1')];
                    break;
                case 'yesterday':
                    $range = [ strtotime('yesterday'), strtotime( 'today')];
                    break;
                case 'last week':
                    $range = [ strtotime('last week 00:00:00'),  strtotime('this week 00:00:00')];
                    break;
                case 'last month':
                    $range = [ strtotime('first Day of last month 00:00:00'), strtotime('first Day of this month 00:00:00')];
                    break;
                case 'last year':
                    $range = [ strtotime('last year 1/1'),  strtotime('this year 1/1')];
                    break;
                case 'quarter':
                    $season = ceil((date('n'))/3);//当月是第几季度
                    $range = [mktime(0, 0, 0,$season*3-3+1,1,date('Y')),mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y'))];
                    break;
                default:
                    $op=explode(' - ',$op);
                    $op[0]=strtotime($op[0]);
                    $op[1]=strtotime($op[1]);
                    $range = $op;
            }
        }
        return ['between',$range];
    }
}