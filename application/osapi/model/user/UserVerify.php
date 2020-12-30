<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/23
 * Time: 17:00
 */

namespace app\osapi\model\user;

use app\admin\model\system\SystemConfig;
use app\osapi\model\BaseModel;

/**
 * 用户验证码
 * Class UserVerify
 * @package app\common\model
 */
class UserVerify extends BaseModel
{

    public static function addData($account, $type = 'mobile')
    {
        $verify = create_rand(6, 'num');
        self::where(array('account' => $account, 'type' => $type))->delete();
        $data['verify'] = $verify;
        $data['account'] = $account;
        $data['type'] = $type;
        $data['create_time'] = time();
        $res = self::create($data);
        if (!$res) {
            return false;
        }
        return $verify;
    }

    public static function getValue($account)
    {
        return self::where(array('account' => $account))->value('verify');
    }

    /**
     * 检验验证码和手机号是否匹配
     * @param $account 手机号
     * @param $type
     * @param $verify 验证码
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public static function checkVerify($account, $type, $verify)
    {
        $is_verify=SystemConfig::getValue('is_sns_verify');
        if($is_verify==0){
            $verify1 = self::where(array('account' => $account, 'type' => $type, 'verify' => $verify))->select()->toArray();
            if (!$verify1) {
                return -1;
            }else{
                $time=$verify1[0]['create_time']+900;
                if($time<time()){
                    return -2;
                }else{
                    self::where(array('account' => $account, 'type' => $type))->delete();
                    return 1;
                }
            }
        }else{
            return 1;
        }
    }

}