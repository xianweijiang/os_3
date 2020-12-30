<?php
namespace app\core\util;

use think\Db;
use app\core\implement\ProviderInterface;
use app\core\implement\TemplateInterface;
/**
 * 小程序模板消息
 * Class RoutineTemplate
 * @package app\routine\model\routine
 */
class RoutineTwoTemplateService implements ProviderInterface,TemplateInterface
{


    //订单支付成功
    const ORDER_PAY_SUCCESS = 'AT0009';
    //砍价成功
    const BARGAIN_SUCCESS = 'AT1173';
    //申请退款通知
    const ORDER_REFUND_STATUS = 'AT0036';
    //退款成功
    const ORDER_REFUND_SUCCESS = 'AT0787';
    //订单发货提醒(快递)
    const ORDER_POSTAGE_SUCCESS = 'AT0007';
    //订单发货提醒(送货)
    const ORDER_DELIVER_SUCCESS = 'AT0177';
    //拼团取消通知
    const PINK_REMOVE='AT2430';
    //拼团失败
    const PINK_Fill='AT0310';
    //拼团成功
    const PINK_TRUE='AT0051';
    //开团成功
    const OPEN_PINK_SUCCESS='AT0541';

    public function register($config)
    {
        return ['routine_two',new self()];
    }
    /**
     * 根据模板编号获取模板ID
     * @param string $tempKey
     * @return mixed|string
     */
    public static function setTemplateId($tempKey = ''){
        if($tempKey == '')return '';
        return Db::name('RoutineTemplate')->where('tempkey',$tempKey)->where('status',1)->value('tempid');
    }
    /**
     * 获取小程序模板库申请的标题列表
     * @param int $offset
     * @param int $count
     * @return mixed
     */
    public static function getTemplateList(){
        return MiniProgramService::noticeService()->getIndustry();
    }

    /**
     * 获取所有模板列表
     * @return \EasyWeChat\Support\Collection
     */
    public static function getPrivateTemplates()
    {
        return MiniProgramService::noticeService()->getPrivateTemplates();
    }

    /**
     * 删除小程序中的某个模板消息
     * @param string $templateId
     * @return bool|mixed
     */
    public static function delTemplate($templateId = ''){
        if($templateId == '') return false;
        return MiniProgramService::noticeService()->deletePrivateTemplate($templateId);
    }

    /**
     * 发送模板消息
     * @param string $openId   接收者（用户）的 openid
     * @param string $tempCode 所需下发的模板编号
     * @param array $dataKey 模板内容，不填则下发空模板
     * @param string $link 点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转。
     * @param string $formId 表单提交场景下，为 submit 事件带上的 formId；支付场景下，为本次支付的 prepay_id
     * @param string $emphasisKeyword 模板需要放大的关键词，不填则默认无放大
     * @return bool|mixed
     */
    public static function sendTemplate($tempCode = '',$openId = '',$dataKey = array(),$formId = '',$link = '',$defaultColor=null)
    {
        if($openId == '' || $tempCode == '' || $formId == '') return false;
        $data=[];
        foreach ($dataKey as $key=>$item){
            $data[$key]['value']=$item;
        }
        try{
            return MiniProgramService::sendTemplate($openId,trim(self::setTemplateId(self::getConstants($tempCode))),$data,$formId,$link,$defaultColor);
        }catch (\Exception $e){
            return false;
        }
    }

    public static function getConstants($code='') {
        $oClass = new \ReflectionClass(__CLASS__);
        $stants=$oClass->getConstants();
        if($code) return isset($stants[$code]) ? $stants[$code] : '';
        else return $stants;
    }

    /**
     * 添加模板并获取模板ID
     * @param $shortId
     * @return \EasyWeChat\Support\Collection
     */
    public static function addTemplate($shortId)
    {
        return MiniProgramService::noticeService()->addTemplate($shortId);
    }
}