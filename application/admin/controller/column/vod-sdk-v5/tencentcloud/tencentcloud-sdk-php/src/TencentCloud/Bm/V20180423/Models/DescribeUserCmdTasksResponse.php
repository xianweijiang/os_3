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
namespace TencentCloud\Bm\V20180423\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method integer getTotalCount() 获取脚本任务信息数量
 * @method void setTotalCount(integer $TotalCount) 设置脚本任务信息数量
 * @method array getUserCmdTasks() 获取脚本任务信息列表
 * @method void setUserCmdTasks(array $UserCmdTasks) 设置脚本任务信息列表
 * @method string getRequestId() 获取唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 * @method void setRequestId(string $RequestId) 设置唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 */

/**
 *DescribeUserCmdTasks返回参数结构体
 */
class DescribeUserCmdTasksResponse extends AbstractModel
{
    /**
     * @var integer 脚本任务信息数量
     */
    public $TotalCount;

    /**
     * @var array 脚本任务信息列表
     */
    public $UserCmdTasks;

    /**
     * @var string 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    public $RequestId;
    /**
     * @param integer $TotalCount 脚本任务信息数量
     * @param array $UserCmdTasks 脚本任务信息列表
     * @param string $RequestId 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
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
        if (array_key_exists("TotalCount",$param) and $param["TotalCount"] !== null) {
            $this->TotalCount = $param["TotalCount"];
        }

        if (array_key_exists("UserCmdTasks",$param) and $param["UserCmdTasks"] !== null) {
            $this->UserCmdTasks = [];
            foreach ($param["UserCmdTasks"] as $key => $value){
                $obj = new UserCmdTask();
                $obj->deserialize($value);
                array_push($this->UserCmdTasks, $obj);
            }
        }

        if (array_key_exists("RequestId",$param) and $param["RequestId"] !== null) {
            $this->RequestId = $param["RequestId"];
        }
    }
}
