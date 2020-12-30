<?php
namespace app\admin\model\store;


use basic\ModelBasic;
use traits\ModelTrait;
use think\Db;

class StoreCouponUser extends ModelBasic
{
    use ModelTrait;

    public static function tidyCouponList($couponList)
    {
        $time = time();
        foreach ($couponList as &$coupon){
            $coupon['_add_time'] = date('Y/m/d',$coupon['add_time']);
            $coupon['_end_time'] = date('Y/m/d',$coupon['end_time']);
            $coupon['use_min_price'] = floatval($coupon['use_min_price']);
            $coupon['coupon_price'] = floatval($coupon['coupon_price']);
            if($coupon['is_fail']){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已失效';
            }else if ($coupon['status'] == 1){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已使用';
            }else if ($coupon['status'] == 2){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已过期';
            }else if($coupon['add_time'] > $time || $coupon['end_time'] < $time){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已过期';
            }else{
                if($coupon['add_time']+ 3600*24 > $time){
                    $coupon['_type'] = 2;
                    $coupon['_msg'] = '可使用';
                }else{
                    $coupon['_type'] = 1;
                    $coupon['_msg'] = '可使用';
                }
            }
            $coupon['integral']= Db::name('store_coupon')->where(['id'=>$coupon['cid']])->value('integral');
        }
        return $couponList;
    }
    //获取个人优惠券列表
    public static function getOneCouponsList($where){
        $list=self::where(['uid'=>$where['uid']])->page((int)$where['page'],(int)$where['limit'])->select();
        return self::tidyCouponList($list);
    }
    //获取优惠劵头部信息
    public static function getCouponBadgeList($where){
        return [
            [
                'name'=>'总发放优惠券',
                'field'=>'张',
                'count'=>self::getModelTime($where, Db::name('store_coupon_issue'))->where('status',1)->sum('total_count'),
                'background_color'=>'layui-bg-blue',
                'col'=>6,
            ],
            [
                'name'=>'总使用优惠券',
                'field'=>'张',
                'count'=>self::getModelTime($where,new self())->where('status',1)->count(),
                'background_color'=>'layui-bg-blue',
                'col'=>6,
            ]
        ];
    }
    //获取优惠劵图表
    public static function getConponCurve($where,$limit=20){
        //优惠劵发放记录
        $list=self::getModelTime($where, Db::name('store_coupon_issue')
            ->where('status',1)
            ->field(['FROM_UNIXTIME(add_time,"%Y-%m-%d") as _add_time','sum(total_count) as total_count'])->group('_add_time')->order('_add_time asc'))->select();
        $date=[];
        $seriesdata=[];
        $zoom='';
        foreach ($list as $item){
            $date[]=$item['_add_time'];
            $seriesdata[]=$item['total_count'];
        }
        unset($item);
        if(count($date)>$limit){
            $zoom=$date[$limit-5];
        }
        //优惠劵使用记录
        $componList=self::getModelTime($where,self::where('status',1)->field(['FROM_UNIXTIME(add_time,"%Y-%m-%d") as _add_time','sum(coupon_price) as coupon_price'])
            ->group('_add_time')->order('_add_time asc'))->select();
        count($componList) && $componList=$componList->toArray();
        $compon_date=[];
        $compon_data=[];
        $compon_zoom='';
        foreach($componList as $item){
            $compon_date[]=$item['_add_time'];
            $compon_data[]=$item['coupon_price'];
        }
        if(count($compon_date)>$limit){
            $compon_zoom=$compon_date[$limit-5];
        }
        return compact('date','seriesdata','zoom','compon_date','compon_data','compon_zoom');
    }

    //推荐产品优惠劵
    public static function getCouponColumnList($where){
        $model = db('store_order');
//        $model = $model->alias('u');
//        $model = $model->join('__STORE_PRODUCT__ p','p.id=u.cid');
//        $model = $model->where('p.is_type',1);
        $model = $model->where(['status'=>['gt',0],'is_zg'=>1,'coupon_id'=>['gt',0]]);
//        $model = $model->where('is_zg',1);
        $count = $model->count();
        return [
            [
                'name'=>'总使用优惠券',
                'field'=>'张',
                'count'=>$count,
                'background_color'=>'layui-bg-blue',
                'col'=>6,
            ]
        ];
    }
    //获取优惠劵图表
    public static function getConponColumn($where,$limit=20){
        //优惠劵使用记录
        $model = db('store_order');
        $model = $model->alias('u');
//        if (!empty($where['data'])) {
//            $model = self::getModelTime($where,new self,$prefix='pay_time');
//        }
        // 使用日期表达式
        switch (strtolower($where['data'])) {
            case 'today':
            case 'd':
                $range = ['today', 'tomorrow'];
                break;
            case 'week':
            case 'w':
                $range = ['this week 00:00:00', 'next week 00:00:00'];
                break;
            case 'month':
            case 'm':
                $range = ['first Day of this month 00:00:00', 'first Day of next month 00:00:00'];
                break;
            case 'year':
            case 'y':
                $range = ['this year 1/1', 'next year 1/1'];
                break;
            case 'yesterday':
                $range = ['yesterday', 'today'];
                break;
            case 'last week':
                $range = ['last week 00:00:00', 'this week 00:00:00'];
                break;
            case 'last month':
                $range = ['first Day of last month 00:00:00', 'first Day of this month 00:00:00'];
                break;
            case 'last year':
                $range = ['last year 1/1', 'this year 1/1'];
                break;
            default:
                $range = $where['data'];
        }
        $op = is_array($range) ? 'between' : '>';
//        $model = $model->join('__STORE_PRODUCT__ p','p.id=u.cid');
        $model = $model->where(['status'=>['gt',0],'is_zg'=>1])->where('pay_time', strtolower($op) . ' time', $range);

        $model = $model->field(['FROM_UNIXTIME(u.pay_time,"%Y-%m-%d %H:%i:%s") as _add_time','sum(u.coupon_price) as coupon_price']);
        $componList = $model->group('_add_time')->order('_add_time asc')->select();
//        count($componList) && $componList=$componList->toArray();
        $compon_date=[];
        $compon_data=[];
        $compon_zoom='';
        foreach($componList as $item){
            $compon_date[]=$item['_add_time'];
            $compon_data[]=$item['coupon_price'];
        }
        if(count($compon_date)>$limit){
            $compon_zoom=$compon_date[$limit-5];
        }
        return compact('compon_date','compon_data','compon_zoom');
    }

}