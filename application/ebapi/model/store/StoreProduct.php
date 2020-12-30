<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/12
 */

namespace app\ebapi\model\store;

use app\admin\model\store\StoreProductAttrValue as StoreProductAttrValuemodel;
use app\core\model\SystemUserLevel;
use app\core\model\UserLevel;
use basic\ModelBasic;
use service\SystemConfigService;
use traits\ModelTrait;
use think\Db;

class StoreProduct extends ModelBasic
{
    use  ModelTrait;

    protected function getSliderImageAttr($value)
    {
        return json_decode($value,true)?:[];
    }

    public static function getValidProduct($productId,$field = 'add_time,browse,is_type,cate_id,code_path,cost,description,ficti,give_integral,id,image,is_bargain,is_benefit,is_best,is_del,is_hot,is_new,is_postage,is_seckill,is_show,keyword,mer_id,mer_use,ot_price,postage,price,sales,slider_image,sort,stock,store_info,store_name,unit_name,vip_price,IFNULL(sales,0) + IFNULL(ficti,0) as fsales,strip_num')
    {
        $res=self::where('is_del',0)->where('is_show',1)->where('id',$productId)->field($field)->find();
        if($res){
            $res=$res->toArray();
            if($res['image']){
                $res['image_150']=thumb_path($res['image'],150,150);
                $res['image_350']=thumb_path($res['image'],350,350);
                $res['image_750']=thumb_path($res['image'],750,750);
            }
            $res['seller_back']=get_seller_back_num($res['strip_num']);
            $res['show_seller']=$res['seller_back']==0?false:true;
            $res['score_name']=db('system_rule')->where('flag','buy')->value('name');
        }else{
            $res='该商品已下架！';
        }
        return $res;
    }

    public static function validWhere()
    {
        return self::where('is_del',0)->where('is_show',1)->where('mer_id',0);
    }

    public static function getProductList($data,$uid)
    {
        $sId = $data['sid'];
        $cId = $data['cid'];
        $keyword = $data['keyword'];
        $priceOrder = $data['priceOrder'];
        $salesOrder = $data['salesOrder'];
        $news = $data['news'];
        $first = $data['first'];
        $limit = $data['limit'];
        $recommend_sell=$data['recommend_sell'];
        $model = self::validWhere();
        if($sId){
            $model ->where(function($query) use($sId) {
                $query->whereOr(['cate_id'=>['like',$sId.',%']])
                    ->whereOr(['cate_id'=>['like','%,'.$sId.',%']])
                    ->whereOr(['cate_id'=>['like','%,'.$sId]])
                    ->whereOr(['cate_id'=>$sId]);
            });
        }elseif($cId){
            $sids = StoreCategory::pidBySidList($cId)?:[];
            if($sids){
                $sidsr = [];
                foreach($sids as $v){
                    $sidsr[] = $v['id'];
                }
                $model->where('cate_id','IN',$sidsr);
            }
        }
        if(!empty($keyword)) $model->where('keyword|store_name','LIKE',htmlspecialchars("%$keyword%"));
        if($recommend_sell==1) $model->where('recommend_sell',1);
        if($news!=0) $model->where('is_new',1);
        $model->where('is_type',0);
        $baseOrder = '';
        if($priceOrder) $baseOrder = $priceOrder == 'desc' ? 'price DESC' : 'price ASC';
//        if($salesOrder) $baseOrder = $salesOrder == 'desc' ? 'sales DESC' : 'sales ASC';//真实销量
        if($salesOrder) $baseOrder = $salesOrder == 'desc' ? 'sales DESC' : 'sales ASC';//虚拟销量
        if($baseOrder) $baseOrder .= ', ';
        $model->order($baseOrder.'sort DESC, add_time DESC');
        $list=$model->limit($first,$limit)->field('id,store_name,cate_id,image,IFNULL(sales,0) + IFNULL(ficti,0) as sales,price,stock,strip_num')->select();
        $list=count($list) ? $list->toArray() : [];
        foreach($list as&$value){
            $value['image_150']=thumb_path($value['image'],150,150);
            $value['image_350']=thumb_path($value['image'],350,350);
            $value['image_750']=thumb_path($value['image'],750,750);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return self::setLevelPrice($list,$uid);
    }
    /**
     * @param $keyword
     * @return jiangxw
     */
    public static function getProductListNyb($data, $uid){
        $sId = $data['sid'];
        $cId = $data['cid'];
        $keyword = $data['keyword'];
        $priceOrder = $data['priceOrder'];
        $salesOrder = $data['salesOrder'];
        $news = $data['news'];
        $limit = $data['limit'];
        $page = $data['page'];
        $recommend_sell = $data['recommend_sell'];
        $model = self::validWhere();
        if($sId){
            $model ->where(function($query) use($sId){
                $query->whereOr(['cate_id'=>['like',$sId,',%']])
                    ->whereOr(['cate_id'=>['like','%,'.$sId.',%']])
                    ->whereOr(['cate_id'=>['like','%,'.$sId]])
                    ->whereOr(['cate_id'=>$sId]);
            });
        }elseif($cId){
            $sids = StoreCategory::pidBySidList($cId)?:[];
            if($sids){
                $sidsr = [];
                foreach($sids as $v){
                    $sidsr[] = $v['id'];
                }
                $model->where('cate_id','IN',$sidsr);
            }

        }
        if(!empty($keyword)) $model->where('keyword|store_name','LIKE',htmlspecialchars("%$keyword%"));
        if($recommend_sell==1) $model->where('recommend_sell',1);
        if($news!=0) $model->where('is_new',1);
        $model->where('is_type',0);
        $baseOrder = '';
        if($priceOrder) $baseOrder = $priceOrder == 'desc' ? 'price DESC' : 'price ASC';
//        if($salesOrder) $baseOrder = $salesOrder == 'desc' ? 'sales DESC' : 'sales ASC';//真实销量
        if($salesOrder) $baseOrder = $salesOrder == 'desc' ? 'sales DESC' : 'sales ASC';//虚拟销量
        if($baseOrder) $baseOrder .= ', ';
        $model->order($baseOrder.'sort DESC, add_time DESC');
        if($page == 0) $page=1;
        $list=$model->limit(($page-1)*$limit,$limit)->field('id,store_name,cate_id,image,IFNULL(sales,0) + IFNULL(ficti,0) as sales,price,stock,strip_num,ot_price,unit_name,sort')->select();
        $list=count($list) ? $list->toArray() : [];
        $urlpath = config('product_url').'?id=';
        foreach($list as&$value){
            $value['id']=(string)$value['id'];
            $value['stock']=(string)$value['stock'];
            $value['sort']=(string)$value['sort'];
            //设置web访问url
            $value['url'] = $urlpath.$value['id'];
            $value['image_500']=thumb_path($value['image'],500,500);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=(string)get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return self::setLevelPrice($list,$uid);
    }
    /*
     * 分类搜索
     * @param string $value
     * @return array
     * */
    public static function getSearchStorePage($keyword)
    {
        $model = self::validWhere();
        if(strlen(trim($keyword))) $model = $model->where('store_name|keyword','LIKE',"%$keyword%");
        $list = $model->field('id,store_name,cate_id,image,sales,price,stock,strip_num')->select();
        foreach($list as&$value){
            $value['image_150']=thumb_path($value['image'],150,150);
            $value['image_350']=thumb_path($value['image'],350,350);
            $value['image_750']=thumb_path($value['image'],750,750);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return $list;
    }
    /**
     * 新品产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getNewProduct($field = '*',$limit = 0)
    {
        $model = self::where('is_new',1)->where('is_del',0)->where('mer_id',0)
            ->where('stock','>',0)->where('is_type',0)->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($limit) $model->limit($limit);
        $list=$model->select();
        foreach($list as&$value){
            $value['image_150']=thumb_path($value['image'],150,150);
            $value['image_350']=thumb_path($value['image'],350,350);
            $value['image_750']=thumb_path($value['image'],750,750);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return $list;
    }
    /**
     * 新品产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @author jiangxw
     */
    public static function getNewProductNyb($field = '*',$limit = 0,$page = 1){
        $model = self::where('is_new',1)->where('is_del',0)->where('mer_id',0)
            ->where('stock','>',0)->where('is_type',0)->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($page==0) $page =1;
        if($limit) $model->limit(($page-1)*$limit, $limit);
        $list=$model->select();
        $urlpath = config('product_url').'?id=';
        foreach($list as &$value){
            $value['id']=(string)$value['id'];
            $value['stock']=(string)$value['stock'];
            $value['sort']=(string)$value['sort'];
            $value['url']=$urlpath.$value['id'];
            $value['image_500']=thumb_path($value['image'], 500, 500);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return $list;
    }
    /**
     * 新品产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @author jiangxw
     */
    public static function getMarketProductNyb($field = '*',$limit = 0,$page = 1){
        $model = self::where('is_market',1)->where('is_del',0)->where('mer_id',0)
            ->where('stock','>',0)->where('is_type',0)->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($page == 0) $page =1;
        if($limit) $model->limit(($page-1)*$limit, $limit);
        $list=$model->select();
        $urlpath = config('product_url').'?id=';
        foreach($list as &$value){
            $value['id']=(string)$value['id'];
            $value['stock']=(string)$value['stock'];
            $value['sort']=(string)$value['sort'];
            $value['url']=$urlpath.$value['id'];
            $value['image_500']=thumb_path($value['image'], 500, 500);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return $list;
    }

    /**
     * 热卖产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getHotProduct($field = '*',$limit = 0,$uid=0)
    {
        $model = self::where('is_hot',1)->where('is_del',0)->where('mer_id',0)
            ->where('stock','>',0)->where('is_type',0)->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($limit) $model->limit($limit);
        $list=$model->select();
        foreach($list as&$value){
            $value['image_150']=thumb_path($value['image'],150,150);
            $value['image_350']=thumb_path($value['image'],350,350);
            $value['image_750']=thumb_path($value['image'],750,750);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return self::setLevelPrice($list,$uid);
    }
    /**
     * 热卖产品 nyb
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getHotProductNyb($field = '*', $limit = 0, $page = 1, $uid = 0)
    {
        $model = self::where('is_hot',1)->where('is_del',0)->where('mer_id',0)
            ->where('stock','>',0)->where('is_type',0)->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($page==0) $page =1;
        if($limit) $model->limit(($page-1)*$limit, $limit);
        $list=$model->select();
        $urlpath = config('product_url').'?id=';
        foreach($list as &$value){
            $value['id']=(string)$value['id'];
            $value['stock']=(string)$value['stock'];
            $value['sort']=(string)$value['sort'];
            $value['url']=$urlpath.$value['id'];
            $value['image_500']=thumb_path($value['image'],500,500);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return self::setLevelPrice($list,$uid);
    }

    /**
     * 新人专享 nyb
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @author jiangxw
     */
    public static function getNewUserBenefitNyb($field = '*', $limit = 0, $page = 1, $uid = 0)
    {
        $model = self::where('is_newuser_benefit',1)->where('is_del',0)->where('mer_id',0)
            ->where('stock','>',0)->where('is_type',0)->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($page==0) $page =1;
        if($limit) $model->limit(($page-1)*$limit, $limit);
        $list=$model->select();
        $urlpath = config('product_url').'?id=';
        foreach($list as &$value){
            $value['id']=(string)$value['id'];
            $value['stock']=(string)$value['stock'];
            $value['sort']=(string)$value['sort'];
            $value['url']=$urlpath.$value['id'];
            $value['image_500']=thumb_path($value['image'],500,500);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return self::setLevelPrice($list,$uid);
    }

    /**
     * 热卖产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getHotProductLoading($field = '*',$offset = 0,$limit = 0)
    {
        $model = self::where('is_hot',1)->where('is_del',0)->where('mer_id',0)
            ->where('stock','>',0)->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($limit) $model->limit($offset,$limit);
        $data=$model->select();
        foreach ($data as &$value){
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return $data;
    }

    /**
     * 精品产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBestProduct($field = '*',$limit = 0,$uid=0)
    {
        $model = self::where('is_best',1)->where('is_del',0)->where('mer_id',0)
            ->where('stock','>',0)->where('is_type',0)->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($limit) $model->limit($limit);
        $list=$model->select();
        foreach($list as&$value){
            $value['image_150']=thumb_path($value['image'],150,150);
            $value['image_350']=thumb_path($value['image'],350,350);
            $value['image_750']=thumb_path($value['image'],750,750);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return self::setLevelPrice($list,$uid);
    }
    /**
     * nyb精品产品
     * @param string $field
     * @param int $limit
     * @param bool $isSingle
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBestProductNyb($field = '*',$limit = 0, $page = 1, $uid=0)
    {
        $model = self::where('is_best',1)->where('is_del',0)->where('mer_id',0)
            ->where('stock','>',0)->where('is_type',0)->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($page==0) $page =1;
        if($limit) $model->limit(($page-1)*$limit, $limit);
        $list=$model->select();
        $urlpath = config('product_url').'?id=';
        foreach($list as &$value){
            $value['id']=(string)$value['id'];
            $value['stock']=(string)$value['stock'];
            $value['sort']=(string)$value['sort'];
            $value['url']=$urlpath.$value['id'];
            $value['image_500']=thumb_path($value['image'],500,500);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=(string)get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return self::setLevelPrice($list,$uid);

    }

    /*
     * 设置会员价格
     * @param object | array $list 产品列表
     * @param int $uid 用户uid
     * @return array
     * */
    public static function setLevelPrice($list,$uid,$isSingle=false)
    {
        if(is_object($list)){
            $list= $list->toArray();
        }
        $levelId=UserLevel::getUserLevel($uid);
        if($levelId){
            $discount=UserLevel::getUserLevelInfo($levelId,'discount');
            $discount=bcsub(1,bcdiv($discount,100,2),2);
        }else{
            $discount=SystemUserLevel::getLevelDiscount();
            $discount=bcsub(1,bcdiv($discount,100,2),2);
        }
        //如果不是数组直接执行减去会员优惠金额
        if(!is_array($list))
            //不是会员原价返回
            if($levelId)
                //如果$isSingle==true 返回优惠后的总金额，否则返回优惠的金额
                return $isSingle ? bcsub($list,bcmul($discount,$list,2),2) : bcmul($discount,$list,2);
            else
                return $isSingle ? $list : 0;
        //当$list为数组时$isSingle==true为一维数组 ，否则为二维
        if($isSingle)
            $list['vip_price']=isset($list['price']) ? bcsub($list['price'],bcmul($discount,$list['price'],2),2) : 0;
        else
            foreach ($list as &$item){
                $item['vip_price']=isset($item['price']) ? bcsub($item['price'],bcmul($discount,$item['price'],2),2) : 0;
            }
        return $list;
    }


    /**
     * 优惠产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBenefitProduct($field = '*',$limit = 0)
    {
        $model = self::where('is_benefit',1)
            ->where('is_del',0)->where('is_type',0)->where('mer_id',0)->where('stock','>',0)
            ->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($limit) $model->limit($limit);
        $product=$model->select();
        foreach ($product as &$value){
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return $product;
    }
    /**
     * 优惠产品  特惠
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @author jiangxw
     */
    public static function getBenefitProductNyb($field = '*',$limit = 0,$page = 1)
    {
        $model = self::where('is_benefit',1)
            ->where('is_del',0)->where('is_type',0)->where('mer_id',0)->where('stock','>',0)
            ->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($page==0) $page =1;
        if($limit) $model->limit(($page-1)*$limit, $limit);
        $product=$model->select()->toArray();
        $urlpath = config('product_url').'?id=';
        foreach ($product as &$value){
            $value['id']=(string)$value['id'];
            $value['stock']=(string)$value['stock'];
            $value['sort']=(string)$value['sort'];
            $value['url']=$urlpath.$value['id'];
            $value['image_500']=thumb_path($value['image'],500,500);
            $value['seller_back']=(string)get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return $product;
    }

    public static function cateIdBySimilarityProduct($cateId,$field='*',$limit = 0)
    {
        $pid = StoreCategory::cateIdByPid($cateId)?:$cateId;
        $cateList = StoreCategory::pidByCategory($pid,'id') ?:[];
        $cid = [$pid];
        foreach ($cateList as $cate){
            $cid[] = $cate['id'];
        }
        $model = self::where('cate_id','IN',$cid)->where('is_show',1)->where('is_del',0)
            ->field($field)->order('sort DESC,id DESC');
        if($limit) $model->limit($limit);
        return $model->select();
    }

    public static function isValidProduct($productId)
    {
        return self::be(['id'=>$productId,'is_del'=>0,'is_show'=>1]) > 0;
    }

    public static function getProductStock($productId,$uniqueId = '')
    {
        return  $uniqueId == '' ?
            self::where('id',$productId)->value('stock')?:0
            : StoreProductAttr::uniqueByStock($uniqueId);
    }

    public static function decProductStock($num,$productId,$unique = '')
    {
        if($unique){
            $res = StoreProductAttrValuemodel::decProductAttrStock($productId,$unique,$num);
            $res = $res && self::where('id',$productId)->setInc('sales',$num);
        }else{
            $res = self::where('id',$productId)->where('stock','>=',$num)->dec('stock',$num)->inc('sales',$num)->update();
        }
        return $res;
    }

    /*
     * 减少销量,增加库存
     * @param int $num 增加库存数量
     * @param int $productId 产品id
     * @param string $unique 属性唯一值
     * @return boolean
     * */
    public static function incProductStock($num,$productId,$unique = '')
    {
        if($unique){
            $res = StoreProductAttrValuemodel::incProductAttrStock($productId,$unique,$num);
            $res = $res && self::where('id',$productId)->where('sales','>=',$num)->setDec('sales',$num);
        }else{
            $res = self::where('id',$productId)->where('sales','>=',$num)->inc('stock',$num)->dec('sales',$num)->update();
        }
        return $res;
    }

    public static function getPacketPrice($storeInfo,$productValue)
    {
        $store_brokerage_ratio=SystemConfigService::get('store_brokerage_ratio');
        $store_brokerage_ratio=bcdiv($store_brokerage_ratio,100,2);
        if(count($productValue)){
            $maxPrice=self::getArrayMax($productValue,'price');
            $minPrice=self::getArrayMin($productValue,'price');
            $maxPrice=bcmul($store_brokerage_ratio,$maxPrice,0);
            $minPrice=bcmul($store_brokerage_ratio,$minPrice,0);
            return $minPrice.'~'.$maxPrice;
        }else{
            return bcmul($store_brokerage_ratio,$storeInfo['price'],0);
        }
    }
    /*
     * 获取二维数组中最大的值
     * */
    public static function getArrayMax($arr,$field)
    {
        $temp=[];
        foreach ($arr as $k=>$v){
            $temp[]=$v[$field];
        }
        return max($temp);
    }
    /*
     * 获取二维数组中最小的值
     * */
    public static function getArrayMin($arr,$field)
    {
        $temp=[];
        foreach ($arr as $k=>$v){
            $temp[]=$v[$field];
        }
        return min($temp);
    }

    /**
     * 搜索商品
     */
    public static function searchProduct($keyword,$page=1,$row=10){
        $product=self::where('store_name','like','%'.$keyword.'%')->where('is_type',0)->page($page, $row)->order('add_time desc')->select();
        foreach ($product as &$value){
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        $product['allCount']=self::where('store_name','like','%'.$keyword.'%')->where('is_type',0)->count();
        return $product;
    }

    /**
     * 推荐产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getColumnProduct($field = '*', $size = 4, $page = 1, $orderBy = 'id DESC, sort DESC',$select,$sid=0)
    {
        $field = 'b.mer_id,b.id,b.image,b.store_name,b.price,b.sales+b.ficti sales,b.store_info,a.name,a.type,a.is_read,b.strip_num';
        $ct = Db::name('ColumnText')
                ->where('is_show',1)
                ->order('id desc')
                ->limit(10000000000)//不加这一行代码就有可能出现不是最新的代码
                ->buildSql();//构建查询语句ficti
        $lists =  Db::table($ct.' a')
                ->join('StoreProduct b','a.pid=b.id')
                ->group('a.pid')->fetchSql(true)
                ->where("b.keyword like '%".$select."%' OR b.store_name like '%".$select."%'")
                ->where('b.is_del',0)->where('b.stock','>',0)->where('b.is_column',1)->where('b.is_type',1);
        $lists =  $lists->where('b.is_show',1);
        if (!empty($sid) && !$select) {
            $lists =  $lists->where(" `b`.`cate_id` LIKE '".$sid."' OR `b`.`cate_id` LIKE '".$sid.",%' OR `b`.`cate_id` LIKE '%,".$sid.",%' OR `b`.`cate_id` LIKE '%,".$sid."' ");
        }
        $lists =  $lists->order($orderBy)
                ->field($field)
                ->limit(($page-1)*$size,$size)
                ->select();
        $list=Db::query("$lists");
        foreach($list as &$value){
            $value['image_150']=thumb_path($value['image'],150,150);
            $value['image_350']=thumb_path($value['image'],350,235);
            $value['image_750']=thumb_path($value['image'],750,750);
            //判断是否是秒杀商品
            $value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return $list;
    }

    /* 专栏分销产品列表[推荐好货]
     * @param string $field
     * @param int $limit
     */
    public static function getReColumnProduct( $field = '*', $size = 4, $page = 1, $orderBy = 'id DESC, sort DESC',$select)
		{
			$field = 'b.mer_id,b.id,b.image,b.store_name,b.price,b.sales+b.ficti sales,b.store_info,a.name,a.type,a.is_read,b.strip_num,b.recommend_sell';
			$ct = Db::name('ColumnText')
							->where('is_show',1)
							->order('id desc')
							->limit(10000000000) //不加这一行代码就有可能出现不是最新的代码
							->buildSql(); //构建查询语句ficti
			$lists =  Db::table($ct.' a')
									->join('StoreProduct b','a.pid=b.id')
									->group('a.pid')->fetchSql(true)
									->where("b.keyword like '%".$select."%' OR b.store_name like '%".$select."%'")
									->where('b.is_del',0)
									->where('b.recommend_sell',1)
									->where('b.is_column',1)
									->where('b.is_type',1);
			$lists =  $lists->where('b.is_show',1)
											->order($orderBy)
											->field($field)
											->limit(($page-1)*$size,$size)
											->select();
			$list=Db::query("$lists");
			foreach($list as &$value){
				$value['image_150']=thumb_path($value['image'],150,150);
				$value['image_350']=thumb_path($value['image'],350,235);
				$value['image_750']=thumb_path($value['image'],750,750);
				//判断是否是秒杀商品
				$value['is_seckill']=db('store_seckill')->where(['product_id'=>$value['id'],'stop_time'=>['gt',time()],'status'=>1])->count()?true:false;
				$value['seller_back']=get_seller_back_num($value['strip_num']);
				$value['show_seller']=$value['seller_back']==0?false:true;
			}
			unset($value);
			return $list;
		}

	/**
     * 推荐产品数量
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getColumnCountProduct($select,$sid)
    {
        $lists = self::where('sp.is_column',1)->where('sp.is_type',1)->alias('sp')
            ->where('sp.is_del',0)->where('sp.stock','>',0)
            ->where('sp.is_show',1);
        if (!empty($sid)) {
            $lists =  $lists->where(" `sp`.`cate_id` LIKE '".$sid.",%' OR `sp`.`cate_id` LIKE '%,".$sid.",%' OR `sp`.`cate_id` LIKE '%,".$sid."' ");
        }
        if (!empty($select)) {
            $lists =  $lists->where('sp.keyword','like',"%".$select."%");
        }
        $lists =  $lists->join('ColumnText ct','sp.id=ct.pid')
            ->group('ct.pid')
            ->count();
        return $lists;
    }

		/* 专栏分销产品列表产品数量[推荐好货]
	 	 * @param $select 搜索关键字
	   */
		public static function getReColumnCountProduct($select)
		{
			$lists = self::where('sp.is_column',1)->where('sp.is_type',1)
																						->alias('sp')
																						->where('sp.is_del',0)
																						->where('sp.recommend_sell',1)
																						->where('sp.is_show',1);
			if (!empty($select)) {
				$lists =  $lists->where('sp.keyword','like',"%".$select."%");
			}
			$lists =  $lists->join('ColumnText ct','sp.id=ct.pid')
											->group('ct.pid')
											->count();
			return $lists;
		}

    /**
     * 推荐产品详情
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getContentProduct($gid, $field = '*')
    {
        return self::where('id',$gid)
            ->where('is_show',1)->field($field)
            ->find()->toArray();
    }

    /**
     * 增加浏览量
     */
    public static function setInc_bow($id)
    {
        self::where('id',$id)->setInc('browse',1);
    }


    /**
     * 推荐分销产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getSellerProduct($field = '*',$offset = 0,$limit = 4)
    {
        $model = self::where('recommend_sell',1)->where('is_del',0)->where('mer_id',0)
            ->where('stock','>',0)->where('is_show',1)->field($field)
            ->order('sort DESC, id DESC');
        if($limit) $model->limit($offset,$limit);
        $data=$model->select();
        foreach ($data as &$value){
            $value['image_150']=thumb_path($value['image'],150,150);
            $value['image_350']=thumb_path($value['image'],350,350);
            $value['image_750']=thumb_path($value['image'],750,750);
            $value['seller_back']=get_seller_back_num($value['strip_num']);
            $value['show_seller']=$value['seller_back']==0?false:true;
        }
        unset($value);
        return $data;
    }
    /* 专栏推荐分销产品
     * */
	public static function getColumnSellerProduct($field = '*',$offset = 0,$limit = 4)
	{
		$model = self::where('recommend_sell',1)
								 ->where('is_del',0)
								 ->where('is_type',1)
								 ->where('is_show',1)
								 ->field($field)
								 ->order('sort DESC, id DESC');
		if($limit) $model->limit($offset,$limit);
		$data=$model->select();
		foreach ($data as &$value){
			$value['image_150']=thumb_path($value['image'],150,150);
			$value['image_350']=thumb_path($value['image'],350,350);
			$value['image_750']=thumb_path($value['image'],750,750);
			$value['seller_back']=get_seller_back_num($value['strip_num']);
			$value['show_seller']=$value['seller_back']==0?false:true;
		}
		unset($value);
		return $data;
	}

    /**
		 * 获得期刊显示排序
		 * @param $id 商品(期刊)ID
		 * @return string 排序规则:ASC or DESC
     * */
    public static function getCategoryContentSortStyle($id){
			$categorySortData = self::where('id',$id) -> where('is_type',1) -> find();
			$orderNum = $categorySortData['sort_style'];

			if( $orderNum == 1 ){
				//1:升序(顺序)
				$orderString = 'ASC';
			}elseif( $orderNum == 2 ){
				//2:降序(倒序)
				$orderString = 'DESC';
			}else{
				//默认升序
				$orderString = 'ASC';
			}

			return $orderString;
		}

}