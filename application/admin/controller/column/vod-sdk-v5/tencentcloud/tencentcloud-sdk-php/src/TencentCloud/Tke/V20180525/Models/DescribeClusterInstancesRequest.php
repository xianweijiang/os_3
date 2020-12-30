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
namespace TencentCloud\Tke\V20180525\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getClusterId() 获取集群ID
 * @method void setClusterId(string $ClusterId) 设置集群ID
 * @method integer getOffset() 获取偏移量,默认0
 * @method void setOffset(integer $Offset) 设置偏移量,默认0
 * @method integer getLimit() 获取最大输出条数，默认20
 * @method void setLimit(integer $Limit) 设置最大输出条数，默认20
 * @method string getInstanceIds() 获取需要获取的节点实例Id列表(默认为空，表示拉取集群下所有节点实例)
 * @method void setInstanceIds(string $InstanceIds) 设置需要获取的节点实例Id列表(默认为空，表示拉取集群下所有节点实例)
 */

/**
 *DescribeClusterInstances请求参数结构体
 */
class DescribeClusterInstancesRequest extends AbstractModel
{
    /**
     * @var string 集群ID
     */
    public $ClusterId;

    /**
     * @var integer 偏移量,默认0
     */
    public $Offset;

    /**
     * @var integer 最大输出条数，默认20
     */
    public $Limit;

    /**
     * @var string 需要获取的节点实例Id列表(默认为空，表示拉取集群下所有节点实例)
     */
    public $InstanceIds;
    /**
     * @param string $ClusterId 集群ID
     * @param integer $Offset 偏移量,默认0
     * @param integer $Limit 最大输出条数，默认20
     * @param string $InstanceIds 需要获取的节点实例Id列表(默认为空，表示拉取集群下所有节点实例)
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
        if (array_key_exists("ClusterId",$param) and $param["ClusterId"] !== null) {
            $this->ClusterId = $param["ClusterId"];
        }

        if (array_key_exists("Offset",$param) and $param["Offset"] !== null) {
            $this->Offset = $param["Offset"];
        }

        if (array_key_exists("Limit",$param) and $param["Limit"] !== null) {
            $this->Limit = $param["Limit"];
        }

        if (array_key_exists("InstanceIds",$param) and $param["InstanceIds"] !== null) {
            $this->InstanceIds = $param["InstanceIds"];
        }
    }
}
