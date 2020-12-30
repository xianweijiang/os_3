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
namespace TencentCloud\Tsf\V20180326\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getApplicationId() 获取应用Id
 * @method void setApplicationId(string $ApplicationId) 设置应用Id
 * @method integer getOffset() 获取偏移量，取值从0开始
 * @method void setOffset(integer $Offset) 设置偏移量，取值从0开始
 * @method integer getLimit() 获取分页个数，默认为20， 取值应为1~100
 * @method void setLimit(integer $Limit) 设置分页个数，默认为20， 取值应为1~100
 * @method integer getQueryImageIdFlag() 获取不填和0:查询 1:不查询
 * @method void setQueryImageIdFlag(integer $QueryImageIdFlag) 设置不填和0:查询 1:不查询
 */

/**
 *DescribeImageTags请求参数结构体
 */
class DescribeImageTagsRequest extends AbstractModel
{
    /**
     * @var string 应用Id
     */
    public $ApplicationId;

    /**
     * @var integer 偏移量，取值从0开始
     */
    public $Offset;

    /**
     * @var integer 分页个数，默认为20， 取值应为1~100
     */
    public $Limit;

    /**
     * @var integer 不填和0:查询 1:不查询
     */
    public $QueryImageIdFlag;
    /**
     * @param string $ApplicationId 应用Id
     * @param integer $Offset 偏移量，取值从0开始
     * @param integer $Limit 分页个数，默认为20， 取值应为1~100
     * @param integer $QueryImageIdFlag 不填和0:查询 1:不查询
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

        if (array_key_exists("Offset",$param) and $param["Offset"] !== null) {
            $this->Offset = $param["Offset"];
        }

        if (array_key_exists("Limit",$param) and $param["Limit"] !== null) {
            $this->Limit = $param["Limit"];
        }

        if (array_key_exists("QueryImageIdFlag",$param) and $param["QueryImageIdFlag"] !== null) {
            $this->QueryImageIdFlag = $param["QueryImageIdFlag"];
        }
    }
}
