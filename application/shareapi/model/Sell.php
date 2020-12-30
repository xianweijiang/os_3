<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/19
 * Time: 13:12
 */

namespace app\shareapi\model;


use app\admin\model\system\SystemConfig;
use basic\ModelBasic;
use Carbon\Carbon;
use service\PHPExcelService;
use traits\ModelTrait;

class Sell extends ModelBasic
{
    use ModelTrait;

    /**
     * 分销商列表
     * @param $where
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function sellerList($where)
    {
        $model = self::_getModelObject($where)->field(['*']);
        if(!(isset($where['excel']) && $where['excel']==1)) {
            $model = $model->page((int)$where['page'], (int)$where['limit']);
        }
        $data = ($data = $model->order('create_time desc')->select()) && count($data) ? $data->toArray() : [];
        $user_list=array();
        if(count($data)){
            $uids=array_merge(array_column($data,'uid'),array_column($data,'father1'));
            $user_list=db('user')->where('uid','in',$uids)->field('uid,nickname,avatar,phone')->select();
            $user_list=array_combine(array_column($user_list,'uid'),$user_list);
        }
        $user_list[0]=['uid'=>'','nickname'=>'','avatar'=>'','phone'=>''];
        //供应商列表
        foreach ($data as &$item){
            $item['user_info']=isset($user_list[$item['uid']])?$user_list[$item['uid']]:['uid'=>'','nickname'=>'','avatar'=>'','phone'=>''];
            $item['father1_info']=$user_list[$item['father1']];
            $item['create_time']=time_format($item['create_time']);
            $item['audit_time']=time_format($item['audit_time']);
        }
        unset($item);
        //是导出excel
        if(isset($where['excel']) && $where['excel']==1){
            self::_saveExcel($data);
            exit;
        }
        $count = self::_getModelObject($where)->count();
        return compact('count', 'data');
    }

    /*
     * 保存并下载excel
     * $list array
     * return
     */
    public static function _saveExcel($list){
        $export = [];
        foreach ($list as $item){
            if(!isset($item['father1_info'])||$item['father1_info']==''){
                $item['father1_info']=['uid'=>'','nickname'=>''];
            }
            $export[] = [
                $item['uid'],$item['user_info']['nickname'],$item['user_info']['phone'],$item['child1_num'],$item['child2_num'],
                $item['order_num'],$item['order_money'],$item['total_income'],$item['out_income'],$item['out_num'],$item['has_income'],
                [$item['father1_info']['uid'],$item['father1_info']['nickname']],
                [$item['status']==2?'未审核':($item['status']==1?'已通过':'已驳回')],$item['create_time'],$item['audit_time']

            ];
        }
        PHPExcelService::setExcelHeader(['UID','用户昵称','用户电话','一级推广用户量','二级推广用户量',
            '订单数','推广订单金额','佣金金额','已提现金额','提现次数','未提现金额',
            '上级推广人',
            '审核状态','申请时间','审核时间'])
            ->setExcelTile('分销商列表导出','分销商'.time(),' 生成时间：'.date('Y-m-d H:i:s',time()))
            ->setExcelContent($export)
            ->ExcelSave();
    }

    /**
     * 获取连表Model
     * @param $where
     * @return object
     */
    private static function _getModelObject($where = [])
    {
        $model = new self();
        if (!empty($where)) {
            // data 日期
            $model->where(function($query) use($where){
                switch ($where['select_date']) {
                    case 'yesterday':
                    case 'today':
                    case 'week':
                    case 'month':
                    case 'year':
                        $query->whereTime('create_time', $where['select_date']);
                        break;
                    case 'quarter':
                        $start = strtotime(Carbon::now()->startOfQuarter());
                        $end   = strtotime(Carbon::now()->endOfQuarter());
                        $query->whereTime('create_time', 'between', [$start, $end]);
                        break;
                    case '':
                        ;
                        break;
                    default:
                        $between = explode(' - ', $where['select_date']);
                        $query->whereTime('create_time', 'between', [$between[0], $between[1]]);
                        break;
                }
            });

            if(isset($where['status']) && $where['status']!=10 && $where['status']!=""){
                $model = $model->where('status',$where['status']);
            }
            if(isset($where['key_word']) && $where['key_word']!=''){
                $uids = db('user')->where('nickname|phone','LIKE',"%{$where['key_word']}%")->column('uid');
                if(intval($where['key_word'])>0){
                    $where['key_word']=intval($where['key_word']);
                    if(count($uids)){
                        $uids[]=$where['key_word'];
                        $model->where('uid', 'in', $uids);
                    }else{
                        $model->where('uid', $where['key_word']);
                    }
                }else{
                    if(count($uids)){
                        $model->where('uid', 'in', $uids);
                    }
                }
            }
        }
        return $model;
    }

    /**
     * 购买商品是否成为分销商
     * @param $order
     * @return bool|mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function buyProductBeSeller($order)
    {
        $agent_config = SystemConfig::getValue('agent_config');
        if($agent_config['agent_way']!=3){
            return true;//不是通过购买商品成为分销商
        }
        $uid=$order['uid'];
        $seller_info=Sell::get(['uid'=>$uid]);
        if($seller_info&&in_array($seller_info['status'],[0,1])) {
            return true;//已经是分销商或者已禁用状态
        }
        $be_seller=false;
        if($agent_config['goods_type']=='all_goods'){
            $be_seller=true;
        }else{
            $need_buy_goods_id=intval($agent_config['goods_id']);

            $product_ids=db('store_cart')->where('id','in',$order['cart_id'])->column('product_id');
            if(in_array($need_buy_goods_id,$product_ids)){
                $be_seller=true;
            }
        }
        if(!$be_seller){
            return true;//不符合成为分销商的条件
        }

        if($seller_info){
            if(in_array($seller_info['status'],[2,3])){
                $model=Sell::update(['status'=>1,'audit_time'=>time(),'fail_reason'=>$seller_info['fail_reason'].'-->购买商品成为分销商'],['uid'=>$uid]);
            }else{
                return true;//已经是分销商，不用调整
            }
        }else{
            $invite_level=db('invite_level')->where('uid',$uid)->find();
            $model=Sell::set([
                'uid'=>$uid,
                'child1_num'=>0,
                'child2_num'=>0,
                'order_num'=>0,
                'order_money'=>0,
                'total_income'=>0,
                'out_income'=>0,
                'out_num'=>0,
                'has_income'=>0,
                'father1'=>$invite_level?$invite_level['father1']:0,
                'father2'=>$invite_level?$invite_level['father2']:0,
                'status'=>1,
                'create_time'=>time(),
                'audit_time'=>time(),
                'fail_reason'=>'购买商品成为分销商',
            ],true);
        }
        $tag='IS_SELLER_'.$uid;
        cache($tag,null);
        return $model->result;
    }
}