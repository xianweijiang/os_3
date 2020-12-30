<?php

namespace app\admin\controller\agent;

use app\admin\controller\AuthController;
use app\admin\model\order\StoreOrder;
use app\admin\model\user\User;
use app\admin\model\wechat\WechatUser as UserModel;
use app\admin\library\FormBuilder;
use app\shareapi\model\InviteLevel;
use app\shareapi\model\Sell;
use app\wap\model\user\UserBill;
use service\JsonService;
use service\UtilService as Util;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;
use app\admin\model\system\SystemConfig;

/**
 * 分销商管理控制器
 * Class AgentManage
 * @package app\admin\controller\agent
 */
class AgentManage extends AuthController
{

//    /**
//     * @return mixed
//     */
//    public function index()
//    {
//        $where = Util::getMore([
//            ['nickname',''],
//            ['data',''],
//            ['tagid_list',''],
//            ['groupid','-1'],
//            ['sex',''],
//            ['export',''],
//            ['stair',''],
//            ['second',''],
//            ['order_stair',''],
//            ['order_second',''],
//            ['subscribe',''],
//            ['now_money',''],
//            ['is_promoter',1],
//        ],$this->request);
//        $this->assign([
//            'where'=>$where,
//        ]);
//        $limitTimeList = [
//            'today'=>implode(' - ',[date('Y/m/d'),date('Y/m/d',strtotime('+1 day'))]),
//            'week'=>implode(' - ',[
//                date('Y/m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),
//                date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600))
//            ]),
//            'month'=>implode(' - ',[date('Y/m').'/01',date('Y/m').'/'.date('t')]),
//            'quarter'=>implode(' - ',[
//                date('Y').'/'.(ceil((date('n'))/3)*3-3+1).'/01',
//                date('Y').'/'.(ceil((date('n'))/3)*3).'/'.date('t',mktime(0,0,0,(ceil((date('n'))/3)*3),1,date('Y')))
//            ]),
//            'year'=>implode(' - ',[
//                date('Y').'/01/01',date('Y/m/d',strtotime(date('Y').'/01/01 + 1year -1 day'))
//            ])
//        ];
//        $uidAll = UserModel::getAll($where);
//        $this->assign(compact('limitTimeList','uidAll'));
//        $this->assign(UserModel::agentSystemPage($where));
//        return $this->fetch();
//    }
//
//    /**
//     * 一级推荐人页面
//     * @return mixed
//     */
//    public function stair($uid = ''){
//        if($uid == '') return $this->failed('参数错误');
//        $list = User::alias('u')
//            ->where('u.spread_uid',$uid)
//            ->field('u.avatar,u.nickname,u.now_money,u.add_time,u.uid')
//            ->where('u.status',1)
//            ->order('u.add_time DESC')
//            ->select()
//            ->toArray();
//        foreach ($list as $key=>$value) $list[$key]['orderCount'] = StoreOrder::getOrderCount($value['uid'])?:0;
//        $this->assign('list',$list);
//        return $this->fetch();
//    }
//
//    /**
//     * 个人资金详情页面
//     * @return mixed
//     */
//    public function now_money($uid = ''){
//        if($uid == '') return $this->failed('参数错误');
//        $list = UserBill::where('uid',$uid)->where('category','now_money')
//            ->field('mark,pm,number,add_time')
//            ->where('status',1)->order('add_time DESC')->select()->toArray();
//        foreach ($list as &$v){
//            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
//        }
//        $this->assign('list',$list);
//        return $this->fetch();
//    }

    /**
     * 分销商列表页面
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function agent()
    {
        $data=Util::getMore([
            ['status',10],
        ]);
        $this->assign('status',$data['status']);
        $this->assign([
            'year'=> getMonth('y'),
        ]);
        $show_data=[
            'seller_num'=>Sell::where('status',1)->count(),
            'order_num'=>db('sell_order')->where('back_status','in',[1,2])->count(),
            'order_money'=>db('sell_order')->where('back_status','in',[1,2])->sum('pay_money'),
            'order_back_money'=>db('sell_order')->where('back_status','in',[1,2])->sum('back_money'),
            'out_money'=>Sell::where('status',1)->sum('out_income')
        ];
        $this->assign('show_data',$show_data);
        return $this->fetch();
    }

    /**
     * 异步查找分销商列表
     *
     * @return json
     */
    public function agent_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['status',10],
            ['select_date',''],
            ['key_word',''],
            ['excel',0],
        ]);
        return JsonService::successlayui(Sell::sellerList($where));
    }

    /**
     * 分销商审核
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function auditSeller()
    {
        $data=Util::getMore([
            'uid',
            ['status',1],
            ['fail_reason',''],
        ]);
        if(!in_array($data['status'],[1,3])) return JsonService::fail('非法操作');
        Sell::edit(['status'=>$data['status'],'fail_reason'=>$data['fail_reason'],'need_tip'=>1,'audit_time'=>time()],$data['uid'],'uid');
        if($data['status']==1){
            $set=MessageTemplate::getMessageSet(21);
            $web_name=SystemConfig::getValue('website_name');
            $temp=str_replace('{应用名称}', $web_name, $set['template']);
            if($set['status']==1){
                $message_id=Message::sendMessage($data['uid'],0,$temp,1,$set['title'],1,'','promotion_center');
                $read_id=MessageRead::createMessageRead($data['uid'],$message_id,$set['popup'],1);
            }
            if($set['sms']==1&&$set['status']==1){
                $account=User::where('uid',$data['uid'])->value('phone');
                $config = SystemConfig::getMore('cl_sms_sign,cl_sms_template');
                $sms=ChuanglanSmsApi::sendSMS($account,'【'.$config['cl_sms_sign'].$temp); //发送短信
                $sms=json_decode($sms,true);
                if ($sms['code']==0) {
                    $read_data['is_sms']=1;
                    $read_data['sms_time']=time();
                    MessageRead::where('id',$read_id)->update($read_data);
                }
            }
        }else{
            $set=MessageTemplate::getMessageSet(22);
            $length=mb_strlen($data['fail_reason'],'UTF-8');
            if($length>7){
                $data['fail_reason']=mb_substr($data['fail_reason'],0,7,'UTF-8').'…';
            }
            $template=str_replace('{驳回理由}', $data['fail_reason'], $set['template']);
            if($set['status']==1){
                $message_id=Message::sendMessage($data['uid'],0,$template,1,$set['title'],1,'','my');
                $read_id=MessageRead::createMessageRead($data['uid'],$message_id,$set['popup'],1);
            }
            if($set['sms']==1&&$set['status']==1){
                $account=User::where('uid',$data['uid'])->value('phone');
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
        $tag='IS_SELLER_'.$data['uid'];
        cache($tag,null);
        return JsonService::successful('操作成功');
    }

    /**
     * 取消分销权限
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function delSeller()
    {
        $data=Util::getMore([
            'uid'
        ]);
        Sell::edit(['status'=>0],$data['uid'],'uid');
        $set=MessageTemplate::getMessageSet(23);
        if($set['status']==1){
            $message_id=Message::sendMessage($data['uid'],0,$set['template'],1,$set['title'],1,'','my');
            $read_id=MessageRead::createMessageRead($data['uid'],$message_id,$set['popup'],1);
        }
        if($set['sms']==1&&$set['status']==1){
            $account=User::where('uid',$data['uid'])->value('phone');
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
        $tag='IS_SELLER_'.$data['uid'];
        cache($tag,null);
        return JsonService::successful('取消分销权限');
    }


    /**
     * 分销商推广人列表
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function sell_child()
    {
        $where=Util::getMore([
            ['seller_uid',0],
        ]);
        $this->assign([
            'year'=> getMonth('y'),
            'seller_uid'=>$where['seller_uid'],
        ]);
        return $this->fetch();
    }

    /**
     * 获取订单列表
     * return json
     */
    public function sell_child_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['select_date',''],
            ['type','all'],//用户类型    all：全部、level1：一级、level2：二级
            ['keywords',''],
            ['seller_uid',0],
        ]);
        return JsonService::successlayui(InviteLevel::sellerChildList($where));
    }
}