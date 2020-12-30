<?php
namespace app\ebapi\controller;


use app\core\model\routine\RoutineCode;//待完善
use app\ebapi\model\store\StoreCategory;
use app\ebapi\model\store\StoreOrderCartInfo;
use app\ebapi\model\store\StoreProduct;
use app\ebapi\model\store\StoreProductAttr;
use app\ebapi\model\store\StoreProductRelation;
use app\ebapi\model\store\StoreProductReply;
use app\core\util\GroupDataService;
use service\JsonService;
use app\core\util\SystemConfigService;
use service\UtilService;
use app\core\util\MiniProgramService;

/**
 * 小程序产品和产品分类api接口
 * Class StoreApi
 * @package app\routine\controller
 *
 */
class StoreApi extends AuthController
{

    public static function whiteList()
    {
        return [
            'goods_search',
            'get_routine_hot_search',
            'get_search_referral',
            'get_pid_cate',
            'get_product_category',
            'get_product_category_nyb',
            'get_product_list',
            'get_product_list_nyb',
            'details',
        ];
    }
    /**
     * 分类搜索页面
     * @param Request $request
     * @return \think\response\Json
     */
    public function goods_search()
    {
        list($keyword) = UtilService::getMore([['keyword',0]],null,true);
        return JsonService::successful(StoreProduct::getSearchStorePage($keyword));
    }
    /**
     * 分类页面
     * @param Request $request
     * @return \think\response\Json
     */
    public function store1(Request $request)
    {
        $data = UtilService::postMore([['keyword',''],['cid',''],['sid','']],$request);
        $keyword = addslashes($data['keyword']);
        $cid = intval($data['cid']);
        $sid = intval($data['sid']);
        $category = null;
        if($sid) $category = StoreCategory::get($sid);
        if($cid && !$category) $category = StoreCategory::get($cid);
        $data['keyword'] = $keyword;
        $data['cid'] = $cid;
        $data['sid'] = $sid;
        return JsonService::successful($data);
    }
    /**
     * 一级分类
     * @return \think\response\Json
     */
    public function get_pid_cate(){
        $data = StoreCategory::pidByCategory(0,'id,cate_name');//一级分类
        return JsonService::successful($data);
    }
    /**
     * 二级分类
     * @param Request $request
     * @return \think\response\Json
     */
    public function get_id_cate(Request $request){
        $data = UtilService::postMore([['id',0]],$request);
        $dataCateA = [];
        $dataCateA[0]['id'] = $data['id'];
        $dataCateA[0]['cate_name'] = '全部商品';
        $dataCateA[0]['pid'] = 0;
        $dataCateE = StoreCategory::pidBySidList($data['id']);//根据一级分类获取二级分类
        if($dataCateE) $dataCateE = $dataCateE->toArray();
        $dataCate = [];
        $dataCate = array_merge_recursive($dataCateA,$dataCateE);
        return JsonService::successful($dataCate);
    }
    /**
     * 分类页面产品
     * @param string $keyword
     * @param int $cId
     * @param int $sId
     * @param string $priceOrder
     * @param string $salesOrder
     * @param int $news
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_product_list()
    {
        $data = UtilService::getMore([
            ['sid',0],
            ['cid',0],
            ['keyword',''],
            ['priceOrder',''],
            ['salesOrder',''],
            ['news',0],
            ['first',0],
            ['limit',0],
            ['recommend_sell',0],
            ['type',0]
        ],$this->request);
        return JsonService::successful(StoreProduct::getProductList($data,$this->uid));
    }
    /**
     * 获取单个分类下所有子分类和商品列表
     * @param int $sId
     * @author jiangxw
     */
    public function get_product_list_nyb()
    {
        $data = UtilService::getMore([
            ['sid',0],
            ['cid',0],
            ['keyword',''],
            ['priceOrder',''],
            ['salesOrder',''],
            ['news',0],
            ['page',1],
            ['limit',0],
            ['recommend_sell',0],
            ['type',0]
        ],$this->request);
        return JsonService::successful(StoreProduct::getProductListNyb($data,$this->uid));
    }
    /**
     * 商品详情页
     * @param Request $request
     */
    public function details(){
        $id=osx_input('id',0,'intval');
        if(!$id || !($storeInfo = StoreProduct::getValidProduct($id))) return JsonService::fail('商品不存在或已下架');
        if($storeInfo=='该商品已下架！') return JsonService::fail('商品不存在或已下架');
        $storeInfo['userCollect'] = StoreProductRelation::isProductRelation($id,$this->userInfo['uid'],'collect');
        list($productAttr,$productValue) = StoreProductAttr::getProductAttrDetail($id);
        setView($this->userInfo['uid'],$id,$storeInfo['cate_id'],'viwe');
        $data['storeInfo'] = StoreProduct::setLevelPrice($storeInfo,$this->uid,true);
        $data['similarity'] = StoreProduct::cateIdBySimilarityProduct($storeInfo['cate_id'],'id,store_name,image,price,sales,ficti',4);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        $data['priceName']=StoreProduct::getPacketPrice($storeInfo,$productValue);
        $data['reply'] = StoreProductReply::getRecProductReply($storeInfo['id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id',$storeInfo['id'])->count();
        if($data['replyCount']){
            $goodReply=StoreProductReply::productValidWhere()->where('product_id',$storeInfo['id'])->where('product_score',5)->count();
            $data['replyChance']=bcdiv($goodReply,$data['replyCount'],2);
            $data['replyChance']=bcmul($data['replyChance'],100,3);
        }else $data['replyChance']=0;
        $data['mer_id'] = StoreProduct::where('id',$storeInfo['id'])->value('mer_id');
        if($_SERVER['REQUEST_METHOD']!='OPTIONS'){
            StoreProduct::setInc_bow($id);
        }
        return JsonService::successful($data);
    }

    /*
     * 获取产品是否收藏
     *
     * */
    public function get_product_collect()
    {
        $product_id=osx_input('product_id',0,'intval');
        return JsonService::successful(['userCollect'=>StoreProductRelation::isProductRelation($product_id,$this->userInfo['uid'],'collect')]);
    }
    /**
     * 获取产品评论
     * @return \think\response\Json
     */
    public function get_product_reply(){
        $productId=osx_input('productId',0,'intval');
        if(!$productId) return JsonService::fail('参数错误');
        $replyCount = StoreProductReply::productValidWhere()->where('product_id',$productId)->count();
        $reply = StoreProductReply::getRecProductReply($productId);
        return JsonService::successful(['replyCount'=>$replyCount,'reply'=>$reply]);
    }

    /**
     * 添加点赞
     * @return \think\response\Json
     */
    public function like_product(){
        $productId=osx_input('productId',0,'intval');
        $category=osx_input('category','product','text');
        if(!$productId || !is_numeric($productId))  return JsonService::fail('参数错误');
        $res = StoreProductRelation::productRelation($productId,$this->userInfo['uid'],'like',$category);
        if(!$res) return  JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful();
    }

    /**
     * 取消点赞
     * @return \think\response\Json
     */
    public function unlike_product(){
        $productId=osx_input('productId',0,'intval');
        $category=osx_input('category','product','text');
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $res = StoreProductRelation::unProductRelation($productId,$this->userInfo['uid'],'like',$category);
        if(!$res) return JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful();
    }

    /**
     * 添加收藏
     * @return \think\response\Json
     */
    public function collect_product(){
        $productId=osx_input('productId',0,'intval');
        $category=osx_input('category','product','text');
        $is_zg=osx_input('is_zg',0,'intval');
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $res = StoreProductRelation::productRelation($productId,$this->userInfo['uid'],'collect',$category,$is_zg);
        if(!$res) return JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful();
    }

    /**
     * 批量收藏
     * @return \think\response\Json
     */
    public function collect_product_all(){
        $productId=osx_input('productId',0,'intval');
        $category=osx_input('category','product','text');
        $is_zg=osx_input('is_zg',0,'intval');
        if($productId == '') return JsonService::fail('参数错误');
        $productIdS = explode(',',$productId);
        $res = StoreProductRelation::productRelationAll($productIdS,$this->userInfo['uid'],'collect',$category,$is_zg);
        if(!$res) return JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful('收藏成功');
    }

    /**
     * 取消收藏
     * @return \think\response\Json
     */
    public function uncollect_product(){
        $productId=osx_input('productId',0,'intval');
        $category=osx_input('category','product','text');
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $res = StoreProductRelation::unProductRelation($productId,$this->userInfo['uid'],'collect',$category);
        if(!$res) return JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful();
    }

    /**
     * 获取收藏产品
     * @return \think\response\Json
     */
    public function get_user_collect_product()
    {
        $page=osx_input('page',0,'intval');
        $limit=osx_input('limit',8,'intval');
        $list=StoreProductRelation::getUserCollectProduct($this->uid,$page,$limit);
        $list=array_values($list);
        return JsonService::successful($list);
    }
    /**
     * 获取收藏产品删除
     * @return \think\response\Json
     */
    public function get_user_collect_product_del()
    {
        $pid=osx_input('pid',0,'intval');
        if($pid){
            $list = StoreProductRelation::where('uid',$this->userInfo['uid'])->where('product_id',$pid)->delete();
            return JsonService::successful($list);
        }else
            return JsonService::fail('缺少参数');
    }

    /**
     * 获取订单内的某个产品信息
     * @return \think\response\Json
     */
    public function get_order_product(){
        $unique=osx_input('unique','','text');
        if(!$unique || !StoreOrderCartInfo::be(['unique'=>$unique]) || !($cartInfo = StoreOrderCartInfo::where('unique',$unique)->find())) return JsonService::fail('评价产品不存在!');
        return JsonService::successful($cartInfo);
    }

    /**
     * 获取一级和二级分类
     * @return \think\response\Json
     */
    public function get_product_category()
    {
        return JsonService::successful(StoreCategory::getProductCategory());
    }
    /**
     * app 获取一级二级分类
     * @author jiangxw
     * @return \think\response\Json
     */
    public function get_product_category_nyb(){
        return JsonService::successful(StoreCategory::getProductCategoryNyb());
    }

    /**
     * 获取产品评论
     * @return \think\response\Json
     */
    public function product_reply_list()
    {
        $productId=osx_input('productId','','text');
        $page=osx_input('page',0,'intval');
        $limit=osx_input('limit',8,'intval');
        $type=osx_input('type',0,'text');
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误!');
        $list = StoreProductReply::getProductReplyList($productId,(int)$type,$page,$limit);
        return JsonService::successful($list);
    }

    public function product_reply_list_zg()
    {
        $productId=osx_input('productId','','text');
        $page=osx_input('page',0,'intval');
        $limit=osx_input('limit',8,'intval');
        $type=osx_input('type',0,'text');
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误!');
        $list = StoreProductReply::getProductReplyLists($productId,(int)$type,$page,$limit);
        return JsonService::successful($list);
    }

    /*
     * 获取评论数量和评论好评度
     * @param int $productId
     * @return \think\response\Json
     * */
    public function product_reply_count()
    {
        $productId=osx_input('productId','','text');
        if(!$productId) return JsonService::fail('缺少参数');
        return JsonService::successful(StoreProductReply::productReplyCount($productId));
    }

    public function product_reply_count_zg()
    {
        $productId=osx_input('productId','','text');
        if(!$productId) return JsonService::fail('缺少参数');
        return JsonService::successful(StoreProductReply::productReplyCounts($productId));
    }

    /**
     * 获取商品属性数据
     * @param string $productId
     * @return \think\response\Json
     */
    public function product_attr_detail()
    {
        $productId=osx_input('productId','','text');
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误!');
        list($productAttr,$productValue) = StoreProductAttr::getProductAttrDetail($productId);
        return JsonService::successful(compact('productAttr','productValue'));

    }

    /*
    * 获取产品海报
    * @param int $id 产品id
    * */
    public function poster($id = 0){
//        if(!$id) return JsonService::fail('参数错误');
//        $productInfo = StoreProduct::getValidProduct($id,'store_name,id,price,image,code_path');
//        if(empty($productInfo)) return JsonService::fail('参数错误');
//        if(strlen($productInfo['code_path'])< 10) {
//            $path = 'public'.DS.'uploads'.DS.'codepath'.DS.'product';
//            $codePath = $path.DS.$productInfo['id'].'.jpg';
//            if(!file_exists($codePath)){
//                if(!is_dir($path)) mkdir($path,0777,true);
//                $res = file_put_contents($codePath,RoutineCode::getPages('pages/goods_details/index?id='.$productInfo['id']));
//            }
//            $res = StoreProduct::edit(['code_path'=>$codePath],$id);
//            if($res) $productInfo['code_path'] = $codePath;
//            else return JsonService::fail('没有查看权限');
//        }
//        $posterPath = createPoster($productInfo);
//        return JsonService::successful($posterPath);
    }

    /**
     * 产品海报二维码
     * @param int $id
     */
    public function product_promotion_code(){
        $id=osx_input('id',0,'intval');
        if(!$id) return JsonService::fail('参数错误ID不存在');
        $count = StoreProduct::validWhere()->count();
        if(!$count) return JsonService::fail('参数错误');
        $path = makePathToUrl('routine/codepath/product/',4);
        if($path == '') return JsonService::fail('生成上传目录失败,请检查权限!');
        $codePath = $path.$id.'_'.$this->userInfo['uid'].'_product.jpg';
        $domain = SystemConfigService::get('site_url').'/';
        if(!file_exists($codePath)){
            if(!is_dir($path)) mkdir($path,0777,true);
            $res = RoutineCode::getPageCode('pages/goods_details/index','id='.$id.( $this->userInfo['is_promoter'] ? '&pid='.$this->uid : '') ,280);
            if($res) file_put_contents($codePath,$res);
            else return JsonService::fail('二维码生成失败');
        }
        return JsonService::successful($domain.$codePath);
    }

    /**
     * 热门搜索
     */
    public function get_routine_hot_search(){
        $routineHotSearch = GroupDataService::getData('routine_hot_search') ? :[];
        return JsonService::successful($routineHotSearch);
    }

    /**
     * 主页搜索推荐
     * @author jiangxw
     */
    public function get_search_referral(){
        $searchReferral = GroupDataService::getData('nyb_search_referral') ? : [];
        return JsonService::successful($searchReferral);
    }

    /**
     * 查询查看历史
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.12
     */
    public function getViewList(){
        $page=osx_input('page',1,'intval');
        $view=getViewList($this->userInfo['uid'],$page);
        foreach ($view['data'] as $key=>&$vo){
            $vo['store_data']=StoreProduct::getValidProduct($vo['product_id']);
            if($vo['store_data']=='该商品已下架！'){
                unset($view['data'][$key]);
                $view['count']--;
            }
            $vo['userCollect'] = StoreProductRelation::isProductRelation($vo['product_id'],$this->userInfo['uid'],'collect');
        }
        unset($key,$vo);
        $view['data']=array_values($view['data']);
        if($view){
            JsonService::successful($view);
        }else{
            return JsonService::fail('未查询到数据');
        }
    }

    /**
     * 历史记录删除
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.12
     */
    public function ViewDelete(){
        $views=osx_input('views','','text');
        if($views == '') return JsonService::fail('参数错误');
        $viewIds = explode(',',$views);
        $res = deleteView($viewIds);
        if(!$res) return JsonService::fail('删除失败');
        else return JsonService::successful('删除成功');
    }

    /**
     * 删除收藏
     * @author zxh  zxh@ourstu.com
     *时间：2019.09.12
     */
    public function collectDelete($productId = ''){
        $productId=osx_input('productId','','text');
        if($productId == '') return JsonService::fail('参数错误');
        $productIdS = explode(',',$productId);
        $res=false;
        foreach ($productIdS as $vo){
            $res = StoreProductRelation::unProductRelation($vo,$this->userInfo['uid'],'collect','product');
        }
        if(!$res) return JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful('取消收藏成功');
    }
}