<?php

namespace app\admin\controller;

use app\admin\model\store\StoreProduct;
use app\admin\model\system\SystemConfig;
use app\admin\model\system\SystemMenus;
use app\admin\model\system\SystemRole;
use app\admin\model\order\StoreOrder as StoreOrderModel;//订单
use app\admin\model\user\UserExtract as UserExtractModel;//分销
use app\admin\model\user\User as UserModel;//用户
use app\admin\model\store\StoreProductReply as StoreProductReplyModel;//评论
use app\admin\model\store\StoreProduct as ProductModel;//产品
use app\commonapi\model\SystemCountLogToShow;
use FormBuilder\Json;
use service\UtilService;
use think\DB;
use think\Request;

/**
 * 首页控制器
 * Class Index
 * @package app\admin\controller
 *
 */
class Index extends AuthController
{
    public function index()
    {
        //获取当前登录后台的管理员信息
        $adminInfo = $this->adminInfo->toArray();
        $roles  = explode(',',$adminInfo['roles']);
        $site_logo = SystemConfig::getOneConfig('menu_name','site_logo')->toArray();
        $menuList=SystemMenus::menuList();
        $hide_admin_menu=$this->_getHideAdminMenu();
        $this->_changeMenuShow($menuList,$hide_admin_menu);
        $this->_removeNoChild($menuList);
        $this->assign([
            'menuList'=>$menuList,
            'site_logo'=>json_decode($site_logo['value'],true),
            'role_name'=>SystemRole::where('id',$roles[0])->field('role_name')->find()
        ]);

        return $this->fetch();
    }

    private function _changeMenuShow(&$menuList,$hide_admin_menu)
    {

        foreach ($menuList as $key=>&$val){
            if(in_array($val['id'],$hide_admin_menu)||in_array($val['menu_name'],$hide_admin_menu)){
                unset($menuList[$key]);
                continue;
            }
            if(isset($val['child'])&&count($val['child'])>0){
                $this->_changeMenuShow($val['child'],$hide_admin_menu);
            }
        }
        unset($key,$val);
        return true;
    }
    private function _removeNoChild(&$menuList)
    {
        foreach ($menuList as $key=>&$val){
            if(isset($val['child'])&&count($val['child'])>0) {
                $has_child = $this->_checkHasChild($val['child']);
            }else{
                $has_child=false;
            }
            if(!$has_child&&($val['url']=='javascript:void(0);')){
                unset($menuList[$key]);
                continue;
            }
            if(isset($val['child'])&&count($val['child'])>0){
                $this->_removeNoChild($val['child']);
            }
        }
        unset($val);
        return true;
    }
    private function _checkHasChild($menuList)
    {
        foreach ($menuList as $val){
            if(isset($val['child'])&&count($val['child'])>0){
                $has_child=$this->_checkHasChild($val);
                if($has_child){
                    return true;
                }
            }
            if($val['url']!='javascript:void(0);'){
                unset($val);
                return true;
            }
        }
        unset($val);
        return false;
    }

    //后台首页内容
    public function main()
    {
        /*首页第一行统计*/
        $now_month = strtotime(date('Y-m'));//本月
        $pre_month = strtotime(date('Y-m',strtotime('-1 month')));//上月
        $now_day = strtotime(date('Y-m-d'));//今日
        $pre_day = strtotime(date('Y-m-d',strtotime('-1 day')));//昨天时间戳
        $beforyester_day = strtotime(date('Y-m-d',strtotime('-2 day')));//前天时间戳
        //待发货数量
        $topData['orderDeliveryNum'] = StoreOrderModel::where('status',0)
            ->where('paid',1)
            ->where('refund_status',0)
            ->count();
        //退换货订单数
        $topData['orderRefundNum'] = StoreOrderModel::where('paid',1)
            ->where('refund_status','IN','1')
            ->count();
        //库存预警
        $replenishment_num = SystemConfig::getValue('store_stock') > 0 ? SystemConfig::getValue('store_stock') : 20;//库存预警界限
        $topData['stockProduct'] = StoreProduct::where('stock','<=',$replenishment_num)->where('is_show',1)->where('is_del',0)->count();
        //待处理提现
        $topData['treatedExtract'] = UserExtractModel::where('status',0)->count();


        //订单数->日
        $now_day_order_p = StoreOrderModel::where('paid',1)->where('pay_time','gt',$now_day)->count();
        $pre_day_order_p = StoreOrderModel::where('paid',1)->where('pay_time','gt',$pre_day)->where('pay_time','lt',$now_day)->count();
        $first_line['d_num'] = [
            'data' => $now_day_order_p ? $now_day_order_p : 0,
            'percent' => $pre_day_order_p==0?:floatval(round(abs($now_day_order_p - $pre_day_order_p)*100.0/$pre_day_order_p,4)),
            'is_plus' => $now_day_order_p - $pre_day_order_p > 0 ? 1 : ($now_day_order_p - $pre_day_order_p == 0 ? -1 : 0)
        ];

        //交易额->日
        $now_month_order_p = StoreOrderModel::where('paid',1)->where('pay_time','gt',$now_day)->value('sum(pay_price)');
        $pre_month_order_p = StoreOrderModel::where('paid',1)->where('pay_time','gt',$pre_day)->where('pay_time','lt',$now_day)->value('sum(pay_price)');
        $first_line['d_price'] = [
            'data' => $now_month_order_p > 0 ? $now_month_order_p : 0,
            'percent' => $pre_month_order_p==0?:floatval(round(abs($now_month_order_p - $pre_month_order_p)*100.0/$pre_month_order_p,4)),
            'is_plus' => $now_month_order_p - $pre_month_order_p > 0 ? 1 : ($now_month_order_p - $pre_month_order_p == 0 ? -1 : 0)
        ];

        //交易额->月
        $now_month_order_p = StoreOrderModel::where('paid',1)->where('pay_time','gt',$now_month)->value('sum(pay_price)');
        $pre_month_order_p = StoreOrderModel::where('paid',1)->where('pay_time','gt',$pre_month)->where('pay_time','lt',$now_month)->value('sum(pay_price)');
        $first_line['m_price'] = [
            'data' => $now_month_order_p > 0 ? $now_month_order_p : 0,
            'percent' => $pre_month_order_p==0?:floatval(round(abs($now_month_order_p - $pre_month_order_p)*100.0/$pre_month_order_p,4)),
            'is_plus' => $now_month_order_p - $pre_month_order_p > 0 ? 1 : ($now_month_order_p - $pre_month_order_p == 0 ? -1 : 0)
        ];

        //新粉丝->日
        $now_day_user = DB::name('User')->where('add_time','gt',$now_day)->count();
        $pre_day_user = DB::name('User')->where('add_time','gt',$pre_day)->where('add_time','lt',$now_day)->count();
        $pre_day_user = $pre_day_user ? $pre_day_user : 0;
        $first_line['day'] = [
            'data' => $now_day_user ? $now_day_user : 0,
            'percent' => $pre_day_user==0?:floatval(round(abs($now_day_user - $pre_day_user)*100.0/$pre_day_user,4)),
            'is_plus' => $now_day_user - $pre_day_user > 0 ? 1 : ($now_day_user - $pre_day_user == 0 ? -1 : 0)
        ];

        //新粉丝->月
        $now_month_user = DB::name('User')->where('add_time','gt',$now_month)->count();
        $pre_month_user = DB::name('User')->where('add_time','gt',$pre_month)->where('add_time','lt',$now_month)->count();
        $first_line['month'] = [
            'data' => $now_month_user ? $now_month_user : 0,
            'percent' => $pre_month_user==0?:floatval(round(abs($now_month_user - $pre_month_user)*100.0/$pre_month_user,4)),
            'is_plus' => $now_month_user - $pre_month_user > 0 ? 1 : ($now_month_user - $pre_month_user == 0 ? -1 : 0)
        ];

        //本月订单总数
        $now_order_info_c = StoreOrderModel::where('add_time','gt',$now_month)->count();
        $pre_order_info_c = StoreOrderModel::where('add_time','gt',$pre_month)->where('add_time','lt',$now_month)->count();
        $order_info['first'] = [
            'data' => $now_order_info_c ? $now_order_info_c : 0,
            'percent' => $pre_order_info_c==0?:floatval(round(abs($now_order_info_c - $pre_order_info_c)*100.0/$pre_order_info_c,4)),
            'is_plus' => $now_order_info_c - $pre_order_info_c > 0 ? 1 : ($now_order_info_c - $pre_order_info_c == 0 ? -1 : 0)
        ];

        //上月订单总数
        $second_now_month = strtotime(date('Y-m',strtotime('-1 month')));
        $second_pre_month = strtotime(date('Y-m',strtotime('-2 month')));
        $now_order_info_c = StoreOrderModel::where('add_time','gt',$pre_month)->where('add_time','lt',$now_month)->count();
        $pre_order_info_c = StoreOrderModel::where('add_time','gt',$second_pre_month)->where('add_time','lt',$second_now_month)->count();
        $order_info["second"] = [
            'data' => $now_order_info_c ? $now_order_info_c : 0,
            'percent' => $pre_order_info_c==0?:floatval(round(abs($now_order_info_c - $pre_order_info_c)*100.0/$pre_order_info_c,4)),
            'is_plus' => $now_order_info_c - $pre_order_info_c > 0 ? 1 : ($now_order_info_c - $pre_order_info_c == 0 ? -1 : 0)
        ];
        $second_line['order_info'] = $order_info;



        $log_list=SystemCountLogToShow::where('day',$pre_day)->where('place','all')->cache('system_count_log_to_show_'.$pre_day.'_all')->select()->toArray();
        $log_list=array_combine(array_column($log_list,'type'),$log_list);
        $default=['new_count'=>0,'active_count'=>0,'view_count'=>0,'total_count'=>0,'share_count'=>0];
        $system_count=[
            'today'=>SystemCountLogToShow::getTodayCount('all'),
            'yesterday'=>isset($log_list['day'])?$log_list['day']:$default,
            'average'=>isset($log_list['average'])?$log_list['average']:$default,
            'max'=>isset($log_list['max'])?$log_list['max']:$default,
        ];

        $this->assign([
            'first_line' => $first_line,
            'second_line' => $second_line,
            'topData' => $topData,
            'system_count'=>$system_count,
        ]);
        return $this->fetch();
    }

    /**
     * 获取其它几种平台的单独数据
     * @param Request $request
     * @return mixed
     */
    public function getSystemCountData(Request $request){
        $data = UtilService::postMore([
            ['place','all']
        ],$request);

        $pre_day = strtotime(date('Y-m-d',strtotime('-1 day')));//昨天时间戳
        $place=$data['place'];
        $log_list=SystemCountLogToShow::where('day',$pre_day)->where('place',$place)->cache('system_count_log_to_show_'.$pre_day.'_'.$place)->select()->toArray();
        $log_list=array_combine(array_column($log_list,'type'),$log_list);
        $default=['new_count'=>0,'active_count'=>0,'view_count'=>0,'total_count'=>0,'share_count'=>0];
        $system_count=[
            'today'=>SystemCountLogToShow::getTodayCount($place),
            'yesterday'=>isset($log_list['day'])?$log_list['day']:$default,
            'average'=>isset($log_list['average'])?$log_list['average']:$default,
            'max'=>isset($log_list['max'])?$log_list['max']:$default,
        ];
        return Json::succ('ok',$system_count);
    }

    /**
     * 订单图表
     */
    public function orderchart(){
        header('Content-type:text/json');
        $cycle = $this->request->param('cycle')?:'thirtyday';//默认30天
        $datalist = [];
        switch ($cycle){
            case 'thirtyday':
                $datebefor = date('Y-m-d',strtotime('-30 day'));
                $dateafter = date('Y-m-d');
                //上期
                $pre_datebefor = date('Y-m-d',strtotime('-60 day'));
                $pre_dateafter = date('Y-m-d',strtotime('-30 day'));
                for($i=-30;$i < 0;$i++){
                    $datalist[date('m-d',strtotime($i.' day'))] = date('m-d',strtotime($i.' day'));
                }
                $order_list = StoreOrderModel::where('add_time','between time',[$datebefor,$dateafter])
                    ->field("FROM_UNIXTIME(add_time,'%m-%d') as day,count(*) as count,sum(pay_price) as price")
                    ->group("FROM_UNIXTIME(add_time, '%Y%m%d')")
                    ->order('add_time asc')
                    ->select()->toArray();
                if(empty($order_list)) return Json::fail('无数据');
                foreach ($order_list as $k=>&$v){
                    $order_list[$v['day']] = $v;
                }
                $cycle_list = [];
                foreach ($datalist as $dk=>$dd){
                    if(!empty($order_list[$dd])){
                        $cycle_list[$dd] = $order_list[$dd];
                    }else{
                        $cycle_list[$dd] = ['count'=>0,'day'=>$dd,'price'=>''];
                    }
                }
                $chartdata = [];
                $data = [];//临时
                $chartdata['yAxis']['maxnum'] = 0;//最大值数量
                $chartdata['yAxis']['maxprice'] = 0;//最大值金额
                foreach ($cycle_list as $k=>$v){
                    $data['day'][] = $v['day'];
                    $data['count'][] = $v['count'];
                    $data['price'][] = round($v['price'],2);
                    if($chartdata['yAxis']['maxnum'] < $v['count'])
                        $chartdata['yAxis']['maxnum'] = $v['count'];//日最大订单数
                    if($chartdata['yAxis']['maxprice'] < $v['price'])
                        $chartdata['yAxis']['maxprice'] = $v['price'];//日最大金额
                }
                $chartdata['legend'] = ['订单金额','订单数'];//分类
                $chartdata['xAxis'] = $data['day'];//X轴值
                //,'itemStyle'=>$series
                $series= ['normal'=>['label'=>['show'=>true,'position'=>'top']]];
                $chartdata['series'][] = ['name'=>$chartdata['legend'][0],'type'=>'bar','itemStyle'=>$series,'data'=>$data['price']];//分类1值
                $chartdata['series'][] = ['name'=>$chartdata['legend'][1],'type'=>'bar','itemStyle'=>$series,'data'=>$data['count']];//分类2值
                //统计总数上期
                $pre_total = StoreOrderModel::where('add_time','between time',[$pre_datebefor,$pre_dateafter])
                    ->field("count(*) as count,sum(pay_price) as price")
                    ->find();
                if($pre_total){
                    $chartdata['pre_cycle']['count'] = [
                        'data' => $pre_total['count']? : 0
                    ];
                    $chartdata['pre_cycle']['price'] = [
                        'data' => $pre_total['price']? : 0
                    ];
                }
                //统计总数
                $total = StoreOrderModel::where('add_time','between time',[$datebefor,$dateafter])
                    ->field("count(*) as count,sum(pay_price) as price")
                    ->find();
                if($total){
                    $cha_count = intval($pre_total['count']) - intval($total['count']);
                    $pre_total['count'] = $pre_total['count']==0 ? 1 : $pre_total['count'];
                    $chartdata['cycle']['count'] = [
                        'data' => $total['count']? : 0,
                        'percent' => round((abs($cha_count)/intval($pre_total['count'])*100),2),
                        'is_plus' => $cha_count > 0 ? -1 : ($cha_count == 0 ? 0 : 1)
                    ];
                    $cha_price = round($pre_total['price'],2) - round($total['price'],2);
                    $pre_total['price'] = $pre_total['price']==0 ? 1 : $pre_total['price'];
                    $chartdata['cycle']['price'] = [
                        'data' => $total['price']? : 0,
                        'percent' => round(abs($cha_price)/$pre_total['price']*100,2),
                        'is_plus' => $cha_price > 0 ? -1 : ($cha_price == 0 ? 0 : 1)
                    ];
                }
                return Json::succ('ok',$chartdata);
                break;
            case 'week':
                $weekarray=array(['周日'],['周一'],['周二'],['周三'],['周四'],['周五'],['周六']);
                $datebefor = date('Y-m-d',strtotime('-1 week Monday'));
                $dateafter = date('Y-m-d',strtotime('-1 week Sunday'));
                $order_list = StoreOrderModel::where('add_time','between time',[$datebefor,$dateafter])
                    ->field("FROM_UNIXTIME(add_time,'%w') as day,count(*) as count,sum(pay_price) as price")
                    ->group("FROM_UNIXTIME(add_time, '%Y%m%e')")
                    ->order('add_time asc')
                    ->select()->toArray();
                //数据查询重新处理
                $new_order_list = [];
                foreach ($order_list as $k=>$v){
                    $new_order_list[$v['day']] = $v;
                }
                $now_datebefor = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
                $now_dateafter = date('Y-m-d',strtotime("+1 day"));
                $now_order_list = StoreOrderModel::where('add_time','between time',[$now_datebefor,$now_dateafter])
                    ->field("FROM_UNIXTIME(add_time,'%w') as day,count(*) as count,sum(pay_price) as price")
                    ->group("FROM_UNIXTIME(add_time, '%Y%m%e')")
                    ->order('add_time asc')
                    ->select()->toArray();
                //数据查询重新处理 key 变为当前值
                $new_now_order_list = [];
                foreach ($now_order_list as $k=>$v){
                    $new_now_order_list[$v['day']] = $v;
                }
                foreach ($weekarray as $dk=>$dd){
                    if(!empty($new_order_list[$dk])){
                        $weekarray[$dk]['pre'] = $new_order_list[$dk];
                    }else{
                        $weekarray[$dk]['pre'] = ['count'=>0,'day'=>$weekarray[$dk][0],'price'=>'0'];
                    }
                    if(!empty($new_now_order_list[$dk])){
                        $weekarray[$dk]['now'] = $new_now_order_list[$dk];
                    }else{
                        $weekarray[$dk]['now'] = ['count'=>0,'day'=>$weekarray[$dk][0],'price'=>'0'];
                    }
                }
                $chartdata = [];
                $data = [];//临时
                $chartdata['yAxis']['maxnum'] = 0;//最大值数量
                $chartdata['yAxis']['maxprice'] = 0;//最大值金额
                foreach ($weekarray as $k=>$v){
                    $data['day'][] = $v[0];
                    $data['pre']['count'][] = $v['pre']['count'];
                    $data['pre']['price'][] = round($v['pre']['price'],2);
                    $data['now']['count'][] = $v['now']['count'];
                    $data['now']['price'][] = round($v['now']['price'],2);
                    if($chartdata['yAxis']['maxnum'] < $v['pre']['count'] || $chartdata['yAxis']['maxnum'] < $v['now']['count']){
                        $chartdata['yAxis']['maxnum'] = $v['pre']['count']>$v['now']['count']?$v['pre']['count']:$v['now']['count'];//日最大订单数
                    }
                    if($chartdata['yAxis']['maxprice'] < $v['pre']['price'] || $chartdata['yAxis']['maxprice'] < $v['now']['price']){
                        $chartdata['yAxis']['maxprice'] = $v['pre']['price']>$v['now']['price']?$v['pre']['price']:$v['now']['price'];//日最大金额
                    }
                }
                $chartdata['legend'] = ['上周金额','本周金额','上周订单数','本周订单数'];//分类
                $chartdata['xAxis'] = $data['day'];//X轴值
                //,'itemStyle'=>$series
                $series= ['normal'=>['label'=>['show'=>true,'position'=>'top']]];
                $chartdata['series'][] = ['name'=>$chartdata['legend'][0],'type'=>'bar','itemStyle'=>$series,'data'=>$data['pre']['price']];//分类1值
                $chartdata['series'][] = ['name'=>$chartdata['legend'][1],'type'=>'bar','itemStyle'=>$series,'data'=>$data['now']['price']];//分类1值
                $chartdata['series'][] = ['name'=>$chartdata['legend'][2],'type'=>'line','itemStyle'=>$series,'data'=>$data['pre']['count']];//分类2值
                $chartdata['series'][] = ['name'=>$chartdata['legend'][3],'type'=>'line','itemStyle'=>$series,'data'=>$data['now']['count']];//分类2值

                //统计总数上期
                $pre_total = StoreOrderModel::where('add_time','between time',[$datebefor,$dateafter])
                    ->field("count(*) as count,sum(pay_price) as price")
                    ->find();
                if($pre_total){
                    $chartdata['pre_cycle']['count'] = [
                        'data' => $pre_total['count']? : 0
                    ];
                    $chartdata['pre_cycle']['price'] = [
                        'data' => $pre_total['price']? : 0
                    ];
                }
                //统计总数
                $total = StoreOrderModel::where('add_time','between time',[$now_datebefor,$now_dateafter])
                    ->field("count(*) as count,sum(pay_price) as price")
                    ->find();
                if($total){
                    $cha_count = intval($pre_total['count']) - intval($total['count']);
                    $pre_total['count'] = $pre_total['count']==0 ? 1 : $pre_total['count'];
                    $chartdata['cycle']['count'] = [
                        'data' => $total['count']? : 0,
                        'percent' => round((abs($cha_count)/intval($pre_total['count'])*100),2),
                        'is_plus' => $cha_count > 0 ? -1 : ($cha_count == 0 ? 0 : 1)
                    ];
                    $cha_price = round($pre_total['price'],2) - round($total['price'],2);
                    $pre_total['price'] = $pre_total['price']==0 ? 1 : $pre_total['price'];
                    $chartdata['cycle']['price'] = [
                        'data' => $total['price']? : 0,
                        'percent' => round(abs($cha_price)/$pre_total['price']*100,2),
                        'is_plus' => $cha_price > 0 ? -1 : ($cha_price == 0 ? 0 : 1)
                    ];
                }
                return Json::succ('ok',$chartdata);
                break;
            case 'month':
                $weekarray=array('01'=>['1'],'02'=>['2'],'03'=>['3'],'04'=>['4'],'05'=>['5'],'06'=>['6'],'07'=>['7'],'08'=>['8'],'09'=>['9'],'10'=>['10'],'11'=>['11'],'12'=>['12'],'13'=>['13'],'14'=>['14'],'15'=>['15'],'16'=>['16'],'17'=>['17'],'18'=>['18'],'19'=>['19'],'20'=>['20'],'21'=>['21'],'22'=>['22'],'23'=>['23'],'24'=>['24'],'25'=>['25'],'26'=>['26'],'27'=>['27'],'28'=>['28'],'29'=>['29'],'30'=>['30'],'31'=>['31']);

                $datebefor = date('Y-m-01',strtotime('-1 month'));
                $dateafter = date('Y-m-d',strtotime(date('Y-m-01')));
                $order_list = StoreOrderModel::where('add_time','between time',[$datebefor,$dateafter])
                    ->field("FROM_UNIXTIME(add_time,'%d') as day,count(*) as count,sum(pay_price) as price")
                    ->group("FROM_UNIXTIME(add_time, '%Y%m%e')")
                    ->order('add_time asc')
                    ->select()->toArray();
                //数据查询重新处理
                $new_order_list = [];
                foreach ($order_list as $k=>$v){
                    $new_order_list[$v['day']] = $v;
                }
                $now_datebefor = date('Y-m-01');
                $now_dateafter = date('Y-m-d',strtotime("+1 day"));
                $now_order_list = StoreOrderModel::where('add_time','between time',[$now_datebefor,$now_dateafter])
                    ->field("FROM_UNIXTIME(add_time,'%d') as day,count(*) as count,sum(pay_price) as price")
                    ->group("FROM_UNIXTIME(add_time, '%Y%m%e')")
                    ->order('add_time asc')
                    ->select()->toArray();
                //数据查询重新处理 key 变为当前值
                $new_now_order_list = [];
                foreach ($now_order_list as $k=>$v){
                    $new_now_order_list[$v['day']] = $v;
                }
                foreach ($weekarray as $dk=>$dd){
                    if(!empty($new_order_list[$dk])){
                        $weekarray[$dk]['pre'] = $new_order_list[$dk];
                    }else{
                        $weekarray[$dk]['pre'] = ['count'=>0,'day'=>$weekarray[$dk][0],'price'=>'0'];
                    }
                    if(!empty($new_now_order_list[$dk])){
                        $weekarray[$dk]['now'] = $new_now_order_list[$dk];
                    }else{
                        $weekarray[$dk]['now'] = ['count'=>0,'day'=>$weekarray[$dk][0],'price'=>'0'];
                    }
                }
                $chartdata = [];
                $data = [];//临时
                $chartdata['yAxis']['maxnum'] = 0;//最大值数量
                $chartdata['yAxis']['maxprice'] = 0;//最大值金额
                foreach ($weekarray as $k=>$v){
                    $data['day'][] = $v[0];
                    $data['pre']['count'][] = $v['pre']['count'];
                    $data['pre']['price'][] = round($v['pre']['price'],2);
                    $data['now']['count'][] = $v['now']['count'];
                    $data['now']['price'][] = round($v['now']['price'],2);
                    if($chartdata['yAxis']['maxnum'] < $v['pre']['count'] || $chartdata['yAxis']['maxnum'] < $v['now']['count']){
                        $chartdata['yAxis']['maxnum'] = $v['pre']['count']>$v['now']['count']?$v['pre']['count']:$v['now']['count'];//日最大订单数
                    }
                    if($chartdata['yAxis']['maxprice'] < $v['pre']['price'] || $chartdata['yAxis']['maxprice'] < $v['now']['price']){
                        $chartdata['yAxis']['maxprice'] = $v['pre']['price']>$v['now']['price']?$v['pre']['price']:$v['now']['price'];//日最大金额
                    }

                }
                $chartdata['legend'] = ['上月金额','本月金额','上月订单数','本月订单数'];//分类
                $chartdata['xAxis'] = $data['day'];//X轴值
                //,'itemStyle'=>$series
                $series= ['normal'=>['label'=>['show'=>true,'position'=>'top']]];
                $chartdata['series'][] = ['name'=>$chartdata['legend'][0],'type'=>'bar','itemStyle'=>$series,'data'=>$data['pre']['price']];//分类1值
                $chartdata['series'][] = ['name'=>$chartdata['legend'][1],'type'=>'bar','itemStyle'=>$series,'data'=>$data['now']['price']];//分类1值
                $chartdata['series'][] = ['name'=>$chartdata['legend'][2],'type'=>'line','itemStyle'=>$series,'data'=>$data['pre']['count']];//分类2值
                $chartdata['series'][] = ['name'=>$chartdata['legend'][3],'type'=>'line','itemStyle'=>$series,'data'=>$data['now']['count']];//分类2值

                //统计总数上期
                $pre_total = StoreOrderModel::where('add_time','between time',[$datebefor,$dateafter])
                    ->field("count(*) as count,sum(pay_price) as price")
                    ->find();
                if($pre_total){
                    $chartdata['pre_cycle']['count'] = [
                        'data' => $pre_total['count']? : 0
                    ];
                    $chartdata['pre_cycle']['price'] = [
                        'data' => $pre_total['price']? : 0
                    ];
                }
                //统计总数
                $total = StoreOrderModel::where('add_time','between time',[$now_datebefor,$now_dateafter])
                    ->field("count(*) as count,sum(pay_price) as price")
                    ->find();
                if($total){
                    $cha_count = intval($pre_total['count']) - intval($total['count']);
                    $pre_total['count'] = $pre_total['count']==0 ? 1 : $pre_total['count'];
                    $chartdata['cycle']['count'] = [
                        'data' => $total['count']? : 0,
                        'percent' => round((abs($cha_count)/intval($pre_total['count'])*100),2),
                        'is_plus' => $cha_count > 0 ? -1 : ($cha_count == 0 ? 0 : 1)
                    ];
                    $cha_price = round($pre_total['price'],2) - round($total['price'],2);
                    $pre_total['price'] = $pre_total['price']==0 ? 1 : $pre_total['price'];
                    $chartdata['cycle']['price'] = [
                        'data' => $total['price']? : 0,
                        'percent' => round(abs($cha_price)/$pre_total['price']*100,2),
                        'is_plus' => $cha_price > 0 ? -1 : ($cha_price == 0 ? 0 : 1)
                    ];
                }
                return Json::succ('ok',$chartdata);
                break;
            case 'year':
                $weekarray=array('01'=>['一月'],'02'=>['二月'],'03'=>['三月'],'04'=>['四月'],'05'=>['五月'],'06'=>['六月'],'07'=>['七月'],'08'=>['八月'],'09'=>['九月'],'10'=>['十月'],'11'=>['十一月'],'12'=>['十二月']);
                $datebefor = date('Y-01-01',strtotime('-1 year'));
                $dateafter = date('Y-12-31',strtotime('-1 year'));
                $order_list = StoreOrderModel::where('add_time','between time',[$datebefor,$dateafter])
                    ->field("FROM_UNIXTIME(add_time,'%m') as day,count(*) as count,sum(pay_price) as price")
                    ->group("FROM_UNIXTIME(add_time, '%Y%m')")
                    ->order('add_time asc')
                    ->select()->toArray();
                //数据查询重新处理
                $new_order_list = [];
                foreach ($order_list as $k=>$v){
                    $new_order_list[$v['day']] = $v;
                }
                $now_datebefor = date('Y-01-01');
                $now_dateafter = date('Y-m-d');
                $now_order_list = StoreOrderModel::where('add_time','between time',[$now_datebefor,$now_dateafter])
                    ->field("FROM_UNIXTIME(add_time,'%m') as day,count(*) as count,sum(pay_price) as price")
                    ->group("FROM_UNIXTIME(add_time, '%Y%m')")
                    ->order('add_time asc')
                    ->select()->toArray();
                //数据查询重新处理 key 变为当前值
                $new_now_order_list = [];
                foreach ($now_order_list as $k=>$v){
                    $new_now_order_list[$v['day']] = $v;
                }
                foreach ($weekarray as $dk=>$dd){
                    if(!empty($new_order_list[$dk])){
                        $weekarray[$dk]['pre'] = $new_order_list[$dk];
                    }else{
                        $weekarray[$dk]['pre'] = ['count'=>0,'day'=>$weekarray[$dk][0],'price'=>'0'];
                    }
                    if(!empty($new_now_order_list[$dk])){
                        $weekarray[$dk]['now'] = $new_now_order_list[$dk];
                    }else{
                        $weekarray[$dk]['now'] = ['count'=>0,'day'=>$weekarray[$dk][0],'price'=>'0'];
                    }
                }
                $chartdata = [];
                $data = [];//临时
                $chartdata['yAxis']['maxnum'] = 0;//最大值数量
                $chartdata['yAxis']['maxprice'] = 0;//最大值金额
                foreach ($weekarray as $k=>$v){
                    $data['day'][] = $v[0];
                    $data['pre']['count'][] = $v['pre']['count'];
                    $data['pre']['price'][] = round($v['pre']['price'],2);
                    $data['now']['count'][] = $v['now']['count'];
                    $data['now']['price'][] = round($v['now']['price'],2);
                    if($chartdata['yAxis']['maxnum'] < $v['pre']['count'] || $chartdata['yAxis']['maxnum'] < $v['now']['count']){
                        $chartdata['yAxis']['maxnum'] = $v['pre']['count']>$v['now']['count']?$v['pre']['count']:$v['now']['count'];//日最大订单数
                    }
                    if($chartdata['yAxis']['maxprice'] < $v['pre']['price'] || $chartdata['yAxis']['maxprice'] < $v['now']['price']){
                        $chartdata['yAxis']['maxprice'] = $v['pre']['price']>$v['now']['price']?$v['pre']['price']:$v['now']['price'];//日最大金额
                    }
                }
                $chartdata['legend'] = ['去年金额','今年金额','去年订单数','今年订单数'];//分类
                $chartdata['xAxis'] = $data['day'];//X轴值
                //,'itemStyle'=>$series
                $series= ['normal'=>['label'=>['show'=>true,'position'=>'top']]];
                $chartdata['series'][] = ['name'=>$chartdata['legend'][0],'type'=>'bar','itemStyle'=>$series,'data'=>$data['pre']['price']];//分类1值
                $chartdata['series'][] = ['name'=>$chartdata['legend'][1],'type'=>'bar','itemStyle'=>$series,'data'=>$data['now']['price']];//分类1值
                $chartdata['series'][] = ['name'=>$chartdata['legend'][2],'type'=>'line','itemStyle'=>$series,'data'=>$data['pre']['count']];//分类2值
                $chartdata['series'][] = ['name'=>$chartdata['legend'][3],'type'=>'line','itemStyle'=>$series,'data'=>$data['now']['count']];//分类2值

                //统计总数上期
                $pre_total = StoreOrderModel::where('add_time','between time',[$datebefor,$dateafter])
                    ->field("count(*) as count,sum(pay_price) as price")
                    ->find();
                if($pre_total){
                    $chartdata['pre_cycle']['count'] = [
                        'data' => $pre_total['count']? : 0
                    ];
                    $chartdata['pre_cycle']['price'] = [
                        'data' => $pre_total['price']? : 0
                    ];
                }
                //统计总数
                $total = StoreOrderModel::where('add_time','between time',[$now_datebefor,$now_dateafter])
                    ->field("count(*) as count,sum(pay_price) as price")
                    ->find();
                if($total){
                    $cha_count = intval($pre_total['count']) - intval($total['count']);
                    $pre_total['count'] = $pre_total['count']==0 ? 1 : $pre_total['count'];
                    $chartdata['cycle']['count'] = [
                        'data' => $total['count']? : 0,
                        'percent' => round((abs($cha_count)/intval($pre_total['count'])*100),2),
                        'is_plus' => $cha_count > 0 ? -1 : ($cha_count == 0 ? 0 : 1)
                    ];
                    $cha_price = round($pre_total['price'],2) - round($total['price'],2);
                    $pre_total['price'] = $pre_total['price']==0 ? 1 : $pre_total['price'];
                    $chartdata['cycle']['price'] = [
                        'data' => $total['price']? : 0,
                        'percent' => round(abs($cha_price)/$pre_total['price']*100,2),
                        'is_plus' => $cha_price > 0 ? -1 : ($cha_price == 0 ? 0 : 1)
                    ];
                }
                return Json::succ('ok',$chartdata);
                break;
            default:
                break;
        }


    }
    /**
     * 用户图表
     */
    public function userchart(){
        header('Content-type:text/json');

        $starday = date('Y-m-d',strtotime('-30 day'));
        $yesterday = date('Y-m-d');

        $user_list = UserModel::where('add_time','between time',[$starday,$yesterday])
            ->field("FROM_UNIXTIME(add_time,'%m-%e') as day,count(*) as count")
            ->group("FROM_UNIXTIME(add_time, '%Y%m%e')")
            ->order('add_time asc')
            ->select()->toArray();
        $chartdata = [];
        $data = [];
        $chartdata['legend'] = ['用户数'];//分类
        $chartdata['yAxis']['maxnum'] = 0;//最大值数量
        if(empty($user_list))return Json::fail('无数据');
        foreach ($user_list as $k=>$v){
            $data['day'][] = $v['day'];
            $data['count'][] = $v['count'];
            if($chartdata['yAxis']['maxnum'] < $v['count'])
                $chartdata['yAxis']['maxnum'] = $v['count'];
        }
        $chartdata['xAxis'] = $data['day'];//X轴值
        $chartdata['series'] = $data['count'];//分类1值

        return Json::succ('ok',$chartdata);
    }

    /**待办事统计
     * @param Request|null $request
     */
    public function Jnotice()
    {
        header('Content-type:text/json');
        $data = [];
        $data['ordernum'] = StoreOrderModel::statusByWhere(1)->count();//待发货
        $replenishment_num = SystemConfig::getValue('store_stock') > 0 ? SystemConfig::getValue('store_stock') : 2;//库存预警界限
        $data['inventory'] = ProductModel::where('stock','<=',$replenishment_num)->where('is_show',1)->where('is_del',0)->count();//库存
        $replenishment_num_one = SystemConfig::getValue('replenishment_num') > 0 ? SystemConfig::getValue('replenishment_num') : 2;//待补货数量
        $data['replenishment'] = ProductModel::where('stock','<=',$replenishment_num_one)->where('is_show',1)->where('is_del',0)->count();//库存
        $data['commentnum'] = StoreProductReplyModel::where('is_reply',0)->count();//评论
        $data['reflectnum'] = UserExtractModel::where('status',0)->count();;//提现
        $data['msgcount'] = intval($data['ordernum'])+intval($data['inventory'])+intval($data['replenishment'])+intval($data['commentnum'])+intval($data['reflectnum']);
        return Json::succ('ok',$data);
    }
}


