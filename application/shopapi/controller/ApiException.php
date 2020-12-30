<?php

namespace app\shopapi\controller;


use Exception;
use service\JsonService;
use think\exception\Handle;
use think\exception\ValidateException;

class ApiException extends Basic
{
    public function render(Exception $e){
        //可以在此交由系统处理
        if($this->Debug) return Handle::render($e);
        // 参数验证错误
        if ($e instanceof ValidateException) return JsonService::fail($e->getError(), 422);
        return JsonService::fail('系统错误');
    }
}