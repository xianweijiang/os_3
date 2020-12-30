<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/25
 * Time: 14:19
 */

namespace app\shareapi\controller;


use app\admin\model\system\SystemConfig;
use app\shareapi\model\CashOut;
use app\shareapi\model\Sell;
use basic\ControllerBasic;
use GuzzleHttp\Psr7\Request;
use service\UtilService;

class Money extends ControllerBasic
{

    /**
     * 收益提现页面
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function index()
    {
        $uid=$this->_needLogin();
        $is_seller=is_seller($uid);
        if(!$is_seller){
            return $this->apiError('请先成为分销商');
        }
        $seller_info=Sell::get(['uid'=>$uid]);
        $agent_tixian_config=SystemConfig::getMore(['agent_tixian_config_max','agent_tixian_config_min','agent_tixian_config_rules']);
        $this->assign('agent_tixian_config',$agent_tixian_config);

        $last_out_map_weixin=['type'=>'weixin','uid'=>$uid];
        $weixin=db('cash_out')->order('create_time desc')->field('account,image,weixin_name')->where($last_out_map_weixin)->find();
        $last_out_map_alipay=['type'=>'alipay','uid'=>$uid];
        $alipay=db('cash_out')->order('create_time desc')->field('account,image')->where($last_out_map_alipay)->find();

        $return_data=[
            'has_income'=>$seller_info['has_income'],//可提现金额
            'min_out'=>$agent_tixian_config['agent_tixian_config_min'],//提现金额最低X元
            'max_out'=>$agent_tixian_config['agent_tixian_config_max'],//提现金额最高X元
            'rules'=>$agent_tixian_config['agent_tixian_config_rules'],//提现规则
            'weixin'=>$weixin,//上次微信提现记录，[account=>微信号,image=>收款码]
            'alipay'=>$alipay,//上次支付宝提现记录，[account=>支付宝帐号,image=>真实姓名]
        ];
        return $this->apiSuccess($return_data);
    }

    /**
     * 检测是否可提现
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function canCashOut()
    {
        $uid=$this->_needLogin();
        $is_seller=is_seller($uid);
        if(!$is_seller){
            return $this->apiError('请先成为分销商');
        }
        $has_on_out=db('cash_out')->where('uid',$uid)->where('status','in',[1,2])->count();
        if($has_on_out>0){
            return $this->apiError('您还有未处理完的提现订单');
        }else{
            return $this->apiSuccess('可以提现');
        }
    }

    /**
     * 发起提现请求
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function doCashOut()
    {
        $uid=$this->_needLogin();

        //提现请求查重 start
        $send_cash_out_request=cache('cash_out_request'.$uid);
        if($send_cash_out_request){
            return $this->apiError('请求已经提交，请勿重复点击');
        }
        cache('cash_out_request'.$uid,1,5);//5秒内不能提交两次
        //提现请求查重 end

        $is_seller=is_seller($uid);
        if(!$is_seller){
            return $this->apiError('请先成为分销商');
        }
        $has_on_out=CashOut::where('uid',$uid)->where('status','in',[1,2])->count();
        if($has_on_out>0){
            return $this->apiError('您还有未处理完的提现订单');
        }else{
            list($type,$account,$image,$out_num,$weixin_name)=UtilService::postMore([
                ['type','weixin'],
                ['account',''],
                ['image',''],
                ['out_num',0],
                ['weixin_name','']
            ],$this->request,true);
            if(!in_array($type,['weixin','alipay'])){
                return $this->apiError('请正确选择提现类型');
            }
            if($account==''||$image==''){
                return $this->apiError('请正确填写提现账户信息');
            }

            $seller_info=Sell::get(['uid'=>$uid]);
            if(bccomp($out_num,$seller_info['has_income'],2)==1){
                return $this->apiError('可提现余额不足');
            }

            $agent_tixian_config=SystemConfig::getMore(['agent_tixian_config_max','agent_tixian_config_min']);
            $this->assign('agent_tixian_config',$agent_tixian_config);
            if(($agent_tixian_config['agent_tixian_config_max']>0&&bccomp($out_num,$agent_tixian_config['agent_tixian_config_max'],2)==1)||bccomp($out_num,$agent_tixian_config['agent_tixian_config_min'],2)==-1){
                return $this->apiError('提现金额最低'.$agent_tixian_config['agent_tixian_config_min'].'元，最高'.$agent_tixian_config['agent_tixian_config_max'].'元');
            }
            $save_data=[
                'order_num'=>CashOut::getNewOrderId(),
                'uid'=>$uid,
                'type'=>$type,
                'account'=>$account,
                'image'=>$image,
                'weixin_name'=>$weixin_name,
                'out_num'=>$out_num,
                'create_time'=>time(),
                'status'=>1
            ];
            $res=CashOut::doCashOut($save_data);
            if($res){
                return $this->apiSuccess('提现申请成功！');
            }else{
                return $this->apiError('提现申请失败'.CashOut::getErrorInfo());
            }
        }
    }

    /**
     * 提现列表
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function cashOutList()
    {
        $uid=$this->_needLogin();
        $is_seller=is_seller($uid);
        if(!$is_seller){
            return $this->apiError('请先成为分销商');
        }
        $seller_info=Sell::get(['uid'=>$uid]);
        $map['uid']=$uid;
        $cashOutList=CashOut::getList($map,50);
        $return_data=[
            'out_income'=>$seller_info['out_income'],
            'out_list'=>$cashOutList,
        ];
        $this->apiSuccess($return_data);
    }
}