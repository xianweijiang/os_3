<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-12 09:25:48
 */

namespace app\admin\model\certification;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

use think\Config;
use think\Db;
use think\Model;
use think\Request;
use think\Validate;

/**
 * 资料项  model
 * Class CertificationDatum
 * @package app\admin\model\certification
 */
class CertificationDatum extends ModelBasic
{
    use ModelTrait;
    
    public static function setTypeIdAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function types()
    {
        return $this->hasMany('CertificationType','id','type_id');
    }
    /**
     * 获取限定条件集合
     * @param array $where
     * @return array
     */
    public static function getList($where)
    {
        $model = new self;
        $list = $model->where('status',1)->where($where)->order('sort DESC,id DESC')->select();
        return $list;
    }
    /**
     * 获取指定列表
     * @param array $params
     * @return page
     */
    public static function getAdminPage($params,$ajax)
    {
        $model = self::getModelObject($params)->field(['*']);
        if ($ajax) {
            $model=$model->page((int)$params['page'],(int)$params['limit']);
            $data=($data=$model->order('id DESC')->select()) && count($data) ? $data->toArray():[];
            foreach ($data as $key => $value) {
               $data[$key]['type_id']= getCertificationType($value['type_id']);
            }
            $count=self::getModelObject($params)->count();
            return compact('count','data');
        }
        $model = $model->order('sort DESC,id DESC');
        return self::page($model,$params);
    }

    public static function getModelObject($params = [])
    {
        $model = new self();
        if (!empty($params)) {
            $model = new self;
            if($params['status'] !== '') $model = $model->where('status',$params['status']);
            if($params['keyword'] !== '') $model = $model->where('name|id|field','LIKE',"%$params[keyword]%");
        }
        return $model;
    }

    public static function delData($id)
    {
        return self::del($id);
    }

    /**
     * 编辑属性时，检测属性类型变更
     * @param $data
     * @param $id
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function editDataAddCheckType($data,$id)
    {
        $old_cate_info=self::get($id);
        self::startTrans();
        $res=true;
        if(CertificationCate::getColumnType($old_cate_info['form_type'])!=CertificationCate::getColumnType($data['form_type'])){
            $cateList=CertificationCate::select()->toArray();
            foreach ($cateList as $oneCate){
                $tableList = Db::query('SHOW TABLES LIKE "'.CertificationCate::getTableName($oneCate['table_name'],true).'"');
                if(count($tableList)>0){
                    $table_info = Db::name(CertificationCate::getTableName($oneCate['table_name']))->getTableInfo();
                    foreach ($table_info['fields'] as $oneField){
                        if($oneField==CertificationCate::getColumnName($old_cate_info['field'])){
                            if(CertificationCate::getColumnType($data['form_type'])!==$table_info['type'][$oneField]){
                                $comment=self::where('field',$old_cate_info['field'])->value('name');
                                $sql = "ALTER TABLE " . CertificationCate::getTableName($oneCate['table_name'],true) . " MODIFY COLUMN " . $oneField . " " . CertificationCate::getColumnType($data['form_type']) . " NOT NULL COMMENT '" . $comment . "';";
                                $res = $res && false !==  Db::execute($sql);
                            }
                            break;
                        }
                    }
                    unset($oneField);
                }
            }
            unset($oneCate);
        }
        $res = $res && false !== self::edit($data,$id);
        self::checkTrans($res);
        return $res;
    }

}