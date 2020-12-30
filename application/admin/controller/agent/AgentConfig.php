<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/18
 * Time: 14:05
 */

namespace app\admin\controller\agent;


use app\admin\controller\AuthController;
use app\admin\model\store\StoreProduct;
use app\admin\model\system\SystemConfig;
use app\shareapi\model\InviteShare;
use service\FormBuilder;
use service\JsonService;
use service\UtilService as Util;
use think\Request;
use think\Url;

class AgentConfig extends AuthController
{
    /**
     * 分销配置页面
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function index()
    {
        $agent_config=SystemConfig::getValue('agent_config');
        if($agent_config['agent_way']==2){
            $agent_config['store_pay_value']=$agent_config['com_post_value']=$agent_config['total_score_value']=$agent_config['column_pay_value']='';
            foreach ($agent_config['agent_rules'] as $val){
                $rule_list[]=$val['key'];
                $agent_config[$val['key'].'_value']=$val['value'];
            }
            unset($val);
            $agent_config['agent_rules']=implode(',',$rule_list);
        }
        $goods_info['store_name']='';
        if($agent_config['agent_way']==3){
            !isset($agent_config['goods_id'])&&$agent_config['goods_id']=0;
            if($agent_config['goods_id']){
                $goods_info=StoreProduct::where('id',$agent_config['goods_id'])->where('is_del',0)->find()->toArray();

            }
        }
        $this->assign('goods_info',$goods_info);
        $this->assign('agent_config',$agent_config);
        return $this->fetch();
    }
		/* 商品快速查找
		 * */
    public function add_goods()
    {
        return $this->fetch();
    }
		/* 专栏商品快速查找
	 	* */
		public function add_columns()
		{
			return $this->fetch();
		}
    /**
     * 分销配置保存
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function saveConfig()
    {
        $request = Request::instance();
        if($request->isPost()){
            $post = $request->post();
            $agent_way = $post['agent_way'];
            switch ($agent_way){
                case 1://无条件（需要审核）
                    $save_data['agent_way']=1;
                    break;
                case 2://设置条件（需要审核）
                    $save_data['agent_way']=2;
                    $rules=$post['agent_rules'];
                    /**
                     * store_pay  商城消费满**元
                     * com_post   社区发帖满**条
                     * total_score 累计经验值达**
										 * column_pay  知识商城消费满**元
                     */
                    foreach ($rules as $val){
                        $rule_value=intval($post[$val.'_value']);
                        $rule_list[]=['key'=>$val,'value'=>$rule_value];
                    }
                    unset($val);
                    $save_data['agent_rules']=$rule_list;
                    break;
                case 3://购买商品自动生效
                    $save_data['agent_way']=3;
                    $save_data['goods_type']=$post['goods_type'];//all_goods 全部商品   ；one_goods 指定商品
                    if($save_data['goods_type']=='one_goods' || $save_data['goods_type']=='column_goods'){
                        $save_data['goods_id']=$post['goods_id'];//指定商品时指定商品的id
                    }
                    break;
                default:
                    $save_data['agent_way']=1;
            }
            SystemConfig::edit(['value' => json_encode($save_data)],'agent_config','menu_name');
            return JsonService::successful('修改成功');
        }
    }

    /**
     * 佣金配置页面
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function yong_jin()
    {
        $agent_yongjin_config=SystemConfig::getValue('agent_yongjin_config');
        $config['agent_yongjin_config']=$agent_yongjin_config;
        $config['agent_yongjin_config_2']=100-floatval($agent_yongjin_config);
        $this->assign('agent_yongjin_config',$config);
        return $this->fetch();
    }

    /**
     * 佣金配置保存
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function saveYongJin()
    {
        $request = Request::instance();
        if($request->isPost()){
            $post = $request->post();
            $agent_yongjin_config = $post['agent_yongjin_config'];
            if($agent_yongjin_config<=0||$agent_yongjin_config>100){
                return $this->failedNotice('一级返佣范围是0%（不含）~100%（含）');
            }
            SystemConfig::edit(['value' => json_encode($agent_yongjin_config)],'agent_yongjin_config','menu_name');
            return JsonService::successful('修改成功');
        }
    }

    /**
     * 提现配置页面
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function ti_xian()
    {
        $agent_tixian_config=SystemConfig::getMore(['agent_tixian_config_max','agent_tixian_config_min','agent_tixian_config_day','agent_tixian_config_rules']);
        $this->assign('agent_tixian_config',$agent_tixian_config);
        return $this->fetch();
    }

    /**
     * 提现配置保存
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function saveTiXian()
    {
        $request = Request::instance();
        if($request->isPost()){
            $post = $request->post();
            foreach ($post as $k=>&$v){
                if($k=='agent_tixian_config_rules'){
                    $v=osx_input('post.agent_tixian_config_rules','','html');
                }
                if($k=='agent_tixian_config_day'){
                    if($v<=0||$v>28){
                        $v=25;
                    }
                }
                if(is_array($v)){
                    $res = SystemConfig::where('menu_name',$k)->column('type,upload_type');
                    foreach ($res as $kk=>$vv){
                        if($kk == 'upload'){
                            if($vv == 1 || $vv == 3){
                                $post[$k] = $v[0];
                            }
                        }
                    }
                }
            }
            unset($k,$v);
            $result=true;

            $end_day=intval(SystemConfig::getValue('agent_tixian_config_day'));//获得历史配置
            if($end_day<=0||$end_day>28){
                $end_day=25;
            }

            foreach ($post as $k=>$v){
                $result=$result && false!== SystemConfig::edit(['value' => json_encode($v)],$k,'menu_name');//修改配置
            }
            if($result&&$end_day!==intval($post['agent_tixian_config_day'])){
                $this_month_end_day_time=strtotime(time_format(time(),'Y-m-'.$end_day.' 00:00:00'));
                db('sell_order')->whereTime('give_back_time','month')->where('back_status',2)->setField('give_back_time',$this_month_end_day_time);
            }
            return JsonService::successful('修改成功');
        }
    }

    /**
     * 申请协议配置页面
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function xie_yi()
    {
        $agent_xieyi_config=SystemConfig::getValue('agent_xieyi_config');
        $this->assign('agent_xieyi_config',$agent_xieyi_config);
        return $this->fetch();
    }

    /**
     * 申请协议配置保存
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function saveXieyi()
    {
        $request = Request::instance();
        if($request->isPost()){
            $agent_xieyi_config = osx_input('post.agent_xieyi_config','','html');
            SystemConfig::edit(['value' => json_encode($agent_xieyi_config)],'agent_xieyi_config','menu_name');
            return JsonService::successful('修改成功');
        }
    }

    /**
     * 收益说明配置页面
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function income_statement()
    {
        $agent_income_config=SystemConfig::getValue('agent_income_config');
        $this->assign('agent_income_config',$agent_income_config);
        return $this->fetch();
    }

    /**
     * 收益说明配置保存
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function saveIncomeStatement()
    {
        $request = Request::instance();
        if($request->isPost()){
            $agent_income_config =  osx_input('post.agent_income_config','','html');
            SystemConfig::edit(['value' => json_encode($agent_income_config)],'agent_income_config','menu_name');
            return JsonService::successful('修改成功');
        }
    }

    /**
     * 分享海报配置页面-分享商品页面中的企业logo和企业名称
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function share_config()
    {
        $agent_share_config=SystemConfig::getMore('agent_share_config_logo,agent_share_config_title');
        $this->assign('agent_share_config',$agent_share_config);
        return $this->fetch();
    }

    /**
     * 分享海报配置保存
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function saveShareConfig()
    {
        $request = Request::instance();
        if($request->isPost()){
            $post = $request->post();
            $agent_share_config_logo = $post['agent_share_config_logo'];
            $agent_share_config_title = $post['agent_share_config_title'];
            SystemConfig::edit(['value' => json_encode($agent_share_config_logo)],'agent_share_config_logo','menu_name');
            SystemConfig::edit(['value' => json_encode($agent_share_config_title)],'agent_share_config_title','menu_name');
            return JsonService::successful('修改成功');
        }
    }
}