<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/5/24
 * Time: 17:11
 */

namespace app\osapi\controller;


use app\commonapi\model\Gong;
use app\osapi\lib\File;
use app\osapi\model\com\ComForum;
use app\osapi\model\com\ComForumMember;
use app\osapi\model\com\ComPost;
use app\osapi\model\com\ComTopic;
use app\osapi\model\common\Support;
use app\osapi\model\common\Blacklist;
use app\osapi\model\user\UserModel;
use app\ebapi\model\store\StoreProduct;
use app\core\util\GroupDataService;
use app\osapi\model\com\ComThread;
use app\admin\model\system\SystemConfig;
use app\wechat\sdk\WechatAuth;
use think\Cache;
use app\commonapi\model\rank\RankSearch;

class Common extends Base
{
    /**
     * 图片上传接口
     * @author qhy(qhy@ourstu.com)
     * @date slf
     */
    public function uploadPicture()
    {
        $files = request()->file('file');
        $res=File::uploadPicture($files);
        /*if (strpos($_SERVER['HTTP_USER_AGENT'], 'miniprogram') !== false&&$files->getMime()!='image/webp') {
            $filePath=ROOT.'/public/upload/tmp'.rand(1000,9999).'.png';
            file_put_contents($filePath, $files);
            $obj = new \CURLFile(realpath($filePath));
            $obj->setMimeType("image/jpeg");
            $file['media'] = $obj;
            $token=Cache::get('miniprogram_token');
            if(!$token){
                $appId = SystemConfig::getValue('routine_appId');
                $appSecret = SystemConfig::getValue('routine_appsecret');
                $wx = new WechatAuth($appId,$appSecret);
                $token= $wx->getAccessToken();
                Cache::set('miniprogram_token',$token,7200);
            }
            $url = "https://api.weixin.qq.com/wxa/img_sec_check?access_token=".$token['access_token'];
            $info = $this->http_request($url,$file);
            $info =  json_decode($info,true);
            if($info['errcode']==87014){
                $res['path']=get_domain().'/public/system/images/gl.jpg';
            }
            unlink($filePath);
        }*/
        //isset($new_file_path)&&unlink($new_file_path);
        if($res==false){
            $this->apiError(File::getError());
        }else{
            $this->apiSuccess($res);
        }
    }

    /**
     * 多图上传接口
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function uploadMulitPicture()
    {
        $files = request()->file('mulitfile');
        $res=File::uploadMulitPicture($files);
        if($res==false){
            $this->apiError('批量上传操作执行失败！');
        }else{
            $this->apiSuccess($res);
        }
    }

    /**
     * 上传base64位图片
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    /*public function uploadPictureBase64()
    {
        $aData = input('post.file','','text');
        $res=File::uploadAvatar($aData);
        if($res==false){
            $this->apiError(File::getError());
        }else{
            $this->apiSuccess($res);
        }
    }*/

    /**
     * 点赞或取消点赞
     * @author 郑钟良(zzl@ourstu.com)
     * @date slf
     */
    public function doSupport()
    {
        $uid=$this->_needLogin();
        $model=input('post.model/t','thread');
        $row=input('post.row/d',0);//点赞对象id
        $model_arr=['thread','forum','ucard','reply'];
        if(!in_array($model,$model_arr)){
            $this->apiError('非法操作！');
        }
        $is_support=Support::isSupport($model,$row,$uid);
        if($model=='thread'||$model=='reply') {
            $author_uid = ComPost::where('id', $row)->value('author_uid');
            $tid = ComPost::where('id', $row)->value('tid');
        }
        if(!$is_support){
            if($model=='thread'||$model=='reply'){
                $is_black=Blacklist::isBlack($author_uid,$uid);
                if ($is_black) {
                    $this->apiError('由于对方的权限设置，您无法进行该操作');
                }
            }
            if($model=='ucard'){
                $is_black=Blacklist::isBlack($row,$uid);
                if ($is_black) {
                    $this->apiError('由于对方的权限设置，您无法进行该操作');
                }
            }
            $res=Support::doSupport($model,$row,$uid);

            //完成任务加积分
            Gong::finishtask('dianzan','support','uid') ;
            //首次点赞加积分
            Gong::firstaction('support','shoucidianzan','uid');
            // 点赞行为加积分
            Gong::actionadd('dianzan','support','uid') ;

            if($model=='thread'){
                website_connect_notify($uid,$tid,$author_uid,'osapi_common_doSupport_thread');//通知第三方平台，任务回调
            }
            if($model=='reply'){
                website_connect_notify($uid,$row,$author_uid,'osapi_common_doSupport_post');//通知第三方平台，任务回调
            }
            if($model=='ucard'){
                website_connect_notify($uid,0,$row,'osapi_common_doSupport_ucard');//通知第三方平台，任务回调
            }
            if($model=='forum'){
                website_connect_notify($uid,$row,0,'osapi_common_doSupport_forum');//通知第三方平台，任务回调
            }
        }else{
            //减去任务增加的积分
            Gong::canceltask('dianzan','support','uid') ;
            //减去点赞增加的积分
            Gong::actionsub('dianzan','support','uid') ;
            $res=Support::doDelSupport($model,$row,$uid);

            if($model=='thread'){
                website_connect_notify($uid,$tid,$author_uid,'osapi_common_doDelSupport_thread');//通知第三方平台，任务回调
            }
            if($model=='reply'){
                website_connect_notify($uid,$row,$author_uid,'osapi_common_doDelSupport_post');//通知第三方平台，任务回调
            }
            if($model=='ucard'){
                website_connect_notify($uid,0,$row,'osapi_common_doDelSupport_ucard');//通知第三方平台，任务回调
            }
            if($model=='forum'){
                website_connect_notify($uid,$row,0,'osapi_common_doDelSupport_forum');//通知第三方平台，任务回调
            }


        }
        if($res){
            $data['info']='操作成功！';
            $data['support_count']=db('support')->where('row',$row)->where('model',$model)->where('status',1)->count();
            if($model=='thread'){
                ComThread::setListCache($uid);
                Cache::rm('com_index_top'.$uid);
                Cache::set('thread_detail_view_num_reget_'.$row.'_uid_'.$uid,1,60);
                Cache::clear('thread_rank_list'.$uid);
            }
            $this->apiSuccess($data);
        }else{
            $this->apiError('操作失败！'.Support::getErrorInfo());
        }
    }



    /**
     * 收藏
     */
    public function collect(){
        $uid=$this->_needLogin();
        $tid = input('post.tid/d', 0);
        $collect=db('collect')->where('uid',$uid)->where('tid',$tid)->where('status',1)->find();
        if($collect){
            $this->apiError('该帖子已收藏！');
        }
        $author_uid=ComThread::where('id',$tid)->value('author_uid');
        $is_black=Blacklist::isBlack($author_uid,$uid);
        if ($is_black) {
            $this->apiError('由于对方的权限设置，您无法进行该操作');
        }
        $data['uid']=$uid;
        $data['tid']=$tid;
        $data['create_time']=time();
        $data['status']=1;
        $res=db('collect')->insert($data);
        if($res){
            db('com_thread')->where('id',$tid)->setInc('collect_count');
            db('com_post')->where('tid',$tid)->setInc('collect_count');
            db('user')->where('uid',$uid)->setInc('collect');
            $data['count']=db('com_thread')->where('id',$tid)->value('collect_count');

            $is_collect = db('user')->where('uid',$uid)->value('is_collect');
            if($is_collect == 1){
                //首次收藏加积分
                Gong::firstaction('collect','shoucishoucang','uid');
                db('user')->where('uid',$uid)->update(['is_collect' => 2]);
            }


            //完成任务加积分
            Gong::finishtask('shoucang','collect','uid') ;

            // 收藏行为加积分
            Gong::actionadd('shoucangtiezi','collect','uid') ;

            website_connect_notify($uid,$tid,$author_uid,'osapi_common_collect');//通知第三方平台，任务回调

            $data['info']='收藏成功！';
            Cache::rm('user_info_'.$uid);
            Cache::set('thread_detail_view_num_reget_'.$tid.'_uid_'.$uid,1,60);
            Cache::clear('thread_rank_list'.$uid);
            $this->apiSuccess($data);
        }else{
            $this->apiError('收藏失败！');
        }
    }

    /**
     * 取消收藏
     */
    public function delCollect(){
        $uid=$this->_needLogin();
        $tid = input('post.tid/d', 0);
        //减去任务增加的积分
        Gong::canceltask('shoucang','collect','uid') ;
        //减去点赞增加的积分
        Gong::actionsub('shoucangtiezi','collect','uid') ;
        $res=db('collect')->where('uid',$uid)->where('tid',$tid)->delete();
        if($res){
            db('com_thread')->where('id',$tid)->setDec('collect_count');
            db('com_post')->where('tid',$tid)->setDec('collect_count');
            db('user')->where('uid',$uid)->setDec('collect');
            $data['count']=db('com_thread')->where('id',$tid)->value('collect_count');
            $data['info']='取消收藏成功！';
            Cache::rm('user_info_'.$uid);
            Cache::set('thread_detail_view_num_reget_'.$tid.'_uid_'.$uid,1,60);
            Cache::clear('thread_rank_list'.$uid);

            $author_uid=ComThread::where('id',$tid)->value('author_uid');
            website_connect_notify($uid,$tid,$author_uid,'osapi_common_delCollect');//通知第三方平台，任务回调

            $this->apiSuccess($data);
        }else{
            $this->apiError('取消收藏失败！');
        }
    }



    /**
     * 全站搜索
     */
    public function search(){
        $uid=get_uid();
        $keyword = input('post.keyword', '','text');
        $page=input('page',1,'intval');
        $row=input('row',10,'intval');
        $data['user']=UserModel::searchUser($keyword,$page,$row);
        $data['forum']=ComForum::searchForum($keyword,$page,$row);
        foreach($data['forum'] as $key => &$value){
            $follow_count=db('com_forum_member')->where('fid',$value['id'])->where('status',1)->count();
            $value['is_follow']=ComForumMember::isForumUser($uid,$value['id']);
            $value['follow_count']=$follow_count+$value['false_num'];
            $sort_arr[] = $value['follow_count'];
        }
        unset($value);
//        $data['forum']['allCount']=ComForum::where('name','like','%'.$keyword.'%')->count();
        $data['thread']=ComThread::searchThread($keyword,$page,$row);
        $data['thread_news']=ComThread::searchThreadNews($keyword,$page,$row);
        $data['product']=StoreProduct::searchProduct($keyword,$page,$row);
        $data['thread_video']=ComThread::searchForumVideo($keyword,$page,$row);
        $data['thread_weibo']=ComThread::searchForumWeibo($keyword,$page,$row);
        RankSearch::addRankSearch($keyword);
        $this->apiSuccess($data);
    }

    /**
     * 社区搜索
     */
    public function searchOSX(){
        $uid=get_uid();
        $keyword = input('post.keyword', '','text');
        $page=input('page',1,'intval');
        $row=input('row',10,'intval');
        $data['user']=UserModel::searchUser($keyword,$page,$row);
        $data['forum']=ComForum::searchForum($keyword,$page,$row);
        foreach($data['forum'] as $key => &$value){
            $follow_count=db('com_forum_member')->where('fid',$value['id'])->where('status',1)->count();
            $value['is_follow']=ComForumMember::isForumUser($uid,$value['id']);
            $value['follow_count']=$follow_count+$value['false_num'];
            $sort_arr[] = $value['follow_count'];
        }
        unset($value);
        $data['thread']=ComThread::searchThread($keyword,$page,$row);
        $data['thread_news']=ComThread::searchThreadNews($keyword,$page,$row);
        RankSearch::addRankSearch($keyword);
        $this->apiSuccess($data);
    }

    /**
     * 版块内搜索
     */
    public function searchForum(){
        $keyword = input('post.keyword', '','text');
        $fid = input('post.fid/d', 0);
        $page=input('page',1,'intval');
        $row=input('row',10,'intval');
        $data['thread']=ComThread::searchForumThread($keyword,$page,$row,$fid);
        RankSearch::addRankSearch($keyword);
        $this->apiSuccess($data);
    }

    /**
     * 判断是否已经绑定手机号
     */
    public function is_mobile(){
        $uid=get_uid();
        $mobile=db('user')->where('uid',$uid)->value('phone');
        if(empty($mobile)){
            $is_phone=false;
        }else{
            $is_phone=true;
        }
        $this->apiSuccess($is_phone);
    }

    /**
     * 导航
     */
    public function nav(){
        $type = input('post.type', 0);
        $list=Cache::get('nav'.$type);
        if(!$list){
            $list=db('com_nav')->where('type',$type)->order('sort asc')->where('status',1)->select();
            foreach($list as &$val){
                $val['icon']=thumb_path($val['icon'],180,180);
                $val['link_url']=link_select_url($val['url']);
            }
            unset($val);
            Cache::set('nav'.$type,$list,600);
        }
        $this->apiSuccess($list);
    }
    /**
     * 导航类目 nyb
     * @author jiangxw
     */
    public function nav_nyb(){
        $type = input('post.type', 0);
        $list =Cache::get('nav'.$type);
        if(!$list){
            $list=db('com_nav')->where('type',$type)->order('sort asc')->where('status',1)->select();

        }
        $this->apiSuccess($list);
    }

    /**
     * 任务导航
     */
    public function renwu_nav(){
        $list=Cache::get('renwu_nav');
        if(!$list){
            $list=db('renwu_nav')->order('sort asc')->where('status',1)->select();
            foreach($list as &$val){
                $val['image']=thumb_path($val['image'],90,90);
                $val['link']=link_select_url($val['link']);
            }
            unset($val);
            Cache::set('renwu_nav',$list,600);
        }
        $this->apiSuccess($list);
    }

    /**
     * 获取用户已购列表
     */
    public function user_buy_product()
    {
        $page=input('page',1,'intval');
        $row=input('row',10,'intval');
        $uid=get_uid();
        $product_id=db('store_cart')->where('uid',$uid)->where('is_del',0)->page($page, $row)->where('is_pay',1)->order('add_time desc')->column('product_id');
        $product=array_unique($product_id);
        foreach($product as &$val){
            $val=db('store_product')->where('id',$val)->page($page,$row)->select();
            if($val){
                foreach($val as &$value){
                    $value['image_150']=thumb_path($value['image'],150,150);
                    $value['image_350']=thumb_path($value['image'],350,350);
                    $value['image_750']=thumb_path($value['image'],750,750);
                }
                unset($value);
            }
        }
        $this->apiSuccess($product);
    }

    /**
     * 获取社区设置配置
     */
    public function get_com_site(){
        $data=db('com_site')->where('id',1)->find();
        $this->apiSuccess($data);
    }


    /**
     * 获取后台配置信息
     */
    public function set_info()
    {
        $data=array();
        $data['share_title']=SystemConfig::getValue('share_title');
        $data['share_picture']=SystemConfig::getValue('share_picture');
        $data['share_picture']=get_root_path($data['share_picture']);
        $data['share_content']=SystemConfig::getValue('share_content');
        $data['forum_num_limit']=SystemConfig::getValue('forum_num_limit');
        $data['weibo_store_limit']=SystemConfig::getValue('weibo_store_limit');
        $data['weibo_content_limit']=SystemConfig::getValue('weibo_content_limit');
        $data['forum_content_limit']=SystemConfig::getValue('forum_content_limit');
        $data['forum_product_limit']=SystemConfig::getValue('forum_product_limit');
        $data['share_suffix']=SystemConfig::getValue('share_suffix');
        $data['code_site']=SystemConfig::getValue('code_site');
        $data['invite_show']=SystemConfig::getValue('invite_show');
        $data['phone']=SystemConfig::getValue('shop_phone');
        $data['weixin']=get_root_path(SystemConfig::getValue('service_code'));
        $this->apiSuccess($data);
    }

    /**
     * 获取后台视频配置信息
     */
    public function video_info()
    {
        $data=array();
        $data['video_size']=SystemConfig::getValue('video_size');
        $data['video_product']=SystemConfig::getValue('video_product');
        $data['video_title']=SystemConfig::getValue('video_title');
        $data['video_content']=SystemConfig::getValue('video_content');
        $data['video_title_down']=SystemConfig::getValue('video_title_down');
        $data['video_content_down']=SystemConfig::getValue('video_content_down');
        $this->apiSuccess($data);
    }

    /**
     * 关于我们
     */
    public function about_us()
    {
        $data=array();
        $data['website_name']=SystemConfig::getValue('website_name');
        $data['website_introduce']=SystemConfig::getValue('website_introduce');
        $data['website_logo']=get_root_path(SystemConfig::getValue('website_logo'));
        if(strpos($data['website_logo'],'http') === false){
            $url='http://'.$_SERVER['SERVER_NAME'];
            $data['website_logo']= $data['website_logo']? $url.$data['website_logo']:$data['website_logo'];
        }
        $data['business_cooperation']=SystemConfig::getValue('business_cooperation');
        $data['feedback']=SystemConfig::getValue('feedback');
        $this->apiSuccess($data);
    }

    /**
     * 获取用户协议
     */
    public function user_agreement()
    {
        $agreement=db('user_agreement')->where('id',1)->value('content');
        $this->apiSuccess($agreement);
    }

    /**
     * 全站热门搜索
     */
    public function get_all_hot_search(){
        $routineHotSearch = GroupDataService::getData('all_hot_search') ? :[];
        $this->apiSuccess($routineHotSearch);
    }

    /**
     * 获取pc端配置信息
     */
    public function pc_set()
    {
        $data=array();
        $data['default_avatar']=SystemConfig::getValue('default_avatar');
        $data['website_name']=SystemConfig::getValue('pc_icp');
        $data['pc_logo']=SystemConfig::getValue('pc_logo');
        $data['pc_logo']=get_root_path($data['pc_logo']);
        $data['default_avatar']=get_root_path($data['default_avatar']);
        $this->apiSuccess($data);
    }

    /**
     * 获取版主图标
     */
    public function forun_admin_set()
    {
        $data=array();
        $data['forum_admin_one']=SystemConfig::getValue('forum_admin_one');
        $data['forum_admin_one']=get_root_path($data['forum_admin_one']);
        $data['forum_admin_two']=SystemConfig::getValue('forum_admin_two');
        $data['forum_admin_two']=get_root_path($data['forum_admin_two']);
        $this->apiSuccess($data);
    }

    /**
     * 邀请有礼
     */
    public function invite_reward(){
        $page=input('post.page/d',1);
        $row=input('post.row/d',10);
        $time=time()-604800;
        $invite_reward=SystemConfig::getValue('invite_reward');
        if($invite_reward==1){
            $log=db('invite_log')->where('create_time','>',$time)->page($page,$row)->select();
            foreach ($log as &$value){
                if($value['reward_type']=='积分奖励'){
                    $score=json_decode($value['reward'],true);
                    $nickname=UserModel::where('uid',$value['father_uid'])->value('nickname');
                    $frist = mb_substr($nickname,0,1 );
                    $last = mb_substr($nickname,-1,1);
                    $nickname=$frist.'***'.$last;
                    $template=$nickname.'成功邀请好友1名，获得：';
                    if($score){
                        foreach ($score as &$val){
                            $val['name']=db('system_rule')->where('flag',$val['flag'])->value('name');
                            $template=$template.$val['name'].':'.$val['value'].'；';
                        }
                    }
                    unset($val);
                    $value['template']=$template;
                }
            }
            unset($value);
        }else{
            $log='';
        }
        $this->apiSuccess($log);
    }

    private function http_request($url, $data = null)
    {
        $postUrl = $url;
        $curlPost = $data;
        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($curl, CURLOPT_HEADER, 0);//设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_POST, true);//post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS,$curlPost);//提交的参数
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($curl);//运行curl
        curl_close($curl);

        return $data;
    }

    /**
     * 话题搜索
     */
    public function topic_search(){
        $keyword = input('post.keyword', '','text');
        $page=input('page',1,'intval');
        $row=input('row',10,'intval');
        $topic=ComTopic::searchTopic($keyword,$page,$row);
        RankSearch::addRankSearch($keyword);
        $this->apiSuccess($topic);
    }

    /**
     * 关注话题
     */
    public function followTopic(){
        $uid=$this->_needLogin();
        $oid = input('post.oid/d', 0);
        $id=db('com_topic_follow')->where('oid',$oid)->where('uid',$uid)->value('id');
        if($id){
            $data['status']=1;
            $data['create_time']=time();
            $res=db('com_topic_follow')->where('id',$id)->update($data);
        }else{
            $data['status']=1;
            $data['create_time']=time();
            $data['uid']=$uid;
            $data['oid']=$oid;
            $res=db('com_topic_follow')->insert($data);
        }
        if($res!==false){
            Cache::rm('topic_detail'.$oid);
            Cache::clear('user_follow_topic_list');
            Cache::clear('user_send_topic_list');
            website_connect_notify($uid,$oid,0,'osapi_common_followTopic');//通知第三方平台，任务回调

            $this->apiSuccess('关注成功');
        }else{
            $this->apiError('关注失败');
        }
    }

    /**
     * 取消关注话题
     */
    public function followTopicDel(){
        $uid=$this->_needLogin();
        $oid = input('post.oid/d', 0);
        $data['status']=0;
        $res=db('com_topic_follow')->where('oid',$oid)->where('uid',$uid)->update($data);
        if($res!==false){
            Cache::rm('topic_detail'.$oid);
            Cache::clear('user_follow_topic_list');
            Cache::clear('user_send_topic_list');
            website_connect_notify($uid,$oid,0,'osapi_common_followTopicDel');//通知第三方平台，任务回调

            $this->apiSuccess('取消关注成功');
        }else{
            $this->apiError('取消关注失败');
        }
    }

    /**
     * 拉黑或取消拉黑
     * @author qhy
     */
    public function doBlack()
    {
        $uid=$this->_needLogin();
        $black_uid = input('post.uid/d', 0);//需要拉黑的用户uid
        $is_black=Blacklist::isBlack($uid,$black_uid);
        if(!$is_black){
            $res=Blacklist::doBlack($uid,$black_uid);
        }else{
            $res=Blacklist::doDelBlack($uid,$black_uid);
        }
        if($res!==false){
            Cache::rm('Blacklist'.$uid);
            $this->apiSuccess('操作成功！');
        }else{
            $this->apiError('操作失败！');
        }
    }

    /**
     * 黑名单列表
     * @author qhy
     */
    public function Blacklist()
    {
        $uid=get_uid();
        $list=Cache::get('Blacklist'.$uid);
        if(!$list){
            $list=Blacklist::getBlackList($uid);
            Cache::set('Blacklist'.$uid,$list,3600);
        }
        $this->apiSuccess($list);
    }

    /**
     * 支付设置
     * @author qhy
     */
    public function PaySet()
    {
        $data=db('pay_set')->select();
        $this->apiSuccess($data);
    }

    /**
     * 小程序视频是否开启
     * @author qhy
     */
    public function VideoIsOn()
    {
        $data['xcx_video']=SystemConfig::getValue('xcx_video');
        $this->apiSuccess($data);
    }

}