<?php


namespace app\admin\model\com;

use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService;
use app\admin\model\com\ComForum as ForumModel;
/**
 * Class ComThreadClass
 * @package app\admin\model\store
 */
class ComThreadClass extends ModelBasic
{
    use ModelTrait;

    // 自动写入时间戳
    protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat         = 'Y-m-d H:i:s';

    /*
     * 异步获取分类列表
     * @param $where
     * @return array
     */
    public static function ClassList($where){
        $data=($data=self::systemPage($where,true)->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() :[];
        foreach ($data as &$item){
            if($item['fid']){
                $item['fid_name'] = ForumModel::where('id',$item['fid'])->value('name');
            }else{
                $item['fid'] = '未关联版块';
            }
        }
        $count=self::systemPage($where,true)->count();
        return compact('count','data');
    }
    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where,$isAjax=false){
        $model = new self;
        if($where['fid'] != '')  $model = $model->where('fid',$where['fid']);
        if($where['status'] == '') {
            $model = $model->where(['status'=>['in', [0,1]]]);
        }else{
            $model = $model->where(array('status'=>$where['status']));
        }
        if($where['name'] != '')  $model = $model->where('name','LIKE',"%$where[name]%");
        if($isAjax===true){
            if(isset($where['order']) && $where['order']!=''){
                $model=$model->order(self::setOrder($where['order']));
            }
            return $model;
        }

        return self::page($model,function ($item){
            if($item['fid']){
                $item['fid_name'] = ForumModel::where('id',$item['fid'])->value('name');
            }else{
                $item['fid_name'] = '未关联版块';
            }
        },$where);
    }

    /**
     * 获取顶级分类
     * @return array
     */
    public static function getCategory($field = 'id,cate_name')
    {
        return self::where('is_show',1)->column($field);
    }

    /**
     * 分级排序列表
     * @param null $model
     * @return array
     */
    public static function getCatTierList($model = null)
    {
        if($model === null) $model = new self();
        return $model->field(['id'=>'value', 'name'=>'label'])->where('status', 1)->select()->toArray();
    }


    public static function delCategory($id){
        $count = self::where('pid',$id)->count();
        if($count)
            return false;
        else{
            return self::del($id);
        }
    }
}