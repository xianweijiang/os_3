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
namespace TencentCloud\Tiems\V20190416\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getName() 获取运行环境名称
 * @method void setName(string $Name) 设置运行环境名称
 * @method string getFramework() 获取运行环境框架
 * @method void setFramework(string $Framework) 设置运行环境框架
 * @method string getDescription() 获取运行环境描述
 * @method void setDescription(string $Description) 设置运行环境描述
 * @method boolean getPublic() 获取是否为公开运行环境
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setPublic(boolean $Public) 设置是否为公开运行环境
注意：此字段可能返回 null，表示取不到有效值。
 * @method boolean getHealthCheckOn() 获取是否打开健康检查
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setHealthCheckOn(boolean $HealthCheckOn) 设置是否打开健康检查
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getImage() 获取镜像地址
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setImage(string $Image) 设置镜像地址
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getCreateTime() 获取创建时间
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setCreateTime(string $CreateTime) 设置创建时间
注意：此字段可能返回 null，表示取不到有效值。
 */

/**
 *运行环境
 */
class Runtime extends AbstractModel
{
    /**
     * @var string 运行环境名称
     */
    public $Name;

    /**
     * @var string 运行环境框架
     */
    public $Framework;

    /**
     * @var string 运行环境描述
     */
    public $Description;

    /**
     * @var boolean 是否为公开运行环境
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Public;

    /**
     * @var boolean 是否打开健康检查
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $HealthCheckOn;

    /**
     * @var string 镜像地址
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Image;

    /**
     * @var string 创建时间
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $CreateTime;
    /**
     * @param string $Name 运行环境名称
     * @param string $Framework 运行环境框架
     * @param string $Description 运行环境描述
     * @param boolean $Public 是否为公开运行环境
注意：此字段可能返回 null，表示取不到有效值。
     * @param boolean $HealthCheckOn 是否打开健康检查
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $Image 镜像地址
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $CreateTime 创建时间
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
        if (array_key_exists("Name",$param) and $param["Name"] !== null) {
            $this->Name = $param["Name"];
        }

        if (array_key_exists("Framework",$param) and $param["Framework"] !== null) {
            $this->Framework = $param["Framework"];
        }

        if (array_key_exists("Description",$param) and $param["Description"] !== null) {
            $this->Description = $param["Description"];
        }

        if (array_key_exists("Public",$param) and $param["Public"] !== null) {
            $this->Public = $param["Public"];
        }

        if (array_key_exists("HealthCheckOn",$param) and $param["HealthCheckOn"] !== null) {
            $this->HealthCheckOn = $param["HealthCheckOn"];
        }

        if (array_key_exists("Image",$param) and $param["Image"] !== null) {
            $this->Image = $param["Image"];
        }

        if (array_key_exists("CreateTime",$param) and $param["CreateTime"] !== null) {
            $this->CreateTime = $param["CreateTime"];
        }
    }
}
