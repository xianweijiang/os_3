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
namespace TencentCloud\Clb\V20180317\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getLocationId() 获取转发规则的 ID
 * @method void setLocationId(string $LocationId) 设置转发规则的 ID
 * @method string getDomain() 获取转发规则的域名
 * @method void setDomain(string $Domain) 设置转发规则的域名
 * @method string getUrl() 获取转发规则的路径。
 * @method void setUrl(string $Url) 设置转发规则的路径。
 * @method array getTargets() 获取后端机器的信息
 * @method void setTargets(array $Targets) 设置后端机器的信息
 */

/**
 *HTTP/HTTPS监听器下的转发规则的机器绑定信息
 */
class RuleTargets extends AbstractModel
{
    /**
     * @var string 转发规则的 ID
     */
    public $LocationId;

    /**
     * @var string 转发规则的域名
     */
    public $Domain;

    /**
     * @var string 转发规则的路径。
     */
    public $Url;

    /**
     * @var array 后端机器的信息
     */
    public $Targets;
    /**
     * @param string $LocationId 转发规则的 ID
     * @param string $Domain 转发规则的域名
     * @param string $Url 转发规则的路径。
     * @param array $Targets 后端机器的信息
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
        if (array_key_exists("LocationId",$param) and $param["LocationId"] !== null) {
            $this->LocationId = $param["LocationId"];
        }

        if (array_key_exists("Domain",$param) and $param["Domain"] !== null) {
            $this->Domain = $param["Domain"];
        }

        if (array_key_exists("Url",$param) and $param["Url"] !== null) {
            $this->Url = $param["Url"];
        }

        if (array_key_exists("Targets",$param) and $param["Targets"] !== null) {
            $this->Targets = [];
            foreach ($param["Targets"] as $key => $value){
                $obj = new Backend();
                $obj->deserialize($value);
                array_push($this->Targets, $obj);
            }
        }
    }
}
