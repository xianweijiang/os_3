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
namespace TencentCloud\Dayu\V20180709\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method array getData() 获取云产品信息列表，如果没有查询到则返回空数组，值说明如下：
Key为ProductName时，value表示云产品实例的名称；
Key为ProductInstanceId时，value表示云产品实例的ID；
Key为ProductType时，value表示的是云产品的类型（cvm表示云主机、clb表示负载均衡）;
Key为IP时，value表示云产品实例的IP；
 * @method void setData(array $Data) 设置云产品信息列表，如果没有查询到则返回空数组，值说明如下：
Key为ProductName时，value表示云产品实例的名称；
Key为ProductInstanceId时，value表示云产品实例的ID；
Key为ProductType时，value表示的是云产品的类型（cvm表示云主机、clb表示负载均衡）;
Key为IP时，value表示云产品实例的IP；
 * @method string getRequestId() 获取唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 * @method void setRequestId(string $RequestId) 设置唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 */

/**
 *DescribeIPProductInfo返回参数结构体
 */
class DescribeIPProductInfoResponse extends AbstractModel
{
    /**
     * @var array 云产品信息列表，如果没有查询到则返回空数组，值说明如下：
Key为ProductName时，value表示云产品实例的名称；
Key为ProductInstanceId时，value表示云产品实例的ID；
Key为ProductType时，value表示的是云产品的类型（cvm表示云主机、clb表示负载均衡）;
Key为IP时，value表示云产品实例的IP；
     */
    public $Data;

    /**
     * @var string 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    public $RequestId;
    /**
     * @param array $Data 云产品信息列表，如果没有查询到则返回空数组，值说明如下：
Key为ProductName时，value表示云产品实例的名称；
Key为ProductInstanceId时，value表示云产品实例的ID；
Key为ProductType时，value表示的是云产品的类型（cvm表示云主机、clb表示负载均衡）;
Key为IP时，value表示云产品实例的IP；
     * @param string $RequestId 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    function __construct()
    {

    }
    /**
     * For internal only. DO NOT USE IT.
     */
    public function deserialize($param)
    {
        if ($param === null) {
            return;
        }
        if (array_key_exists("Data",$param) and $param["Data"] !== null) {
            $this->Data = [];
            foreach ($param["Data"] as $key => $value){
                $obj = new KeyValueRecord();
                $obj->deserialize($value);
                array_push($this->Data, $obj);
            }
        }

        if (array_key_exists("RequestId",$param) and $param["RequestId"] !== null) {
            $this->RequestId = $param["RequestId"];
        }
    }
}
