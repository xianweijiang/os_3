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
 * @method string getGroupId() 获取部署组ID，分组唯一标识
 * @method void setGroupId(string $GroupId) 设置部署组ID，分组唯一标识
 * @method string getServer() 获取镜像server
 * @method void setServer(string $Server) 设置镜像server
 * @method string getTagName() 获取镜像版本名称,如v1
 * @method void setTagName(string $TagName) 设置镜像版本名称,如v1
 * @method integer getInstanceNum() 获取实例数量
 * @method void setInstanceNum(integer $InstanceNum) 设置实例数量
 * @method string getReponame() 获取旧版镜像名，如/tsf/nginx
 * @method void setReponame(string $Reponame) 设置旧版镜像名，如/tsf/nginx
 * @method string getCpuLimit() 获取最大的 CPU 核数，对应 K8S 的 limit；不填时默认为 request 的 2 倍
 * @method void setCpuLimit(string $CpuLimit) 设置最大的 CPU 核数，对应 K8S 的 limit；不填时默认为 request 的 2 倍
 * @method string getMemLimit() 获取最大的内存 MiB 数，对应 K8S 的 limit；不填时默认为 request 的 2 倍
 * @method void setMemLimit(string $MemLimit) 设置最大的内存 MiB 数，对应 K8S 的 limit；不填时默认为 request 的 2 倍
 * @method string getJvmOpts() 获取jvm参数
 * @method void setJvmOpts(string $JvmOpts) 设置jvm参数
 * @method string getCpuRequest() 获取分配的 CPU 核数，对应 K8S 的 request
 * @method void setCpuRequest(string $CpuRequest) 设置分配的 CPU 核数，对应 K8S 的 request
 * @method string getMemRequest() 获取分配的内存 MiB 数，对应 K8S 的 request
 * @method void setMemRequest(string $MemRequest) 设置分配的内存 MiB 数，对应 K8S 的 request
 * @method boolean getDoNotStart() 获取是否不立即启动
 * @method void setDoNotStart(boolean $DoNotStart) 设置是否不立即启动
 * @method string getRepoName() 获取（优先使用）新版镜像名，如/tsf/nginx
 * @method void setRepoName(string $RepoName) 设置（优先使用）新版镜像名，如/tsf/nginx
 * @method integer getUpdateType() 获取更新方式：0:快速更新 1:滚动更新
 * @method void setUpdateType(integer $UpdateType) 设置更新方式：0:快速更新 1:滚动更新
 * @method integer getUpdateIvl() 获取滚动更新必填，更新间隔
 * @method void setUpdateIvl(integer $UpdateIvl) 设置滚动更新必填，更新间隔
 */

/**
 *DeployContainerGroup请求参数结构体
 */
class DeployContainerGroupRequest extends AbstractModel
{
    /**
     * @var string 部署组ID，分组唯一标识
     */
    public $GroupId;

    /**
     * @var string 镜像server
     */
    public $Server;

    /**
     * @var string 镜像版本名称,如v1
     */
    public $TagName;

    /**
     * @var integer 实例数量
     */
    public $InstanceNum;

    /**
     * @var string 旧版镜像名，如/tsf/nginx
     */
    public $Reponame;

    /**
     * @var string 最大的 CPU 核数，对应 K8S 的 limit；不填时默认为 request 的 2 倍
     */
    public $CpuLimit;

    /**
     * @var string 最大的内存 MiB 数，对应 K8S 的 limit；不填时默认为 request 的 2 倍
     */
    public $MemLimit;

    /**
     * @var string jvm参数
     */
    public $JvmOpts;

    /**
     * @var string 分配的 CPU 核数，对应 K8S 的 request
     */
    public $CpuRequest;

    /**
     * @var string 分配的内存 MiB 数，对应 K8S 的 request
     */
    public $MemRequest;

    /**
     * @var boolean 是否不立即启动
     */
    public $DoNotStart;

    /**
     * @var string （优先使用）新版镜像名，如/tsf/nginx
     */
    public $RepoName;

    /**
     * @var integer 更新方式：0:快速更新 1:滚动更新
     */
    public $UpdateType;

    /**
     * @var integer 滚动更新必填，更新间隔
     */
    public $UpdateIvl;
    /**
     * @param string $GroupId 部署组ID，分组唯一标识
     * @param string $Server 镜像server
     * @param string $TagName 镜像版本名称,如v1
     * @param integer $InstanceNum 实例数量
     * @param string $Reponame 旧版镜像名，如/tsf/nginx
     * @param string $CpuLimit 最大的 CPU 核数，对应 K8S 的 limit；不填时默认为 request 的 2 倍
     * @param string $MemLimit 最大的内存 MiB 数，对应 K8S 的 limit；不填时默认为 request 的 2 倍
     * @param string $JvmOpts jvm参数
     * @param string $CpuRequest 分配的 CPU 核数，对应 K8S 的 request
     * @param string $MemRequest 分配的内存 MiB 数，对应 K8S 的 request
     * @param boolean $DoNotStart 是否不立即启动
     * @param string $RepoName （优先使用）新版镜像名，如/tsf/nginx
     * @param integer $UpdateType 更新方式：0:快速更新 1:滚动更新
     * @param integer $UpdateIvl 滚动更新必填，更新间隔
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
        if (array_key_exists("GroupId",$param) and $param["GroupId"] !== null) {
            $this->GroupId = $param["GroupId"];
        }

        if (array_key_exists("Server",$param) and $param["Server"] !== null) {
            $this->Server = $param["Server"];
        }

        if (array_key_exists("TagName",$param) and $param["TagName"] !== null) {
            $this->TagName = $param["TagName"];
        }

        if (array_key_exists("InstanceNum",$param) and $param["InstanceNum"] !== null) {
            $this->InstanceNum = $param["InstanceNum"];
        }

        if (array_key_exists("Reponame",$param) and $param["Reponame"] !== null) {
            $this->Reponame = $param["Reponame"];
        }

        if (array_key_exists("CpuLimit",$param) and $param["CpuLimit"] !== null) {
            $this->CpuLimit = $param["CpuLimit"];
        }

        if (array_key_exists("MemLimit",$param) and $param["MemLimit"] !== null) {
            $this->MemLimit = $param["MemLimit"];
        }

        if (array_key_exists("JvmOpts",$param) and $param["JvmOpts"] !== null) {
            $this->JvmOpts = $param["JvmOpts"];
        }

        if (array_key_exists("CpuRequest",$param) and $param["CpuRequest"] !== null) {
            $this->CpuRequest = $param["CpuRequest"];
        }

        if (array_key_exists("MemRequest",$param) and $param["MemRequest"] !== null) {
            $this->MemRequest = $param["MemRequest"];
        }

        if (array_key_exists("DoNotStart",$param) and $param["DoNotStart"] !== null) {
            $this->DoNotStart = $param["DoNotStart"];
        }

        if (array_key_exists("RepoName",$param) and $param["RepoName"] !== null) {
            $this->RepoName = $param["RepoName"];
        }

        if (array_key_exists("UpdateType",$param) and $param["UpdateType"] !== null) {
            $this->UpdateType = $param["UpdateType"];
        }

        if (array_key_exists("UpdateIvl",$param) and $param["UpdateIvl"] !== null) {
            $this->UpdateIvl = $param["UpdateIvl"];
        }
    }
}
