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
namespace TencentCloud\Cat\V20180409\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method array getDetails() 获取拨测失败详情列表
 * @method void setDetails(array $Details) 设置拨测失败详情列表
 * @method array getSummary() 获取拨测失败汇总列表
 * @method void setSummary(array $Summary) 设置拨测失败汇总列表
 * @method string getBeginTime() 获取开始时间
 * @method void setBeginTime(string $BeginTime) 设置开始时间
 * @method string getEndTime() 获取截至时间
 * @method void setEndTime(string $EndTime) 设置截至时间
 * @method string getRequestId() 获取唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 * @method void setRequestId(string $RequestId) 设置唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 */

/**
 *GetReturnCodeHistory返回参数结构体
 */
class GetReturnCodeHistoryResponse extends AbstractModel
{
    /**
     * @var array 拨测失败详情列表
     */
    public $Details;

    /**
     * @var array 拨测失败汇总列表
     */
    public $Summary;

    /**
     * @var string 开始时间
     */
    public $BeginTime;

    /**
     * @var string 截至时间
     */
    public $EndTime;

    /**
     * @var string 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    public $RequestId;
    /**
     * @param array $Details 拨测失败详情列表
     * @param array $Summary 拨测失败汇总列表
     * @param string $BeginTime 开始时间
     * @param string $EndTime 截至时间
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
        if (array_key_exists("Details",$param) and $param["Details"] !== null) {
            $this->Details = [];
            foreach ($param["Details"] as $key => $value){
                $obj = new CatReturnDetail();
                $obj->deserialize($value);
                array_push($this->Details, $obj);
            }
        }

        if (array_key_exists("Summary",$param) and $param["Summary"] !== null) {
            $this->Summary = [];
            foreach ($param["Summary"] as $key => $value){
                $obj = new CatReturnSummary();
                $obj->deserialize($value);
                array_push($this->Summary, $obj);
            }
        }

        if (array_key_exists("BeginTime",$param) and $param["BeginTime"] !== null) {
            $this->BeginTime = $param["BeginTime"];
        }

        if (array_key_exists("EndTime",$param) and $param["EndTime"] !== null) {
            $this->EndTime = $param["EndTime"];
        }

        if (array_key_exists("RequestId",$param) and $param["RequestId"] !== null) {
            $this->RequestId = $param["RequestId"];
        }
    }
}
