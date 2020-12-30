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
namespace TencentCloud\Kms\V20190118\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getKeyId() 获取CMK的唯一标识，用于指定目标导入密钥材料的CMK。
 * @method void setKeyId(string $KeyId) 设置CMK的唯一标识，用于指定目标导入密钥材料的CMK。
 * @method string getImportToken() 获取导入密钥材料需要的token，用于作为 ImportKeyMaterial 的参数。
 * @method void setImportToken(string $ImportToken) 设置导入密钥材料需要的token，用于作为 ImportKeyMaterial 的参数。
 * @method string getPublicKey() 获取用于加密密钥材料的RSA公钥，base64编码。使用PublicKey base64解码后的公钥将导入密钥进行加密后作为 ImportKeyMaterial 的参数。
 * @method void setPublicKey(string $PublicKey) 设置用于加密密钥材料的RSA公钥，base64编码。使用PublicKey base64解码后的公钥将导入密钥进行加密后作为 ImportKeyMaterial 的参数。
 * @method integer getParametersValidTo() 获取该导出token和公钥的有效期，超过该时间后无法导入，需要重新调用GetParametersForImport获取。
 * @method void setParametersValidTo(integer $ParametersValidTo) 设置该导出token和公钥的有效期，超过该时间后无法导入，需要重新调用GetParametersForImport获取。
 * @method string getRequestId() 获取唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 * @method void setRequestId(string $RequestId) 设置唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 */

/**
 *GetParametersForImport返回参数结构体
 */
class GetParametersForImportResponse extends AbstractModel
{
    /**
     * @var string CMK的唯一标识，用于指定目标导入密钥材料的CMK。
     */
    public $KeyId;

    /**
     * @var string 导入密钥材料需要的token，用于作为 ImportKeyMaterial 的参数。
     */
    public $ImportToken;

    /**
     * @var string 用于加密密钥材料的RSA公钥，base64编码。使用PublicKey base64解码后的公钥将导入密钥进行加密后作为 ImportKeyMaterial 的参数。
     */
    public $PublicKey;

    /**
     * @var integer 该导出token和公钥的有效期，超过该时间后无法导入，需要重新调用GetParametersForImport获取。
     */
    public $ParametersValidTo;

    /**
     * @var string 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    public $RequestId;
    /**
     * @param string $KeyId CMK的唯一标识，用于指定目标导入密钥材料的CMK。
     * @param string $ImportToken 导入密钥材料需要的token，用于作为 ImportKeyMaterial 的参数。
     * @param string $PublicKey 用于加密密钥材料的RSA公钥，base64编码。使用PublicKey base64解码后的公钥将导入密钥进行加密后作为 ImportKeyMaterial 的参数。
     * @param integer $ParametersValidTo 该导出token和公钥的有效期，超过该时间后无法导入，需要重新调用GetParametersForImport获取。
     * @param string $RequestId 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
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
        if (array_key_exists("KeyId",$param) and $param["KeyId"] !== null) {
            $this->KeyId = $param["KeyId"];
        }

        if (array_key_exists("ImportToken",$param) and $param["ImportToken"] !== null) {
            $this->ImportToken = $param["ImportToken"];
        }

        if (array_key_exists("PublicKey",$param) and $param["PublicKey"] !== null) {
            $this->PublicKey = $param["PublicKey"];
        }

        if (array_key_exists("ParametersValidTo",$param) and $param["ParametersValidTo"] !== null) {
            $this->ParametersValidTo = $param["ParametersValidTo"];
        }

        if (array_key_exists("RequestId",$param) and $param["RequestId"] !== null) {
            $this->RequestId = $param["RequestId"];
        }
    }
}
