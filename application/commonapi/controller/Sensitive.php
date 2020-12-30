<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * 脚本控制器
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/22
 * Time: 9:39
 */

namespace app\commonapi\controller;


use basic\ControllerBasic;
use app\commonapi\model\sensitive\Sensitive as SensitiveModel;
use app\commonapi\model\sensitive\SensitiveLog;

class Sensitive extends ControllerBasic
{
    /**
     * @todo 敏感词过滤，返回结果
     * @param array $list  定义敏感词一维数组
     * @param string $string 要过滤的内容
     * @return string $log 处理结果
     */
    public static function sensitive($string,$action){
        $uid=get_uid();
        $count = 0; //违规词的个数
        $sensitiveWord = '';  //违规词
        $stringAfter = $string;  //替换后的内容
        $page=1;
        $row=1000;
        do{
            $list=SensitiveModel::where('status',1)->page($page,$row)->column('sensitive');
            if(!$list){
                if($action=='用户名'||$action=='个性签名'||$action=='后台帖子'){
                    $res['status']=1;
                    $res['word']='';
                    return $res;
                }else{
                    return $stringAfter;
                }
            }
            $pattern = "/".implode("|",$list)."/i"; //定义正则表达式
            if(preg_match_all($pattern, $stringAfter, $matches)){ //匹配到了结果
                $patternList = $matches[0];  //匹配到的数组
                $count = $count+count($patternList);
                $sensitiveWord = $sensitiveWord.' '.implode(',', $patternList); //敏感词数组转字符串
                $replaceArray = array_combine($patternList,array_fill(0,count($patternList),'**')); //把匹配到的数组进行合并，替换使用
                $stringAfter = strtr($stringAfter, $replaceArray); //结果替换
            }
            $page++;
        }while(count($list)==$row);
        if($count==0){
            if($action=='用户名'||$action=='个性签名'||$action=='后台帖子'){
                $res['status']=1;
                $res['word']='';
                return $res;
            }else{
                return $stringAfter;
            }
        }else{
            $data['uid']=$uid;
            $data['sensitive']=$sensitiveWord;
            $data['content']=$string;
            $data['create_time']=time();
            $data['action']=$action;
            $data['level']=1;
            $data['status']=1;
            if($action=='用户名'||$action=='个性签名'||$action=='后台帖子'){
                $res['status']=0;
                $res['word']=$sensitiveWord;
                if($action!='后台帖子'){
                    SensitiveLog::insert($data);
                }
                return $res;
            }else{
                SensitiveLog::insert($data);
                return $stringAfter;
            }
        }
    }

}