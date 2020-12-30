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
namespace TencentCloud\Yunjing\V20180228\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method array getWeeklyReportVuls() 获取专业周报漏洞数据数组。
 * @method void setWeeklyReportVuls(array $WeeklyReportVuls) 设置专业周报漏洞数据数组。
 * @method integer getTotalCount() 获取记录总数。
 * @method void setTotalCount(integer $TotalCount) 设置记录总数。
 * @method string getRequestId() 获取唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 * @method void setRequestId(string $RequestId) 设置唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 */

/**
 *DescribeWeeklyReportVuls返回参数结构体
 */
class DescribeWeeklyReportVulsResponse extends AbstractModel
{
    /**
     * @var array 专业周报漏洞数据数组。
     */
    public $WeeklyReportVuls;

    /**
     * @var integer 记录总数。
     */
    public $TotalCount;

    /**
     * @var string 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    public $RequestId;
    /**
     * @param array $WeeklyReportVuls 专业周报漏洞数据数组。
     * @param integer $TotalCount 记录总数。
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
        if (array_key_exists("WeeklyReportVuls",$param) and $param["WeeklyReportVuls"] !== null) {
            $this->WeeklyReportVuls = [];
            foreach ($param["WeeklyReportVuls"] as $key => $value){
                $obj = new WeeklyReportVul();
                $obj->deserialize($value);
                array_push($this->WeeklyReportVuls, $obj);
            }
        }

        if (array_key_exists("TotalCount",$param) and $param["TotalCount"] !== null) {
            $this->TotalCount = $param["TotalCount"];
        }

        if (array_key_exists("RequestId",$param) and $param["RequestId"] !== null) {
            $this->RequestId = $param["RequestId"];
        }
    }
}
