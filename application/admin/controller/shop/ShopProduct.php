<?php

namespace app\admin\controller\shop;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use traits\CurdControllerTrait;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use app\admin\model\shop\ShopColumn as ColumnModel;
use app\admin\model\shop\ShopProduct as ProductModel;
use think\Url;

use app\admin\model\system\SystemAttachment;


/**
 * 商品管理
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class ShopProduct extends AuthController
{

    use CurdControllerTrait;

    protected $bindModel = ProductModel::class;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {

        $status=$this->request->param('status');
        //获取分类
        $this->assign('column',ColumnModel::getColumnList());
        $onsale =  ProductModel::where('status',1)->count();
        //回收站
        $recycle =  ProductModel::where('status',-1)->count();

        $this->assign(compact('status','onsale','recycle'));
        return $this->fetch();
    }
    /**
     * 异步查找商品
     *
     * @return json
     */
    public function product_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['order',''],
            ['store_name',''],
            ['is_on',''],
            ['column',''],
            ['status',$this->request->param('status')]
        ]);
        return JsonService::successlayui(ProductModel::ProductList($where));
    }
    /**
     * 设置单个商品上架|下架
     *
     * @return json
     */
    public function set_on($is_on='',$id=''){
        ($is_on=='' || $id=='') && JsonService::fail('缺少参数');
        $res=ProductModel::where(['id'=>$id])->update(['is_on'=>(int)$is_on]);
        if($res){
            return JsonService::successful($is_on==1 ? '上架成功':'下架成功');
        }else{
            return JsonService::fail($is_on==1 ? '上架失败':'下架失败');
        }
    }
    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_product($field='',$id='',$value=''){
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(ProductModel::where(['id'=>$id])->update([$field=>$value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
//        $this->assign(['title'=>'添加商品','action'=>Url::build('save'),'rules'=>$this->rules()->getContent()]);
//        return $this->fetch('public/common_form');
        $field = [
            Form::select('column_id','商品栏目')->setOptions(function(){
                $list = ColumnModel::getColumnList();
                $menus=[];
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('store_name','商品名称')->col(Form::col(24)),
            Form::input('store_info','商品简介(50个字以内)')->type('textarea'),
            Form::input('unit_name','商品单位','件'),
            Form::frameImageOne('image','商品主图片(750*750)',Url::build('admin/widget.images/index',array('fodder'=>'image')))->icon('image')->width('100%')->height('500px'),
            Form::frameImages('slider_image','商品轮播图(750*750)',Url::build('admin/widget.images/index',array('fodder'=>'slider_image')))->maxLength(5)->icon('images')->width('100%')->height('500px')->spin(0),
            Form::number('score_price','积分价格')->min(0)->col(8),
            Form::number('cash_price','支付价格')->min(0)->col(8),
            Form::number('postage','邮费')->min(0)->col(Form::col(8)),
            Form::number('sales','销量',0)->min(0)->precision(0)->col(8)->readonly(1),
            Form::number('ficti','虚拟销量')->min(0)->precision(0)->col(8),
            Form::number('limit_num','限购数量')->min(0)->precision(0)->col(8),
            Form::number('stock','库存')->min(0)->precision(0)->col(8),
            Form::number('sort','排序')->col(8),
        ];
        $form = Form::make_post_form('添加商品',$field,Url::build('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 上传图片
     * @return \think\response\Json
     */
    public function upload()
    {
        $res = Upload::image('file','store/product/'.date('Ymd'));
        $thumbPath = Upload::thumb($res->dir);
        //商品图片上传记录
        $fileInfo = $res->fileInfo->getinfo();
        SystemAttachment::attachmentAdd($res->fileInfo->getSaveName(),$fileInfo['size'],$fileInfo['type'],$res->dir,$thumbPath,1);
        if($res->status == 200)
            return Json::successful('图片上传成功!',['name'=>$res->fileInfo->getSaveName(),'url'=>Upload::pathToUrl($thumbPath)]);
        else
            return Json::fail($res->error);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'column_id',
            'store_name',
            'store_info',
            ['unit_name','件'],
            ['image',[]],
            ['slider_image',[]],
            ['score_price',0],
            ['cash_price',0],
            ['limit_num',0],
            ['postage',0],
            ['sort',0],
            ['stock',100],
            'sales',
            ['ficti',100],
        ],$request);
        if(!$data['column_id']) return Json::fail('请选择商品栏目');
        if(!$data['store_name']) return Json::fail('请输入商品名称');
        if(count($data['image'])<1) return Json::fail('请上传商品图片');
        if(count($data['slider_image'])<1) return Json::fail('请上传商品轮播图');
        if($data['stock'] == '' || $data['stock'] < 0) return Json::fail('请输入库存');
        if(mb_strlen($data['store_info'])>50) return Json::fail('简介超过字数限制');
        $data['image'] = $data['image'][0];
        $data['slider_image'] = json_encode($data['slider_image']);
        $data['add_time'] = time();
        $data['status'] = 1;
        $data['description'] = '';
        ProductModel::set($data);
        return Json::successful('添加商品成功!');
    }


    public function edit_content($id){
        if(!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $this->assign([
            'content'=>ProductModel::where('id',$id)->value('description'),
            'field'=>'description',
            'action'=>Url::build('change_field',['id'=>$id,'field'=>'description'])
        ]);
        return $this->fetch('public/edit_content');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $field = [
            Form::select('column_id','商品栏目',(string)$product->getData('column_id'))->setOptions(function(){
                $list = ColumnModel::getColumnList();
                $menus=[];
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('store_name','商品名称',$product->getData('store_name')),
            Form::input('store_info','商品简介(50个字以内)',$product->getData('store_info'))->type('textarea'),
            Form::input('unit_name','商品单位',$product->getData('unit_name')),
            Form::frameImageOne('image','商品主图片(750*750)',Url::build('admin/widget.images/index',array('fodder'=>'image')),$product->getData('image'))->icon('image')->width('100%')->height('500px'),
            Form::frameImages('slider_image','商品轮播图(750*750)',Url::build('admin/widget.images/index',array('fodder'=>'slider_image')),json_decode($product->getData('slider_image'),1))->maxLength(5)->icon('images')->width('100%')->height('500px'),
            Form::number('score_price','积分价格',$product->getData('score_price'))->min(0)->col(8),
            Form::number('cash_price','支付价格',$product->getData('cash_price'))->min(0)->col(8),
            Form::number('postage','邮费',$product->getData('postage'))->min(0)->col(8),
            Form::number('sales','销量',$product->getData('sales'))->min(0)->precision(0)->col(8)->readonly(1),
            Form::number('ficti','虚拟销量',$product->getData('ficti'))->min(0)->precision(0)->col(8),
            Form::number('stock','库存',$product->getData('stock'))->min(0)->precision(0)->col(8),
            Form::number('limit_num','限购数量',$product->getData('limit_num'))->min(0)->col(8),
            Form::number('sort','排序',$product->getData('sort'))->col(8),
        ];
        $form = Form::make_post_form('编辑商品',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }



    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'column_id',
            'store_name',
            'store_info',
            ['unit_name','件'],
            ['image',[]],
            ['slider_image',[]],
            ['score_price',0],
            ['cash_price',0],
            ['limit_num',0],
            ['postage',0],
            ['sort',0],
            ['stock',100],
            'sales',
            ['ficti',100],
        ],$request);
        if(!$data['column_id']) return Json::fail('请选择商品栏目');
        if(!$data['store_name']) return Json::fail('请输入商品名称');
        if(count($data['image'])<1) return Json::fail('请上传商品图片');
        if(count($data['slider_image'])<1) return Json::fail('请上传商品轮播图');
        if($data['stock'] == '' || $data['stock'] < 0) return Json::fail('请输入库存');
        if(mb_strlen($data['store_info'])>50) return Json::fail('简介超过字数限制');
        $data['image'] = $data['image'][0];
        $data['slider_image'] = json_encode($data['slider_image']);
        ProductModel::edit($data,$id);
        return Json::successful('修改成功!');
    }


    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(!$id) return $this->failed('数据不存在');
        if(!ProductModel::be(['id'=>$id])) return $this->failed('商品数据不存在');
        if(ProductModel::be(['id'=>$id,'status'=>-1])){
            $data['status'] = 1;
            if(!ProductModel::edit($data,$id)){
                return Json::fail(ProductModel::getErrorInfo('恢复失败,请稍候再试!'));
            } else{
                return Json::successful('成功恢复商品!');
            }
        }else{
            $data['status'] = -1;
            if(!ProductModel::edit($data,$id)){
                return Json::fail(ProductModel::getErrorInfo('删除失败,请稍候再试!'));
            } else{
                return Json::successful('成功移到回收站!');
            }
        }
    }

    public function delall($id)
    {
        if(!$id) return $this->failed('数据不存在');
        if(!ProductModel::be(['id'=>$id])) return $this->failed('商品数据不存在');
        $res=ProductModel::where('id',$id)->delete();
        if(!$res){
            return Json::successful('删除失败,请稍候再试!');
        }else{
            return Json::successful('成功删除!');
        }
    }


    /**
     * 修改商品库存
     * @param Request $request
     */
    public function edit_product_stock(Request $request){
        $data = Util::postMore([
            ['id',0],
            ['stock',0],
        ],$request);
        if(!$data['id']) return Json::fail('参数错误');
        $res = ProductModel::edit(['stock'=>$data['stock']],$data['id']);
        if($res) return Json::successful('修改成功');
        else return Json::fail('修改失败');
    }



}
