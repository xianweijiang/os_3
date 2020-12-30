<?php
/**
 * Created by PhpStorm.
 * User: zzl-yf
 * Date: 2020/2/14
 * Time: 16:38
 */

namespace app\commonapi\model;


use basic\ModelBasic;
use think\Cache;
use traits\ModelTrait;

class SystemCountLogToShow extends ModelBasic
{
    use ModelTrait;

    public static $platform_list=[
        'android',//安卓
        'ios',//苹果
        'h5',//手机网页
        'mini_program',//微信小程序
        'alipay_mini_program',//支付宝小程序
        'headline_mini_program',//头条小程序
    ];

    /**
     * 每日计划任务计算数据并存储
     * @return bool
     */
    public static function countData()
    {
        $day=strtotime(date("Y-m-d",strtotime("-1 day")));//昨日0点时间戳
        $last_day=self::max('day');
        if($last_day){
            if($last_day>=$day){//已经统计过，无需继续统计
                return true;
            }
        }else{
            $last_day=$day;
        }
        do{
            $res=self::_countOneDayData($last_day);
            if(!$res){
                return false;
            }
            $last_day=intval($last_day)+24*60*60;
        }while($last_day<=$day);
        return true;
    }
    
    /**
     * 计算某一天的数据并存储
     * @param $day_time
     * @return bool
     */
    private static function _countOneDayData($day_time)
    {
        $platform_list=self::$platform_list;
        $logUserModel=db('system_count_log_user');
        $viewModel=db('system_count_log_view');
        $shareModel=db('system_count_log_share');
        //添加当日数据

        self::startTrans();
        //当日数据，公用部分
        $one_data['type']='day';
        $one_data['day']=$day_time;
        //当日数据，公用部分end

        $one_data['new_count']=$logUserModel->whereBetween('create_time',[$day_time,$day_time+24*60*60-1])->where('type','new')->count();
        $one_data['active_count']=$logUserModel->whereBetween('create_time',[$day_time,$day_time+24*60*60-1])->count();
        $one_data['total_count']=$logUserModel->where('create_time','<',$day_time+24*60*60)->where('type','new')->count();
        $one_data['view_count']=$viewModel->whereBetween('create_time',[$day_time,$day_time+24*60*60-1])->sum('num');
        $one_data['share_count']=$shareModel->whereBetween('create_time',[$day_time,$day_time+24*60*60-1])->count();
        $one_data['place']='all';
        $data_list['all']=$one_data;
        foreach ($platform_list as $val){
            $one_data['place']=$val;
            $one_data['new_count']=$logUserModel->whereBetween('create_time',[$day_time,$day_time+24*60*60-1])->where('place',$val)->where('type','new')->count();
            $one_data['active_count']=$logUserModel->whereBetween('create_time',[$day_time,$day_time+24*60*60-1])->where('place',$val)->count();
            $one_data['total_count']=$logUserModel->where('create_time','<',$day_time+24*60*60)->where('place',$val)->where('type','new')->count();
            $one_data['view_count']=$viewModel->whereBetween('create_time',[$day_time,$day_time+24*60*60-1])->where('place',$val)->sum('num');
            $one_data['share_count']=$shareModel->whereBetween('create_time',[$day_time,$day_time+24*60*60-1])->where('place',$val)->count();
            $data_list[$val]=$one_data;
        }
        unset($val);
        $res=self::setAll($data_list);
        //添加当日数据 end
        if(!$res){
            self::rollback();
            self::setErrorInfo('数据记录添加失败！');
            return false;
        }
        //添加每日平均和历史峰值数据

        $data_list=[];
        //公用部分
        $one_data['day']=$day_time;
        //公用部分end
        //每日平均
        $one_data['type']='average';
        $one_data['place']='all';
        $selectModel=self::where('day','<',$day_time+24*60*60)->where('type','day');
        $one_data['new_count']=$selectModel->where('place','all')->avg('new_count');
        $one_data['active_count']=$selectModel->where('place','all')->avg('active_count');
        $one_data['total_count']=$selectModel->where('place','all')->avg('total_count');
        $one_data['view_count']=$selectModel->where('place','all')->avg('view_count');
        $one_data['share_count']=$selectModel->where('place','all')->avg('share_count');
        $data_list[]=$one_data;

        foreach ($platform_list as $val){
            $one_data['place']=$val;
            $one_data['new_count']=$selectModel->where('place',$val)->avg('new_count');
            $one_data['active_count']=$selectModel->where('place',$val)->avg('active_count');
            $one_data['total_count']=$selectModel->where('place',$val)->avg('total_count');
            $one_data['view_count']=$selectModel->where('place',$val)->avg('view_count');
            $one_data['share_count']=$selectModel->where('place',$val)->avg('share_count');
            $data_list[]=$one_data;
        }
        unset($val);

        //历史峰值
        $one_data['type']='max';
        $one_data['place']='all';
        $one_data['new_count']=$selectModel->where('place','all')->max('new_count');
        $one_data['active_count']=$selectModel->where('place','all')->max('active_count');
        $one_data['total_count']=$selectModel->where('place','all')->max('total_count');
        $one_data['view_count']=$selectModel->where('place','all')->max('view_count');
        $one_data['share_count']=$selectModel->where('place','all')->max('share_count');
        $data_list[]=$one_data;

        foreach ($platform_list as $val){
            $one_data['place']=$val;
            $one_data['new_count']=$selectModel->where('place',$val)->max('new_count');
            $one_data['active_count']=$selectModel->where('place',$val)->max('active_count');
            $one_data['total_count']=$selectModel->where('place',$val)->max('total_count');
            $one_data['view_count']=$selectModel->where('place',$val)->max('view_count');
            $one_data['share_count']=$selectModel->where('place',$val)->max('share_count');
            $data_list[]=$one_data;
        }
        unset($val);
        $res1=self::setAll($data_list);
        if(!$res1){
            self::rollback();
            self::setErrorInfo('数据记录添加失败！');
            return false;
        }

        self::commitTrans();
        return true;
    }

    /**
     * 今日数据获取
     * @param string $place
     * @return mixed
     */
    public static function getTodayCount($place='all')
    {
        $tag='system_count_log_to_show_today_'.$place;
        $one_data=Cache::get($tag);
        if(!$one_data){
            $day_start=strtotime(date("Y-m-d",time()));//今日0点时间戳
            $logUserModel=db('system_count_log_user');
            $viewModel=db('system_count_log_view');
            $shareModel=db('system_count_log_share');
            if($place=='all'){
                $one_data['place']='all';
                $one_data['new_count']=$logUserModel->where('create_time','>=',$day_start)->where('type','new')->count();
                $one_data['active_count']=$logUserModel->where('create_time','>=',$day_start)->count();
                $one_data['total_count']=$logUserModel->where('create_time','<',$day_start+24*60*60)->where('type','new')->count();
                $one_data['view_count']=$viewModel->where('create_time','>=',$day_start)->sum('num');
                $one_data['share_count']=$shareModel->where('create_time','>=',$day_start)->count();
            }else{
                $one_data['place']=$place;
                $one_data['new_count']=$logUserModel->where('create_time','>=',$day_start)->where('type','new')->where('place',$place)->count();
                $one_data['active_count']=$logUserModel->where('create_time','>=',$day_start)->where('place',$place)->count();
                $one_data['total_count']=$logUserModel->where('create_time','<',$day_start+24*60*60)->where('type','new')->where('place',$place)->count();
                $one_data['view_count']=$viewModel->where('create_time','>=',$day_start)->where('place',$place)->sum('num');
                $one_data['share_count']=$shareModel->where('create_time','>=',$day_start)->where('place',$place)->count();
            }
            Cache::set($tag,$one_data,60);
        }
        return $one_data;
    }
}