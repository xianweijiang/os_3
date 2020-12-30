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
namespace TencentCloud\Live\V20180801\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getDomainName() 获取域名。
 * @method void setDomainName(string $DomainName) 设置域名。
 * @method integer getEnable() 获取是否启用，0：关闭，1：启用。
 * @method void setEnable(integer $Enable) 设置是否启用，0：关闭，1：启用。
 * @method string getAuthKey() 获取鉴权key。
 * @method void setAuthKey(string $AuthKey) 设置鉴权key。
 * @method integer getAuthDelta() 获取有效时间，单位：秒。
 * @method void setAuthDelta(integer $AuthDelta) 设置有效时间，单位：秒。
 * @method string getAuthBackKey() 获取鉴权BackKey。
 * @method void setAuthBackKey(string $AuthBackKey) 设置鉴权BackKey。
 */

/**
 *播放鉴权key信息
 */
class PlayAuthKeyInfo extends AbstractModel
{
    /**
     * @var string 域名。
     */
    public $DomainName;

    /**
     * @var integer 是否启用，0：关闭，1：启用。
     */
    public $Enable;

    /**
     * @var string 鉴权key。
     */
    public $AuthKey;

    /**
     * @var integer 有效时间，单位：秒。
     */
    public $AuthDelta;

    /**
     * @var string 鉴权BackKey。
     */
    public $AuthBackKey;
    /**
     * @param string $DomainName 域名。
     * @param integer $Enable 是否启用，0：关闭，1：启用。
     * @param string $AuthKey 鉴权key。
     * @param integer $AuthDelta 有效时间，单位：秒。
     * @param string $AuthBackKey 鉴权BackKey。
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
        if (array_key_exists("DomainName",$param) and $param["DomainName"] !== null) {
            $this->DomainName = $param["DomainName"];
        }

        if (array_key_exists("Enable",$param) and $param["Enable"] !== null) {
            $this->Enable = $param["Enable"];
        }

        if (array_key_exists("AuthKey",$param) and $param["AuthKey"] !== null) {
            $this->AuthKey = $param["AuthKey"];
        }

        if (array_key_exists("AuthDelta",$param) and $param["AuthDelta"] !== null) {
            $this->AuthDelta = $param["AuthDelta"];
        }

        if (array_key_exists("AuthBackKey",$param) and $param["AuthBackKey"] !== null) {
            $this->AuthBackKey = $param["AuthBackKey"];
        }
    }
}
