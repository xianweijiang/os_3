<?php
namespace app\core\util;

class DingDingService{

    const  APPID = "819802844";
    const  APP_KEY = "dingkosand3z4vpk0nki";
    const  CHAT_ID = "chatf2ae11a972d5b08539b930dbdeeb76d5";
    const  APP_SECRET = "2rBuuew5Zbe7t2eFovco30hImXFpscx0gHTZ4id2A3ANequCSLcqoi1wSz2AS0sV";
    const URL = "https://oapi.dingtalk.com/chat/send";

    const CorpId = "dingac6f8cc56ae39002ee0f45d8e4f7c288";
    const SEND_CHAT_URL = "https://oapi.dingtalk.com/chat/send";

    const QUERY_CHAT_ID = "chat9be63f497073c9361fbd6dfac5a2eb70";

    public $timestamp;
    public $signSystem;

    public function __construct(){
        //$this->timestamp = getTime();

    }


    public function send($message){
        $access_token = $this->getToken();
        if($access_token == false){
            $access_token = $this->getToken(true);
            if($access_token == false){
                //echo "错误";
                return;
            }
        }

        //$SendToUser_config = 'https://oapi.dingtalk.com/message/send?access_token='.$access_token;

        $SendToUser_config = self::SEND_CHAT_URL . "?access_token=" . $access_token;


        $SendToUser_data = ['chatid'=>self::CHAT_ID,
            "msgtype"=>"text",
            'text'=>["content"=>$message]];

        $SendToUser = $this->request_post_curl($SendToUser_config, json_encode($SendToUser_data));

        //string(69) "{"errcode":0,"errmsg":"ok","messageId":"msgco4BXp9445sVEd9A+eTWWA=="}"
        $data = json_decode($SendToUser, true);
        if($data['errcode'] == "0" && $data['errmsg'] == "ok"){
            //echo "成功";
            return;
        }else{
            \Think\Log::write("钉钉发送-提现提醒群-错误-错误信息".$data['messageId']."-数据-".$SendToUser);
        }

    }


    /**
     * 发送钉钉信息
     * 到账户余额提醒群
     * @param $type =1 默认给提现提醒发信息  =2 给余额提醒群发信息
     * @return void
     */
    public function send_msg($message,$type = 1){


        $access_token = $this->getToken();
        if($access_token == false){
            $access_token = $this->getToken(true);
            if($access_token == false){
                //echo "错误";
                return;
            }
        }

        //$SendToUser_config = 'https://oapi.dingtalk.com/message/send?access_token='.$access_token;

        $SendToUser_config = self::SEND_CHAT_URL . "?access_token=" . $access_token;
        $chat_id='';
        if($type == 1){
            $chat_id = self::CHAT_ID;
        }else{
            $chat_id = self::QUERY_CHAT_ID;
        }

        $SendToUser_data = ['chatid'=>$chat_id,
            "msgtype"=>"text",
            'text'=>["content"=>$message]];

        $SendToUser = $this->request_post_curl($SendToUser_config, json_encode($SendToUser_data));

        //string(69) "{"errcode":0,"errmsg":"ok","messageId":"msgco4BXp9445sVEd9A+eTWWA=="}"
        $data = json_decode($SendToUser, true);
        if($data['errcode'] == "0" && $data['errmsg'] == "ok"){
            \Think\Log::write("=======钉钉发送信息成功======",'INFO');
            //echo "成功";
            return;
        }else{
            \Think\Log::write("钉钉发送-提现提醒群-错误-错误信息".$data['messageId']."-数据-".$SendToUser);
        }

    }




    /**
     * dingding 获取token
     * @param bool $flag 传true 强制获取最新的
     * @return void
     */
    public function getToken($flag = false){
        $token_table = M("dingtalk_token");
        $where['type'] = "token";
        $old_token = $token_table->where($where)->find();
        //判断时间
        if($old_token['expires_in']+7200 > time() && $flag == false){
            $access_token['access_token'] = $old_token['token'];

        }else{

            $gettoken_config = 'https://oapi.dingtalk.com/gettoken'.'?corpid='.self::APP_KEY.'&corpsecret='.self::APP_SECRET;
            $access_token=$this->http_get($gettoken_config);
            //获取access_token 
            $access_token = json_decode($access_token,true);

            if($access_token['errcode'] == "0"){

                $token['token'] = $access_token['access_token'];
                $token['expires_in'] = time();
                $token_table->where($where)->save($token);
            }else{
                return false;
            }

        }

        return $access_token['access_token'];

    }

    public function withdrawToDindTalk($msg){

        vendor('taobaoSdk.TopSdk');

        $msgArr = $this->dingtalkMsgEntity($msg);

        $res = $this->curl_post(self::URL, $msgArr);
        return $res;

    }


    public function getSignSystem(){

        $sign = md5(self::APP_KEY . self::APPID . $this->timestmap);
        return $this->signSystem = $sign;
    }


    public function getTime(){
        return date('YmdHis', time());
    }

    public function getMessage(){

        $msg = "掌种宝-提现通知";

        return $msg;
    }

    public function dingtalkMsgEntity($msg){

        $this->getSignSystem();

        $data = array(
            'appid' => self::APPID,
            'timestamp'  => $this->timestamp,
            'signSystem' => $this->signSystem,
            'chateId'    => self::CHAT_ID,
            'noticeMsg'  => $msg

        );

        return $data;
    }

    public function request_post_curl($remote_server, $post_string){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            ['Content-Type: application/json;charset=utf-8']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }






    function http_get($url){
        //$headers[] = "Content-type: application/x-www-form-urlencoded";
        //$headers[] = "Zoomkey-Auth-Token: 9CD0F0F60AFDF00";
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        //curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $tmpInfo = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        return $tmpInfo;
    }



    function curl_post($url, $data = array(), $second = 30){
        $ch = curl_init();
        // 超时时间
        curl_setopt ( $ch, CURLOPT_TIMEOUT, $second );
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type:text/xml; charset=utf-8"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        if (0 != curl_errno($ch)) {
            $result  = "Error:\n" . curl_error($ch);
        }
        curl_close($ch);
        //return $this->xmlstr_to_array ( $result );
        return $result;
    }










}