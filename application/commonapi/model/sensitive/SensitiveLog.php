<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\commonapi\model\sensitive;

use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;


/**
 * 产品管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class SensitiveLog extends ModelBasic
{
    use ModelTrait;

    /*
     * 获取产品列表
     * @param $where array
     * @return array
     *
     */
    public static function SensitiveLogList($where){
        $model = new self;
        $model = self::getModelObject($where,$model);
        $data=($data=$model->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $item['nickname']=db('user')->where('uid',$item['uid'])->value('nickname');
            $item['content'] = mb_strcut(strip_tags(htmlspecialchars_decode(text($item['content']))),0,180,'utf-8');
            switch($item['level']){
                case 1:
                    $item['level'] = '替换';
                    break;
                case 2:
                    $item['level'] = '删除';
                    break;
                case 3:
                    $item['level'] = '审核';
                    break;
            }
            switch($item['status']){
                case 1:
                    $item['status'] = '已处理';
                    break;
            }
            $item['create_time']=time_format($item['create_time']);
        }
        $count=self::getModelObject($where,$model)->count();
        return compact('count','data');
    }

    public static function getModelObject($where,$model,$aler='',$join=''){
        if($where['data'] !== ''){
            $model = self::getModelTime($where,$model,$aler.'create_time');
        }
        return $model;
    }

}