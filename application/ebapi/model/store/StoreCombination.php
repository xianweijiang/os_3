<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\ebapi\model\store;


use traits\ModelTrait;
use basic\ModelBasic;

/**
 * 拼团model
 * Class StoreCombination
 * @package app\routine\model\store
 */
class StoreCombination extends ModelBasic
{
    use ModelTrait;

    /**
     * @param $where
     * @return array
     */
    public static function get_list($length=10){
        if($post=input('post.')){
            $where=$post['where'];
            $model = new self();
            $model = $model->alias('c');
            $model = $model->join('StoreProduct s','s.id=c.product_id');
            $model = $model->where('c.is_show',1)->where('c.is_del',0)->where('c.start_time','LT',time())->where('c.stop_time','GT',time());
            if(!empty($where['search'])){
                $model = $model->where('c.title','like',"%{$where['search']}%");
                $model = $model->whereOr('s.keyword','like',"{$where['search']}%");
            }
            $model = $model->field('c.*,s.price as product_price');
            if($where['key']){
                if($where['sales']==1){
                    $model = $model->order('c.sales desc');
                }else if($where['sales']==2){
                    $model = $model->order('c.sales asc');
                }
                if($where['price']==1){
                    $model = $model->order('c.price desc');
                }else if($where['price']==2){
                    $model = $model->order('c.price asc');
                }
                if($where['people']==1){
                    $model = $model->order('c.people asc');
                }
                if($where['default']==1){
                    $model = $model->order('c.sort desc,c.id desc');
                }
            }else{
                $model = $model->order('c.sort desc,c.id desc');
            }
            $page=is_string($where['page'])?(int)$where['page']+1:$where['page']+1;
            $list = $model->page($page,$length)->select()->toArray();   
            return ['list'=>$list,'page'=>$page];
        }
    }

    /**
     * 获取所有拼团数据
     * @param int $limit
     * @param int $length
     * @return mixed
     */
    public static function getAll($limit = 0,$length = 0){
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s','s.id=c.product_id');
        $model = $model->field('c.*,s.price as product_price');
        $model = $model->order('c.sort asc,c.id desc');
        $model = $model->where('c.is_show',1);
        $model = $model->where('c.is_del',0);
        $model = $model->where('s.is_del',0);
        $model = $model->where('c.start_time','LT',time());
        $model = $model->where('c.stop_time','GT',time());
        if($limit && $length) $model = $model->limit($limit,$length);
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }
    /**
     * 获取所有拼团数据
     * @param int $limit
     * @param int $length
     * @return mixed
     * @author jiangxw
     */
    public static function getAllNyb($page = 1,$limit = 0){
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s','s.id=c.product_id');
        $model = $model->field('c.id,c.product_id,c.mer_id,c.image,c.title,c.people,c.info,c.price,c.sort,c.sales,c.stock,c.combination,c.postage,s.price as product_price');
        $model = $model->order('c.sort asc,c.id desc');
        $model = $model->where('c.is_show',1);
        $model = $model->where('c.is_del',0);
        $model = $model->where('s.is_del',0);
        $model = $model->where('c.start_time','LT',time());
        $model = $model->where('c.stop_time','GT',time());
        if($page == 0) $page = 1;
        if($limit) $model = $model->limit(($page-1)*$limit, $limit);
        $list = $model->select();
        if($list){
            $urlpath = config('pink_url').'?id=';
            $list->toArray();
            foreach($list as &$value){
                $value['id'] = (string)$value['id'];
                $value['product_id'] = (string)$value['product_id'];
                $value['mer_id'] = (string)$value['mer_id'];
                $value['people'] = (string)$value['people'];
                $value['info'] = (string)$value['info'];
                $value['sort'] = (string)$value['sort'];
                $value['sales'] = (string)$value['sales'];
                $value['stock'] = (string)$value['stock'];
                $value['url'] = $urlpath.$value['id'];
                $value['combination'] = (string)$value['combination'];

                $value['image_500'] = thumb_path($value['image'],500,500);

            }
            unset($value);
            return $list;
        } else{ return [];};
    }

    /*
     * 获取是否有拼团产品
     * */
    public static function getPinkIsOpen()
    {
        return self::alias('c')->join('StoreProduct s','s.id=c.product_id')->where('c.is_show',1)->where('c.is_del',0)
            ->where('c.start_time','LT',time())->where('c.stop_time','GT',time())->count();
    }

    /**
     * 获取一条拼团数据
     * @param $id
     * @return mixed
     */
    public static function getCombinationOne($id){
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s','s.id=c.product_id');
        $model = $model->field('c.*,s.price as product_price');
        $model = $model->where('c.is_show',1);
        $model = $model->where('c.is_del',0);
        $model = $model->where('c.id',$id);
//        $model = $model->where('c.start_time','LT',time());
//        $model = $model->where('c.stop_time','GT',time()-86400);
        $list = $model->find();
        if($list){
            //增加缩略图
            if(!empty($list['image'])){
                $list['image_150'] = thumb_path($list['image'],150,150);
            }
            return $list->toArray();
        }
        else return [];
    }

    /**
     * 获取推荐的拼团产品
     * @return mixed
     */
    public static function getCombinationHost($limit = 0){
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s','s.id=c.product_id');
        $model = $model->field('c.id,c.image,c.price,c.sales,c.title,c.people,s.price as product_price');
        $model = $model->where('c.is_del',0);
        $model = $model->where('c.is_host',1);
        $model = $model->where('c.start_time','LT',time());
        $model = $model->where('c.stop_time','GT',time());
        if($limit) $model = $model->limit($limit);
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }

    /**
     * 修改销量和库存
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public static function decCombinationStock($num,$CombinationId)
    {
        $res = self::where('id',$CombinationId)->where('stock','>=',$num)->dec('stock',$num)->inc('sales',$num)->update();
        return $res;
    }

    /**
     * 增加库存,减少销量
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public static function incCombinationStock($num,$CombinationId)
    {
        $res = self::where('id',$CombinationId)->where('sales','>=',$num)->inc('stock',$num)->dec('sales',$num)->update();
        return $res;
    }

    /**
     * 判断库存是否足够
     * @param $id
     * @param $cart_num
     * @return int|mixed
     */
    public static function getCombinationStock($id,$cart_num){
        $stock = self::where('id',$id)->value('stock');
        return $stock > $cart_num ? $stock : 0;
    }
    /**
     * 获取产品状态
     * @param $id
     * @return mixed
     */
    public static function isValidCombination($id){
        $model = new self();
        $model = $model->where('id',$id);
        $model = $model->where('is_del',0);
        $model = $model->where('is_show',1);
        return $model->count();
    }

}