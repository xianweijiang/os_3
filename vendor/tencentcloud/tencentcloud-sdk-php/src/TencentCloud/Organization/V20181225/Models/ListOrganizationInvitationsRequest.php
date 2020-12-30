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
namespace TencentCloud\Organization\V20181225\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method integer getInvited() 获取是否被邀请。1：被邀请，0：发出的邀请
 * @method void setInvited(integer $Invited) 设置是否被邀请。1：被邀请，0：发出的邀请
 * @method integer getOffset() 获取偏移量
 * @method void setOffset(integer $Offset) 设置偏移量
 * @method integer getLimit() 获取限制数目
 * @method void setLimit(integer $Limit) 设置限制数目
 */

/**
 *ListOrganizationInvitations请求参数结构体
 */
class ListOrganizationInvitationsRequest extends AbstractModel
{
    /**
     * @var integer 是否被邀请。1：被邀请，0：发出的邀请
     */
    public $Invited;

    /**
     * @var integer 偏移量
     */
    public $Offset;

    /**
     * @var integer 限制数目
     */
    public $Limit;
    /**
     * @param integer $Invited 是否被邀请。1：被邀请，0：发出的邀请
     * @param integer $Offset 偏移量
     * @param integer $Limit 限制数目
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
        if (array_key_exists("Invited",$param) and $param["Invited"] !== null) {
            $this->Invited = $param["Invited"];
        }

        if (array_key_exists("Offset",$param) and $param["Offset"] !== null) {
            $this->Offset = $param["Offset"];
        }

        if (array_key_exists("Limit",$param) and $param["Limit"] !== null) {
            $this->Limit = $param["Limit"];
        }
    }
}
