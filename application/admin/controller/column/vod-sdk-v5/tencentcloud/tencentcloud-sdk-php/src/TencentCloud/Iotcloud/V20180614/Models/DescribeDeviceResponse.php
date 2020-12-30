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
namespace TencentCloud\Iotcloud\V20180614\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getDeviceName() 获取设备名
 * @method void setDeviceName(string $DeviceName) 设置设备名
 * @method integer getOnline() 获取设备是否在线，0不在线，1在线
 * @method void setOnline(integer $Online) 设置设备是否在线，0不在线，1在线
 * @method integer getLoginTime() 获取设备登陆时间
 * @method void setLoginTime(integer $LoginTime) 设置设备登陆时间
 * @method string getVersion() 获取设备固件版本
 * @method void setVersion(string $Version) 设置设备固件版本
 * @method integer getLastUpdateTime() 获取设备最后更新时间
 * @method void setLastUpdateTime(integer $LastUpdateTime) 设置设备最后更新时间
 * @method string getDeviceCert() 获取设备证书
 * @method void setDeviceCert(string $DeviceCert) 设置设备证书
 * @method string getDevicePsk() 获取设备密钥
 * @method void setDevicePsk(string $DevicePsk) 设置设备密钥
 * @method array getTags() 获取设备属性
 * @method void setTags(array $Tags) 设置设备属性
 * @method integer getDeviceType() 获取设备类型
 * @method void setDeviceType(integer $DeviceType) 设置设备类型
 * @method string getImei() 获取IMEI
 * @method void setImei(string $Imei) 设置IMEI
 * @method integer getIsp() 获取运营商类型
 * @method void setIsp(integer $Isp) 设置运营商类型
 * @method integer getConnIP() 获取IP地址
 * @method void setConnIP(integer $ConnIP) 设置IP地址
 * @method string getNbiotDeviceID() 获取NB IoT运营商处的DeviceID
 * @method void setNbiotDeviceID(string $NbiotDeviceID) 设置NB IoT运营商处的DeviceID
 * @method string getLoraDevEui() 获取Lora设备的dev eui
 * @method void setLoraDevEui(string $LoraDevEui) 设置Lora设备的dev eui
 * @method integer getLoraMoteType() 获取Lora设备的mote type
 * @method void setLoraMoteType(integer $LoraMoteType) 设置Lora设备的mote type
 * @method string getRequestId() 获取唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 * @method void setRequestId(string $RequestId) 设置唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 */

/**
 *DescribeDevice返回参数结构体
 */
class DescribeDeviceResponse extends AbstractModel
{
    /**
     * @var string 设备名
     */
    public $DeviceName;

    /**
     * @var integer 设备是否在线，0不在线，1在线
     */
    public $Online;

    /**
     * @var integer 设备登陆时间
     */
    public $LoginTime;

    /**
     * @var string 设备固件版本
     */
    public $Version;

    /**
     * @var integer 设备最后更新时间
     */
    public $LastUpdateTime;

    /**
     * @var string 设备证书
     */
    public $DeviceCert;

    /**
     * @var string 设备密钥
     */
    public $DevicePsk;

    /**
     * @var array 设备属性
     */
    public $Tags;

    /**
     * @var integer 设备类型
     */
    public $DeviceType;

    /**
     * @var string IMEI
     */
    public $Imei;

    /**
     * @var integer 运营商类型
     */
    public $Isp;

    /**
     * @var integer IP地址
     */
    public $ConnIP;

    /**
     * @var string NB IoT运营商处的DeviceID
     */
    public $NbiotDeviceID;

    /**
     * @var string Lora设备的dev eui
     */
    public $LoraDevEui;

    /**
     * @var integer Lora设备的mote type
     */
    public $LoraMoteType;

    /**
     * @var string 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    public $RequestId;
    /**
     * @param string $DeviceName 设备名
     * @param integer $Online 设备是否在线，0不在线，1在线
     * @param integer $LoginTime 设备登陆时间
     * @param string $Version 设备固件版本
     * @param integer $LastUpdateTime 设备最后更新时间
     * @param string $DeviceCert 设备证书
     * @param string $DevicePsk 设备密钥
     * @param array $Tags 设备属性
     * @param integer $DeviceType 设备类型
     * @param string $Imei IMEI
     * @param integer $Isp 运营商类型
     * @param integer $ConnIP IP地址
     * @param string $NbiotDeviceID NB IoT运营商处的DeviceID
     * @param string $LoraDevEui Lora设备的dev eui
     * @param integer $LoraMoteType Lora设备的mote type
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
        if (array_key_exists("DeviceName",$param) and $param["DeviceName"] !== null) {
            $this->DeviceName = $param["DeviceName"];
        }

        if (array_key_exists("Online",$param) and $param["Online"] !== null) {
            $this->Online = $param["Online"];
        }

        if (array_key_exists("LoginTime",$param) and $param["LoginTime"] !== null) {
            $this->LoginTime = $param["LoginTime"];
        }

        if (array_key_exists("Version",$param) and $param["Version"] !== null) {
            $this->Version = $param["Version"];
        }

        if (array_key_exists("LastUpdateTime",$param) and $param["LastUpdateTime"] !== null) {
            $this->LastUpdateTime = $param["LastUpdateTime"];
        }

        if (array_key_exists("DeviceCert",$param) and $param["DeviceCert"] !== null) {
            $this->DeviceCert = $param["DeviceCert"];
        }

        if (array_key_exists("DevicePsk",$param) and $param["DevicePsk"] !== null) {
            $this->DevicePsk = $param["DevicePsk"];
        }

        if (array_key_exists("Tags",$param) and $param["Tags"] !== null) {
            $this->Tags = [];
            foreach ($param["Tags"] as $key => $value){
                $obj = new DeviceTag();
                $obj->deserialize($value);
                array_push($this->Tags, $obj);
            }
        }

        if (array_key_exists("DeviceType",$param) and $param["DeviceType"] !== null) {
            $this->DeviceType = $param["DeviceType"];
        }

        if (array_key_exists("Imei",$param) and $param["Imei"] !== null) {
            $this->Imei = $param["Imei"];
        }

        if (array_key_exists("Isp",$param) and $param["Isp"] !== null) {
            $this->Isp = $param["Isp"];
        }

        if (array_key_exists("ConnIP",$param) and $param["ConnIP"] !== null) {
            $this->ConnIP = $param["ConnIP"];
        }

        if (array_key_exists("NbiotDeviceID",$param) and $param["NbiotDeviceID"] !== null) {
            $this->NbiotDeviceID = $param["NbiotDeviceID"];
        }

        if (array_key_exists("LoraDevEui",$param) and $param["LoraDevEui"] !== null) {
            $this->LoraDevEui = $param["LoraDevEui"];
        }

        if (array_key_exists("LoraMoteType",$param) and $param["LoraMoteType"] !== null) {
            $this->LoraMoteType = $param["LoraMoteType"];
        }

        if (array_key_exists("RequestId",$param) and $param["RequestId"] !== null) {
            $this->RequestId = $param["RequestId"];
        }
    }
}
