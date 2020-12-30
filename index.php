<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: honor <rongyao_xu@163.com>
// +----------------------------------------------------------------------
// [ 应用入口文件 ]
header('content-type:application:json;charset=utf8');
//header('Access-Control-Allow-Origin: http://osxfenew.demo.opensns.cn');
header('Access-Control-Allow-Origin: *');//正式部署时换成特定域名，如上
header('Access-Control-Allow-Headers: Access-Token,Authorization, Content-Type, If-Match, Platform-Token,If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
header('Access-Control-Allow-Method: GET, POST, PATCH, PUT, DELETE,OPTIONS');
header('Access-Control-Max-Age:3600');
if($_SERVER['REQUEST_METHOD']=='OPTIONS'){
    exit(true);
}


// 检测PHP环境
if(version_compare(PHP_VERSION,'5.5.9','<'))  die('require PHP > 5.5.9 !');
//error_reporting(E_ALL ^ E_NOTICE);//显示除去 E_NOTICE 之外的所有错误信息
error_reporting(E_ERROR | E_WARNING | E_PARSE);//报告运行时错误
//检测是否已安装opensnsx系统
if(file_exists("./public/install/") && !file_exists("./public/install/install.lock")){
    if($_SERVER['PHP_SELF'] != '/index.php'){
        header("Content-type: text/html; charset=utf-8");
        exit("请在域名根目录下安装,如:<br/> www.xxx.com/index.php 正确 <br/>  www.xxx.com/www/index.php 错误,域名后面不能圈套目录, 但项目没有根目录存放限制,可以放在任意目录,apache虚拟主机配置一下即可");
    }
    header('Location:/public/install/index.php');
    exit();
}
// [ 应用入口文件 ]
// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
// 项目根目录
define('ROOT', __DIR__);
//静态文件目录
define('PUBILC_PATH', '/public/');
//上传文件目录
define('UPLOAD_PATH', 'public/uploads');
try{
    // 加载框架引导文件
    require __DIR__ . '/thinkphp/start.php';
}catch (Exception $e){

    $show_error_detail=false;//是否展示错误详情，开发环境配置为true，生产环境配置为false

    if($show_error_detail){
        throw $e;
    }else{
        //var_dump($e);
        header("Content-type: text/html; charset=utf-8");
        exit($e->getMessage());
    }
}

