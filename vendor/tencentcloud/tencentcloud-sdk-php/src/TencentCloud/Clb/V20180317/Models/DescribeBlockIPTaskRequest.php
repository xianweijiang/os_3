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
 * @method string getTaskId() 获取ModifyBlockIPList 接口返回的异步任务的ID。
 * @method void setTaskId(string $TaskId) 设置ModifyBlockIPList 接口返回的异步任务的ID。
 */

/**
 *DescribeBlockIPTask请求参数结构体
 */
class DescribeBlockIPTaskRequest extends AbstractModel
{
    /**
     * @var string ModifyBlockIPList 接口返回的异步任务的ID。
     */
    public $TaskId;
    /**
     * @param string $TaskId ModifyBlockIPList 接口返回的异步任务的ID。
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
        if (array_key_exists("TaskId",$param) and $param["TaskId"] !== null) {
            $this->TaskId = $param["TaskId"];
        }
    }
}
