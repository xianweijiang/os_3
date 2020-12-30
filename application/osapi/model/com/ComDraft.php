<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/30
 * Time: 13:10
 */

namespace app\osapi\model\com;


use app\osapi\model\BaseModel;
use app\osapi\model\common\Support;
use app\osapi\model\user\UserFollow;
use app\osapi\model\user\UserModel;
use think\Cache;

class ComDraft extends BaseModel
{
    public static function createDraft($data)
    {
        $data['content']=html($data['content']);
        $data['content']=self::_limitPictureCount($data['content']);
        $data['content']=html($data['content']);
        self::beginTrans();
        try{
                $draft_id=self::add($data);
            self::commitTrans();
            return $draft_id;
        }catch (\Exception $e){
            self::rollbackTrans();
            self::setErrorInfo('发布过程中出现异常！发布失败：'.self::getErrorInfo().$e->getMessage());
            return false;
        }
    }

    /**
     * 图片限制
     * @param $content
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _limitPictureCount($content){
        //默认最多显示10张图片
        $maxImageCount = modC('LIMIT_IMAGE', 10);

        //正则表达式配置
        $beginMark = 'BEGIN0000hfuidafoidsjfiadosj';
        $endMark = 'END0000fjidoajfdsiofjdiofjasid';
        $imageRegex = '/<img(.*?)\\>/i';
        $reverseRegex = "/{$beginMark}(.*?){$endMark}/i";

        //如果图片数量不够多，那就不用额外处理了。
        $imageCount = preg_match_all($imageRegex, $content);
        if ($imageCount <= $maxImageCount) {
            return $content;
        }

        //清除伪造图片
        $content = preg_replace($reverseRegex, "<img$1>", $content);

        //临时替换图片来保留前$maxImageCount张图片
        $content = preg_replace($imageRegex, "{$beginMark}$1{$endMark}", $content, $maxImageCount);

        //替换多余的图片
        $content = preg_replace($imageRegex, "[图片]", $content);

        //将替换的东西替换回来
        $content = preg_replace($reverseRegex, "<img$1>", $content);

        //返回结果
        return $content;
    }

    /**
     * 将content中的图片信息提取出来,取前三张图
     * @param $content
     * @return array|null
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    private static function _contentToImage($content)
    {
        $content = htmlspecialchars_decode(text($content));  //将编码过的字符转回html标签
        preg_match_all('/<img[^>]*\>/', $content, $match);  //获取图片标签
        if (is_array($match[0])) {  //若有多张图片，循环处理
            foreach ($match[0] as $k => &$v) {
                if($k==3){
                    break;
                }
                $img = substr(substr($v, 10), 0, -2);
                //从10开始才是src路径，然后再截取去掉最后的标签符号
                $length = "-" . strlen(strstr($v, 'width'));
                //组件传上来的img标签里会自动有width属性，计算这部分长度然后也去掉
                $imgs[] = substr($img, 0, $length);
                //去掉width属性，此时只剩下一个完整路径
            }
            unset($v);
        } else {  //单图处理
            $imgs[] = substr(substr($match[0], 10), 0, -2);
        }
        if ($match[0] == null) {
            $imgs = null;
        }
        return $imgs;
    }
}