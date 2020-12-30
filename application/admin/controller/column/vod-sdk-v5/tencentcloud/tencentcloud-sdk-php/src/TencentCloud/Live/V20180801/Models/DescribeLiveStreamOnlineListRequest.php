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
 * @method string getDomainName() 获取您的加速域名。
 * @method void setDomainName(string $DomainName) 设置您的加速域名。
 * @method string getAppName() 获取应用名称。
 * @method void setAppName(string $AppName) 设置应用名称。
 * @method integer getPageNum() 获取取得第几页，默认1。
 * @method void setPageNum(integer $PageNum) 设置取得第几页，默认1。
 * @method integer getPageSize() 获取每页大小，最大100。 
取值：1~100之前的任意整数。
默认值：10
 * @method void setPageSize(integer $PageSize) 设置每页大小，最大100。 
取值：1~100之前的任意整数。
默认值：10
 */

/**
 *DescribeLiveStreamOnlineList请求参数结构体
 */
class DescribeLiveStreamOnlineListRequest extends AbstractModel
{
    /**
     * @var string 您的加速域名。
     */
    public $DomainName;

    /**
     * @var string 应用名称。
     */
    public $AppName;

    /**
     * @var integer 取得第几页，默认1。
     */
    public $PageNum;

    /**
     * @var integer 每页大小，最大100。 
取值：1~100之前的任意整数。
默认值：10
     */
    public $PageSize;
    /**
     * @param string $DomainName 您的加速域名。
     * @param string $AppName 应用名称。
     * @param integer $PageNum 取得第几页，默认1。
     * @param integer $PageSize 每页大小，最大100。 
取值：1~100之前的任意整数。
默认值：10
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
        if (array_key_exists("DomainName",$param) and $param["DomainName"] !== null) {
            $this->DomainName = $param["DomainName"];
        }

        if (array_key_exists("AppName",$param) and $param["AppName"] !== null) {
            $this->AppName = $param["AppName"];
        }

        if (array_key_exists("PageNum",$param) and $param["PageNum"] !== null) {
            $this->PageNum = $param["PageNum"];
        }

        if (array_key_exists("PageSize",$param) and $param["PageSize"] !== null) {
            $this->PageSize = $param["PageSize"];
        }
    }
}
