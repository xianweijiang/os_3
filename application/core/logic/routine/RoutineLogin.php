<?php
/**
 * Created by opensnsx.
 * User: 136327134@qq.com
 * Date: 2019/4/11 9:47
 */

namespace app\core\logic\routine;

use app\core\implement\ProviderInterface;
use service\JsonService;
use think\Request;

class RoutineLogin extends JsonService implements ProviderInterface
{
    public function register($config)
    {

    }

    /*
     * 运行登录
     *
     * */
    public function run()
    {
        $request=Request::instance();
        if($route=$request->routeInfo()){
            var_dump($route);
        }else{

        }
    }



}