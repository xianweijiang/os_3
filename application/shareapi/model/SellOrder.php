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


use basic\ModelBasic;
use service\PHPExcelService;
use traits\ModelTrait;

class SellOrder extends ModelBasic
{
    use ModelTrait;

    /**
     * 分页获取分销订单数据
     * @param $map
     * @param int $page
     * @param int $row
     * @param string $order
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function getListPage($map,$page=1,$row=10,$order='create_time desc')
    {
        $order_list=self::where($map)->page($page,$row)->order($order)->select()->toArray();
        foreach ($order_list as &$val){
            if($val['back_status']==2){
                switch ($val['order_status']){
                    case 4:
                        $val['status_show']="待付款";
                        break;
                    case 0:
                        $val['status_show']="待发货";
                        break;
                    case 1:
                        $val['status_show']="待收货";
                        break;
                    case 2:
                        $val['status_show']="已收货,待结算";
                        break;
                    case -1:
                        $val['status_show']="退款中";
                        break;
                    default:
                        $val['status_show']="未结算";
                }
            }else{
                $val['status_show']=$val['back_status']==1?"已结算":"已失效";
            }
            $val['create_time']=time_format($val['create_time']);
            $val['create_time_show']=time_to_show($val['create_time']);
            $val['end_time_show']=$val['end_time']==0?"未结算":time_to_show($val['end_time']);
            $val['goods_info_list']=json_decode($val['goods_info'],true);
            $goods_ids=array_column($val['goods_info_list'],'product_id');
            if(count($goods_ids)){
                $goods_detail=db('store_product')->where('id','in',$goods_ids)->field('id,store_name,image')->select();
                $goods_detail=array_combine(array_column($goods_detail,'id'),$goods_detail);
                foreach ($val['goods_info_list'] as &$goods){
                    $goods['store_name']=$goods_detail[$goods['product_id']]['store_name'];
                    if($goods['sku']!=''){
                        $goods['image']=db('store_product_attr_value')->where('suk',$goods['sku'])->where('product_id',$goods['product_id'])->value('image');
                    }
                    if(!isset($goods['image'])||$goods['image']==''){
                        $goods['image']=$goods_detail[$goods['product_id']]['image'];
                    }
                    $goods['image_150']=thumb_path($goods['image'],150,150);
                    $goods['image_350']=thumb_path($goods['image'],350,350);
                    $goods['image_750']=thumb_path($goods['image'],750,750);
                }
                unset($goods);
            }
        }
        unset($val);
        $count=self::where($map)->count();
        return [$order_list,$count];
    }


    /**
     * 后台管理分销订单，全部
     * @param $where
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function getListPageAdmin($where)
    {
        $model = self::_getModelObject($where)->field(['*']);
        if(!(isset($where['excel']) && $where['excel']==1)) {
            $model = $model->page((int)$where['page'], (int)$where['limit']);
        }
        $orderList = ($orderList = $model->order('create_time desc')->select()) && count($orderList) ? $orderList->toArray() : [];
        //上级信息获取 start
        $order_uids=array_column($orderList,'uid');
        $father1_uids=array_column($orderList,'father1');
        $father2_uids=array_column($orderList,'father2');
        if(count($father2_uids)){
            $uids=array_merge($order_uids,$father1_uids,$father2_uids);
        }else{
            $uids=array_merge($order_uids,$father1_uids);
        }
        $user_info=db('user')->where('uid','in',$uids)->field('nickname,avatar,uid,phone')->select();
        $user_info=array_combine(array_column($user_info,'uid'),$user_info);
        //上级信息获取 end
        foreach ($orderList as &$val){
            switch ($val['order_status']){
                case 4:
                    $val['order_status_show']="未付款";
                    break;
                case 0:
                    $val['order_status_show']="待发货";
                    break;
                case 1:
                    $val['order_status_show']="待收货";
                    break;
                case 2:
                    $val['order_status_show']="已收货,待评价";
                    break;
                case 3:
                    $val['order_status_show']="订单完成,已评价";
                    break;
                case -1:
                    $val['order_status_show']="退款中";
                    break;
                default:
                    $val['order_status_show']="未知状态";
            }
            $val['back_status_show']=$val['back_status']==2?"未结算":($val['back_status']==1?"已结算":"已失效");

            $val['create_time_show']=time_format($val['create_time']);
            $val['give_back_time_show']=$val['give_back_time']==0?"":time_format($val['give_back_time'],'Y-m-d');
            $val['end_time_show']=$val['end_time']==0?"未结算":time_format($val['end_time']);

            //订单商品信息start
            $val['goods_info_list']=json_decode($val['goods_info'],true);
            $goods_ids=array_column($val['goods_info_list'],'product_id');
            if(count($goods_ids)){
                $goods_detail=db('store_product')->where('id','in',$goods_ids)->field('id,store_name,image')->select();
                $goods_detail=array_combine(array_column($goods_detail,'id'),$goods_detail);
                foreach ($val['goods_info_list'] as &$goods){
                    $goods['store_name']=$goods_detail[$goods['product_id']]['store_name'];
                    if($goods['sku']!=''){
                        $goods['image']=db('store_product_attr_value')->where('suk',$goods['sku'])->where('product_id',$goods['product_id'])->value('image');
                    }
                    if(!isset($goods['image'])||$goods['image']==''){
                        $goods['image']=$goods_detail[$goods['product_id']]['image'];
                    }
                }
                unset($goods);
            }
            //订单商品信息end

            //返利方信息
            $val['user_info']=$user_info[$val['uid']];
            $val['father1']&&$val['father1_info']=$user_info[$val['father1']];
            $val['father2']&&$val['father2_info']=$user_info[$val['father2']];
        }
        unset($val);
        //是导出excel
        if(isset($where['excel']) && $where['excel']==1){
            self::_saveExcel($orderList);
            exit;
        }
        $count = self::_getModelObject($where)->count();
        $data=$orderList;
        return compact('count', 'data');
    }

    /* 后台专栏分销订单
     * */
	public static function getKnowledgeOrderSellListPageAdmin($where)
	{
		$model = self::_getknowledgeModelObject($where)->field(['*']);
		if(!(isset($where['excel']) && $where['excel']==1)) {
			$model = $model->page((int)$where['page'], (int)$where['limit']);
		}
		$orderList = ($orderList = $model->order('create_time desc')->select()) && count($orderList) ? $orderList->toArray() : [];

		//上级信息获取 start
		$order_uids=array_column($orderList,'uid');
		$father1_uids=array_column($orderList,'father1');
		$father2_uids=array_column($orderList,'father2');
		if(count($father2_uids)){
			$uids=array_merge($order_uids,$father1_uids,$father2_uids);
		}else{
			$uids=array_merge($order_uids,$father1_uids);
		}
		$user_info=db('user')->where('uid','in',$uids)->field('nickname,avatar,uid,phone')->select();
		$user_info=array_combine(array_column($user_info,'uid'),$user_info);
		//上级信息获取 end
		foreach ($orderList as &$val){
			switch ($val['order_status']){
				case 4:
					$val['order_status_show']="未付款";
					break;
				case 0:
					$val['order_status_show']="待发货";
					break;
				case 1:
					$val['order_status_show']="待收货";
					break;
				case 2:
					$val['order_status_show']="已收货,待评价";
					break;
				case 3:
					$val['order_status_show']="订单完成,已评价";
					break;
				case -1:
					$val['order_status_show']="退款中";
					break;
				default:
					$val['order_status_show']="未知状态";
			}
			$val['back_status_show']=$val['back_status']==2?"未结算":($val['back_status']==1?"已结算":"已失效");

			$val['create_time_show']=time_format($val['create_time']);
			$val['give_back_time_show']=$val['give_back_time']==0?"":time_format($val['give_back_time'],'Y-m-d');
			$val['end_time_show']=$val['end_time']==0?"未结算":time_format($val['end_time']);

			//订单商品信息start
			$val['goods_info_list']=json_decode($val['goods_info'],true);
			$goods_ids=array_column($val['goods_info_list'],'product_id');
			if(count($goods_ids)){
				$goods_detail=db('store_product')->where('id','in',$goods_ids)->field('id,store_name,image')->select();
				$goods_detail=array_combine(array_column($goods_detail,'id'),$goods_detail);
				foreach ($val['goods_info_list'] as &$goods){
					$goods['store_name']=$goods_detail[$goods['product_id']]['store_name'];
					if($goods['sku']!=''){
						$goods['image']=db('store_product_attr_value')->where('suk',$goods['sku'])->where('product_id',$goods['product_id'])->value('image');
					}
					if(!isset($goods['image'])||$goods['image']==''){
						$goods['image']=$goods_detail[$goods['product_id']]['image'];
					}
				}
				unset($goods);
			}
			//订单商品信息end

			//返利方信息
			$val['user_info']=$user_info[$val['uid']];
			$val['father1']&&$val['father1_info']=$user_info[$val['father1']];
			$val['father2']&&$val['father2_info']=$user_info[$val['father2']];
		}
		unset($val);
		//是导出excel
		if(isset($where['excel']) && $where['excel']==1){
			self::_saveExcel($orderList);
			exit;
		}
		$count = self::_getknowledgeModelObject($where)->count();
		$data=$orderList;
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
            if(!isset($item['father1_info'])){
                $item['father1_info']=['uid'=>'','nickname'=>''];
            }
            if(!isset($item['father2_info'])){
                $item['father2_info']=['uid'=>'','nickname'=>''];
            }
            $show_content='';
            foreach ($item['goods_info_list'] as $goods){
                if($goods['sku']==''){
                    $sku='';
                }else{
                    $sku='('.$goods['sku'].')';
                }
                $show_content.='['.$goods['product_id'].']'.$goods['store_name'].$sku.'x'.$goods['cart_num'].'
';
            }
            $item['show_content']=$show_content;
            $export[] = [
                $item['id'],
                [$item['user_info']['uid'],$item['user_info']['nickname'],$item['user_info']['phone']],
                [$item['father1_info']['uid'],$item['father1_info']['nickname'],$item['father1_info']['phone']],
                $item['show_content'],$item['goods_title'],
                $item['pay_money'],$item['order_status_show'],$item['create_time_show'],$item['back_status_show'],
                $item['father1_info']['uid'],$item['father1_info']['nickname'],$item['father1_back'],
                $item['father2_info']['uid'],$item['father2_info']['nickname'],$item['father2_back'],
            ];
        }
        PHPExcelService::setExcelHeader(['编号',
            '用户信息',
            '推荐人信息',
            '商品信息','商品标题',
            '实付金额','订单状态','下单时间','分销状态',
            '一级分销商UID','一级分销商昵称','一级分销商返利金额',
            '二级分销商UID','二级分销商昵称','二级分销商返利金额',])
            ->setExcelTile('分销订单列表导出','分销订单'.time(),' 生成时间：'.date('Y-m-d H:i:s',time()))
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
            if(isset($where['order_status']) && $where['order_status']!=''){
                $model = $model->where('order_status',$where['order_status']);
            }
            if(isset($where['back_status']) && $where['back_status']!=''){
                $model = $model->where('back_status',$where['back_status']);
            }

            if(isset($where['keywords']) && $where['keywords']!=''){
                switch ($where['keywords_type']){
                    case 'order_id':
                        $model = $model->where('order_id','LIKE',"%{$where['keywords']}%");
                        break;
                    case 'user':
                        $uids = db('user')->where('nickname|phone','LIKE',"%{$where['keywords']}%")->column('uid');
                        if(intval($where['keywords'])>0){
                            $where['keywords']=intval($where['keywords']);
                            if(count($uids)){
                                $uids[]=$where['keywords'];
                                $model->where('uid', 'in', $uids);
                            }else{
                                $model->where('uid', $where['keywords']);
                            }
                        }else{
                            if(count($uids)){
                                $model->where('uid', 'in', $uids);
                            }
                        }
                        break;
                    case 'product':
                        $model = $model->where('goods_title','LIKE',"%{$where['keywords']}%");
                        break;
                    default:
                }
            }
        }
        return $model;
    }

    /*获得知识商城分销连表Model
     * */
		private static function _getknowledgeModelObject($where = [])
		{
			$model = new self();

			if (!empty($where)) {
				//获得智果分销订单
				$getStoreOrders = db('store_order') -> where('is_zg',1) -> select();
				//$model = $model -> where('order_id', 'in', $getStoreOrders['order_id']);

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
				if(isset($where['order_status']) && $where['order_status']!=''){
					$model = $model->where('order_status',$where['order_status'])
													->where('order_id', 'in', $getStoreOrders['order_id']);
				}
				if(isset($where['back_status']) && $where['back_status']!=''){
					$model = $model->where('back_status',$where['back_status'])
													->where('order_id', 'in', $getStoreOrders['order_id']);
				}

				if(isset($where['keywords']) && $where['keywords']!=''){
					switch ($where['keywords_type']){
						case 'order_id':
							$model = $model->where('order_id','LIKE',"%{$where['keywords']}%")
							   							->where('order_id', 'in', $getStoreOrders['order_id']);
							break;
						case 'user':
							$uids = db('user')->where('nickname|phone','LIKE',"%{$where['keywords']}%")->column('uid');
							if(intval($where['keywords'])>0){
								$where['keywords']=intval($where['keywords']);
								if(count($uids)){
									$uids[]=$where['keywords'];
									$model->where('uid', 'in', $uids)
													->where('order_id', 'in', $getStoreOrders['order_id']);
								}else{
									$model->where('uid', $where['keywords'])
												->where('order_id', 'in', $getStoreOrders['order_id']);
								}
							}else{
								if(count($uids)){
									$model->where('uid', 'in', $uids)
												->where('order_id', 'in', $getStoreOrders['order_id']);
								}
							}
							break;
						case 'product':
							$model = $model->where('goods_title','LIKE',"%{$where['keywords']}%")
															->where('order_id', 'in', $getStoreOrders['order_id']);
							break;
						default:
					}
				}
			}
			return $model;
		}

    /**
     * 后台分销商管理中推广订单
     * @param $where
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function getSellOrderListPageAdmin($where)
    {
        $model = self::_getSellOrderModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $orderList = ($orderList = $model->order('create_time desc')->select()) && count($orderList) ? $orderList->toArray() : [];
        //用户信息获取 start
        $order_uids=array_column($orderList,'uid');
        $user_info=db('user')->where('uid','in',$order_uids)->field('nickname,avatar,uid,phone')->select();
        $user_info=array_combine(array_column($user_info,'uid'),$user_info);
        //用户信息获取 end
        foreach ($orderList as &$val){
            switch ($val['order_status']){
                case 4:
                    $val['order_status_show']="未付款";
                    break;
                case 0:
                    $val['order_status_show']="待发货";
                    break;
                case 1:
                    $val['order_status_show']="待收货";
                    break;
                case 2:
                    $val['order_status_show']="已收货,待评价";
                    break;
                case 3:
                    $val['order_status_show']="订单完成,已评价";
                    break;
                case -1:
                    $val['order_status_show']="退款中";
                    break;
                default:
                    $val['order_status_show']="未知状态";
            }
            $val['back_status_show']=$val['back_status']==2?"未结算":($val['back_status']==1?"已结算":"已失效");

            $val['create_time_show']=time_to_show($val['create_time']);
            $val['give_back_time_show']=$val['give_back_time']==0?"":time_format($val['give_back_time'],'Y-m-d');
            $val['end_time_show']=$val['end_time']==0?"未结算":time_to_show($val['end_time']);

            //订单商品信息start
            $val['goods_info_list']=json_decode($val['goods_info'],true);
            $goods_ids=array_column($val['goods_info_list'],'product_id');
            if(count($goods_ids)){
                $goods_detail=db('store_product')->where('id','in',$goods_ids)->field('id,store_name,image')->select();
                $goods_detail=array_combine(array_column($goods_detail,'id'),$goods_detail);
                foreach ($val['goods_info_list'] as &$goods){
                    $goods['store_name']=$goods_detail[$goods['product_id']]['store_name'];
                    if($goods['sku']!=''){
                        $goods['image']=db('store_product_attr_value')->where('suk',$goods['sku'])->where('product_id',$goods['product_id'])->value('image');
                    }
                    if(!isset($goods['image'])||$goods['image']==''){
                        $goods['image']=$goods_detail[$goods['product_id']]['image'];
                    }
                }
                unset($goods);
            }
            //订单商品信息end

            //返利方信息
            $val['user_info']=$user_info[$val['uid']];

            $val['back_to_seller_money']=(intval($where['seller_uid'])==$val['father1'])?$val['father1_back']:$val['father2_back'];
        }
        unset($val);

        $count = self::_getSellOrderModelObject($where)->count();
        $data=$orderList;
        return compact('count', 'data');
    }

    /**
     * 获取连表Model
     * @param $where
     * @return object
     */
    private static function _getSellOrderModelObject($where = [])
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


            if(isset($where['keywords']) && $where['keywords']!=''){
                $uids = db('user')->where('nickname|phone','LIKE',"%{$where['keywords']}%")->column('uid');
                if(count($uids)){
                    $uids[]=$where['keywords'];
                    $model->where('uid', 'in', $uids);
                }else{
                    $model->where('uid', $where['keywords']);
                }
                $model->where('order_id','LIKE',"%{$where['keywords']}%");
            }

            $where['seller_uid']=intval($where['seller_uid']);
            if($where['seller_uid']>0){
                switch ($where['type']){
                    case 'level1':
                        $model->where('father1',$where['seller_uid']);
                        break;
                    case 'level2':
                        $model->where('father2',$where['seller_uid']);
                        break;
                    case 'all':
                    default:
                        $model->where('father1|father2',$where['seller_uid']);
                        break;
                }
            }else{
                $model->where('father1',-1);//查询空数据
            }
        }
        return $model;
    }

    /**
     * 单个给返利-供计划任务调用
     * @param $sell_order
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function giveOrderBack($sell_order)
    {
        if($sell_order['back_status']!==2){
            return false;
        }
        self::startTrans();
        $res=self::where('id',$sell_order['id'])->where('back_status',2)->whereIn('order_status',[2,3])->where('give_back_time','elt',time())->update(['end_time'=>time(),'back_status'=>1]);
        $res=$res&&Sell::where('uid',$sell_order['father1'])->setInc('total_income',$sell_order['father1_back']);
        $res=$res&&Sell::where('uid',$sell_order['father1'])->setInc('has_income',$sell_order['father1_back']);
        if($sell_order['father2']!=0){
            $res=$res&&Sell::where('uid',$sell_order['father2'])->setInc('total_income',$sell_order['father2_back']);
            $res=$res&&Sell::where('uid',$sell_order['father2'])->setInc('has_income',$sell_order['father2_back']);
        }
        self::checkTrans($res);
        if($res){
            return true;
        }else{
            return false;
        }
    }
}