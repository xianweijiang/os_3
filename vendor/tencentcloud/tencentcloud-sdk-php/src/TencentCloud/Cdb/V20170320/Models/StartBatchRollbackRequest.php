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
namespace TencentCloud\Cdb\V20170320\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method array getInstances() 获取用于回档的实例详情信息。
 * @method void setInstances(array $Instances) 设置用于回档的实例详情信息。
 */

/**
 *StartBatchRollback请求参数结构体
 */
class StartBatchRollbackRequest extends AbstractModel
{
    /**
     * @var array 用于回档的实例详情信息。
     */
    public $Instances;
    /**
     * @param array $Instances 用于回档的实例详情信息。
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
        if (array_key_exists("Instances",$param) and $param["Instances"] !== null) {
            $this->Instances = [];
            foreach ($param["Instances"] as $key => $value){
                $obj = new RollbackInstancesInfo();
                $obj->deserialize($value);
                array_push($this->Instances, $obj);
            }
        }
    }
}
