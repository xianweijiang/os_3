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
namespace TencentCloud\Tmt\V20180321\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getText() 获取待识别的文本，文本统一使用utf-8格式编码，非utf-8格式编码字符会翻译失败
 * @method void setText(string $Text) 设置待识别的文本，文本统一使用utf-8格式编码，非utf-8格式编码字符会翻译失败
 * @method integer getProjectId() 获取项目id
 * @method void setProjectId(integer $ProjectId) 设置项目id
 */

/**
 *LanguageDetect请求参数结构体
 */
class LanguageDetectRequest extends AbstractModel
{
    /**
     * @var string 待识别的文本，文本统一使用utf-8格式编码，非utf-8格式编码字符会翻译失败
     */
    public $Text;

    /**
     * @var integer 项目id
     */
    public $ProjectId;
    /**
     * @param string $Text 待识别的文本，文本统一使用utf-8格式编码，非utf-8格式编码字符会翻译失败
     * @param integer $ProjectId 项目id
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
        if (array_key_exists("Text",$param) and $param["Text"] !== null) {
            $this->Text = $param["Text"];
        }

        if (array_key_exists("ProjectId",$param) and $param["ProjectId"] !== null) {
            $this->ProjectId = $param["ProjectId"];
        }
    }
}
