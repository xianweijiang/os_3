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

namespace TencentCloud\Vod\V20180717;
use TencentCloud\Common\AbstractClient;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Credential;
use TencentCloud\Vod\V20180717\Models as Models;

/**
* @method Models\ApplyUploadResponse ApplyUpload(Models\ApplyUploadRequest $req) * 该接口用于申请媒体文件（和封面文件）的上传，获取文件上传到腾讯云点播的元信息（包括上传路径、上传签名等），用于后续上传接口。
* 上传流程请参考[服务端上传综述](https://cloud.tencent.com/document/product/266/9759#.E4.B8.8A.E4.BC.A0.E6.B5.81.E7.A8.8B)。
* @method Models\CommitUploadResponse CommitUpload(Models\CommitUploadRequest $req) 该接口用于确认媒体文件（和封面文件）上传到腾讯云点播的结果，并存储媒体信息，返回文件的播放地址和文件 ID。
* @method Models\CreateClassResponse CreateClass(Models\CreateClassRequest $req) * 用于对媒体进行分类管理；
* 该接口不影响既有媒体的分类，如需修改媒体分类，请调用[修改媒体文件属性](/document/product/266/31762)接口。
* 分类层次不可超过 4 层。
* 每个分类的子类数量不可超过 500 个。
* @method Models\DeleteClassResponse DeleteClass(Models\DeleteClassRequest $req) * 仅当待删分类无子分类且无媒体关联情况下，可删除分类；
* 否则，请先执行[删除媒体](/document/product/266/31764)及子分类，再删除该分类；
* @method Models\DeleteMediaResponse DeleteMedia(Models\DeleteMediaRequest $req) * 删除媒体及其对应的视频处理文件（如转码视频、雪碧图、截图、微信发布视频等）；
* 可单独删除指定 ID 的视频文件下的转码，或者微信发布文件；
* @method Models\DescribeAllClassResponse DescribeAllClass(Models\DescribeAllClassRequest $req) * 获得用户的所有分类信息。
* @method Models\DescribeMediaInfosResponse DescribeMediaInfos(Models\DescribeMediaInfosRequest $req) 1. 该接口可以获取多个视频的多种信息，包括：
    1. 基础信息（basicInfo）：包括视频名称、大小、时长、封面图片等。
    2. 元信息（metaData）：包括视频流信息、音频流信息等。
    3. 转码结果信息（transcodeInfo）：包括该视频转码生成的各种码率的视频的地址、规格、码率、分辨率等。
    4. 转动图结果信息（animatedGraphicsInfo）：对视频转动图（如 gif）后，动图相关信息。
    5. 采样截图信息（sampleSnapshotInfo）：对视频采样截图后，相关截图信息。
    6. 雪碧图信息（imageSpriteInfo）：对视频截取雪碧图之后，雪碧图的相关信息。
    7. 指定时间点截图信息（snapshotByTimeOffsetInfo）：对视频依照指定时间点截图后，各个截图的信息。
    8. 视频打点信息（keyFrameDescInfo）：对视频设置的各个打点信息。
2. 可以指定回包只返回部分信息。
* @method Models\ModifyClassResponse ModifyClass(Models\ModifyClassRequest $req) 修改媒体分类属性。
* @method Models\ModifyMediaInfoResponse ModifyMediaInfo(Models\ModifyMediaInfoRequest $req) 修改媒体文件的属性，包括分类、名称、描述、标签、过期时间、打点信息、视频封面等。
* @method Models\SearchMediaResponse SearchMedia(Models\SearchMediaRequest $req) 搜索媒体信息，支持各种条件筛选，以及对返回结果进行排序、过滤等功能，具体包括：
- 根据媒体文件名或描述信息进行文本模糊搜索。
- 根据媒体分类、标签进行检索。
    - 指定分类集合 ClassIds（见输入参数），返回满足集合中任意分类的媒体。例如：假设媒体分类有电影、电视剧、综艺，其中电影又有子分类历史片、动作片、言情片。如果 ClassIds 指定了电影、电视剧，那么电影和电视剧下的所有子分类
    都会返回；而如果 ClassIds 指定的是历史片、动作片，那么只有这 2 个子分类下的媒体才会返回。
    - 指定标签集合 Tags（见输入参数），返回满足集合中任意标签的媒体。例如：假设媒体标签有二次元、宫斗、鬼畜，如果 Tags 指定了二次元、鬼畜 2 个标签，那么只要符合这 2 个标签中任意一个的媒体都会被检索出来。
- 允许指定筛选某一来源 Source（见输入参数）的媒体。
- 允许根据直播推流码、Vid（见输入参数）筛选直播录制的媒体。
- 允许根据媒体的创建范围筛选媒体。
- 允许对上述条件进行任意组合，检索同时满足以上条件的媒体。例如可以筛选从 2018 年 12 月 1 日到 2018 年 12 月 8 日创建的电影、电视剧分类下带有宫斗、鬼畜标签的媒体。
- 允许对结果进行排序，允许通过 Offset 和 Limit 实现只返回部分结果。

接口搜索限制：
- 搜索结果超过 5000条，不再支持分页查询超过 5000 部分的数据。
 */

class VodClient extends AbstractClient
{
    /**
     * @var string 产品默认域名
     */
    protected $endpoint = "vod.tencentcloudapi.com";

    /**
     * @var string api版本号
     */
    protected $version = "2018-07-17";

    /**
     * CvmClient constructor.
     * @param Credential $credential 认证类实例
     * @param string $region 地域
     * @param ClientProfile $profile client配置
     */
    function __construct($credential, $region, $profile=null)
    {
        parent::__construct($this->endpoint, $this->version, $credential, $region, $profile);
    }

    public function returnResponse($action, $response)
    {
        $respClass = "TencentCloud"."\\".ucfirst("vod")."\\"."V20180717\\Models"."\\".ucfirst($action)."Response";
        $obj = new $respClass();
        $obj->deserialize($response);
        return $obj;
    }
}
