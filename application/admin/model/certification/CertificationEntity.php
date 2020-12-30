<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-10 15:02:17
 */

namespace app\admin\model\certification;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;
use Carbon\Carbon;

/**
 * 认证实体  model
 * Class CertificationEntity
 * @package app\admin\model\certification
 */
class CertificationEntity extends ModelBasic
{
    use ModelTrait;

    public function getCreateTimeAttr($time){
        return $time;//返回create_time原始数据，不进行时间戳转换。
    }
    public function cate()
    {
        return $this->hasOne('CertificationCate','id','cate_id');
    }
    /**
     * 获取指定列表
     * @param array $params
     * @return page
     */
    public static function getAdminPage($params,$ajax)
    {
        $model = self::getModelObject($params)->field(['*']);
        if ($ajax) {
            $model=$model->page((int)$params['page'],(int)$params['limit']);
            $data=($data=$model->with(['cate'])->order('id DESC')->select()) && count($data) ? $data->toArray():[];
            $rztx_id=db('certification_datum')->where('field','rztx')->value('id');
            $cate_ids=db('certification_cate_datum')->where('datum_id',$rztx_id)->column('cate_id');
            foreach ($data as $key => &$value) {
                $datum_datas=unserialize($value['datum_data']);
                if(!empty($datum_datas['rztx'])){
                    $value['rztx']=$datum_datas['rztx'];
                }else{
                    $value['rztx']='';
                }
                if(in_array($value['cate_id'],$cate_ids)){
                    $value['rztx_edit']=1;
                }else{
                    $value['rztx_edit']=0;
                }
            }
            $count=self::getModelObject($params)->count();
            return compact('count','data');
        }
        $model = $model->order('create_time DESC');
        return self::page($model,$params);
    }

    public static function getModelObject($params = [])
    {
        $model = new self();
        if (!empty($params)) {
            $model = new self;
            $limitTimeList = [
                'yesterday'=>implode(' - ',[date('Y/m/d',strtotime('-1 day')),date('Y/m/d')]),
                'today'=>implode(' - ',[date('Y/m/d'),date('Y/m/d',strtotime('+1 day'))]),
                'week'=>implode(' - ',[
                    date('Y/m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),
                    date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600))
                ]),
                'month'=>implode(' - ',[date('Y/m').'/01',date('Y/m').'/'.date('t')]),
                'quarter'=>implode(' - ',[
                    date('Y').'/'.(ceil((date('n'))/3)*3-3+1).'/01',
                    date('Y').'/'.(ceil((date('n'))/3)*3).'/'.date('t',mktime(0,0,0,(ceil((date('n'))/3)*3),1,date('Y')))
                ]),
                'year'=>implode(' - ',[
                    date('Y').'/01/01',date('Y/m/d',strtotime(date('Y').'/01/01 + 1year -1 day'))
                ])
            ];
            if($params['create_time'] !== '') {
                //$model = $model->where('create_time',$limitTimeList[$params['create_time']]);
                //dump($limitTimeList[$params['create_time']]);
                $where['data']=$limitTimeList[$params['create_time']];
                $model=self::getModelTime($where,$model,'create_time');
            }
            if($params['create_time_between'] !== ''){
                $where['data']=$params['create_time_between'];
                $model=self::getModelTime($where,$model,'create_time');
                // $between=explode(" - ", $params['create_time_between']);
                // $where['create_time'] = array('between',$between);
                // $model = $model->where($where['create_time']);
            }
            // date 日期
            if (isset($params['select_date'])) {
                $where=$params;
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
            }
            if($params['status'] !== '') $model = $model->where('status',$params['status']);

            if($params['cate_id'] !== '') $model = $model->where('cate_id',$params['cate_id']);
            if($params['keyword'] !== '') $model = $model->where('truename|phone|uid','LIKE',"%$params[keyword]%");
            if($params['nickname'] !== '') $model = $model->where('nickname','LIKE',"%$params[nickname]%");
        }
        return $model;
    }

    public static function delData($id)
    {
        return self::del($id);
    }


}