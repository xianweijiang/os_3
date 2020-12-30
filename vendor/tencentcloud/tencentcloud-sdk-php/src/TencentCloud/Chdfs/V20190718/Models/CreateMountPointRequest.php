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
namespace TencentCloud\Chdfs\V20190718\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getMountPointName() 获取挂载点名称
 * @method void setMountPointName(string $MountPointName) 设置挂载点名称
 * @method string getFileSystemId() 获取文件系统ID
 * @method void setFileSystemId(string $FileSystemId) 设置文件系统ID
 * @method string getAccessGroupId() 获取权限组ID
 * @method void setAccessGroupId(string $AccessGroupId) 设置权限组ID
 * @method string getVpcId() 获取VPC网络ID
 * @method void setVpcId(string $VpcId) 设置VPC网络ID
 * @method integer getMountPointStatus() 获取挂载点状态（1：打开；2：关闭）
 * @method void setMountPointStatus(integer $MountPointStatus) 设置挂载点状态（1：打开；2：关闭）
 * @method integer getVpcType() 获取VPC网络类型（1：CVM；2：黑石1.0；3：黑石2.0）
 * @method void setVpcType(integer $VpcType) 设置VPC网络类型（1：CVM；2：黑石1.0；3：黑石2.0）
 */

/**
 *CreateMountPoint请求参数结构体
 */
class CreateMountPointRequest extends AbstractModel
{
    /**
     * @var string 挂载点名称
     */
    public $MountPointName;

    /**
     * @var string 文件系统ID
     */
    public $FileSystemId;

    /**
     * @var string 权限组ID
     */
    public $AccessGroupId;

    /**
     * @var string VPC网络ID
     */
    public $VpcId;

    /**
     * @var integer 挂载点状态（1：打开；2：关闭）
     */
    public $MountPointStatus;

    /**
     * @var integer VPC网络类型（1：CVM；2：黑石1.0；3：黑石2.0）
     */
    public $VpcType;
    /**
     * @param string $MountPointName 挂载点名称
     * @param string $FileSystemId 文件系统ID
     * @param string $AccessGroupId 权限组ID
     * @param string $VpcId VPC网络ID
     * @param integer $MountPointStatus 挂载点状态（1：打开；2：关闭）
     * @param integer $VpcType VPC网络类型（1：CVM；2：黑石1.0；3：黑石2.0）
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
        if (array_key_exists("MountPointName",$param) and $param["MountPointName"] !== null) {
            $this->MountPointName = $param["MountPointName"];
        }

        if (array_key_exists("FileSystemId",$param) and $param["FileSystemId"] !== null) {
            $this->FileSystemId = $param["FileSystemId"];
        }

        if (array_key_exists("AccessGroupId",$param) and $param["AccessGroupId"] !== null) {
            $this->AccessGroupId = $param["AccessGroupId"];
        }

        if (array_key_exists("VpcId",$param) and $param["VpcId"] !== null) {
            $this->VpcId = $param["VpcId"];
        }

        if (array_key_exists("MountPointStatus",$param) and $param["MountPointStatus"] !== null) {
            $this->MountPointStatus = $param["MountPointStatus"];
        }

        if (array_key_exists("VpcType",$param) and $param["VpcType"] !== null) {
            $this->VpcType = $param["VpcType"];
        }
    }
}
