<?php

namespace app\ebapi\model\system;

use traits\ModelTrait;
use basic\ModelBasic;

class SystemUserLevel extends ModelBasic
{
    use ModelTrait;

    /*
     * 获取某个等级的详细信息
     * @param int $level_id 等级id
     * @return array
     * */
    public static function getUserLevel($level_id)
    {
        return self::valiWhere()->where(['id'=>$level_id])->find();
    }


    /*
     * 设置查询条件
     * @param string $alias 表别名
     * @param object $model 模型类实例化结果
     * @return object
     * */
    public static function valiWhere($alias='',$model=null)
    {
        if(is_null($model)) $model=new self();
        if($alias){
            $model=$model->alias($alias);
            $alias.='.';
        }
        return $model->where(["{$alias}is_show"=>1,"{$alias}is_del"=>0]);
    }

}
