<?php
/**
 *
 * @author: cyx<cyx@ourstu.com>
 * @day: 2019/4/12
 */

namespace app\admin\model\com;

use service\PHPExcelService;
use service\JsonService;
use app\commonapi\controller\Sensitive;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use Carbon\Carbon;
use service\UtilService;
use app\admin\model\user\User as UserModel;
use app\admin\model\com\ComForum as ForumModel;
use app\admin\model\com\ComThreadClass as ThreadClassModel;
/**
 * 帖子主题 model
 * Class ComThread
 * @package app\admin\model\com
 */
class ComTopic extends ModelBasic
{
    use ModelTrait;

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 分级排序列表
     * @param null $model
     * @return array
     */
    public static function getCatTierList($model = null)
    {
        if($model === null) $model = new self();
        return UtilService::sortListTier($model->select()->toArray());
    }

    public static function getThreadType($index){
        $index_array=[
            1=>'图文帖子',
            2=>'活动帖',
            3=>'视频贴',
            4=>'资讯帖',
            5=>'活动'
        ];
        return $index_array[$index];
    }

    public static function getStatus($index){
        $index_array = [
            0  => '驳回',
            1  => '审核通过',
            2  => '待审核',
            3  => '草稿箱',
            -1 => '删除',
        ];
        return $index_array[$index];
    }



    /*
     * 获取帖子列表
     * @param $where array
     * @return array
     *
     */
    public static function TopicList($where)
    {
        // trace($where);
        $model = self::getModelObject($where)->field(['*']);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('create_time desc')->select()) && count($data) ? $data->toArray() : [];
        //普通列表
        foreach ($data as &$item){
            $item['title']='#'.$item['title'].'#';
            $item['class']      = db('com_topic_class')->where('id',$item['class_id'])->value('name');
            $item['nickname'] = db('user')->where('uid',$item['uid'])->value('nickname');
            $item['hot']     = $item['is_hot']? '是':'否';
            switch ($item['status']){
                case 1:
                    $item['status_name']='已审核';
                    break;
                case 2:
                    $item['status_name']='未审核';
                    break;
                case -1:
                    $item['status_name']='已删除';
                    break;
            }
            $item['create_time']=time_format($item['create_time']);
            $item['update_time']=time_format($item['update_time']);
            $item['hot_end_time']=time_format($item['hot_end_time']);
        }

        //是导出excel

        $count = self::getModelObject($where)->count();
        return compact('count', 'data');
    }

    /**
     * 获取连表MOdel
     * @param $model
     * @return object
     */
    public static function getModelObject($where = [])
    {
        $model = new self();
        //$model=$model->alias('p')->join('StoreProductAttrValue pav','p.id=pav.product_id','LEFT');
        if (!empty($where)) {
            // data 日期
            $model->where(function($query) use($where){
                switch ($where['data']) {
                    case 'yesterday':
                    case 'today':
                    case 'week':
                    case 'month':
                    case 'year':
                        $query->whereTime('create_time', $where['data']);
                        break;
                    case 'quarter':
                        $start = strtotime(Carbon::now()->startOfQuarter());
                        $end   = strtotime(Carbon::now()->endOfQuarter());
                        $query->whereTime('create_time', 'between', [$start, $end]);
                        break;
                    case '':
                        ;
                        break;
                    default:
                        $between = explode(' - ', $where['data']);
                        $query->whereTime('create_time', 'between', [$between[0], $between[1]]);
                        break;
                }
            });
            if(isset($where['status']) && $where['status']!=''){
                $model = $model->where('status',$where['status']);
            }
            if($where['is_hot'] != ''){
                $model->where('is_hot', $where['is_hot']);
                //$model->where('hot_end_time','>', time());
            }
            if($where['id'] != ''){
                $model->where('id', $where['id']);
            }
            if(isset($where['title']) && $where['title']!=''){
                $model->where('title','LIKE',"%{$where['title']}%");
            }
            if($where['uid']){
                $author_uids = db('user')->where('uid|nickname','LIKE',"%{$where['uid']}%")->column('uid');
                if($author_uids){
                    $model->where('uid', 'in', $author_uids);
                }
            }
            if($where['class']){
                $model->where('class_id', $where['class']);
            }
            if($where['class_id']){
                $model->where('class_id', $where['class_id']);
            }
        }
        return $model;
    }


    public static function add($data)
    {
        $object=self::create($data);
        return $object->getLastInsID();
    }


}