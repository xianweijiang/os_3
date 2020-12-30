<?php
/*
 * Copyright (c) 2017-2018 THL A29 Limited, a Tencent company. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace TencentCloud\Iot\V20180123\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getUserName() 获取用户名
 * @method void setUserName(string $UserName) 设置用户名
 * @method string getPassword() 获取密码
 * @method void setPassword(string $Password) 设置密码
 * @method integer getExpire() 获取TTL
 * @method void setExpire(integer $Expire) 设置TTL
 */

/**
 *AppGetToken请求参数结构体
 */
class AppGetTokenRequest extends AbstractModel
{
    /**
     * @var string 用户名
     */
    public $UserName;

    /**
     * @var string 密码
     */
    public $Password;

    /**
     * @var integer TTL
     */
    public $Expire;
    /**
     * @param string $UserName 用户名
     * @param string $Password 密码
     * @param integer $Expire TTL
     */
    function __construct()
    {

    }
    /**
     * 内部实现，用户禁止调用
     */
    public function deserialize($param)
    {
        if ($param === null) {
            return;
        }
        if (array_key_exists("UserName",$param) and $param["UserName"] !== null) {
            $this->UserName = $param["UserName"];
        }

        if (array_key_exists("Password",$param) and $param["Password"] !== null) {
            $this->Password = $param["Password"];
        }

        if (array_key_exists("Expire",$param) and $param["Expire"] !== null) {
            $this->Expire = $param["Expire"];
        }
    }
}
