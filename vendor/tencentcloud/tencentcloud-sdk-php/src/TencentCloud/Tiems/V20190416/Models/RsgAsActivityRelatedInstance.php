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
namespace TencentCloud\Tiems\V20190416\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getInstanceId() 获取节点 ID
 * @method void setInstanceId(string $InstanceId) 设置节点 ID
 * @method string getInstanceStatus() 获取节点状态
 * @method void setInstanceStatus(string $InstanceStatus) 设置节点状态
 */

/**
 *伸缩组活动关联的节点
 */
class RsgAsActivityRelatedInstance extends AbstractModel
{
    /**
     * @var string 节点 ID
     */
    public $InstanceId;

    /**
     * @var string 节点状态
     */
    public $InstanceStatus;
    /**
     * @param string $InstanceId 节点 ID
     * @param string $InstanceStatus 节点状态
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
        if (array_key_exists("InstanceId",$param) and $param["InstanceId"] !== null) {
            $this->InstanceId = $param["InstanceId"];
        }

        if (array_key_exists("InstanceStatus",$param) and $param["InstanceStatus"] !== null) {
            $this->InstanceStatus = $param["InstanceStatus"];
        }
    }
}
