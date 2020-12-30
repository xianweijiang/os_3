<?php

namespace app\ebapi\model\store;

use think\Cache;
use think\Model;
use basic\ModelBasic;

class StoreCategoryColumn extends ModelBasic
{
    public static function pidByCategory($pid,$field = '*',$limit = 0)
    {
        $model = self::where('pid',$pid)->where('is_show',1)->field($field);
        if($limit) $model->limit($limit);
        return $model->select();
    }

    public static function pidBySidList($pid)
    {
        return self::where('pid',$pid)->field('id,cate_name,pid')->select();
    }

    public static function cateIdByPid($cateId)
    {
        return self::where('id',$cateId)->value('pid');
    }

    /*
     * 获取一级和二级分类
     * @return array
     * */
    public static function getProductCategory($expire=800)
    {
        if(Cache::has('column')){
            return Cache::get('column');
        }else {
            $parentCategory = self::pidByCategory(0, 'id,cate_name')->toArray();
            foreach ($parentCategory as $k => $category) {
                $category['child'] = self::pidByCategory($category['id'], 'id,cate_name,pic')->toArray();
                foreach ($category['child'] as $l => $cat) {
                    $cat['child'] = self::pidByCategory($cat['id'], 'id,cate_name,pic')->toArray();
                    $category['child'][$l] = $cat;
                }
                $parentCategory[$k] = $category;
            }
            Cache::set('column',$parentCategory,$expire);
            return $parentCategory;
        }
    }

}
