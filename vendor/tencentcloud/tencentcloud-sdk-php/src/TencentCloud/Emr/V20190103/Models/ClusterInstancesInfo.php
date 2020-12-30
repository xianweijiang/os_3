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
namespace TencentCloud\Emr\V20190103\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method integer getId() 获取ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setId(integer $Id) 设置ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getClusterId() 获取集群ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setClusterId(string $ClusterId) 设置集群ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getFtitle() 获取标题
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setFtitle(string $Ftitle) 设置标题
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getClusterName() 获取集群名
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setClusterName(string $ClusterName) 设置集群名
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getRegionId() 获取地域ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setRegionId(integer $RegionId) 设置地域ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getZoneId() 获取地区ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setZoneId(integer $ZoneId) 设置地区ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getAppId() 获取用户APPID
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setAppId(integer $AppId) 设置用户APPID
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getUin() 获取用户UIN
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setUin(string $Uin) 设置用户UIN
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getProjectId() 获取项目Id
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setProjectId(integer $ProjectId) 设置项目Id
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getVpcId() 获取集群VPCID
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setVpcId(integer $VpcId) 设置集群VPCID
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getSubnetId() 获取子网ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setSubnetId(integer $SubnetId) 设置子网ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getStatus() 获取状态
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setStatus(integer $Status) 设置状态
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getAddTime() 获取添加时间
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setAddTime(string $AddTime) 设置添加时间
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getRunTime() 获取已经运行时间
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setRunTime(string $RunTime) 设置已经运行时间
注意：此字段可能返回 null，表示取不到有效值。
 * @method EmrProductConfigOutter getConfig() 获取集群产品配置信息
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setConfig(EmrProductConfigOutter $Config) 设置集群产品配置信息
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getMasterIp() 获取主节点外网IP
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setMasterIp(string $MasterIp) 设置主节点外网IP
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getEmrVersion() 获取EMR版本
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setEmrVersion(string $EmrVersion) 设置EMR版本
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getChargeType() 获取收费类型
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setChargeType(integer $ChargeType) 设置收费类型
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getTradeVersion() 获取交易版本
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setTradeVersion(integer $TradeVersion) 设置交易版本
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getResourceOrderId() 获取资源订单ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setResourceOrderId(integer $ResourceOrderId) 设置资源订单ID
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getIsTradeCluster() 获取是否计费集群
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setIsTradeCluster(integer $IsTradeCluster) 设置是否计费集群
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getAlarmInfo() 获取集群错误状态告警信息
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setAlarmInfo(string $AlarmInfo) 设置集群错误状态告警信息
注意：此字段可能返回 null，表示取不到有效值。
 */

/**
 *集群实例信息
 */
class ClusterInstancesInfo extends AbstractModel
{
    /**
     * @var integer ID
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Id;

    /**
     * @var string 集群ID
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $ClusterId;

    /**
     * @var string 标题
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Ftitle;

    /**
     * @var string 集群名
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $ClusterName;

    /**
     * @var integer 地域ID
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $RegionId;

    /**
     * @var integer 地区ID
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $ZoneId;

    /**
     * @var integer 用户APPID
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $AppId;

    /**
     * @var string 用户UIN
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Uin;

    /**
     * @var integer 项目Id
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $ProjectId;

    /**
     * @var integer 集群VPCID
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $VpcId;

    /**
     * @var integer 子网ID
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $SubnetId;

    /**
     * @var integer 状态
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Status;

    /**
     * @var string 添加时间
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $AddTime;

    /**
     * @var string 已经运行时间
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $RunTime;

    /**
     * @var EmrProductConfigOutter 集群产品配置信息
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Config;

    /**
     * @var string 主节点外网IP
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $MasterIp;

    /**
     * @var string EMR版本
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $EmrVersion;

    /**
     * @var integer 收费类型
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $ChargeType;

    /**
     * @var integer 交易版本
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $TradeVersion;

    /**
     * @var integer 资源订单ID
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $ResourceOrderId;

    /**
     * @var integer 是否计费集群
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $IsTradeCluster;

    /**
     * @var string 集群错误状态告警信息
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $AlarmInfo;
    /**
     * @param integer $Id ID
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $ClusterId 集群ID
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $Ftitle 标题
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $ClusterName 集群名
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $RegionId 地域ID
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $ZoneId 地区ID
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $AppId 用户APPID
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $Uin 用户UIN
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $ProjectId 项目Id
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $VpcId 集群VPCID
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $SubnetId 子网ID
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $Status 状态
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $AddTime 添加时间
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $RunTime 已经运行时间
注意：此字段可能返回 null，表示取不到有效值。
     * @param EmrProductConfigOutter $Config 集群产品配置信息
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $MasterIp 主节点外网IP
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $EmrVersion EMR版本
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $ChargeType 收费类型
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $TradeVersion 交易版本
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $ResourceOrderId 资源订单ID
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $IsTradeCluster 是否计费集群
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $AlarmInfo 集群错误状态告警信息
注意：此字段可能返回 null，表示取不到有效值。
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
        if (array_key_exists("Id",$param) and $param["Id"] !== null) {
            $this->Id = $param["Id"];
        }

        if (array_key_exists("ClusterId",$param) and $param["ClusterId"] !== null) {
            $this->ClusterId = $param["ClusterId"];
        }

        if (array_key_exists("Ftitle",$param) and $param["Ftitle"] !== null) {
            $this->Ftitle = $param["Ftitle"];
        }

        if (array_key_exists("ClusterName",$param) and $param["ClusterName"] !== null) {
            $this->ClusterName = $param["ClusterName"];
        }

        if (array_key_exists("RegionId",$param) and $param["RegionId"] !== null) {
            $this->RegionId = $param["RegionId"];
        }

        if (array_key_exists("ZoneId",$param) and $param["ZoneId"] !== null) {
            $this->ZoneId = $param["ZoneId"];
        }

        if (array_key_exists("AppId",$param) and $param["AppId"] !== null) {
            $this->AppId = $param["AppId"];
        }

        if (array_key_exists("Uin",$param) and $param["Uin"] !== null) {
            $this->Uin = $param["Uin"];
        }

        if (array_key_exists("ProjectId",$param) and $param["ProjectId"] !== null) {
            $this->ProjectId = $param["ProjectId"];
        }

        if (array_key_exists("VpcId",$param) and $param["VpcId"] !== null) {
            $this->VpcId = $param["VpcId"];
        }

        if (array_key_exists("SubnetId",$param) and $param["SubnetId"] !== null) {
            $this->SubnetId = $param["SubnetId"];
        }

        if (array_key_exists("Status",$param) and $param["Status"] !== null) {
            $this->Status = $param["Status"];
        }

        if (array_key_exists("AddTime",$param) and $param["AddTime"] !== null) {
            $this->AddTime = $param["AddTime"];
        }

        if (array_key_exists("RunTime",$param) and $param["RunTime"] !== null) {
            $this->RunTime = $param["RunTime"];
        }

        if (array_key_exists("Config",$param) and $param["Config"] !== null) {
            $this->Config = new EmrProductConfigOutter();
            $this->Config->deserialize($param["Config"]);
        }

        if (array_key_exists("MasterIp",$param) and $param["MasterIp"] !== null) {
            $this->MasterIp = $param["MasterIp"];
        }

        if (array_key_exists("EmrVersion",$param) and $param["EmrVersion"] !== null) {
            $this->EmrVersion = $param["EmrVersion"];
        }

        if (array_key_exists("ChargeType",$param) and $param["ChargeType"] !== null) {
            $this->ChargeType = $param["ChargeType"];
        }

        if (array_key_exists("TradeVersion",$param) and $param["TradeVersion"] !== null) {
            $this->TradeVersion = $param["TradeVersion"];
        }

        if (array_key_exists("ResourceOrderId",$param) and $param["ResourceOrderId"] !== null) {
            $this->ResourceOrderId = $param["ResourceOrderId"];
        }

        if (array_key_exists("IsTradeCluster",$param) and $param["IsTradeCluster"] !== null) {
            $this->IsTradeCluster = $param["IsTradeCluster"];
        }

        if (array_key_exists("AlarmInfo",$param) and $param["AlarmInfo"] !== null) {
            $this->AlarmInfo = $param["AlarmInfo"];
        }
    }
}
