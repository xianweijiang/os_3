<?php
/**
 * Created by PhpStorm.
 * User: zxh
 * Date: 2019/10/25
 * Time: 10:11
 */

namespace app\osapi\model\user;


use app\osapi\model\user\UserModel;
use app\shareapi\model\Sell;
use basic\ModelBasic;
use tests\thinkphp\library\think\dbTest;
use traits\ModelTrait;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;
use app\osapi\model\common\Support;
use app\admin\model\system\SystemConfig;

class InviteCode extends ModelBasic
{
    use ModelTrait;

    /**
     * 邀请码
     * @param $uid
     * @return mixed
     * @author zxh  zxh@ourstu.com
     *时间：2019.10.25
     */
    public static function addCode($uid){
        $data['uid']=$uid;
        $data['code']=self::checkCode();
        $data['create_time']=time();
        $data['invite_num']=0;
        $res=self::set($data);
        return $res?$data:false;
    }

    /**
     * 邀请码是否存在
     * @param $code
     * @return array|false|\PDOStatement|string|\think\Model
     * @author zxh  zxh@ourstu.com
     *时间：2019.10.25
     */
    public static function getCode($code){
        return self::where(['code'=>$code])->find();
    }

    /**
     * 检测邀请码是否重复且重新获取新的邀请码
     * @return string
     * @author zxh  zxh@ourstu.com
     *时间：2019.10.25
     */
    public static function checkCode(){
        $code=create_rand(5,'letter');
        if(self::getCode($code)){
           return self::checkCode();
        }else{
           return $code;
        }
    }

    /**
     * 获取邀请code
     * @param $uid
     * @return array|false|mixed|\PDOStatement|string|\think\Model
     * @author zxh  zxh@ourstu.com
     *时间：2019.10.25
     */
    public static function getInviteCode($uid){
        $invite=self::where(['uid'=>$uid])->find();
        $invite=$invite?$invite:self::addCode($uid);
        $invite['code']='U'.$invite['code'].'D';
        return $invite;
    }

    public static function cutInvite($invite){
        if(strlen($invite)==7){
            $invite = substr($invite,1);
            $invite = substr($invite, 0, -1);
        }
        return $invite;
    }

    /**
     * 添加邀请记录
     * @param $inviteCode
     * @param $uid
     * @return array|false|mixed|\PDOStatement|string|\think\Model
     * @author zxh  zxh@ourstu.com
     *时间：2019.10.25
     */
    public static function addInviteLog($inviteCode,$uid){
        if($inviteCode&&$uid){
            $inviteCode=self::cutInvite($inviteCode);
            $invite=self::getCode($inviteCode);
            if($invite['uid']==$uid||empty($invite)){
                return true;
            }
            $invite_reward=SystemConfig::getValue('invite_reward');
            if($invite_reward==1){
                $num=db('invite_log')->where('father_uid',$invite['uid'])->count();
                $num=$num+1;
                $template='';
                $reward=db('invite_reward')->where('status',1)->where('num','>=',$num)->order('num asc')->limit(1)->select();
                if(!$reward){
                    $reward_now=db('invite_reward')->where('status',1)->order('level desc')->limit(1)->select();
                    if($reward_now[0]['reward_type']=='积分奖励'){
                        $score=json_decode($reward_now[0]['reward'],true);
                        $log = [] ;
                        foreach ($score as &$value){
                            $value['name']=db('system_rule')->where('flag',$value['flag'])->value('name');
                            UserModel::where('uid',$invite['uid'])->setInc($value['flag'],$value['value']);
                            $template=$template.$value['name'].':'.$value['value'].'；';
                            $log[$value['flag']] = $value['value'];
                        }
                        unset($value);
                        Support::jiafenlog($invite['uid'],'邀请奖励',$log,1,'行为');
                        $data['reward']=$reward_now[0]['reward'];
                        $data['reward_type']=$reward_now[0]['reward_type'];
                    }
                }else{
                    if($reward[0]['reward_type']=='积分奖励'){
                        $score=json_decode($reward[0]['reward'],true);
                        $log = [] ;
                        foreach ($score as &$value){
                            $value['name']=db('system_rule')->where('flag',$value['flag'])->value('name');
                            UserModel::where('uid',$invite['uid'])->setInc($value['flag'],$value['value']);
                            $template=$template.$value['name'].':'.$value['value'].'；';
                            $log[$value['flag']] = $value['value'] ;
                        }
                        unset($value);
                        Support::jiafenlog($invite['uid'],'邀请奖励',$log,1,'行为');
                        $data['reward']=$reward[0]['reward'];
                        $data['reward_type']=$reward[0]['reward_type'];
                    }
                }
                $set=MessageTemplate::getMessageSet(40);
                $template=str_replace('{奖励内容}', $template, $set['template']);
                if($set['status']==1){
                    $message_id=Message::sendMessage($invite['uid'],0,$template,1,$set['title'],1);
                    $read_id=MessageRead::createMessageRead($invite['uid'],$message_id,$set['popup'],1);
                }
                if($set['sms']==1&&$set['status']==1){
                    $account=UserModel::where('uid',$invite['uid'])->value('phone');
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
            //邀请记录添加
            $data['code']=$inviteCode;
            $data['uid']=$uid;
            $data['father_uid']=$invite['uid'];
            $data['create_time']=time();
            $res1=db('invite_log')->insert($data);

            //是否是分销商
            $is_seller=is_seller($invite['uid']);
            if(!$is_seller){
                return true;
            }

            $level=db('invite_level')->where(['uid'=>$invite['uid']])->find();
            $data2=[
                'uid'=>$uid,
                'father1'=>$invite['uid'],
                'father2'=>$level['father1']?$level['father1']:0,
                'create_time'=>time()
            ];
            $res3=db('invite_level')->insert($data2);
            if($is_seller){
                $set=MessageTemplate::getMessageSet(27);
                if($set['status']==1){
                    $nickname=UserModel::where('uid',$uid)->value('nickname');
                    $temp=str_replace('{用户昵称}', $nickname, $set['template']);
                    $message_id=Message::sendMessage($invite['uid'],0,$temp,1,$set['title'],1,'','promotion_team');
                    MessageRead::createMessageRead($invite['uid'],$message_id,$set['popup'],1);
                    if($level['father1']){
                        $message_id=Message::sendMessage($level['father1'],0,$temp,1,$set['title'],1,'','promotion_team');
                        MessageRead::createMessageRead($level['father1'],$message_id,$set['popup'],1);
                    }
                }
            }
            //邀请人数自增1
            $res2=self::where(['code'=>$inviteCode])->setInc('invite_num',1);
            $res2=$res2&&false!==db('invite_level')->where('uid',$invite['uid'])->setInc('child_num');//不一定存在这条数据记录，$invite['uid']是顶级分销商时，该条数据没有实际记录
            $child1_num=db('invite_level')->where('father1',$invite['uid'])->count();
            $res2=$res2&&Sell::where('uid',$invite['uid'])->setField('child1_num',$child1_num);//分销商记录肯定有
            if($level['father1']){
                $child2_num=db('invite_level')->where('father2',$level['father1'])->count();
                $res2=$res2&&Sell::where('uid',$level['father1'])->setField('child2_num',$child2_num);//分销商记录肯定有
            }


            return $res1&&$res2&&$res3;
        }
    }

    /**
     * 获得邀请日志列表
     * @param $map
     * @param $page
     * @param $limit
     * @param $order
     * @return array
     * @author zxh  zxh@ourstu.com
     *时间：2019.10.25
     */
    public static function getInviteList($map,$page,$limit,$order){
        $table=db('invite_log');
        $invite_log=$table->where($map)->page($page,$limit)->order($order)->select();
        foreach ($invite_log as $key=>&$vo){
            $user=UserModel::getUserInfo($vo['uid']);
            $vo['user']=$user['nickname'];
            $father_user=UserModel::getUserInfo($vo['father_uid']);
            $vo['father_user']=$father_user['nickname'];
            $vo['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
        }
        unset($key,$vo);
        $count=$table->where($map)->count();
        return ['count'=>$count,'data'=>$invite_log];
    }

    /**
     * 检测code邀请码是否正确（用于登录）
     * @param $code
     * @author zxh  zxh@ourstu.com
     * @return false
     *时间：2019.10.26
     */
    public static function checkCodeLogin($code){
        $need_invite= db('system_config')->where(['menu_name'=>'invite_code_need'])->find();
        $code=self::cutInvite($code);
        $invite=self::getCode($code);
        if(intval(str_replace('"','',$need_invite['value']))!=0&&!$invite){
            return false;
        }else{
            return true;
        }
    }
}