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
namespace TencentCloud\Tag\V20180813\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getTagKey() 获取标签键,与标签值同时存在或同时不存在，不存在时表示查询该用户所有标签
 * @method void setTagKey(string $TagKey) 设置标签键,与标签值同时存在或同时不存在，不存在时表示查询该用户所有标签
 * @method string getTagValue() 获取标签值,与标签键同时存在或同时不存在，不存在时表示查询该用户所有标签
 * @method void setTagValue(string $TagValue) 设置标签值,与标签键同时存在或同时不存在，不存在时表示查询该用户所有标签
 * @method integer getOffset() 获取数据偏移量，默认为 0, 必须为Limit参数的整数倍
 * @method void setOffset(integer $Offset) 设置数据偏移量，默认为 0, 必须为Limit参数的整数倍
 * @method integer getLimit() 获取每页大小，默认为 15
 * @method void setLimit(integer $Limit) 设置每页大小，默认为 15
 * @method integer getCreateUin() 获取创建者用户 Uin，不传或为空只将 Uin 作为条件查询
 * @method void setCreateUin(integer $CreateUin) 设置创建者用户 Uin，不传或为空只将 Uin 作为条件查询
 */

/**
 *DescribeTags请求参数结构体
 */
class DescribeTagsRequest extends AbstractModel
{
    /**
     * @var string 标签键,与标签值同时存在或同时不存在，不存在时表示查询该用户所有标签
     */
    public $TagKey;

    /**
     * @var string 标签值,与标签键同时存在或同时不存在，不存在时表示查询该用户所有标签
     */
    public $TagValue;

    /**
     * @var integer 数据偏移量，默认为 0, 必须为Limit参数的整数倍
     */
    public $Offset;

    /**
     * @var integer 每页大小，默认为 15
     */
    public $Limit;

    /**
     * @var integer 创建者用户 Uin，不传或为空只将 Uin 作为条件查询
     */
    public $CreateUin;
    /**
     * @param string $TagKey 标签键,与标签值同时存在或同时不存在，不存在时表示查询该用户所有标签
     * @param string $TagValue 标签值,与标签键同时存在或同时不存在，不存在时表示查询该用户所有标签
     * @param integer $Offset 数据偏移量，默认为 0, 必须为Limit参数的整数倍
     * @param integer $Limit 每页大小，默认为 15
     * @param integer $CreateUin 创建者用户 Uin，不传或为空只将 Uin 作为条件查询
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
        if (array_key_exists("TagKey",$param) and $param["TagKey"] !== null) {
            $this->TagKey = $param["TagKey"];
        }

        if (array_key_exists("TagValue",$param) and $param["TagValue"] !== null) {
            $this->TagValue = $param["TagValue"];
        }

        if (array_key_exists("Offset",$param) and $param["Offset"] !== null) {
            $this->Offset = $param["Offset"];
        }

        if (array_key_exists("Limit",$param) and $param["Limit"] !== null) {
            $this->Limit = $param["Limit"];
        }

        if (array_key_exists("CreateUin",$param) and $param["CreateUin"] !== null) {
            $this->CreateUin = $param["CreateUin"];
        }
    }
}
