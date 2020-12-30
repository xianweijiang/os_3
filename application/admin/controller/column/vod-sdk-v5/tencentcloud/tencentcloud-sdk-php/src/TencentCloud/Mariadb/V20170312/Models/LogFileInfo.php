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
namespace TencentCloud\Mariadb\V20170312\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method integer getMtime() 获取Log最后修改时间
 * @method void setMtime(integer $Mtime) 设置Log最后修改时间
 * @method integer getLength() 获取文件长度
 * @method void setLength(integer $Length) 设置文件长度
 * @method string getUri() 获取下载Log时用到的统一资源标识符
 * @method void setUri(string $Uri) 设置下载Log时用到的统一资源标识符
 */

/**
 *拉取的日志信息
 */
class LogFileInfo extends AbstractModel
{
    /**
     * @var integer Log最后修改时间
     */
    public $Mtime;

    /**
     * @var integer 文件长度
     */
    public $Length;

    /**
     * @var string 下载Log时用到的统一资源标识符
     */
    public $Uri;
    /**
     * @param integer $Mtime Log最后修改时间
     * @param integer $Length 文件长度
     * @param string $Uri 下载Log时用到的统一资源标识符
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
        if (array_key_exists("Mtime",$param) and $param["Mtime"] !== null) {
            $this->Mtime = $param["Mtime"];
        }

        if (array_key_exists("Length",$param) and $param["Length"] !== null) {
            $this->Length = $param["Length"];
        }

        if (array_key_exists("Uri",$param) and $param["Uri"] !== null) {
            $this->Uri = $param["Uri"];
        }
    }
}
