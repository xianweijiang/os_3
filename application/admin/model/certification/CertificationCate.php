<?php

/**
 * @Author: shileicheng
 * @Email: 813711465@qq.com
 * @Date:   2019-11-22 15:23:33
 * @Last Modified by:   shileicheng
 * @Last Modified time: 2019-12-07 10:22:25
 */

namespace app\admin\model\certification;

use app\admin\model\group\Group;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use think\Url;

!defined('DATABASE_PREFIX')&&define('DATABASE_PREFIX', config('database.prefix'));
/**
 * 认证类别  model
 * Class CertificationCate
 * @package app\admin\model\certification
 */
class CertificationCate extends ModelBasic
{
    use ModelTrait;

    public function catedatums()
    {
        return $this->hasMany('CertificationCateDatum', 'cate_id');
    }

    public function cateprivileges()
    {
        return $this->hasMany('CertificationCatePrivilege', 'cate_id');
    }

    public function cateconditions()
    {
        return $this->hasMany('CertificationCateCondition', 'cate_id');
    }

    /**
     * 获取指定列表
     * @param array $params
     * @return page
     */
    public static function getAdminPage($params)
    {
        $model = new self;
        if ($params['status'] !== '') $model = $model->where('status', $params['status']);
        if ($params['keyword'] !== '') $model = $model->where('name|id', 'LIKE', "%$params[keyword]%");
        $model = $model->order('sort DESC,id DESC');
        return self::page($model, $params);
    }

    public static function delData($id)
    {
        CertificationEntity::where('cate_id',$id)->delete();
        $table_name=self::find($id)->table_name;
        $sql = "DROP TABLE IF EXISTS `".self::getTableName($table_name,true)."`;";
        Db::execute($sql);
        return self::del($id);
    }


    /**
     * 新增表并创建表名
     * @param $data
     * @return bool|object
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function addCateAddCreateTable($data)
    {
        self::startTrans();
        $res = self::insertGetId($data);
        //添加到认证用户组
        $group['type']=5;
        $group['name']=$data['name'];
        $group['bind_condition']=$res;
        $group['remark']=$data['desc'];
        $resGroup=Group::add_group_new($group);
        if(!$resGroup){
            self::rollbackTrans();
            return false;
        }
        if ($res) {
            $table_name = $data['table_name'];
            $sql = "CREATE TABLE " . self::getTableName($table_name,true) . "(
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `avatar` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '头像',
  `nickname` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `truename` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '电话',
  `status` tinyint(2) DEFAULT '0' COMMENT '状态（0未审核，1审核通过 -1 审核驳回）默认未审核',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `approve_time` int(11) DEFAULT NULL COMMENT '通过时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='认证管理-认证实体表-" . $data['name'] . "';";
            $res = $res && false !== Db::execute($sql);
        }
        self::checkTrans($res);
        return $res;
    }


    /**
     * 变更表拓展字段
     * @param $data
     * @param $id
     * @param $datums
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function updateDatumAndChangeTableColumn($data, $id, $datums)
    {
        $cate = self::get($id);

        $exist_data = $cate->catedatums->toArray();//已经存在的关联数据
        $save_data = ['cate_id' => $id];//
        $del_where = ['cate_id' => $id];//

        $datums = $datums->toArray();
        $datums = array_combine(array_column($datums, 'field'), $datums);
        self::startTrans();
        $res = true;
        foreach ($data as $key => $value) {
            $value = $value[0];
            if ($value) {
                $update_where = [];
                $save_data['create_time'] = time();
                $save_data['datum_id'] = $value;
                foreach ($exist_data as $k => $val) {
                    if ($value == $val['datum_id']) {
                        //更新
                        $update_where['id'] = $val['id'];
                        unset($save_data['create_time']);
                        $save_data['update_time'] = time();
                        $save_data['datum_id'] = $value;
                        break;
                    }
                }
                if ($update_where) {
                    $res = $res && false !== CertificationCateDatum::where($update_where)->update($save_data);
                } else {
                    if (isset($save_data['datum_id'])) {
                        $res = $res && false !== CertificationCateDatum::set($save_data);
                        $sql = "ALTER TABLE " . self::getTableName($cate['table_name'],true) . " ADD " . self::getColumnName($datums[$key]['field']) . " " . self::getColumnType($datums[$key]['form_type']) . " NOT NULL COMMENT '" . $datums[$key]['name'] . "';";
                        $res = $res && false !==  Db::execute($sql);
                    }
                }
            } else {
                foreach ($datums as $ke => $v) {
                    if ($key == $v['field']) {
                        $del_where['datum_id'] = $v['id'];
                        foreach ($exist_data as $k => $val) {
                            if ($v['id'] == $val['datum_id']) {
                                $sql = "ALTER TABLE " . self::getTableName($cate['table_name'],true) . " DROP COLUMN " . self::getColumnName($v['field']) . ";";
                                $res = $res && false !==  Db::execute($sql);
                            }
                        }
                    }
                }
                $res = $res && false !== CertificationCateDatum::where($del_where)->delete();
            }
        }
        self::checkTrans($res);
        return $res;
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
            return DATABASE_PREFIX . $table_name;
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

    /**
     * 获取列数据库类型
     * @param $form_type
     * @return string
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function getColumnType($form_type)
    {
        switch($form_type){
            case 'text':
            case 'checkbox':
            case 'select':
            case 'email':
            case 'mobile':
                $column_type='varchar(100)';
                break;
            case 'file':
                $column_type='varchar(200)';
                break;
            case 'address':
            case 'textarea':
                $column_type='text';
                break;
            case 'number':
            case 'date':
            case 'datetime':
                $column_type='int(11)';
                break;
            case 'floatnumber':
                $column_type='double';
                break;
            default:
                $column_type='varchar(200)';
        }
        return $column_type;
    }
}