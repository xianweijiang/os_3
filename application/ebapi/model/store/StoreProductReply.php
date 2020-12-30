<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/29
 */

namespace app\ebapi\model\store;


use app\osapi\model\user\UserModel;
use basic\ModelBasic;
use FormBuilder\Json;
use service\UtilService;
use traits\ModelTrait;

class StoreProductReply extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    protected function setPicsAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    protected function getPicsAttr($value)
    {
        return json_decode($value,true);
    }

    public static function reply($group,$type = 'product')
    {
        $group['reply_type'] = $type;
        return self::set($group);
    }

    public static function productValidWhere($alias = '')
    {
        $model = new self;
        if($alias){
            $model->alias($alias);
            $alias .= '.';
        }
        return $model->where("{$alias}is_del",0)->where("{$alias}reply_type",'product');
    }

    public static function productValidWheres($alias = '')
    {
        $model = new self;
        if($alias){
            $model->alias($alias);
            $alias .= '.';
        }
        return $model->where("{$alias}is_del",0);//->where("{$alias}reply_type",'is_zg');
    }

    /*
     * 设置查询产品评论条件
     * @param int $productId 产品id
     * @param string $order 排序方式
     * @return object
     * */
    public static function setProductReplyWhere($productId,$type=0,$alias='A')
    {
        $model = self::productValidWhere($alias)->where('A.product_id',$productId)
            ->field('A.product_score,A.service_score,A.comment,A.merchant_reply_content,A.merchant_reply_time,A.pics,A.add_time,B.nickname,B.avatar,C.cart_info,A.merchant_reply_content')
            ->join('__USER__ B','A.uid = B.uid')
            ->join('__STORE_ORDER_CART_INFO__ C','A.unique = C.unique');
        switch ($type){
            case 1:
                $model=$model->where('A.product_score',5);//好评
                break;
            case 2:
                $model=$model->where('A.product_score',['<',5],['>',2]);//中评
                break;
            case 3:
                $model=$model->where('A.product_score','<',2);//差评
                break;
        }
        return $model;
    }

    public static function setProductReplyWheres($productId,$type=0,$alias='A')
    {
        //认证图标
        //$icon_field=is_icon('B.');
        //'.$icon_field.'B.is_red,
        $model = self::productValidWheres($alias)->where('A.product_id',$productId)
            ->field('A.product_score,A.service_score,A.comment,A.merchant_reply_content,A.merchant_reply_time,A.pics,A.add_time,B.nickname,B.avatar,B.uid,B.exp,C.cart_info,A.merchant_reply_content')
            ->join('__USER__ B','A.uid = B.uid')
            ->join('__STORE_ORDER_CART_INFO__ C','A.unique = C.unique');
        switch ($type){
            case 1:
                $model=$model->where('A.product_score',5);//好评
                break;
            case 2:
                $model=$model->where('A.product_score',['<',5],['>',2]);//中评
                break;
            case 3:
                $model=$model->where('A.product_score','<',2);//差评
                break;
        }
        return $model;
    }

    public static function getProductReplyList($productId,$order = 0,$page = 0,$limit = 8)
    {
        switch ($order){
            case 1:
                $map['product_score']=5;
                break;
            case 2:
                $map['product_score']=['between',[2,4]];
                break;
            case 3:
                $map['product_score']=['lt',2];
                break;
        }
        $map['product_id']=$productId;
        $list =db('store_product_reply')->Where($map)->page((int)$page,(int)$limit)->select();
        foreach ($list as $k=>&$reply){
            $unique=db('store_order_cart_info')->where(['unique'=>$reply['unique']])->field('cart_info')->find();
            $reply['cart_info']=$unique['cart_info'];
            //认证图标
            $icon_field=is_icon('');
            $user=db('user')->where(['uid'=>$reply['uid']])->field('nickname,avatar,'.$icon_field.'is_red')->find();
            $reply['nickname']=mb_substr($user['nickname'],0,1).'*****'.mb_substr($user['nickname'],-1,1);
            $reply['avatar']=$user['avatar'];
            if (isset($user['icon'])) {
                $reply['icon']=$user['icon'];
            }
            $reply['is_red']=$user['is_red'];
            if(!empty($reply['pics'])){
                $reply['pics']=explode(',',$reply['pics']);
            }
        }
        return $list;
    }

    public static function getProductReplyLists($productId,$order = 0,$page = 0,$limit = 8)
    {
        $list = self::setProductReplyWheres($productId,$order)->page((int)$page,(int)$limit)->select()->toArray()?:[];
        foreach ($list as $k=>$reply){
            $list[$k] = self::tidyProductReply($reply);
        }
        return $list;
    }

    public static function tidyProductReply($res)
    {
        $res['cart_info'] = json_decode($res['cart_info'],true)?:[];
        $res['suk'] = isset($res['cart_info']['productInfo']['attrInfo']) ? $res['cart_info']['productInfo']['attrInfo']['suk'] : '';
        $res['nickname'] = UtilService::anonymity($res['nickname']);
        $res['merchant_reply_time'] = date('Y-m-d H:i',$res['merchant_reply_time']);
        $res['add_time'] = date('Y-m-d H:i',$res['add_time']);
        $res['star'] = bcadd($res['product_score'],$res['service_score'],2);
        $res['star'] =bcdiv($res['star'],2,0);
        $res['comment'] = $res['comment'] ? :'此用户没有填写评价';
        unset($res['cart_info']);
        return $res;
    }

    public static function isReply($unique,$reply_type = 'product')
    {
        return self::be(['unique'=>$unique,'reply_type'=>$reply_type]);
    }

    public static function getRecProductReply($productId)
    {
        //认证图标
        $icon_field=is_icon('B.');
        $res = self::productValidWhere('A')->where('A.product_id',$productId)
            ->field('A.product_score,A.service_score,A.comment,A.merchant_reply_content,A.merchant_reply_time,A.pics,A.add_time,B.nickname,B.avatar,B.uid,B.exp,'.$icon_field.'B.is_red,C.cart_info')
            ->join('__USER__ B','A.uid = B.uid')
            ->join('__STORE_ORDER_CART_INFO__ C','A.unique = C.unique')
            ->order('A.add_time DESC,A.product_score DESC, A.service_score DESC, A.add_time DESC')->find();
        if(!$res) return null;
        $res=$res->toArray();
        $res['grade'] = UserModel::cacugrade($res['exp']);
        if($res['avatar']){
            $res['avatar']=get_root_path($res['avatar']);
            $res['avatar_64']=thumb_path($res['avatar'],64,64);
            $res['avatar_128']=thumb_path($res['avatar'],128,128);
            $res['avatar_256']=thumb_path($res['avatar'],256,256);
        }
        return self::tidyProductReply($res);
    }

    public static function productReplyCount($productId)
    {
        $data['sum_count']=self::setProductReplyWhere($productId)->count();
        $data['good_count']=self::setProductReplyWhere($productId,1)->count();
        $data['in_count']=self::setProductReplyWhere($productId,2)->count();
        $data['poor_count']=self::setProductReplyWhere($productId,3)->count();
        $data['reply_chance']=bcdiv($data['good_count'],$data['sum_count'],2);
        $data['reply_star']=bcmul($data['reply_chance'],5,0);
        $data['reply_chance']=bcmul($data['reply_chance'],100,2);
        return $data;
    }

    public static function productReplyCounts($productId)
    {
        $data['sum_count']=self::setProductReplyWheres($productId)->count();
        $data['good_count']=self::setProductReplyWheres($productId,1)->count();
        $data['in_count']=self::setProductReplyWheres($productId,2)->count();
        $data['poor_count']=self::setProductReplyWheres($productId,3)->count();
        if (!empty($data['good_count']) && !empty($data['sum_count'])) {
            $data['reply_chance']=bcdiv($data['good_count'],$data['sum_count'],2);
        }else{
            $data['reply_chance']=0;
        }
        $data['reply_star']=bcmul($data['reply_chance'],5,0);
        $data['reply_chance']=bcmul($data['reply_chance'],100,2);
        return $data;
    }
    static public function getShopId($gid)
    {
        $data['sum_count']=self::setProductReplyWheres($gid)->count();
        $data['good_count']=self::setProductReplyWheres($gid,1)->count();
        $data['in_count']=self::setProductReplyWheres($gid,2)->count();
        $data['poor_count']=self::setProductReplyWheres($gid,3)->count();
        if (!empty($data['good_count']) && !empty($data['sum_count'])) {
            $data['reply_chance']=bcdiv($data['good_count'],$data['sum_count'],2);
        }else{
            $data['reply_chance']=0;
        }
        $data['reply_star']=bcmul($data['reply_chance'],5,0);
        $data['reply_chance']=bcmul($data['reply_chance'],100,2);

        $data['reply_list']=self::setProductReplyWheres($gid)->order('product_score DESC, service_score DESC')->limit(2)->select();
        foreach ($data['reply_list'] as &$val){
            $val['grade'] = UserModel::cacugrade($val['exp']);
        }
        return $data;
    }
}