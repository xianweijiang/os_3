<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-16 13:35:10
 */

namespace app\commonapi\model\certification;

use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

/**
 * 认证类别  model
 * Class CertificationCate
 * @package app\commonapi\model\certification
 */
class CertificationCate extends ModelBasic
{
    use ModelTrait;

    public function catedatums()
    {
        return $this->hasMany('CertificationCateDatum','cate_id');
    }

    public function cateconditions()
    {
        return $this->hasMany('CertificationCateCondition','cate_id');
    }

    public function cateprivileges()
    {
        return $this->hasMany('CertificationCatePrivilege','cate_id');
    }

    /**
     * 获取api认证详情
     * @param array $where
     * @return array
     */
    public static function getApiOne($id,$uid)
    {
        $model = new self;
        $where['p.id']=$id;
        $where['p.status']=1;
        //$where['e.uid']=$uid;
        //$where['e.status']=array('neq',-2);
        $field='p.id as cate_id,p.name,p.desc,p.icon,e.id,e.status';
        $data = $model->alias('p')
            ->join('CertificationEntity e','e.cate_id=p.id and e.status!=-2 and e.uid='.$uid,'LEFT')
            ->field($field)
            ->where($where)
            //->with(['cateconditions.condition','cateprivileges.privilege'])
            ->find();
        $data->cateconditions=$model->with(['cateconditions'])->find($data->cate_id)->cateconditions;
        $data->cateprivileges=$model->with(['cateprivileges.privilege'])->find($data->cate_id)->cateprivileges;
        //认证条件满足情况
        $data['satisfy_status']=true;
        foreach ($data['cateconditions'] as $key => $value) {
            $data['cateconditions'][$key]['condition']['satisfy']=certification_condition($uid,$value['condition']['name'],$value['condition_value']);
            if (!$data['cateconditions'][$key]['condition']['satisfy']['status']) {
                $data['satisfy_status']=false;
            }
        }
        foreach($data['cateprivileges'] as &$value){
            $value['privilege']['icon_150']=thumb_path($value['privilege']['icon'],150,150);
            $value['privilege']['icon_350']=thumb_path($value['privilege']['icon'],350,350);
            $value['privilege']['icon_750']=thumb_path($value['privilege']['icon'],750,750);
        }
        unset($value);
        return $data;
    }
    /**
     * 获取api认证标识显示
     * @param array $where
     * @return array
     */
    public static function getApiIcon($uid)
    {
        $model = new self;
        $where['e.uid']=$uid;
        $where['p.status']=1;
        $where['e.status']=1;
        $field='p.name,p.icon';
        $list = $model->alias('p')
            ->join('CertificationEntity e','e.cate_id=p.id','LEFT')
            ->field($field)
            ->where($where)
            ->order('p.sort DESC')
            ->page(0, 1)->select();
        if (isset($list[0])) {
            return $list[0]['icon'];
        }
        return '';
    }
    /**
     * 获取api类别列表
     * @param array $where
     * @return array
     */
    public static function getApiPage($params)
    {
        if (!isset($params['page'])) {
            $params['page']=0;
        }
        if (!isset($params['page_num'])) {
            $params['page_num']=20;
        }
        if (isset($params['is_read'])) {
            $where['is_read']=$params['is_read'];
        }
        $model = new self;
        $where['p.status']=1;
        //$where['e.uid']=$params['uid'];
        //$where['e.status']=array('neq',-2);

        $field='p.id,p.name,p.desc,p.image,p.icon,e.id as entity_id,e.status,e.reject_note';
        $list = $model->alias('p')
            ->join('CertificationEntity e','e.cate_id=p.id and e.status!=-2 and e.uid='.$params['uid'],'LEFT')
            ->order('p.sort DESC')
            ->field($field)
            ->where($where)
            ->page($params['page'], $params['page_num'])
            ->select();
        foreach($list as &$value){
            $value['image_150']=thumb_path($value['image'],150,150);
            $value['image_350']=thumb_path($value['image'],350,350);
            $value['image_750']=thumb_path($value['image'],750,750);
        }
        unset($value);
        return $list;
    }
    /**
     * 获取api类别列表消息提醒
     * @param array $where
     * @return array
     */
    public static function getApiMsgPage($params)
    {
        if (!isset($params['page'])) {
            $params['page']=0;
        }
        if (!isset($params['page_num'])) {
            $params['page_num']=20;
        }
        if (isset($params['is_read'])) {
            $where['is_read']=$params['is_read'];
        }
        if (isset($params['uid'])) {
            $where['e.uid']=$params['uid'];
        }
        $model = new self;
        $where['p.status']=1;
        $field='p.id,p.name,p.desc,p.icon,e.id as entity_id,e.status,e.reject_note';
        $list = $model->alias('p')
            ->join('CertificationEntity e','e.cate_id=p.id and e.status!=-2 and e.uid='.$params['uid'],'LEFT')
            ->order('p.sort DESC')
            ->field($field)
            ->where($where)
            ->page($params['page'], $params['page_num'])
            ->select();
        return $list;
    }
    /**
     * 获取api类别资料项列表
     * @param array $where
     * @return array
     */
    public static function getApiCateDatumPage($params)
    {
        if (!isset($params['page'])) {
            $params['page']=0;
        }
        if (!isset($params['page_num'])) {
            $params['page_num']=20;
        }
        $model = new self;
        $where['p.status']=1;
        if($params['cate_id'] !== '') $where['cate_id']=$params['cate_id'];
        $field='d.field,d.name,d.input_tips,d.form_type,d.setting';
        $list = $model->alias('p')
            ->join('CertificationCateDatum cd','cd.cate_id=p.id','LEFT')
            ->join('CertificationDatum d','d.id=cd.datum_id','LEFT')
            ->order('d.sort DESC')
            ->field($field)
            ->where($where)
            ->page($params['page'], $params['page_num'])->select();
        return $list;
    }

    /**
     * 获得认证表相关表名
     * @param $table_name
     * @param $with_prefix
     * @return string
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function getTableName($table_name,$with_prefix=false)
    {
        $table_name='certification_table_' . $table_name;
        if($with_prefix){
            return config('database.prefix') . $table_name;
        }else{
            return $table_name;
        }
    }

    /**
     * 获取认证表列名
     * @param $datum_field
     * @return string
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function getColumnName($datum_field)
    {
        //'column_' . 
        return $datum_field;
    }

}