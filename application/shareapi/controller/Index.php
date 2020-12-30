<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/11/19
 * Time: 10:31
 */

namespace app\shareapi\controller;


use app\admin\model\system\SystemConfig;
use app\ebapi\model\store\StoreOrder;
use app\ebapi\model\store\StoreProduct;
use app\osapi\model\user\InviteCode;
use app\shareapi\model\Sell;
use app\shareapi\model\SellOrder;
use basic\ControllerBasic;
use service\UtilService;


class Index extends ControllerBasic
{
    /**
     * 是否是分销商
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function isSeller()
    {
        $uid=$this->_needLogin();
        $is_seller=is_seller($uid);
        $this->apiSuccess(['is_seller'=>$is_seller?1:0]);
    }

    /**
     * 如何成为分销商
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function howToSeller()
    {
        $uid=$this->_needLogin();
        $seller_info=Sell::get(['uid'=>$uid]);
        $is_seller=$on_apply=$is_disable=0;
        if($seller_info){
            if($seller_info['status']==0){
                $is_disable=1;
            }
            if($seller_info['status']==1){
                $is_seller=1;
            }
            if($seller_info['status']==2){
                $on_apply=1;
            }
        }
        if(($seller_info&&$seller_info['status']==3)||!isset($seller_info)) {
            $agent_config = SystemConfig::getValue('agent_config');
            $how_to_seller['agent_way']=$agent_config['agent_way'];
            switch ($agent_config['agent_way']) {
                case 1://无条件（需要审核）
                    break;
                case 2://设置条件（需要审核）
                    $rule_list_title=[];
                    $all_arrive=1;
                    foreach ($agent_config['agent_rules'] as $val){
                        /**
                         * store_pay  商城消费满**元
                         * com_post   社区发帖满**条
                         * total_score 累计经验值达**
                         * column_pay  知识商城消费满**元
                         */
                        if($val['key']=='store_pay'){
                            $map_store['status']=array('in',[2,3]);
                            $map_store['is_del']=0;
                            $map_store['is_zg']=0;
                            $map_store['refund_status']=0;
                            $map_store['uid']=$uid;
                            $total_pay=StoreOrder::where($map_store)->sum('pay_price');
                            if($total_pay>=$val['value']){
                                $rule_list_title[]='商城消费满'.$val['value'].'元(已满足)';
                            }else{
                                $all_arrive=0;
                                $show_val=floatval($val['value'])-floatval($total_pay);
                                $rule_list_title[]='商城消费满'.$val['value'].'元(差'.$show_val.'元)';
                            }
                        }
                        if($val['key']=='com_post'){
                            $map_thread['author_uid']=$uid;
                            $map_thread['status']=1;
                            $total_thread=db('com_thread')->where($map_thread)->count();
                            if($total_thread>=$val['value']){
                                $rule_list_title[]='社区发帖满'.$val['value'].'条(已满足)';
                            }else{
                                $all_arrive=0;
                                $rule_list_title[]='社区发帖满'.$val['value'].'条(差'.($val['value']-$total_thread).'条)';
                            }
                        }
                        if($val['key']=='total_score'){
                            $map_exp['uid']=$uid;
                            $exp=db('user')->where($map_exp)->value('exp');
                            if($exp>=$val['value']){
                                $rule_list_title[]='累计经验值达'.$val['value'].'(已达成)';
                            }else{
                                $all_arrive=0;
                                $rule_list_title[]='累计经验值达'.$val['value'].'(差'.($val['value']-$exp).')';
                            }
                        }
                        if($val['key']=='column_pay'){
                            $map_store['status']=array('in',[2,3]);
                            $map_store['is_del']=0;
                            $map_store['is_zg']=1;
                            $map_store['refund_status']=0;
                            $map_store['uid']=$uid;
                            $total_pay=StoreOrder::where($map_store)->sum('pay_price');
                            if($total_pay>=$val['value']){
                                $rule_list_title[]='知识商城消费满'.$val['value'].'元(已满足)';
                            }else{
                                $all_arrive=0;
                                $show_val=floatval($val['value'])-floatval($total_pay);
                                $rule_list_title[]='知识商城消费满'.$val['value'].'元(差'.$show_val.'元)';
                            }
                        }
                    }
                    unset($val);
                    $how_to_seller['arrive_condition']=$all_arrive;//是否条件都满足了
                    $how_to_seller['need_condition']=$rule_list_title;//具体条件文字
                    break;
                case 3://购买商品自动生效
                    $how_to_seller['goods_type']=$agent_config['goods_type'];//all_goods 全部商品   ；one_goods 指定商品
                    if($agent_config['goods_type']=='one_goods'){
                        $how_to_seller['goods_id']=$agent_config['goods_id'];//指定商品时指定商品的id
                        $how_to_seller['goods']=db('store_product')->find($agent_config['goods_id']);//对应商品信息
                    }elseif($agent_config['goods_type']=='column_goods'){
                        $how_to_seller['goods_id']=$agent_config['goods_id'];
                        $how_to_seller['goods']=db('store_product')->find($agent_config['goods_id']);
                    }
                    break;
                default:
            }
        }
        $how_to_seller['is_seller']=$is_seller;
        $how_to_seller['on_apply']=$on_apply;
        $how_to_seller['is_disable']=$is_disable;
        $this->apiSuccess($how_to_seller);
    }

    /**
     * 分销商申请协议获取
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function getAgreement()
    {
        $agent_xieyi_config = SystemConfig::getValue('agent_xieyi_config');
        $this->apiSuccess(['agent_xieyi_config'=>$agent_xieyi_config]);
    }

    /**
     * 申请成为分销商
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function applySell()
    {
        $uid=$this->_needLogin();
        $seller_info=Sell::get(['uid'=>$uid]);
        if($seller_info&&in_array($seller_info['status'],[0,1,2])) {
            return $this->apiError('申请正在审核中或已禁用……');
        }
        $can_apply=true;
        $agent_config = SystemConfig::getValue('agent_config');
        switch ($agent_config['agent_way']) {
            case 1://无条件（需要审核）
                break;
            case 2://设置条件（需要审核）
                foreach ($agent_config['agent_rules'] as $val){
                    /**
                     * store_pay  商城消费满**元
                     * com_post   社区发帖满**条
                     * total_score 累计经验值达**
                     */
                    if($val['key']=='store_pay'){
                        $map_store['status']=array('in',[2,3]);
                        $map_store['is_del']=0;
                        $map_store['is_zg']=0;
                        $map_store['refund_status']=0;
                        $map_store['uid']=$uid;
                        $total_pay=StoreOrder::where($map_store)->sum('pay_price');
                        if($total_pay<$val['value']){
                            $can_apply=false;
                        }
                    }
                    if($val['key']=='com_post'){
                        $map_thread['author_uid']=$uid;
                        $map_thread['status']=1;
                        $total_thread=db('com_thread')->where($map_thread)->count();
                        if($total_thread<$val['value']){
                            $can_apply=false;
                        }
                    }
                    if($val['key']=='total_score'){
                        $map_exp['uid']=$uid;
                        $exp=db('user')->where($map_exp)->value('exp');
                        if($exp<$val['value']){
                            $can_apply=false;
                        }
                    }
                }
                unset($val);
                break;
            case 3://购买商品自动生效
                $can_apply=false;
                break;
            default:
        }
        if($can_apply){
            if($seller_info&&$seller_info['status']==3) {
                $res=Sell::update(['status'=>2,'create_time'=>time()],['uid'=>$uid,'status'=>3]);
                if($res!==false){
                    return $this->apiSuccess('申请成功，请等待审核……');
                }else{
                    return $this->apiSuccess('申请失败，请稍后再试');
                }
            }else{
                $invite_level=db('invite_level')->where('uid',$uid)->find();
                $res=Sell::set([
                    'uid'=>$uid,
                    'child1_num'=>0,
                    'child2_num'=>0,
                    'order_num'=>0,
                    'order_money'=>0,
                    'total_income'=>0,
                    'out_income'=>0,
                    'out_num'=>0,
                    'has_income'=>0,
                    'father1'=>$invite_level?$invite_level['father1']:0,
                    'father2'=>$invite_level?$invite_level['father2']:0,
                    'status'=>2,
                    'create_time'=>time(),
                ]);
                if($res){
                    return $this->apiSuccess('申请成功，请等待审核……');
                }else{
                    return $this->apiSuccess('申请失败，请稍后再试');
                }
            }
        }else{
            return $this->apiError('不满足申请条件');
        }
    }

    /**
     * 邀请海报
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function shareHaiBao()
    {
        $qrcode_url=UtilService::getMore([
            'qrcode_url'
        ]);
        $qrcode_url=urldecode($qrcode_url['qrcode_url']);
        $haiBaoList=$this->_delHaiBao($qrcode_url);
        $return_data=[
            'haiBaoList'=>$haiBaoList,//海报列表
            'haiBaoNum'=>count($haiBaoList),//海报数量
        ];
        $this->apiSuccess($return_data);
    }


    /**
     * 推广中心首页
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function sellerInfo()
    {
        $uid=$this->_needLogin();
        $seller_info=Sell::get(['uid'=>$uid]);
        if(!$seller_info||$seller_info['status']!=1){
            return $this->apiError('请先成为分销商！');
        }
        $invite_code=InviteCode::getInviteCode($uid);
        //本月收益预估
        $back1_month=SellOrder::where('father1',$uid)->whereTime('create_time','month')->sum('father1_back');
        $back2_month=SellOrder::where('father2',$uid)->whereTime('create_time','month')->sum('father2_back');
        $back_month=$back1_month+$back2_month;
        //今日收益预估
        $back1_day=SellOrder::where('father1',$uid)->whereTime('create_time','today')->sum('father1_back');
        $back2_day=SellOrder::where('father2',$uid)->whereTime('create_time','today')->sum('father2_back');
        $back_day=$back1_day+$back2_day;
        $return_data=[
            'invite_code'=>$invite_code,//邀请码
            'has_income'=>$seller_info['has_income'],//可提现收益
            'agent_tixian_config_day'=>SystemConfig::getValue('agent_tixian_config_day'),//每月X号，可提现上月
            'total_income'=>$seller_info['total_income'],//累计收益
            'month_income'=>$back_month,//本月预估收益
            'day_income'=>$back_day,//今日预估收益
        ];
        $this->apiSuccess($return_data);
    }

    /**
     * 获取推荐分销商品列表
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function sellerGoodsList()
    {
        $field='add_time,browse,is_type,cate_id,code_path,cost,description,ficti,give_integral,id,image,is_bargain,is_benefit,is_best,is_del,is_hot,is_new,is_postage,is_seckill,is_show,keyword,mer_id,mer_use,ot_price,postage,price,sales,slider_image,sort,stock,store_info,store_name,unit_name,vip_price,IFNULL(sales,0) + IFNULL(ficti,0) as fsales,strip_num';
        return $this->apiSuccess(StoreProduct::getSellerProduct($field));
    }

    public function sellerColumnGoodsList()
    {
        $field='add_time,browse,is_type,cate_id,code_path,cost,description,ficti,give_integral,id,image,is_bargain,is_benefit,is_best,is_del,is_hot,is_new,is_postage,is_seckill,is_show,keyword,mer_id,mer_use,ot_price,postage,price,sales,slider_image,sort,stock,store_info,store_name,unit_name,vip_price,IFNULL(sales,0) + IFNULL(ficti,0) as fsales,strip_num';
        return $this->apiSuccess(StoreProduct::getColumnSellerProduct($field));
    }

    /**
     * 分享海报配置-分享商品页面中的企业logo和企业名称
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function shareConfig()
    {
        $agent_share_config=SystemConfig::getMore('agent_share_config_logo,agent_share_config_title');
        $agent_share_config['agent_share_config_logo']=get_root_path($agent_share_config['agent_share_config_logo']);
        $this->apiSuccess(['agent_share_config'=>$agent_share_config]);
    }

    private function _delHaiBao($code_url)
    {
        $uid=$this->_needLogin();
        $is_seller=is_seller($uid);
        $invite_show=SystemConfig::getValue('invite_show');
        //是否显示昵称
        $invite_show_name=SystemConfig::getValue('invite_show_name');

        $invite_code=InviteCode::getInviteCode($uid);
        $invite_code=$invite_code['code'];
        $haiBaoList=db('invite_share')->where('status',1)->order('sort desc')->select();

        //海报宽高
        $img_width=563;
        $img_height=1000;

        //邀请码宽高
        $code_width=165;
        $code_height=165;

        //二维码左边和顶部距离
        $code_x=38;
        $code_y=768;

        //“邀请码”三个字左边和顶部距离、字体大小、颜色
        $word_x=180;
        $word_y=925;
        $word_fontSize = 18;
        $word_color="0,0,0";//rgb的三元素，号拼接
        $word_color=explode(',',$word_color);

        //昵称位置
        $nickname_x=302;
        $nickname_y=864;
        $nickname_fontSize = 22;
        $nickname_color="255,255,255";//rgb的三元素，号拼接
        $nickname_color=explode(',',$nickname_color);
        //头像尺寸
        $avatar_x=241;
        $avatar_y=851;
        $avatar_w=$avatar_h=45;
        $avatar=db('user')->where(['uid'=>$uid])->value('avatar');
        list($avatar,$w)= self::yuan_img($avatar);
        $imgg = self::get_new_size($avatar,$avatar_w,$avatar_h,$w);//小程序头像其实不用裁剪，小程序头像本身就是132*132的，不过文档好像没更新

//        $avatarImg =imagecreatefromstring(file_get_contents($avatar));

        //邀请码文字左边和顶部距离、字体大小、颜色
        $code_word_x=241;
        $code_word_y=913;
        $code_word_fontSize = 20;
        $code_word_color="255,255,255";//rgb的三元素，号拼接
        $code_word_color=explode(',',$code_word_color);

        //字体文件
        $word_font_file = PUBILC_PATH.'/static/font/simsunb.ttf';
        //旋转角度
        $word_circleSize = 0;

        //生成二维码 start
        require_once ROOT.'/vendor/phpqrcode/phpqrcode.php';
        $qrcode=new \QRcode();
        if($invite_show==1){
            $code_url = $code_url.$invite_code;//二维码内容
        }
        $errorCorrectionLevel = 'H';    //容错级别
        $matrixPointSize = 7;           //生成二维码图片大小

        $thumb_dir_path=UPLOAD_PATH.'/haibao/'.$uid.'/';
        if (!is_dir($thumb_dir_path)){
            mkdir($thumb_dir_path,0777,true);
        }

        $qrcode_file_name=$thumb_dir_path.'thumb_qr_code'.$invite_code.'.png';
        ob_start();
        $qrcode::png($code_url,$qrcode_file_name , $errorCorrectionLevel, $matrixPointSize, 2);
        ob_end_clean();//关闭缓冲区
        $qrcode_image = getThumbImage($qrcode_file_name,$code_width,$code_height);
        $qCodeImg =imagecreatefromstring(file_get_contents($qrcode_image['src']));
        unlink($qrcode_file_name);
        if($qrcode_file_name!=$qrcode_image['src']){
            unlink($qrcode_image['src']);
        }
        //生成二维码 end
        $i=0;
        foreach ($haiBaoList as &$val) {
            $bigImgPath = getThumbImage($val['url'], $img_width, $img_height);
            $bigImgPath = $bigImgPath['src'];
            $bigImg = imagecreatefromstring(file_get_contents($bigImgPath));
            imagecopymerge($bigImg, $qCodeImg, $code_x, $code_y, 0, 0, $code_width, $code_height, 100);

            if ($is_seller || $invite_show == 1) {

                $code_word_color_pic = imagecolorallocate($bigImg, $code_word_color[0], $code_word_color[1], $code_word_color[2]);
                imagefttext($bigImg, $code_word_fontSize, $word_circleSize, $code_word_x, $code_word_y, $code_word_color_pic, $word_font_file, '邀请口令:' . $invite_code);
//                //邀请码文字
//                $code_word_color_pic  =  imagecolorallocate ( $bigImg ,  $code_word_color[0] ,  $code_word_color[1] ,  $code_word_color[2] );
//                imagefttext($bigImg, $code_word_fontSize, $word_circleSize, $code_word_x, $code_word_y, $code_word_color_pic, $word_font_file, $invite_code);
            } else {
                //码上有惊喜
                $code_word_fontSize = 20;
                $code_word_color_pic = imagecolorallocate($bigImg, $code_word_color[0], $code_word_color[1], $code_word_color[2]);
                imagefttext($bigImg, $code_word_fontSize, $word_circleSize, $code_word_x, $code_word_y, $code_word_color_pic, $word_font_file, "码上有惊喜");
            }
            //头像绘画
            imagecopymerge($bigImg, $imgg, $avatar_x+10, $avatar_y-20, 0, 0, $avatar_w, $avatar_h, 100);

            //昵称描画
            if($invite_show_name==1){
                $name=db('user')->where(['uid'=>$uid])->value('nickname');
                $code_word_color_pic  =  imagecolorallocate ( $bigImg ,  $nickname_color[0] ,  $nickname_color[1] ,  $nickname_color[2] );
                imagefttext($bigImg, $nickname_fontSize, $word_circleSize, $nickname_x, $nickname_y, $code_word_color_pic, $word_font_file, $name);
            }
            $file_name=$thumb_dir_path.'thumb'.$invite_code.'_'.$i.'.png';
            imagepng($bigImg,$file_name);
            $val['url']=get_root_path($file_name,true);
            $i++;
        }
        unset($val);
        /*if(isset($file_name)){
            unlink($file_name);
        }*/
        return $haiBaoList;
    }

    public function yuan_img($imgpath)
    {
        $wh  = getimagesize($imgpath);//pathinfo()不准
        $src_img = imagecreatefromjpeg($imgpath);
        $w   = $wh[0];
        $h   = $wh[1];
        $w   = min($w, $h);
        $h   = $w;
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r   = $w / 2; //圆半径
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        return [$img,$w];
    }

    /*
    * 根据指定尺寸裁剪目标图片，这里统一转成45*45的
    * 注意第一个参数，为了简便，直接传递的是图片资源，如果是绝对地址图片路径，可以加以改造
    */
    private function get_new_size($imgpath,$new_width,$new_height,$w)
    {
        $image_p = imagecreatetruecolor($new_width, $new_height);//新画布
        $bg = imagecolorallocatealpha($image_p, 255, 255, 255, 127);
        imagefill($image_p, 0, 0, $bg);
        imagecopyresampled($image_p, $imgpath, 0, 0, 0, 0, $new_width, $new_height, $w, $w);
        return $image_p;
    }
}