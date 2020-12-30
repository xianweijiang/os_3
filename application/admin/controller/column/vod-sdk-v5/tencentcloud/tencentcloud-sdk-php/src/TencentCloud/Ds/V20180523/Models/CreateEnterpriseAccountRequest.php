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
namespace TencentCloud\Ds\V20180523\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getModule() 获取模块名
 * @method void setModule(string $Module) 设置模块名
 * @method string getOperation() 获取操作名
 * @method void setOperation(string $Operation) 设置操作名
 * @method string getName() 获取企业用户名称
 * @method void setName(string $Name) 设置企业用户名称
 * @method integer getIdentType() 获取企业用户证件类型，8代表营业执照
 * @method void setIdentType(integer $IdentType) 设置企业用户证件类型，8代表营业执照
 * @method string getIdentNo() 获取企业用户营业执照号码
 * @method void setIdentNo(string $IdentNo) 设置企业用户营业执照号码
 * @method string getMobilePhone() 获取企业联系电话
 * @method void setMobilePhone(string $MobilePhone) 设置企业联系电话
 * @method string getTransactorName() 获取经办人姓名
 * @method void setTransactorName(string $TransactorName) 设置经办人姓名
 * @method integer getTransactorIdentType() 获取经办人证件类型，0代表身份证
 * @method void setTransactorIdentType(integer $TransactorIdentType) 设置经办人证件类型，0代表身份证
 * @method string getTransactorIdentNo() 获取经办人证件号码
 * @method void setTransactorIdentNo(string $TransactorIdentNo) 设置经办人证件号码
 * @method string getTransactorPhone() 获取经办人手机号
 * @method void setTransactorPhone(string $TransactorPhone) 设置经办人手机号
 */

/**
 *CreateEnterpriseAccount请求参数结构体
 */
class CreateEnterpriseAccountRequest extends AbstractModel
{
    /**
     * @var string 模块名
     */
    public $Module;

    /**
     * @var string 操作名
     */
    public $Operation;

    /**
     * @var string 企业用户名称
     */
    public $Name;

    /**
     * @var integer 企业用户证件类型，8代表营业执照
     */
    public $IdentType;

    /**
     * @var string 企业用户营业执照号码
     */
    public $IdentNo;

    /**
     * @var string 企业联系电话
     */
    public $MobilePhone;

    /**
     * @var string 经办人姓名
     */
    public $TransactorName;

    /**
     * @var integer 经办人证件类型，0代表身份证
     */
    public $TransactorIdentType;

    /**
     * @var string 经办人证件号码
     */
    public $TransactorIdentNo;

    /**
     * @var string 经办人手机号
     */
    public $TransactorPhone;
    /**
     * @param string $Module 模块名
     * @param string $Operation 操作名
     * @param string $Name 企业用户名称
     * @param integer $IdentType 企业用户证件类型，8代表营业执照
     * @param string $IdentNo 企业用户营业执照号码
     * @param string $MobilePhone 企业联系电话
     * @param string $TransactorName 经办人姓名
     * @param integer $TransactorIdentType 经办人证件类型，0代表身份证
     * @param string $TransactorIdentNo 经办人证件号码
     * @param string $TransactorPhone 经办人手机号
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
        if (array_key_exists("Module",$param) and $param["Module"] !== null) {
            $this->Module = $param["Module"];
        }

        if (array_key_exists("Operation",$param) and $param["Operation"] !== null) {
            $this->Operation = $param["Operation"];
        }

        if (array_key_exists("Name",$param) and $param["Name"] !== null) {
            $this->Name = $param["Name"];
        }

        if (array_key_exists("IdentType",$param) and $param["IdentType"] !== null) {
            $this->IdentType = $param["IdentType"];
        }

        if (array_key_exists("IdentNo",$param) and $param["IdentNo"] !== null) {
            $this->IdentNo = $param["IdentNo"];
        }

        if (array_key_exists("MobilePhone",$param) and $param["MobilePhone"] !== null) {
            $this->MobilePhone = $param["MobilePhone"];
        }

        if (array_key_exists("TransactorName",$param) and $param["TransactorName"] !== null) {
            $this->TransactorName = $param["TransactorName"];
        }

        if (array_key_exists("TransactorIdentType",$param) and $param["TransactorIdentType"] !== null) {
            $this->TransactorIdentType = $param["TransactorIdentType"];
        }

        if (array_key_exists("TransactorIdentNo",$param) and $param["TransactorIdentNo"] !== null) {
            $this->TransactorIdentNo = $param["TransactorIdentNo"];
        }

        if (array_key_exists("TransactorPhone",$param) and $param["TransactorPhone"] !== null) {
            $this->TransactorPhone = $param["TransactorPhone"];
        }
    }
}
