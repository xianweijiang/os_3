<?php
/**
 * Created by PhpStorm.
 * User: zzl-yf
 * Date: 2020/2/14
 * Time: 16:38
 */

namespace app\commonapi\model;


use app\admin\model\system\SystemConfig;
use app\osapi\model\user\UserModel;
use basic\ModelBasic;
use traits\ModelTrait;

class WebsiteConnect extends ModelBasic
{
    use ModelTrait;

    public static function getNotifyList($where)
    {
        $model=db('website_connect_action_notify');
        if(in_array($where['status'],[-1,0,1,2])){
            $map['notify_status']=$where['status'];
        }else{
            $map['notify_status']=['in',[-1,0,1,2]];
        }
        $model = $model->where($map)->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->order('id desc')->select()) && count($data) ? $data : [];
        $count = $model->where($map)->count();
        foreach ($data as &$val){
            $val['user_info']['nickname']=UserModel::where('uid',$val['uid'])->value('nickname');
            $val['user_info']['user_token']=db('website_connect_token')->where('uid',$val['uid'])->where('status',1)->value('user_token');

            if($val['to_uid']!=0){
                $val['to_user_info']['nickname']=UserModel::where('uid',$val['to_uid'])->value('nickname');
                $val['to_user_info']['user_token']=db('website_connect_token')->where('uid',$val['to_uid'])->where('status',1)->value('user_token');
            }
            $val['send_time_show']=time_format($val['send_time']);
        }
        unset($val);
        return compact('count', 'data');
    }

    /**
     * 用户行为通知，系统调用第三方平台通知接口
     * @param $uid
     * @param $to_id
     * @param $to_uid
     * @param $action
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function userActionNotify($uid,$to_id,$to_uid,$action)
    {
        return true;
        $website_connect_open=SystemConfig::getValue('website_connect_open');
        if($website_connect_open!='1'){
            return false;
        }
        if($uid<=0){
            return false;
        }
        $user_token=db('website_connect_token')->where('uid',$uid)->where('status',1)->value('user_token');
        if(!$user_token){
            return false;
        }
        if($to_uid>0){
            $to_user_token=db('website_connect_token')->where('uid',$to_uid)->where('status',1)->value('user_token');
            if(!$to_user_token){
                $to_user_token=0;
            }
        }else{
            $to_user_token=0;
        }
        $action_token = md5(time().rand(0,1000000).$action);
        $log_data=[
            'uid'=>$uid,
            'to_id'=>$to_id,
            'to_uid'=>$to_uid,
            'action'=>$action,
            'action_token'=>$action_token,
            'num'=>1,
            'send_time'=>time(),
            'last_false_reason'=>'',
            'false_reason'=>'',
            'notify_status'=>2
        ];
        $log_id=db('website_connect_action_notify')->insertGetId($log_data);
        $need_resend=0;//是否需要请求重试
        //发送通知请求
        $args=[
            'user_token' =>$user_token,
            'to_id' => $to_id,
            'to_user_token' => $to_user_token,
            'action' => $action,
            'action_token'=>$action_token
        ];
        $website_connect_userActionNotify_api=SystemConfig::getValue('website_connect_userActionNotify_api');
        $url=self::buildSignUrl($args,$website_connect_userActionNotify_api);

        $ch = curl_init();// 初始化一个新会话
        curl_setopt($ch, CURLOPT_URL, $url);// 设置要求请的url
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        try {
            // 执行CURL请求
            $output = curl_exec($ch);
            // 关闭CURL资源
            curl_close($ch);
            $output=json_decode($output,true);
            if($output['msg']=='ok'){
                if($output['data']['action_token']!=$action_token){
                    $false_reason="用户行为标识，请求与响应的不一致";
                    $need_resend=1;//需要请求重试
                }
                UserModel::beginTrans();
                $res=$res_to=0;
                if(isset($output['data']['user_info'])){//存在用户数据
                    if($output['data']['user_info']['user_token']!=$user_token){
                        $false_reason="用户唯一标识异常,请求和响应的不匹配";
                        $need_resend=1;//需要请求重试
                    }else{
                        $update_data=[
                            'user_token'=>$user_token,
                            'userInfo'=>$output['data']['user_info'],
                        ];
                        unset($update_data['userInfo']['user_token']);
                        $res=UserModel::updateWebsiteUserScore($update_data);
                        if($res<=0){
                            //用户信息更新失败，失败原因：$output['data']
                            $false_reason="更新本地用户信息时失败,";
                            $need_resend=1;//需要请求重试
                        }
                    }
                }else{
                    $res=1;
                }

                if(isset($output['data']['to_user_info'])){//存在被操作用户数据
                    if($output['data']['to_user_info']['to_user_token']!=$to_user_token){
                        $false_reason="被操作用户唯一标识异常,请求和响应的不匹配";
                        $need_resend=1;//需要请求重试
                    }else{
                        $update_data=[
                            'user_token'=>$to_user_token,
                            'userInfo'=>$output['data']['to_user_info'],
                        ];
                        unset($update_data['userInfo']['to_user_token']);
                        $res_to=UserModel::updateWebsiteUserScore($update_data);
                        if($res_to<=0){
                            //用户信息更新失败，失败原因：$output['data']
                            $false_reason="更新本地用户信息时失败";
                            $need_resend=1;//需要请求重试
                        }
                    }
                }else{
                    $res_to=1;
                }
                if($res&&$res_to){
                    UserModel::commitTrans();
                }
            }else{
                //用户信息更新失败，失败原因：$output['data']
                $false_reason=$output['data'];
                $need_resend=1;//需要请求重试
            }
        } catch (\Exception $e) {
            $false_reason='请求异常：'.$e->getMessage();
            $need_resend=1;//需要请求重试
        }
        if($need_resend==0){//不需要请求重试，说明请求及响应成功
            db('website_connect_action_notify')->where('id',$log_id)->update(['notify_status'=>1]);
        }else{
            db('website_connect_action_notify')->where('id',$log_id)->update(['false_reason'=>$false_reason,'notify_status'=>0]);
            $resend_data=[
                'uid'=>$uid,
                'to_id'=>$to_id,
                'to_uid'=>$to_uid,
                'action'=>$action,
                'action_token'=>$action_token,
                'num'=>2,
                'send_time'=>time()+3*60,
                'last_false_reason'=>$false_reason,
                'false_reason'=>'',
                'notify_status'=>2
            ];
            db('website_connect_action_notify')->insert($resend_data);//添加请求重试记录。保证过段时间通知会再次被唤起
        }
        return true;
    }

    /**
     * 重新发送通知
     * @return bool
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function reNotify()
    {
        $website_connect_open=SystemConfig::getValue('website_connect_open');
        if($website_connect_open!='1'){
            return false;
        }
        do{
            $need_notify_list=db('website_connect_action_notify')->where('num','<',8)->where('notify_status',2)->where('send_time','<=',time())->limit(20)->select();
            foreach ($need_notify_list as $val){
                self::_doReNotify($val);
            }
            sleep(1);//降低频率，防止瞬间高并发导致服务器卡死
        }while(count($need_notify_list)==20);
        return true;
    }

    private static function _doReNotify($notify_info)
    {
        if($notify_info['uid']<=0){
            db('website_connect_action_notify')->where('id',$notify_info['id'])->update(['false_reason'=>'用户uid异常','notify_status'=>-1]);
            return true;
        }
        $user_token=db('website_connect_token')->where('uid',$notify_info['uid'])->where('status',1)->value('user_token');
        if(!$user_token){
            db('website_connect_action_notify')->where('id',$notify_info['id'])->update(['false_reason'=>'用户uid无对应的user_token异常','notify_status'=>-1]);
            return true;
        }
        if($notify_info['to_uid']>0){
            $to_user_token=db('website_connect_token')->where('uid',$notify_info['to_uid'])->where('status',1)->value('user_token');
            if(!$to_user_token){
                $to_user_token=0;
            }
        }else{
            $to_user_token=0;
        }
        $action_token =$notify_info['action_token'];
        $need_resend=0;//是否需要请求重试
        //发送通知请求
        $args=[
            'user_token' =>$user_token,
            'to_id' => $notify_info['to_id'],
            'to_user_token' => $to_user_token,
            'action' => $notify_info['action'],
            'action_token'=>$action_token
        ];
        $website_connect_userActionNotify_api=SystemConfig::getValue('website_connect_userActionNotify_api');
        $url=self::buildSignUrl($args,$website_connect_userActionNotify_api);

        $ch = curl_init();// 初始化一个新会话
        curl_setopt($ch, CURLOPT_URL, $url);// 设置要求请的url
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        try {
            // 执行CURL请求
            $output = curl_exec($ch);
            // 关闭CURL资源
            curl_close($ch);
            $output=json_decode($output,true);
            if($output['msg']=='ok'){
                if($output['data']['action_token']!=$action_token){
                    $false_reason="用户行为标识，请求与响应的不一致";
                    $need_resend=1;//需要请求重试
                }
                UserModel::beginTrans();
                $res=$res_to=0;
                if(isset($output['data']['user_info'])){//存在用户数据
                    if($output['data']['user_info']['user_token']!=$user_token){
                        $false_reason="用户唯一标识异常,请求和响应的不匹配";
                        $need_resend=1;//需要请求重试
                    }else{
                        $update_data=[
                            'user_token'=>$user_token,
                            'userInfo'=>$output['data']['user_info'],
                        ];
                        unset($update_data['userInfo']['user_token']);
                        $res=UserModel::updateWebsiteUserScore($update_data);
                        if($res<=0){
                            //用户信息更新失败，失败原因：$output['data']
                            $false_reason="更新本地用户信息时失败,";
                            $need_resend=1;//需要请求重试
                        }
                    }
                }else{
                    $res=1;
                }

                if(isset($output['data']['to_user_info'])){//存在被操作用户数据
                    if($output['data']['to_user_info']['to_user_token']!=$to_user_token){
                        $false_reason="被操作用户唯一标识异常,请求和响应的不匹配";
                        $need_resend=1;//需要请求重试
                    }else{
                        $update_data=[
                            'user_token'=>$to_user_token,
                            'userInfo'=>$output['data']['to_user_info'],
                        ];
                        unset($update_data['userInfo']['to_user_token']);
                        $res_to=UserModel::updateWebsiteUserScore($update_data);
                        if($res_to<=0){
                            //用户信息更新失败，失败原因：$output['data']
                            $false_reason="更新本地用户信息时失败";
                            $need_resend=1;//需要请求重试
                        }
                    }
                }else{
                    $res_to=1;
                }
                if($res&&$res_to){
                    UserModel::commitTrans();
                }
            }else{
                //用户信息更新失败，失败原因：$output['data']
                $false_reason=$output['data'];
                $need_resend=1;//需要请求重试
            }
        } catch (\Exception $e) {
            $false_reason='请求异常：'.$e->getMessage();
            $need_resend=1;//需要请求重试
        }
        if($need_resend==0){//不需要请求重试，说明请求及响应成功
            db('website_connect_action_notify')->where('id',$notify_info['id'])->update(['notify_status'=>1,'send_time'=>time()]);
        }else{
            db('website_connect_action_notify')->where('id',$notify_info['id'])->update(['false_reason'=>$false_reason,'notify_status'=>0,'send_time'=>time()]);
            switch ($notify_info['num']){
                case '2':
                    $next_num=3;
                    $next_send_time=time()+10*60;
                    break;
                case '3':
                    $next_num=4;
                    $next_send_time=time()+30*60;
                    break;
                case '4':
                    $next_num=5;
                    $next_send_time=time()+60*60;
                    break;
                case '5':
                    $next_num=6;
                    $next_send_time=time()+6*60*60;
                    break;
                case '6':
                    $next_num=7;
                    $next_send_time=time()+24*60*60;
                    break;
                case '7':
                    return true;
            }
            $resend_data=[
                'uid'=>$notify_info['uid'],
                'to_id'=>$notify_info['to_id'],
                'to_uid'=>$notify_info['to_uid'],
                'action'=>$notify_info['action'],
                'action_token'=>$action_token,
                'num'=>$next_num,
                'send_time'=>$next_send_time,
                'last_false_reason'=>$false_reason,
                'false_reason'=>'',
                'notify_status'=>2
            ];
            db('website_connect_action_notify')->insert($resend_data);//添加请求重试记录。保证过段时间通知会再次被唤起
        }
        return true;
    }


    /**
     * 构建签名参数
     * @param $args
     * @param $base_url
     * @return string
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public static function buildSignUrl($args,$base_url)
    {
        $website_connect=SystemConfig::getMore('website_connect_app_key,website_connect_app_secret');
        $endtimestamp=time()+5*60;
        $args['endtimestamp']=$endtimestamp;
        $args['appKey']=$website_connect['website_connect_app_key'];
        if(strpos($base_url,'?')!==false){
            $base_url=$base_url.'&';
        }else{
            $base_url=$base_url.'?';
        }
        $params=[];
        foreach ($args as $key=>$val){
            $params[]=$key.'='.$val;
        }
        $base_url=$base_url.implode('&',$params);
        unset($key,$val);
        $args['appSecret']=$website_connect['website_connect_app_secret'];
        ksort($args);//$Map按 键 升序排列
        $before_md5_sign=implode('',$args);//拼接$Map为字符串
        $after_md5_sign=md5($before_md5_sign);//MD5加密

        $base_url=$base_url.'&sign='.$after_md5_sign;


        return $base_url;
    }
}