<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/6/4
 * Time: 10:57
 */

namespace app\osapi\model\com;


use app\osapi\model\BaseModel;

class ComThreadClass extends BaseModel
{

    /**
     * 获取版块下分类列表
     * @param $forum_id
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function getThreadClass($forum_id)
    {
        $map['fid']=$forum_id;
        $map['status']=1;
        $threadClass=self::where($map)->order('sort asc')->select()->toArray();
        return $threadClass;
    }

}