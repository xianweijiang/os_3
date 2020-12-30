<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/12
 */

namespace app\ebapi\model\store;


use basic\ModelBasic;
use think\Cache;

class StoreCategory extends ModelBasic
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
        if(Cache::has('parent_category')){
            return Cache::get('parent_category');
        }else {
            $parentCategory = self::pidByCategory(0, 'id,cate_name')->toArray();
            foreach ($parentCategory as $k => $category) {
                $category['child'] = self::pidByCategory($category['id'], 'id,cate_name,pic')->toArray();
                foreach ($category['child'] as $l => $cat) {
                    $cat['child'] = self::pidByCategory($cat['id'], 'id,cate_name,pic')->toArray();
                    $category['child'][$l] = $cat;
                    $cat['pic_150']=thumb_path($cat['pic'],150,150);
                    $cat['pic_350']=thumb_path($cat['pic'],350,350);
                    $cat['pic_750']=thumb_path($cat['pic'],750,750);
                }
                $parentCategory[$k] = $category;
            }
            Cache::set('parent_category',$parentCategory,$expire);
            return $parentCategory;
        }
    }
    /**
     * 获取一级和二级分类
     * @author jiangxw
     * @return array
     */
    public static function getProductCategoryNyb($expire=800)
    {
        if(Cache::has('parent_category_nyb')){
            return Cache::get('parent_category_nyb');
        }else {
            $parentCategory = self::pidByCategory(0,'id,cate_name as title')->toArray();
            foreach($parentCategory as $k => $category){
                $category['s_classify'] = self::pidByCategory($category['id'], 'id,cate_name as title,pic as image')->toArray();
                $parentCategory[$k] = $category;
            }
            Cache::set('parent_category_nyb',$parentCategory,$expire);
            return $parentCategory;
        }
    }

    /**
     * TODO  获取首页展示的二级分类  排序默认降序
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function byIndexList($limit = 4,$field = 'id,cate_name,pid,pic'){
        $data=self::where('pid','>',0)->where('is_show',1)->field($field)->order('sort DESC')->limit($limit)->select();
        foreach($data as &$value){
            $value['pic_150']=thumb_path($value['pic'],150,150);
            $value['pic_350']=thumb_path($value['pic'],350,350);
            $value['pic_750']=thumb_path($value['pic'],750,750);
        }
        return $data;
    }

}