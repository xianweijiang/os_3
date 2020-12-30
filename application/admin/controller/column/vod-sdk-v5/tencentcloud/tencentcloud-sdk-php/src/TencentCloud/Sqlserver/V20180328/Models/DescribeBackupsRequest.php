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
namespace TencentCloud\Sqlserver\V20180328\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getStartTime() 获取开始时间(yyyy-MM-dd HH:mm:ss)
 * @method void setStartTime(string $StartTime) 设置开始时间(yyyy-MM-dd HH:mm:ss)
 * @method string getEndTime() 获取结束时间(yyyy-MM-dd HH:mm:ss)
 * @method void setEndTime(string $EndTime) 设置结束时间(yyyy-MM-dd HH:mm:ss)
 * @method string getInstanceId() 获取实例ID，形如mssql-njj2mtpl
 * @method void setInstanceId(string $InstanceId) 设置实例ID，形如mssql-njj2mtpl
 * @method integer getLimit() 获取分页返回，每页返回数量，默认为20，最大值为 100
 * @method void setLimit(integer $Limit) 设置分页返回，每页返回数量，默认为20，最大值为 100
 * @method integer getOffset() 获取偏移量，默认为 0
 * @method void setOffset(integer $Offset) 设置偏移量，默认为 0
 */

/**
 *DescribeBackups请求参数结构体
 */
class DescribeBackupsRequest extends AbstractModel
{
    /**
     * @var string 开始时间(yyyy-MM-dd HH:mm:ss)
     */
    public $StartTime;

    /**
     * @var string 结束时间(yyyy-MM-dd HH:mm:ss)
     */
    public $EndTime;

    /**
     * @var string 实例ID，形如mssql-njj2mtpl
     */
    public $InstanceId;

    /**
     * @var integer 分页返回，每页返回数量，默认为20，最大值为 100
     */
    public $Limit;

    /**
     * @var integer 偏移量，默认为 0
     */
    public $Offset;
    /**
     * @param string $StartTime 开始时间(yyyy-MM-dd HH:mm:ss)
     * @param string $EndTime 结束时间(yyyy-MM-dd HH:mm:ss)
     * @param string $InstanceId 实例ID，形如mssql-njj2mtpl
     * @param integer $Limit 分页返回，每页返回数量，默认为20，最大值为 100
     * @param integer $Offset 偏移量，默认为 0
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
        if (array_key_exists("StartTime",$param) and $param["StartTime"] !== null) {
            $this->StartTime = $param["StartTime"];
        }

        if (array_key_exists("EndTime",$param) and $param["EndTime"] !== null) {
            $this->EndTime = $param["EndTime"];
        }

        if (array_key_exists("InstanceId",$param) and $param["InstanceId"] !== null) {
            $this->InstanceId = $param["InstanceId"];
        }

        if (array_key_exists("Limit",$param) and $param["Limit"] !== null) {
            $this->Limit = $param["Limit"];
        }

        if (array_key_exists("Offset",$param) and $param["Offset"] !== null) {
            $this->Offset = $param["Offset"];
        }
    }
}
