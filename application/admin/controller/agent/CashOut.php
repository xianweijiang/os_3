<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/26
 * Time: 10:39
 */

namespace app\admin\controller\agent;


use app\admin\controller\AuthController;
use app\shareapi\model\Sell;
use service\JsonService;
use service\UtilService;
use app\shareapi\model\CashOut as CashOutModel;
use app\osapi\model\com\Message;
use app\osapi\model\com\MessageTemplate;
use app\osapi\model\com\MessageRead;
use app\osapi\lib\ChuanglanSmsApi;

class CashOut extends AuthController
{
    /**
     * 提现申请列表页面
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function index()
    {
        $data=UtilService::getMore([
            ['status',-1],
            ['seller_uid',0]
        ]);
        $this->assign('status',$data['status']);
        $this->assign([
            'year'=> getMonth('y'),
            'seller_uid'=>$data['seller_uid'],
        ]);
        $show_data=[
            'total_out_income'=>CashOutModel::where('status',3)->sum('out_num'),//已提现金额
            'total_on_out_income'=>CashOutModel::where('status','in',[1,2])->sum('out_num'),//待审核提现金额
            'total_income'=>Sell::where('status',1)->sum('total_income'),//佣金总金额
            'total_has_income'=>Sell::where('status',1)->sum('has_income'),//未提现金额
        ];
        $this->assign('show_data',$show_data);
        return $this->fetch();
    }

    /**
     * 异步查找提现申请列表
     *
     * @return json
     */
    public function cash_out_list(){
        $where=UtilService::getMore([
            ['page',1],
            ['limit',20],
            ['status',-1],
            ['select_date',''],
            ['keyword',''],
            ['out_way','all'],//all 全部  ，weixin,alipay
            ['seller_uid',0],
            ['excel',0],
        ]);
        return JsonService::successlayui(CashOutModel::outList($where));
    }

    /**
     * 提现申请审核通过
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function auditSuccess()
    {
        $data=UtilService::getMore([
            'id',
            'uid',
        ]);
        CashOutModel::update(['status'=>2],['id'=>$data['id'],'status'=>1,'uid'=>$data['uid']]);
        return JsonService::successful('操作成功');
    }

    /**
     * 提现打款成功
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function auditFinish()
    {
        $data=UtilService::getMore([
            'id',
            'uid',
        ]);
        CashOutModel::update(['status'=>3,'finish_time'=>time()],['id'=>$data['id'],'status'=>2,'uid'=>$data['uid']]);
        $out_num=CashOutModel::where('id',$data['id'])->value('out_num');
        $set=MessageTemplate::getMessageSet(28);
        $template=str_replace('{金额}',$out_num,$set['template']);
        if($set['status']==1){
            $message_id=Message::sendMessage($data['uid'],0,$template,1,$set['title'],1,'','cash_out');
            MessageRead::createMessageRead($data['uid'],$message_id,$set['popup'],1);
        }
        return JsonService::successful('打款成功');
    }

    /**
     * 提现驳回
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function auditFalse()
    {
        $data=UtilService::getMore([
            'id',
            'uid',
            'reason',
        ]);
        $res=CashOutModel::auditFail($data);
        if($res){
            $out_num=CashOutModel::where('id',$data['id'])->value('out_num');
            $set=MessageTemplate::getMessageSet(29);
            $template=str_replace('{金额}',$out_num,$set['template']);
            $length=mb_strlen($data['reason'],'UTF-8');
            if($length>7){
                $data['reason']=mb_substr($data['reason'],0,7,'UTF-8').'…';
            }
            $template=str_replace('{驳回理由}',$data['reason'],$template);
            if($set['status']==1){
                $message_id=Message::sendMessage($data['uid'],0,$template,1,$set['title'],1,'','cash_out');
                MessageRead::createMessageRead($data['uid'],$message_id,$set['popup'],1);
            }
            return JsonService::successful('申请驳回成功');
        }else{
            return JsonService::fail(CashOutModel::getErrorInfo('提现驳回失败'));
        }
    }

    /**
     * 设置备注
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function setRemark()
    {
        $data=UtilService::getMore([
            'id',
            'remark',
        ]);
        CashOutModel::update(['remark'=>text($data['remark'])],['id'=>$data['id'],'status'=>array('in',[2,3])]);
        return JsonService::successful('备注成功');
    }

}