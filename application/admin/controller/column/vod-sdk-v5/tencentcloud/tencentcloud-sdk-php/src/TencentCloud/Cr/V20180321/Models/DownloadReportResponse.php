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
namespace TencentCloud\Cr\V20180321\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getDailyReportUrl() 获取日报下载地址
 * @method void setDailyReportUrl(string $DailyReportUrl) 设置日报下载地址
 * @method string getResultReportUrl() 获取结果下载地址
 * @method void setResultReportUrl(string $ResultReportUrl) 设置结果下载地址
 * @method string getDetailReportUrl() 获取明细下载地址
 * @method void setDetailReportUrl(string $DetailReportUrl) 设置明细下载地址
 * @method string getRequestId() 获取唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 * @method void setRequestId(string $RequestId) 设置唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 */

/**
 *DownloadReport返回参数结构体
 */
class DownloadReportResponse extends AbstractModel
{
    /**
     * @var string 日报下载地址
     */
    public $DailyReportUrl;

    /**
     * @var string 结果下载地址
     */
    public $ResultReportUrl;

    /**
     * @var string 明细下载地址
     */
    public $DetailReportUrl;

    /**
     * @var string 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    public $RequestId;
    /**
     * @param string $DailyReportUrl 日报下载地址
     * @param string $ResultReportUrl 结果下载地址
     * @param string $DetailReportUrl 明细下载地址
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
        if (array_key_exists("DailyReportUrl",$param) and $param["DailyReportUrl"] !== null) {
            $this->DailyReportUrl = $param["DailyReportUrl"];
        }

        if (array_key_exists("ResultReportUrl",$param) and $param["ResultReportUrl"] !== null) {
            $this->ResultReportUrl = $param["ResultReportUrl"];
        }

        if (array_key_exists("DetailReportUrl",$param) and $param["DetailReportUrl"] !== null) {
            $this->DetailReportUrl = $param["DetailReportUrl"];
        }

        if (array_key_exists("RequestId",$param) and $param["RequestId"] !== null) {
            $this->RequestId = $param["RequestId"];
        }
    }
}
