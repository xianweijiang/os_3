<?php
/**
 * Created by opensnsx.
 * User: 136327134@qq.com
 * Date: 2019/4/12 11:19
 */

namespace app\core\util;


class ApiLogs
{
    //ACCESS_TOKEN缓存前缀
    const ACCESS_TOKEN_PREFIX='AccessToken:';
    //api info 缓存前缀
    const AB_API_INFO='eb_ApiInfo:';

    //缓存时间
    const  EXPIRE=1800;
}