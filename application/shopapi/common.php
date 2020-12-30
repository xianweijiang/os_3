<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 *  设置浏览信息
 * @param $uid
 * @param int $product_id
 * @param int $cate
 * @param string $type
 * @param string $content
 * @param int $min
 */
function setView($uid,$product_id=0,$cate=0,$type='',$content='',$min=20){
    $Db=think\Db::name('store_visit');
    $view=$Db->where(['uid'=>$uid,'product_id'=>$product_id])->field('count,add_time,id')->find();
    if($view && $type!='search'){
        $time=time();
        if(($view['add_time']+$min)<$time){
            $Db->where(['id'=>$view['id']])->update(['count'=>$view['count']+1,'status'=>1,'add_time'=>time()]);
        }
    }else{
        $cate = explode(',',$cate)[0];
        $Db->insert([
            'add_time'=>time(),
            'count'=>1,
            'product_id'=>$product_id,
            'cate_id'=>$cate,
            'type'=>$type,
            'uid'=>$uid,
            'content'=>$content,
            'status'=>1
        ]);
    }
}

/**
 * 获得用户的浏览记录
 * @param $uid
 * @param int $page
 * @return array
 * @author zxh  zxh@ourstu.com
 *时间：2019.09.12
 */
function getViewList($uid,$page=1){
    $Db=think\Db::name('store_visit');
    $view=$Db->where(['uid'=>$uid,'status'=>1])->page($page,50)->order('add_time desc')->field('product_id,count,add_time,id')->select();
    $count=$Db->where(['uid'=>$uid,'status'=>1])->count();
    return array('data'=>$view,'count'=>$count);
}

/**
 * 删除历史记录
 * @param $ids
 * @return int|string
 * @author zxh  zxh@ourstu.com
 *时间：2019.09.12
 */
function deleteView($ids){
    $Db=think\Db::name('store_visit');
    return $Db->where(['id'=>['in',$ids]])->update(['status'=>-1]);
}