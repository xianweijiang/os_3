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
namespace TencentCloud\Tcaplusdb\V20190823\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getApplicationId() 获取表所在应用实例ID
 * @method void setApplicationId(string $ApplicationId) 设置表所在应用实例ID
 * @method array getSelectedTables() 获取待恢复表信息
 * @method void setSelectedTables(array $SelectedTables) 设置待恢复表信息
 */

/**
 *RecoverRecycleTables请求参数结构体
 */
class RecoverRecycleTablesRequest extends AbstractModel
{
    /**
     * @var string 表所在应用实例ID
     */
    public $ApplicationId;

    /**
     * @var array 待恢复表信息
     */
    public $SelectedTables;
    /**
     * @param string $ApplicationId 表所在应用实例ID
     * @param array $SelectedTables 待恢复表信息
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
        if (array_key_exists("ApplicationId",$param) and $param["ApplicationId"] !== null) {
            $this->ApplicationId = $param["ApplicationId"];
        }

        if (array_key_exists("SelectedTables",$param) and $param["SelectedTables"] !== null) {
            $this->SelectedTables = [];
            foreach ($param["SelectedTables"] as $key => $value){
                $obj = new SelectedTableInfo();
                $obj->deserialize($value);
                array_push($this->SelectedTables, $obj);
            }
        }
    }
}
