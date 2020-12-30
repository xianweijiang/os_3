<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\shop;

use service\PHPExcelService;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use app\admin\model\shop\ShopColumn as ColumnModel;
use app\admin\model\order\StoreOrder;
use app\admin\model\system\SystemConfig;

/**
 * 产品管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class ShopProduct extends ModelBasic
{
    use ModelTrait;

    /**删除产品
     * @param $id
     */
    public static function proDelete($id){
//        //删除产品
//        //删除属性
//        //删除秒杀
//        //删除拼团
//        //删除砍价
//        //删除拼团
//        $model=new self();
//        self::beginTrans();
//        $res0 = $model::del($id);
//        $res1 = StoreSeckillModel::where(['product_id'=>$id])->delete();
//        $res2 = StoreCombinationModel::where(['product_id'=>$id])->delete();
//        $res3 = StoreBargainModel::where(['product_id'=>$id])->delete();
//        //。。。。
//        $res = $res0 && $res1 && $res2 && $res3;
//        self::checkTrans($res);
//        return $res;
    }

    /**
     * 获取连表MOdel
     * @param $model
     * @return object
     */
    public static function getModelObject($where=[]){
        $model=new self();
        if(!empty($where)){
            if(isset($where['status']) && $where['status']!=''){
                $model = $model->where('status',$where['status']);
            }
            if(isset($where['store_name']) && $where['store_name']!=''){
                $model = $model->where('store_name|id','LIKE',"%$where[store_name]%");
            }
            if(isset($where['is_on']) && $where['is_on']!=''){
                $model->where('is_on',$where['is_on']);
            }
            if(isset($where['column']) && $where['column']!=''){
                $model = $model->where('column_id',$where['column']);
            }
            if(isset($where['order']) && $where['order']!=''){
                $model = $model->order(self::setOrder($where['order']));
            }else{
                $model = $model->order('id desc');
            }
        }
        return $model;
    }

    /*
     * 获取产品列表
     * @param $where array
     * @return array
     *
     */
    public static function ProductList($where){
        $model=self::getModelObject($where);
        $model=$model->page((int)$where['page'],(int)$where['limit']);
        $data=($data=$model->select()) && count($data) ? $data->toArray():[];
        foreach ($data as &$item){
            $item['column_name'] = ColumnModel::where('id',$item['column_id'])->column('name');
        }
        $count=self::getModelObject($where)->count();
        return compact('count','data');
    }

    public static function getChatrdata($type,$data){
        $legdata=['销量','数量','点赞','收藏'];
        $model=self::setWhereType(self::order('id desc'),$type);
        $list=self::getModelTime(compact('data'),$model)
            ->field('FROM_UNIXTIME(add_time,"%Y-%c-%d") as un_time,count(id) as count,sum(sales) as sales')
            ->group('un_time')
            ->distinct(true)
            ->select()
            ->each(function($item) use($data){
                $item['collect']=self::getModelTime(compact('data'),new StoreProductRelation)->where(['type'=>'collect'])->count();
                $item['like']=self::getModelTime(compact('data'),new StoreProductRelation)->where(['type'=>'like'])->count();
            })->toArray();
        $chatrList=[];
        $datetime=[];
        $data_item=[];
        $itemList=[0=>[],1=>[],2=>[],3=>[]];
        foreach ($list as $item){
            $itemList[0][]=$item['sales'];
            $itemList[1][]=$item['count'];
            $itemList[2][]=$item['like'];
            $itemList[3][]=$item['collect'];
            array_push($datetime,$item['un_time']);
        }
        foreach ($legdata as $key=>$leg){
            $data_item['name']=$leg;
            $data_item['type']='line';
            $data_item['data']=$itemList[$key];
            $chatrList[]=$data_item;
            unset($data_item);
        }
        unset($leg);
        $badge=self::getbadge(compact('data'),$type);
        $count=self::setWhereType(self::getModelTime(compact('data'),new self()),$type)->count();
        return compact('datetime','chatrList','legdata','badge','count');

    }
    //获取 badge 内容
    public static function getbadge($where,$type){
        $StoreOrderModel=new StoreOrder;
        $replenishment_num = SystemConfig::getValue('replenishment_num');
        $replenishment_num = $replenishment_num > 0 ? $replenishment_num : 20;
        $stock1=self::getModelTime($where,new self())->where('stock','<',$replenishment_num)->column('stock');
        $sum_stock=self::where('stock','<',$replenishment_num)->column('stock');
        $stk=[];
        foreach ($stock1 as $item){
            $stk[]=$replenishment_num-$item;
        }
        $lack=array_sum($stk);
        $sum=[];
        foreach ($sum_stock as $val){
            $sum[]=$replenishment_num-$val;
        }
        return [
            [
                'name'=>'商品数量',
                'field'=>'件',
                'count'=>self::setWhereType(new self(),$type)->sum('stock'),
                'content'=>'商品数量总数',
                'background_color'=>'layui-bg-blue',
                'sum'=>self::sum('stock'),
                'class'=>'fa fa fa-ioxhost',
            ],
            [
                'name'=>'新增商品',
                'field'=>'件',
                'count'=>self::setWhereType(self::getModelTime($where,new self),$type)->where('is_new',1)->sum('stock'),
                'content'=>'新增商品总数',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::where('is_new',1)->sum('stock'),
                'class'=>'fa fa-line-chart',
            ],
            [
                'name'=>'活动商品',
                'field'=>'件',
                'count'=>self::getModelTime($where,$StoreOrderModel)->sum('total_num'),
                'content'=>'活动商品总数',
                'background_color'=>'layui-bg-green',
                'sum'=>$StoreOrderModel->sum('total_num'),
                'class'=>'fa fa-bar-chart',
            ],
            [
                'name'=>'缺货商品',
                'field'=>'件',
                'count'=>$lack,
                'content'=>'总商品数量',
                'background_color'=>'layui-bg-orange',
                'sum'=>array_sum($sum),
                'class'=>'fa fa-cube',
            ],
        ];
    }

    /*
     * layui-bg-red 红 layui-bg-orange 黄 layui-bg-green 绿 layui-bg-blue 蓝 layui-bg-cyan 黑
     * 销量排行 top 10
     */
    public static function getMaxList($where,$is_type=0){
        $classs=['layui-bg-red','layui-bg-orange','layui-bg-green','layui-bg-blue','layui-bg-cyan'];
        $model=StoreOrder::alias('a')->join('StoreOrderCartInfo c','a.id=c.oid')->join('__STORE_PRODUCT__ b','b.id=c.product_id');
        $list=self::getModelTime($where,$model,'a.add_time')->where('is_type',$is_type)->group('c.product_id')->order('p_count desc')->limit(10)
            ->field(['count(c.product_id) as p_count','b.store_name','sum(b.price) as sum_price'])->select();
        if(count($list)) $list=$list->toArray();
        $maxList=[];
        $sum_count=0;
        $sum_price=0;
        foreach ($list as $item){
            $sum_count+=$item['p_count'];
            $sum_price=bcadd($sum_price,$item['sum_price'],2);
        }
        unset($item);
        foreach ($list as $key=>&$item){
            $item['w']=bcdiv($item['p_count'],$sum_count,2)*100;
            $item['class']=isset($classs[$key]) ?$classs[$key]:( isset($classs[$key-count($classs)]) ? $classs[$key-count($classs)]:'');
            $item['store_name']=self::getSubstrUTf8($item['store_name']);
        }
        $maxList['sum_count']=$sum_count;
        $maxList['sum_price']=$sum_price;
        $maxList['list']=$list;
        return $maxList;
    }
    //获取利润
    public static function ProfityTop10($where,$is_type=0){
        $classs=['layui-bg-red','layui-bg-orange','layui-bg-green','layui-bg-blue','layui-bg-cyan'];
        $model=StoreOrder::alias('a')->join('StoreOrderCartInfo c','a.id=c.oid')->join('__STORE_PRODUCT__ b','b.id=c.product_id');
        $list=self::getModelTime($where,$model,'a.add_time')->where('is_type',$is_type)->group('c.product_id')->order('profity desc')->limit(10)
            ->field(['count(c.product_id) as p_count','b.store_name','sum(b.price) as sum_price','(b.price-b.cost) as profity'])
            ->select();
        if(count($list)) $list=$list->toArray();
        $maxList=[];
        $sum_count=0;
        $sum_price=0;
        foreach ($list as $item){
            $sum_count+=$item['p_count'];
            $sum_price=bcadd($sum_price,$item['sum_price'],2);
        }
        foreach ($list as $key=>&$item){
            $item['w']=bcdiv($item['sum_price'],$sum_price,2)*100;
            $item['class']=isset($classs[$key]) ?$classs[$key]:( isset($classs[$key-count($classs)]) ? $classs[$key-count($classs)]:'');
            $item['store_name']=self::getSubstrUTf8($item['store_name'],30);
        }
        $maxList['sum_count']=$sum_count;
        $maxList['sum_price']=$sum_price;
        $maxList['list']=$list;
        return $maxList;
    }
    //获取缺货
    public static function getLackList($where){
        $replenishment_num = SystemConfig::getValue('replenishment_num');
        $replenishment_num = $replenishment_num > 0 ? $replenishment_num : 20;
        $list=self::where('stock','<',$replenishment_num)->field(['id','store_name','stock','price'])->page((int)$where['page'],(int)$where['limit'])->order('stock asc')->select();
        if(count($list)) $list=$list->toArray();
        $count=self::where('stock','<',$replenishment_num)->count();
        return ['count'=>$count,'data'=>$list];
    }
    //获取差评
    public static function getnegativelist($where,$is_type=0){
        $list=self::alias('s')->join('StoreProductReply r','s.id=r.product_id')
            ->field('s.id,s.store_name,s.price,count(r.product_id) as count')
            ->page((int)$where['page'],(int)$where['limit'])
            ->where('r.product_score',1)
            ->where('s.is_type',$is_type)
            ->order('count desc')
            ->group('r.product_id')
            ->select();
        if(count($list)) $list=$list->toArray();
        $count=self::alias('s')->join('StoreProductReply r','s.id=r.product_id')->group('r.product_id')->where('r.product_score',1)->count();
        return ['count'=>$count,'data'=>$list];
    }
    public static function TuiProductList(){
        $perd=StoreOrder::alias('s')->join('StoreOrderCartInfo c','s.id=c.oid')
            ->field('count(c.product_id) as count,c.product_id as id')
            ->group('c.product_id')
            ->where('s.status',-1)
            ->order('count desc')
            ->limit(10)
            ->select();
        if(count($perd)) $perd=$perd->toArray();
        foreach ($perd as &$item){
            $item['store_name']=self::where(['id'=>$item['id']])->value('store_name');
            $item['price']=self::where(['id'=>$item['id']])->value('price');
        }
        return $perd;
    }
    //编辑库存
    public static function changeStock($stock,$productId)
    {
        return self::edit(compact('stock'),$productId);
    }


    public static function getTierList($model = null)
    {
        if($model === null) $model = new self();
        return $model->field('id,store_name')->where('is_del',0)->select()->toArray();
    }
    /**
     * 设置查询条件
     * @param array $where
     * @return array
     */
    public static function setWhere($where){
        $time['data']='';
        if(isset($where['start_time']) && $where['start_time']!='' && isset($where['end_time']) && $where['end_time']!=''){
            $time['data']=$where['start_time'].' - '.$where['end_time'];
        }else{
            $time['data']=isset($where['data'])? $where['data']:'';
        }
        $model=self::getModelTime($time, Db::name('store_cart')->alias('a')->join('__STORE_PRODUCT__ b','a.product_id=b.id'),'a.add_time');
        if(isset($where['title']) && $where['title']!=''){
            $model=$model->where('b.store_name|b.id','like',"%$where[title]%");
        }
        if(!empty($where['is_type']) && $where['title']!=0){
            $model=$model->where('a.type','is_zg');
            $model=$model->where('b.is_type',1);
        }
        return $model;
    }
    /**
     * 获取真实销量排行
     * @param array $where
     * @return array
     */
    public static function getSaleslists($where,$is_type=0){
        $data=self::setWhere($where)->where('a.is_pay',1)
            ->group('a.product_id')
            ->where('is_type',$is_type)
            ->field(['sum(a.cart_num) as num_product','b.store_name','b.image','b.price','b.id'])
            ->order('num_product desc')
            ->page((int)$where['page'],(int)$where['limit'])
            ->select();
        $count=self::setWhere($where)->where('a.is_pay',1)->group('a.product_id')->count();
        foreach ($data as &$item){
            $item['sum_price']=bcmul($item['num_product'],$item['price'],2);
        }
        return compact('data','count');
    }
    public static function SaveProductExport($where){
        $list=self::setWhere($where);
        if (!empty($where['is_type'])) $list = $list->where('is_type',$where['is_type']);
        $list = $list->where('a.is_pay',1)
            ->field(['sum(a.cart_num) as num_product','b.store_name','b.image','b.price','b.id'])
            ->order('num_product desc')
            ->group('a.product_id')
            ->select();
        $export=[];
        foreach ($list as $item){
            $export[]=[
                $item['id'],
                $item['store_name'],
                $item['price'],
                bcmul($item['num_product'],$item['price'],2),
                $item['num_product'],
            ];
        }
        PHPExcelService::setExcelHeader(['商品编号','商品名称','商品售价','销售额','销量'])
            ->setExcelTile('产品销量排行','产品销量排行',' 生成时间：'.date('Y-m-d H:i:s',time()))
            ->setExcelContent($export)
            ->ExcelSave();
    }
    /*
     *  单个商品详情的头部查询
     *  $id 商品id
     *  $where 条件
     */
    public static function getProductBadgeList($id,$where,$is_type=0){
        $data['data']=$where;
        $list=self::setWhere($data)
            ->field(['sum(a.cart_num) as num_product','b.id','b.price'])
            ->where('a.is_pay',1)
            ->where('is_type',$is_type)
            ->group('a.product_id')
            ->order('num_product desc')
            ->select();
        //排名
        $ranking=0;
        //销量
        $xiaoliang=0;
        //销售额 数组
        $list_price=[];
        foreach ($list as $key=>$item){
            if($item['id']==$id){
                $ranking=$key+1;
                $xiaoliang=$item['num_product'];
            }
            $value['sum_price']=$item['price']*$item['num_product'];
            $value['id']=$item['id'];
            $list_price[]=$value;
        }
        //排序
        $list_price=self::my_sort($list_price,'sum_price',SORT_DESC);
        //销售额排名
        $rank_price=0;
        //当前销售额
        $num_price=0;
        if($list_price!==false && is_array($list_price)){
            foreach ($list_price as $key=>$item){
                if($item['id']==$id){
                    $num_price=$item['sum_price'];
                    $rank_price=$key+1;
                    continue;
                }
            }
        }
        return [
            [
                'name'=>'销售额排名',
                'field'=>'名',
                'count'=>$rank_price,
                'background_color'=>'layui-bg-blue',
            ],
            [
                'name'=>'销量排名',
                'field'=>'名',
                'count'=>$ranking,
                'background_color'=>'layui-bg-blue',
            ],
            [
                'name'=>'商品销量',
                'field'=>'名',
                'count'=>$xiaoliang,
                'background_color'=>'layui-bg-blue',
            ],
            [
                'name'=>'点赞次数',
                'field'=>'个',
                'count'=>Db::name('store_product_relation')->where('product_id',$id)->where('type','like')->count(),
                'background_color'=>'layui-bg-blue',
            ],
            [
                'name'=>'销售总额',
                'field'=>'元',
                'count'=>$num_price,
                'background_color'=>'layui-bg-blue',
                'col'=>12,
            ],
        ];
    }
    /*
     * 处理二维数组排序
     * $arrays 需要处理的数组
     * $sort_key 需要处理的key名
     * $sort_order 排序方式
     * $sort_type 类型 可不填写
     */
    public static function my_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){
        if(is_array($arrays)){
            foreach ($arrays as $array){
                if(is_array($array)){
                    $key_arrays[] = $array[$sort_key];
                }else{
                    return false;
                }
            }
        }
        if(isset($key_arrays)){
            array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
            return $arrays;
        }
        return false;
    }
    /*
     * 查询单个商品的销量曲线图
     *
     */
    public static function getProductCurve($where){
        $list=self::setWhere($where)
            ->where('a.product_id',$where['id'])
            ->where('a.is_pay',1)
            ->field(['FROM_UNIXTIME(a.add_time,"%Y-%m-%d") as _add_time','sum(a.cart_num) as num'])
            ->group('_add_time')
            ->order('_add_time asc')
            ->select();
        $seriesdata=[];
        $date=[];
        $zoom='';
        foreach ($list as $item){
            $date[]=$item['_add_time'];
            $seriesdata[]=$item['num'];
        }
        if(count($date)>$where['limit']) $zoom=$date[$where['limit']-5];
        return compact('seriesdata','date','zoom');
    }
    /*
     * 查询单个商品的销售列表
     *
     */
    public static function getSalelList($where){
        return self::setWhere($where)
            ->where(['a.product_id'=>$where['id'],'a.is_pay'=>1])
            ->join('user c','c.uid=a.uid')
            ->field(['FROM_UNIXTIME(a.add_time,"%Y-%m-%d") as _add_time','c.nickname','b.price','a.id','a.cart_num as num'])
            ->page((int)$where['page'],(int)$where['limit'])
            ->select();
    }

    /**
    * 专栏统计
    **/
    public static function getColumnSummary($type,$data,$is_type=1){
        $legdata=['销量','点赞','收藏'];
        $model=self::setWhereType(self::order('id desc'),$type,$is_type);
        $list=self::getModelTime(compact('data'),$model)->where('is_type',$is_type)
            ->field('FROM_UNIXTIME(add_time,"%Y-%c-%d") as un_time,count(id) as count,sum(sales) as sales')
            ->group('un_time')
            ->distinct(true)
            ->select()
            ->each(function($item) use($data){
                $item['collect']=self::getModelTime(compact('data'),new StoreProductRelation)->where(['type'=>'collect'])->count();
                $item['like']=self::getModelTime(compact('data'),new StoreProductRelation)->where(['type'=>'like'])->count();
            })->toArray();
        $chatrList=[];
        $datetime=[];
        $data_item=[];
        $itemList=[0=>[],1=>[],2=>[],3=>[]];
        foreach ($list as $item){
            $itemList[0][]=$item['sales'];
            $itemList[1][]=$item['count'];
            $itemList[2][]=$item['like'];
            $itemList[3][]=$item['collect'];
            array_push($datetime,$item['un_time']);
        }
        foreach ($legdata as $key=>$leg){
            $data_item['name']=$leg;
            $data_item['type']='line';
            $data_item['data']=$itemList[$key];
            $chatrList[]=$data_item;
            unset($data_item);
        }
        unset($leg);
        $badge=self::getColumnCount(compact('data'),$type);
        $count=self::setWhereType(self::getModelTime(compact('data'),new self()),$type,$is_type)->where('is_type',$is_type)->count();
        return compact('datetime','chatrList','legdata','badge','count');
    }

    //获取 badge 内容
    public static function getColumnCount($where,$type){
        return [
            [
                'name'=>'专栏种类',
                'field'=>'种',
                'count'=>self::setWhereType(new self(),$type)->where('is_type',1)->where('add_time','<',mktime(0,0,0,date('m'),date('d'),date('Y')))->count(),
                'content'=>'专栏数量',
                'background_color'=>'layui-bg-blue',
                'sum'=>self::sum('stock'),
                'class'=>'fa fa fa-ioxhost',
            ],
//            [
//                'name'=>'新增专栏数量',
//                'field'=>'期',
//                'count'=>self::setWhereType(self::getModelTime($where,new self),$type)->where('is_type',1)->where('is_new',1)->sum('stock'),
//                'content'=>'新增专栏数量',
//                'background_color'=>'layui-bg-cyan',
//                'sum'=>self::where('is_new',1)->sum('stock'),
//                'class'=>'fa fa-line-chart',
//            ],
        ];
    }
}