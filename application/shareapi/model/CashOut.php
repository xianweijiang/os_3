<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/25
 * Time: 15:33
 */

namespace app\shareapi\model;


use basic\ModelBasic;
use Carbon\Carbon;
use service\PHPExcelService;
use traits\ModelTrait;

class CashOut extends ModelBasic
{
    use ModelTrait;

    /**
     * 发起提现请求
     * @param $data
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function doCashOut($data)
    {
        self::startTrans();

        //提现记录添加
        $res=self::set($data);
        if(!$res){
            return self::setErrorInfo('添加提现记录失败',true);
        }

        //分销商持有收益、提现收益、提现次数变更
        $res=$res && Sell::where('uid',$data['uid'])->where('has_income','egt',$data['out_num'])->setDec('has_income',$data['out_num']);
        $res=$res && Sell::where('uid',$data['uid'])->setInc('out_num');
        $res=$res && Sell::where('uid',$data['uid'])->setInc('out_income',$data['out_num']);

        if(!$res){
            return self::setErrorInfo('提现失败',true);
        }
        self::commitTrans();
        return true;
    }

    /**
     * 获取提现记录列表
     * @param $map
     * @param int $limit
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function getList($map,$limit=50)
    {
        $list=self::where($map)->order('create_time desc')->limit(0,$limit)->select()->toArray();
        foreach ($list as &$val){
            $val['create_time_show']=time_format($val['create_time']);
            $val['finish_time_show']=time_format($val['finish_time']);
            $val['id']=$val['order_num'];
        }
        unset($val);
        return $list;
    }

    public static function getNewOrderId()
    {
        $count = (int) self::whereTime('create_time','today')->count();
        return 'out'.date('YmdHis',time()).(10000+$count+1);
    }

    /**
     * 后台提现列表、管理
     * @param $where
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function outList($where)
    {
        $model = self::_getModelObject($where)->field(['*']);
        if(!(isset($where['excel']) && $where['excel']==1)) {
            $model = $model->page((int)$where['page'], (int)$where['limit']);
        }
        $data = ($data = $model->order('create_time desc')->select()) && count($data) ? $data->toArray() : [];
        $user_list=array();
        if(count($data)){
            $uids=array_column($data,'uid');
            $user_list=db('user')->where('uid','in',$uids)->field('uid,nickname,avatar,phone')->select();
            $user_list=array_combine(array_column($user_list,'uid'),$user_list);
        }
        //提现记录列表
        foreach ($data as &$item){
            $item['create_time']=time_format($item['create_time']);
            $item['finish_time']=time_format($item['finish_time']);
            $item['user_info']=$user_list[$item['uid']];
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
            $export[] = [
                $item['id'],
                $item['order_num'],
                $item['user_info']['uid'],$item['user_info']['nickname'],$item['user_info']['phone'],
                $item['out_num'],$item['type'],[$item['account'],($item['type']=='weixin'?'<img src="'.$item['image'].'"/>':'')],$item['type']=='weixin'?$item['weixin_name']:$item['image'],
                $item['create_time'],$item['finish_time'],
                $item['status']==3?"已完成":($item['status']==2?"审核通过待打款":($item['status']==1?"发起提现请求":"已驳回")),$item['remark'],
            ];
        }
        PHPExcelService::setExcelHeader(['编号',
            '提现单号',
            '用户UID', '用户昵称', '用户手机号',
            '提现金额','提现方式','提现账号','姓名',
            '申请时间','结束时间',
            '状态','备注'])
            ->setExcelTile('提现记录列表导出','提现记录'.time(),' 生成时间：'.date('Y-m-d H:i:s',time()))
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

            if(isset($where['status']) && $where['status']!=-1){
                $model = $model->where('status',$where['status']);
            }
            if($where['seller_uid']>0){
                $model->where('uid', $where['seller_uid']);
                if(isset($where['keyword']) && $where['keyword']!=''){
                    $model->where('order_num' ,'LIKE',"%{$where['keyword']}%");
                }
            }else{
                if(isset($where['keyword']) && $where['keyword']!=''){
                    $uids = db('user')->where('nickname|phone','LIKE',"%{$where['keyword']}%")->column('uid');
                    if(count($uids)){
                        intval($where['keyword'])>0&&$uids[]=intval($where['keyword']);
                        $uids=implode(',',$uids);
                        $model->where('order_num LIKE "%'.$where['keyword'].'%" OR uid in('.$uids.')');
                    }else{
                        if(intval($where['keyword'])>0){
                            $model->where('order_num LIKE "%'.$where['keyword'].'%" OR uid ='.$where['keyword']);
                        }else{
                            $model->where('order_num LIKE "%'.$where['keyword'].'%"');
                        }
                    }
                }
            }

            if(isset($where['out_way'])&&$where['out_way']!='all'){
                $model->where('type', $where['out_way']);
            }
        }
        return $model;
    }

    /**
     * 提现申请驳回
     * @param $data
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function auditFail($data)
    {
        $cash_out_info=self::where(['id'=>$data['id'],'uid'=>$data['uid']])->find();
        if(!in_array($cash_out_info['status'],[1,2])){
            return self::setErrorInfo('非法操作');
        }

        self::startTrans();
        $res=self::update(['status'=>0,'fail_reason'=>text($data['reason']),'finish_time'=>time()],['id'=>$data['id'],'status'=>array('in',[1,2]),'uid'=>$data['uid']])->result;
        if(!$res){
            return self::setErrorInfo('提现驳回失败',true);
        }

        //分销商持有收益、提现收益变更
        $res=$res && Sell::where('uid',$data['uid'])->setInc('has_income',$cash_out_info['out_num']);
        $res=$res && Sell::where('uid',$data['uid'])->setDec('out_income',$cash_out_info['out_num']);

        if(!$res){
            return self::setErrorInfo('提现驳回失败',true);
        }
        self::commitTrans();
        return true;
    }
}