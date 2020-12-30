<?php
/**
 *
 * @author: cyx<cyx@ourstu.com>
 * @day: 2019/4/12
 */

namespace app\admin\model\com;

use service\PHPExcelService;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService;
use app\admin\model\user\User as UserModel;
/**
 * 版块 model
 * Class ComForum
 * @package app\admin\model\com
 */
class ComForum extends ModelBasic
{
    use ModelTrait;

    public const status_texts = [
        -1 => '删除',
        0  => '禁用',
        1  => '启用',
        2  => '待审核'
    ];

    public const need_verify_texts = [
        1 => '全部需要审核',
        2 => '全部不需要审核',
        3 => '先发后审'
    ];

    public const allow_user_group = [
        1 => '注册用户全开放',
        2 => '仅限关注用户',
        3 => '仅限管理员',
    ];

    public const types = [
        1 => '普通版块',
        2 => '动态',
        3 => '朋友圈',
        4 => '资讯',
        5 => '资讯',
        6 => '视频（横版）',
        7 => '小视频（竖屏）',
        8 => '聚合版块',
    ];

    // 发帖权限
    public function getAllowEditRulesTextAttr($value, $data){
        $value = $data['allow_user_group'];
        $value_arr = explode(',', $value);
        $names = [];
        if($value_arr){
            foreach ($value_arr as $index) {
                $names[] = self::allow_user_group[$index];
            }
        }
        return implode('、',$names);
    }

    // 获取版块类型
    public static function getTypeText($data){
        $types = $data['type'];
        $arr = [];
        foreach (explode(',', $types) as $type) {
            $arr[] = self::types[$type];
        }
        return implode('、', $arr);
    }

    /**
     * 分级排序列表
     * @param null $model
     * @return array
     */
    public static function getCatTierList($model = null)
    {
        if($model === null) $model = new self();
        return UtilService::sortListTier($model->where('status',1)->select()->toArray());
    }

    /**
     * 分级排序列表
     * @return array
     */
    public static function getSelectList(){
        $model = new self();
        $list = UtilService::sortListTier($model->select()->toArray());
        $menus = [];
        foreach ($list as $menu){
            $menus[] = ['value'=>$menu['id'],'label'=>$menu['name'],'disabled'=>$menu['pid']== 0];
        }
        return $menus;
    }

    public static function getForumType($index){
        $index_array=[
            1=>'普通版面',
            2=>'微博',
            3=>'朋友圈',
            4=>'资讯',
            5=>'活动',
            6=>'视频横版（PGC为主）',
            7=>'视频竖版（UGC为主）',
            8=>'公告'
        ];
        return $index_array[$index];
    }


    /*
     * 获取版块列表
     * @param $where array
     * @return array
     *
     */
    public static function ForumList($where)
    {
        $model = self::getModelObject($where)->field(['*']);
        if ($where['excel'] == 0) $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('create_time desc')->select()) && count($data) ? $data->toArray() : [];
        //普通列表
        //私密帖子id
        $private_id=ForumPower::get_private_id();
        foreach ($data as &$item){
            $item['pid_name']   =$item['pid'] == 0?'顶级': self::where('id',$item['pid'])->value('name');
            if($item['pid'] == 0){
                $item['private']= $item['group']?'私密':'公开';
            }else{
                $item['private']= in_array($item['id'],$private_id)?'私密':'公开';
            }

            $item['type_name']  = self::getTypeText($item);
            $item['is_hot']     =$item['is_hot'] == 0?'否': '是';
            $item['follow_all']     =$item['false_num']+$item['member_count'];
            $item['admin_html'] = '';
            $item['sub_count']  = self::where('pid', $item['id'])->where('status',1)->count();
            $item['class_count']=ComThreadClass::where('fid', $item['id'])->where('status',1)->count();
            $item['create_time']=time_format($item['create_time']);
            $item['update_time']=time_format($item['update_time']);
            $item['admin']= db('com_forum_admin')->where('status',1)->where('fid',$item['id'])->field('uid,level')->select();
            foreach ($item['admin'] as &$v){
                $v['nickname'] = UserModel::where('uid',$v['uid'])->value('nickname');
            }
        /*    $cateName = CategoryModel::where('id', 'IN', $item['cate_id'])->column('cate_name', 'id');
            $item['cate_name']=is_array($cateName) ? implode(',',$cateName) : '';
            $item['collect'] = StoreProductRelation::where('product_id',$item['id'])->where('type','collect')->count();//收藏
            $item['like'] = StoreProductRelation::where('product_id',$item['id'])->where('type','like')->count();//点赞
            $item['stock'] = self::getStock($item['id'])>0?self::getStock($item['id']):$item['stock'];//库存
            $item['stock_attr'] = self::getStock($item['id'])>0 ? true : false;//库存
            $item['sales_attr'] = self::getSales($item['id']);//属性销量
            $item['visitor'] = Db::name('store_visit')->where('product_id',$item['id'])->where('product_type','product')->count();*/
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
        $model      = new self();
        $time_field = $where['time_field'];
        //$model    = $model->alias('p')->join('StoreProductAttrValue pav','p.id=pav.product_id','LEFT');
        if (!empty($where)) {
            $model->where(function($query) use($where, $time_field){
                switch ($where['data']) {
                    case 'yesterday':
                    case 'today':
                    case 'week':
                    case 'month':
                    case 'year':
                        $query->whereTime($time_field, $where['data']);
                        break;
                    case 'quarter':
                        $start = strtotime(Carbon::now()->startOfQuarter());
                        $end   = strtotime(Carbon::now()->endOfQuarter());
                        $query->whereTime($time_field, 'between', [$start, $end]);
                        break;
                    case '':
                        ;
                        break;
                    default:
                        $between = explode(' - ', $where['data']);
                        $query->whereTime($time_field, 'between', [$between[0], $between[1]]);
                        break;
                }
            });
            if(isset($where['status']) && $where['status']!=''){
                $model->where('status',$where['status']);
            }
            if($where['status']==1 && $where['pid'] ==''){
                $model->where('pid',0);
            }
            if(isset($where['is_hot']) && $where['is_hot']!=''){
                $model->where('is_hot',$where['is_hot']);
            }
            if(isset($where['display']) && $where['display'] != ''){
                $model->where('display', $where['display']);
            }
            if (isset($where['level']) && $where['level'] != '' ) {
                switch ($where['level']) {
                    case 1:
                        $model->where('pid', 0);
                        break;
                    case 2:
                        $model->where('pid', 'in', self::where('pid', 0)->column('id')?:[]);
                        break;
                    default:
                        $model->where('pid', 'in', $model->where('pid', 'in', self::where('pid', 0)->column('id')?:[])->column('id'));
                        break;
                }
            }
            if(isset($where['pid']) &&$where['pid'] !=''){
                $model->where('pid', $where['pid']);
            }
            if(isset($where['name']) && $where['name']!=''){
                $model->where('name|summary','LIKE',"%$where[name]%");
            }
            if (isset($where['order']) && $where['order'] != '') {
                $model->order(self::setOrder($where['order']));
            }
        }
        return $model;
    }

    /**根据cateid查询产品 拼sql语句
     * @param $cateid
     * @return string
     */
    protected static function getCateSql($cateid)
    {
        $lcateid = $cateid . ',%';//匹配最前面的cateid
        $ccatid = '%,' . $cateid . ',%';//匹配中间的cateid
        $ratidid = '%,' . $cateid;//匹配后面的cateid
        return " `cate_id` LIKE '$lcateid' OR `cate_id` LIKE '$ccatid' OR `cate_id` LIKE '$ratidid' OR `cate_id`=$cateid";
    }

    /** 如果有子分类查询子分类获取拼接查询sql
     * @param $cateid
     * @return string
     */
    protected static function getPidSql($cateid)
    {

        $sql = self::getCateSql($cateid);
        $ids = CategoryModel::where('pid', $cateid)->column('id');
        //查询如果有子分类获取子分类查询sql语句
        if ($ids) foreach ($ids as $v) $sql .= " OR " . self::getcatesql($v);
        return $sql;
    }

    /**
     * 条件切割
     * @param string $order
     * @param string $file
     * @return string
     */
    public static function setOrder($order, $file = '-')
    {
        if (empty($order)) return '';
        return str_replace($file, ' ', $order);
    }

    public static function cascader_class(){
        $list  = self::getCatTierList();
        $menus = [];
        $all = [['value'=>0, 'label'=>'全部']];
        foreach ($list as $menu){
            $children = ComThreadClass::where('fid', $menu['id'])->field(['id'=>'value', 'name'=>'label'])->select()?:[];
            $menus[] = [
                'value'    => $menu['id'],
                'label'    => $menu['html'].$menu['name'],
                'disabled' => $menu['pid'] == 0,
                'children' => $menu['pid'] == 0? $all:array_merge($all, collection($children)->toArray())
            ];
        }
        return $menus;
    }
}