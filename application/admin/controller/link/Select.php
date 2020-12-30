<?php
/**
 * OpenSNS X
 * Copyright 2014-2020 http://www.thisky.com All rights reserved.
 * ----------------------------------------------------------------------
 * Author: 郑钟良(zzl@ourstu.com)
 * Date: 2019/12/25
 * Time: 10:02
 */

namespace app\admin\controller\link;


use app\admin\controller\AuthController;
use app\admin\model\certification\CertificationCate;
use app\admin\model\column\StoreCategoryColumn;
use app\admin\model\com\ComForum;
use app\admin\model\com\ComTopic;
use app\admin\model\com\ComThread;
use app\admin\model\com\ComThreadClass;
use app\admin\model\shop\ShopColumn;
use app\admin\model\shop\ShopProduct;
use app\admin\model\store\StoreCategory;
use app\admin\model\store\StoreProduct;
use app\admin\model\user\User;
use service\JsonService;
use service\UtilService;

/**
 * 链接选择器
 * Class Select
 * @package app\admin\controller\link
 */

/**
 * 注意：选择出的值$link_url是“说明||链接地址”，实际前台使用时需要做字符串处理，调用link_select_url($link_url)取出“链接地址”部分，返回前台使用
 */

/**
 * 使用方式一：formBuilder中调用   frameInputOne
 * 如下示例
 */
//$field = [
//    FormBuilder::frameInputOne('link_url','跳转链接设置',Url::build('admin/link.select/index',array('fodder'=>'link_url'),$link_url))->icon('link-select'),
//];
//$form = FormBuilder::make_post_form('编辑版块',$field,Url::build('update_one',array('id'=>$id)),2);
//$this->assign(compact('form'));
//return $this->fetch('public/form-builder');
//注意：：：：编辑保存时获得的数据$link_url是“说明||链接地址”，原样保存到数据库，方便后续管理员查看当前链接地址，实际前台使用时接口要将两者分开，可调用link_select_url($link_url)获取实际前台使用url

/**
 * 使用方式二：普通form表单中调用
 * 如下示例
 */

class Select extends AuthController
{
    /**
     * 获取导航菜单列表
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function index(){
        $this->assign('all_tab_list',$this->_getTabList());
        return $this->fetch();
    }


    /**
     * 版块查找
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function forumSelect(){
        $param=UtilService::getMore(['keyword']);
        $keyword=trim(text($param['keyword']));
        if($keyword==''){
            JsonService::success(['list'=>[],'count'=>0]);
        }
        $forumList=ComForum::where('status',1)->where('name','like','%'.$keyword.'%')->where('pid','>',0)->order('sort desc,create_time desc')->limit(20)->select()->toArray();

        //版块列表
        foreach ($forumList as &$item){
            $item['create_time']=time_format($item['create_time']);
            $item['update_time']=time_format($item['update_time']);

            $item['pid_name']   =$item['pid'] == 0?'顶级分区': ComForum::where('id',$item['pid'])->value('name').'【'.$item['pid'].'】';

            $title=str_replace('||','|',$item['name']);
            $item['link_title']='['.$item['id'].']'.(iconv_strlen($title)>8?(mb_substr($title,0,8).'...'):$title);
            switch ($item['type']){
                case 4:
                    $item['link_url']='/packageA/forum-detail/news?fid='.$item['id'];
                    break;
                default:
                    $item['link_url']='/packageA/forum-detail/normal?fid='.$item['id'];
            }
        }
        unset($item);
        $tabList=$this->_getTabList();
        JsonService::successful(['count'=>count($forumList),'list'=>$forumList,'tab_title'=>$tabList['forum']['title']]);
    }

    /**
     * 商品搜索
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function goodsSelect(){
        $param=UtilService::getMore(['keyword']);
        $keyword=trim(text($param['keyword']));
        if($keyword==''){
            JsonService::successful([
                'eb_goods'=>['count'=>0,'list'=>'','tab_title'=>''],
                'zg_goods'=>['count'=>0,'list'=>'','tab_title'=>''],
                'shop_goods'=>['count'=>0,'list'=>'','tab_title'=>'']
            ]);
        }
        $tabList=$this->_getTabList();
        $goods_open_list=$tabList['goods']['bind_name'];
        //商城商品
        if(in_array('eb',$goods_open_list)){
            $ebGoodsList=StoreProduct::where('is_show',1)->where('is_del',0)->where('is_type',0)->where(function ($queryOr) use($keyword) {
                $queryOr->where('id', $keyword)->whereOr('store_name', 'like', '%' . $keyword . '%')->whereOr('keyword', 'like', '%' . $keyword . '%');
            })->order('sort desc,add_time desc')->field('id,image,store_name,price,sales,stock,keyword')->limit(20)->select()->toArray();
            foreach ($ebGoodsList as &$item){
                $title=str_replace('||','|',$item['store_name']);
                $item['link_title']='['.$item['id'].']'.(iconv_strlen($title)>8?(mb_substr($title,0,8).'...'):$title);
                $item['link_url']='/packageB/product/product?id='.$item['id'];
            }
            unset($val);
        }else{
            $ebGoodsList=[];//无商城权限时，用该方案
        }

        //知识商城商品
        if(in_array('zg',$goods_open_list)) {
            $zgGoodsList = StoreProduct::where('is_show', 1)->where('is_del', 0)->where('is_type', 1)->where(function ($queryOr) use ($keyword) {
                $queryOr->where('id', $keyword)->whereOr('store_name', 'like', '%' . $keyword . '%')->whereOr('keyword', 'like', '%' . $keyword . '%');
            })->order('sort desc,add_time desc')->field('id,image,store_name,price,sales,stock,keyword')->limit(20)->select()->toArray();
            foreach ($zgGoodsList as &$item) {
                $title = str_replace('||', '|', $item['store_name']);
                $item['link_title'] = '['.$item['id'].']'.(iconv_strlen($title)>8?(mb_substr($title,0,8).'...'):$title);
                $item['link_url'] = '/packageC/columnsDetails/columnsDetails?id=' . $item['id'];
            }
            unset($val);
        }else{
            $zgGoodsList=[];//无知识商城权限时，用该方案
        }

        //积分商城商品
        if(in_array('shop',$goods_open_list)) {
            $shopGoodsList = ShopProduct::where('is_on', 1)->where('status', 1)->where(function ($queryOr) use ($keyword) {
                $queryOr->where('id', $keyword)->whereOr('store_name', 'like', '%' . $keyword . '%');
            })->order('sort desc,add_time desc')->field('id,image,store_name,score_price,cash_price,sales,ficti,stock')->limit(20)->select()->toArray();
            foreach ($shopGoodsList as &$item) {
                $title = str_replace('||', '|', $item['store_name']);
                $item['link_title'] = '['.$item['id'].']'.(iconv_strlen($title)>8?(mb_substr($title,0,8).'...'):$title);
                $item['link_url'] = '/packageC/points-goods/detail?id=' . $item['id'];
            }
            unset($val);
        }else{
            $shopGoodsList=[];//无积分商城权限时，用该方案
        }

        JsonService::successful([
            'eb_goods'=>['count'=>count($ebGoodsList),'list'=>$ebGoodsList,'tab_title'=>$tabList['eb']['title']],
            //'zg_goods'=>['count'=>count($zgGoodsList),'list'=>$zgGoodsList,'tab_title'=>$tabList['zg']['title']],
            //'shop_goods'=>['count'=>count($shopGoodsList),'list'=>$shopGoodsList,'tab_title'=>$tabList['shop']['title']]
        ]);
    }

    /**
     * 帖子查询
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    public function postSelect(){
        $param=UtilService::getMore(['keyword']);
        $keyword=trim(text($param['keyword']));
        if($keyword==''){
            JsonService::success(['list'=>[],'count'=>0,'tab_title'=>'']);
        }
        $postList=ComThread::where('status',1)->where(function ($queryOr) use($keyword) {
            $queryOr->where('id', $keyword)->whereOr('title', 'like', '%' . $keyword . '%')->whereOr(function ($queryAnd) use($keyword) {
                $queryAnd->where('title', '')->where('content', 'like', '%' . $keyword . '%');
            });
        })->order('sort desc,create_time desc')->limit(20)->select()->toArray();

        //普通列表
        foreach ($postList as &$item){
            $item['title']==''&&$item['title']=mb_strcut(strip_tags(htmlspecialchars_decode(text(json_decode($item['content'])))),0,180,'utf-8');
            $item['forum_name']      =$item['fid'] == 0?'未关联版块': ComForum::where('id',$item['fid'])->cache(1)->value('name').'【'.$item['fid'].'】';
            $item['class_name']      = $item['class_id'] == 0?'未关联分类':ComThreadClass::where('id', $item['class_id'])->cache(1)->value('name')."【{$item['class_id']}】";

            $item['user_info']    = User::where('uid',$item['author_uid'])->field('uid,nickname,avatar')->find()->toArray();
            $item['user_info']['avatar64']=thumb_path($item['user_info']['avatar'],128,128);

            $item['create_time']=time_format($item['create_time']);
            $item['update_time']=time_format($item['update_time']);

            $title=str_replace('||','|',$item['title']);
            $item['link_title']='['.$item['id'].']'.(iconv_strlen($title)>8?(mb_substr($title,0,8).'...'):$title);
            $item['link_url']='/packageA/post-page/post-page?id='.$item['id'];
        }
        unset($item);
        $tabList=$this->_getTabList();
        JsonService::successful(['count'=>count($postList),'list'=>$postList,'tab_title'=>$tabList['post']['title']]);
    }

    /**
     * 话题查询
     * @author 钱豪阳
     * @date 2020-3
     */
    public function topicSelect(){
        $param=UtilService::getMore(['keyword']);
        $keyword=trim(text($param['keyword']));
        if($keyword==''){
            JsonService::success(['list'=>[],'count'=>0]);
        }
        $topicList=ComTopic::where('status',1)->where('title','like','%'.$keyword.'%')->order('update_time desc,create_time desc')->limit(20)->select()->toArray();

        //版块列表
        foreach ($topicList as &$item){
            $item['create_time']=time_format($item['create_time']);
            $item['update_time']=time_format($item['update_time']);
            $item['nickname']=User::where('uid',$item['uid'])->value('nickname');
            $title=str_replace('||','|',$item['title']);
            $item['link_title']='['.$item['id'].']'.(iconv_strlen($title)>8?(mb_substr($title,0,8).'...'):$title);
            $item['link_url']='/packageA/topic/topic-detail?id='.$item['id'];
        }
        unset($item);
        $tabList=$this->_getTabList();
        JsonService::successful(['count'=>count($topicList),'list'=>$topicList,'tab_title'=>$tabList['forum']['title']]);
    }

    /**
     * 侧边导航列表
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private function _getTabList(){
        $linkTabList=[
            'os'=>[
                'name'=>'os',//鉴权唯一标识，不可更改，不可重复
                'title'=>'社区',
                'type'=>'link_list',//链接列表
                'level'=>2,//层级数（2层）
                'tab_list'=>[
                    'tab1'=>[
                        'tab_title'=>'社区常用',
                        'link_list'=>[
                            ['title'=>'社区首页','link_url'=>'/pages/index/index'],
                            ['title'=>'版块列表页','link_url'=>'/pages/forum-page/classify-first'],
                            ['title'=>'发帖页面','link_url'=>'/pages/publish/publish-select'],
                            ['title'=>'全站搜索','link_url'=>'/packageA/search/search'],
                        ],
                    ],
                    'tab2'=>[
                        'tab_title'=>'话题',
                        'link_list'=>[
                            ['title'=>'话题分类','link_url'=>'/packageA/topic/topic-class'],
                            ['title'=>'我的话题','link_url'=>'/packageA/topic/my-topic'],
                        ],
                    ],
                    'tab3'=>[
                        'tab_title'=>'榜单',
                        'link_list'=>[
                            ['title'=>'榜单列表','link_url'=>'/packageA/ranking/index'],
                            ['title'=>'帖子热评榜','link_url'=>'/packageA/ranking/forum?type=0'],
                            ['title'=>'视频热评榜','link_url'=>'/packageA/ranking/forum?type=2'],
                            ['title'=>'动态热评榜','link_url'=>'/packageA/ranking/forum?type=1'],
                            ['title'=>'资讯热评榜','link_url'=>'/packageA/ranking/forum?type=3'],
                            ['title'=>'话题榜','link_url'=>'/packageA/ranking/topic'],
                            ['title'=>'人气榜','link_url'=>'/packageA/ranking/popular'],
                            ['title'=>'热搜榜','link_url'=>'/packageA/ranking/search'],
                        ],
                    ],
                ],
            ],
            'eb'=>[
                'name'=>'eb',//鉴权唯一标识，不可更改，不可重复
                'title'=>'商城',
                'type'=>'link_list',//链接列表
                'level'=>2,//层级数（2层）
                'tab_list'=>[
                    'tab1'=>[
                        'tab_title'=>'商城页面',
                        'link_list'=>[
                            ['title'=>'商城首页','link_url'=>'/pages/mix-mall/index/index'],
                            ['title'=>'商品分类','link_url'=>'/packageB/category/category'],
                            ['title'=>'全部商品页','link_url'=>'/packageB/product/list'],
                            ['title'=>'领券中心','link_url'=>'/packageB/coupon-page/coupon'],
                            ['title'=>'商城搜索','link_url'=>'/packageB/search/search'],
                            ['title'=>'购物车','link_url'=>'/packageB/cart/cart'],
                        ],
                    ],
                    'tab2'=>[
                        'tab_title'=>'商品属性',
                        'link_list'=>[
                            ['title'=>'拼团列表','link_url'=>'/packageB/group/list'],
                            ['title'=>'限时秒杀','link_url'=>'/packageB/seckill/list'],
                            ['title'=>'精品推荐','link_url'=>'/packageB/bastList/bastList?type=1&title=精品推荐'],
                            ['title'=>'新品首发页','link_url'=>'/packageB/bastList/bastList?type=3&title=新品首发'],
                        ],
                    ],
                    'tab3'=>[
                        'tab_title'=>'商品分类',
                        'link_list'=>$this->_getEbCategoryList(),
                    ],
                ],
            ],
            'zg'=>[
                'name'=>'zg',//鉴权唯一标识，不可更改，不可重复
                'title'=>'知识商城',
                'type'=>'link_list',//链接列表
                'level'=>2,//层级数（2层）
                'tab_list'=>[
                    'tab1'=>[
                        'tab_title'=>'商城页面',
                        'link_list'=>[
                            ['title'=>'商城首页','link_url'=>'/pages/mix-mall/index/knowledge'],
                            ['title'=>'商品分类','link_url'=>'/packageC/columns/columns'],
                            ['title'=>'全部商品页','link_url'=>'/packageC/columnsList/columnsList'],
                            ['title'=>'我的书架','link_url'=>'/packageC/bookShelf/bookShelf'],
                        ],
                    ],
//                    'tab2'=>[
//                        'tab_title'=>'商品属性',
//                        'link_list'=>[
//                            ['title'=>'精品专栏','link_url'=>''],
//                            ['title'=>'新品首发页','link_url'=>''],
//                        ],
//                    ],
                    'tab3'=>[
                        'tab_title'=>'商品分类',
                        'link_list'=>$this->_getZgCategoryList(),
                    ],
                ],
            ],
            'my'=>[
                'name'=>'my',//鉴权唯一标识，不可更改，
                'title'=>'我的',
                'type'=>'link_list',//链接列表
                'level'=>2,//层级数（2层）
                'tab_list'=>[
                    'tab1'=>[
                        'name'=>'os',//鉴权唯一标识，不可更改，与社区同一鉴权标识
                        'tab_title'=>'社区信息',
                        'link_list'=>[
                            ['title'=>'关注','link_url'=>'/packageA/ucenter-list/page-follow'],
                            ['title'=>'粉丝','link_url'=>'/packageA/ucenter-list/page-fans'],
                            ['title'=>'帖子','link_url'=>'/packageA/ucenter-list/page-post'],
                            ['title'=>'收藏','link_url'=>'/packageA/ucenter-list/page-collect'],
                        ],
                    ],
                    'tab2'=>[
                        'name'=>'eb',//鉴权唯一标识，不可更改，与商城同一鉴权标识
                        'tab_title'=>'商城信息',
                        'link_list'=>[
                            ['title'=>'收货地址','link_url'=>'/packageB/address/address'],
                            ['title'=>'我的足迹','link_url'=>'/packageB/history/history'],
                            ['title'=>'收藏商品','link_url'=>'/packageB/favorite/favorite'],
                            ['title'=>'我的优惠券','link_url'=>'/packageB/coupon-page/mycoupon'],
                        ],
                    ],
                    'tab3'=>[
                        'name'=>'eb',//鉴权唯一标识，不可更改，与商城同一鉴权标识
                        'tab_title'=>'我的订单',
                        'link_list'=>[
                            ['title'=>'全部订单','link_url'=>'/packageB/order/order?id=0'],
                            ['title'=>'待付款商城订单','link_url'=>'/packageB/order/order?id=1'],
                            ['title'=>'待发货商城订单','link_url'=>'/packageB/order/order?id=2'],
                            ['title'=>'待收货商城订单','link_url'=>'/packageB/order/order?id=3'],
                            ['title'=>'待评价商城订单','link_url'=>'/packageB/order/order?id=4'],
                            ['title'=>'退款/售后','link_url'=>'/packageB/order/returnList'],
                        ],
                    ],
                    'tab4'=>[
                        'name'=>'my_page',//鉴权唯一标识，不可更改，不可重复
                        'tab_title'=>'我的页面',
                        'link_list'=>[
                            ['title'=>'小名片','link_url'=>'/packageA/ucard/ucard'],
                            ['title'=>'签到','link_url'=>'/packageA/sign-in/sign-in'],
                            ['title'=>'联系客服','link_url'=>'/pages/set/contact'],
                            ['title'=>'设置','link_url'=>'/pages/set/set'],
                            ['title'=>'邀请海报','link_url'=>'/packageB/promotion-center/invite-poster'],
                        ],
                    ],
                    'tab5'=>[
                        'name'=>'zg',//鉴权唯一标识，不可更改，不可重复
                        'tab_title'=>'知识商城',
                        'link_list'=>[
                            ['title'=>'全部订单','link_url'=>'/packageC/columnsOrder/order?id=0'],
                            ['title'=>'待付款订单','link_url'=>'/packageC/columnsOrder/order?id=1'],
                            ['title'=>'待评价订单','link_url'=>'/packageC/columnsOrder/order?id=2'],
                        ],
                    ],
                ],
            ],
            'message'=>[
                'name'=>'message',//鉴权唯一标识，不可更改，不可重复
                'title'=>'消息中心',
                'type'=>'link_list',//链接列表
                'level'=>1,//层级数（1层）
                'link_list'=>[
                    ['title'=>'消息中心','link_url'=>'/pages/message-page/index'],
                    ['title'=>'系统通知','link_url'=>'/packageA/message/sys-news'],
                    ['title'=>'被赞通知','link_url'=>'/packageA/message/like-page'],
                    ['title'=>'评论通知','link_url'=>'/packageA/message/comments'],
//                    ['title'=>'互动消息','link_url'=>'/packageA/message/alternate'],
                ],
            ],
            'renzheng'=>[
                'name'=>'renzheng',//鉴权唯一标识，不可更改，不可重复
                'title'=>'认证中心',
                'type'=>'link_list',//链接列表
                'level'=>1,//层级数（1层）
                'link_list'=>$this->_getRenzhengList(),
            ],
            'renwu'=>[
                'name'=>'renwu',//鉴权唯一标识，不可更改，不可重复
                'title'=>'任务中心',
                'type'=>'link_list',//链接列表
                'level'=>1,//层级数（1层）
                'link_list'=>[
                    ['title'=>'任务中心','link_url'=>'/packageA/sign-in/task-center'],
                    ['title'=>'会员等级','link_url'=>'/pages/article/article?id=1'],
                    ['title'=>'积分规则','link_url'=>'/pages/article/article?id=3'],
                    ['title'=>'积分日志','link_url'=>'/packageC/points/my-points'],
                ],
            ],
            'tuiguang'=>[
                'name'=>'tuiguang',//鉴权唯一标识，不可更改，不可重复
                'title'=>'推广中心',//分销
                'type'=>'link_list',//链接列表
                'level'=>1,//层级数（1层）
                'link_list'=>[
                    ['title'=>'推广中心首页','link_url'=>'/packageB/promotion-center/index'],
                    ['title'=>'我的团队','link_url'=>'/packageB/promotion-center/my-team'],
                    ['title'=>'我的收益','link_url'=>'/packageB/promotion-center/my-profit'],
                    ['title'=>'订单明细','link_url'=>'/packageB/promotion-center/order-detail'],
                    ['title'=>'好货列表','link_url'=>'/packageB/product/list?recommend_sell=1'],
                ],
            ],
            'shop'=>[
                'name'=>'shop',//鉴权唯一标识，不可更改，不可重复
                'title'=>'积分商城',
                'type'=>'link_list',//链接列表
                'level'=>1,//层级数（1层）
                'link_list'=>$this->_getShopList(),
            ],
            'forum'=>[
                'name'=>'os',//鉴权唯一标识，不可更改，与社区同一鉴权标识
                'title'=>'版块页面',
                'type'=>'input_select',//输入筛选
                'tip'=>'请输入版块名称进行搜索',//搜索框提示文字
                'select_url'=>url('link.select/forumSelect'),//请求地址
            ],
            'topic'=>[
                'name'=>'topic',//鉴权唯一标识，不可更改，不可重复
                'title'=>'话题',
                'type'=>'input_select',//输入筛选
                'tip'=>'请输入话题名称进行搜索',//搜索框提示文字
                'select_url'=>url('link.select/topicSelect'),//请求地址
            ],
            'goods'=>[
                'name'=>'goods',//鉴权唯一标识，不可更改，不可重复
                'title'=>'商品页面',
                'type'=>'input_select',//输入筛选
                'tip'=>'请输入商品ID、名称、关键词进行搜索',//搜索框提示文字
                'select_url'=>url('link.select/goodsSelect'),//请求地址
                'bind_name'=>[
                    'eb',//绑定商城标识
                    'zg',//绑定知识商城标识
                    'shop',//绑定积分商城标识
                ]
            ],
            'post'=>[
                'name'=>'post',//鉴权唯一标识，不可更改，与社区同一鉴权标识
                'title'=>'帖子页面',
                'type'=>'input_select',//输入筛选
                'tip'=>'请输入帖子ID、标题（无标题帖按内容搜索）进行搜索',//搜索框提示文字
                'select_url'=>url('link.select/postSelect'),//请求地址
            ],
            'defined'=>[
                'name'=>'defined',//鉴权唯一标识，不可更改，不可重复
                'title'=>'装修页面',
                'type'=>'link_list',//链接列表
                'level'=>1,//层级数（1层）
                'link_list'=>$this->_getDefinedList(),
            ],
            'other'=>[
                'name'=>'other',//鉴权唯一标识，不可更改，不可重复
                'title'=>'站外链接',
                'type'=>'input_url',//输入筛选
            ],
        ];

        return $this->_checkAuth($linkTabList);
    }

    /**
     * 各tab权限校验，未授权的unset掉
     * @param $linkTabList
     * @return mixed
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private function _checkAuth($linkTabList){
        //权限校验-各tab根据授权确定是否开启或关闭
        $select_tab_open_list=$this->_getSelectTabOpenList();

        foreach ($linkTabList as $key=>$val){
            switch ($val['name']){
                case 'my':
                    foreach ($val['tab_list'] as $key_my=>$val_my){
                        if(!in_array($val_my['name'],$select_tab_open_list)){
                            unset($linkTabList[$key]['tab_list'][$key_my]);//删除未授权tab
                        }
                    }
                    unset($key_my,$val_my);
                    if(count($val['tab_list'])==0){
                        unset($linkTabList[$key]);//删除未授权tab
                    }
                    break;
                case 'goods':
                    foreach ($val['bind_name'] as $key_goods=>$val_goods){
                        if(!in_array($val_goods,$select_tab_open_list)){
                            unset($linkTabList[$key]['bind_name'][$key_goods]);//删除未授权tab
                        }
                    }
                    unset($key_goods,$val_goods);
                    if(count($val['bind_name'])==0){
                        unset($linkTabList[$key]);//删除未授权tab
                    }
                    break;
                case 'os':
                    //判断榜单是否存在
                    if(!in_array('rank',$select_tab_open_list)){
                        unset($linkTabList[$key]['tab_list']['tab3']);//删除未授权tab
                    }
                    //判断话题
                    if(!in_array('topic',$select_tab_open_list)){
                        unset($linkTabList[$key]['tab_list']['tab2']);//删除未授权tab
                    }
                    break;
                default:
                    if(!in_array($val['name'],$select_tab_open_list)){
                        unset($linkTabList[$key]);//删除未授权tab
                    }
            }
        }
        unset($key,$val);
        return $linkTabList;
    }

    /**
     * 商城分类
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private function _getEbCategoryList(){
        $tag_name='eb_category_list_for_link_select';
        $return_list=cache($tag_name);
        if($return_list===false){
            $return_list=[];
            $category_ids=StoreCategory::where('is_show',1)->column('id');
            $node_ids=StoreCategory::where('pid','>',0)->where('is_show',1)->column('pid');
            if(count($node_ids)>0){
                $category_ids=array_diff($category_ids,$node_ids);//获取叶子结点id
            }
            $category_list=StoreCategory::whereIn('id',$category_ids)->field('id,cate_name')->order('id asc')->select();
            foreach ($category_list as $val){
                $return_list[]=['title'=>$val['cate_name'],'link_url'=>'/packageB/product/list?sid='.$val['id'].'&title='.$val['cate_name']];
            }
            unset($val);

            cache($tag_name,$return_list,60);
        }
        return $return_list;
    }

    /**
     * 知识商城分类
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private function _getZgCategoryList(){
        $tag_name='zg_category_list_for_link_select';
        $return_list=cache($tag_name);
        if($return_list===false){
            $return_list=[];
            $category_ids=StoreCategoryColumn::where('is_show',1)->column('id');
            $node_ids=StoreCategoryColumn::where('pid','>',0)->where('is_show',1)->column('pid');
            if(count($node_ids)>0){
                $category_ids=array_diff($category_ids,$node_ids);//获取叶子结点id
            }
            $category_list=StoreCategoryColumn::whereIn('id',$category_ids)->field('id,cate_name')->order('id asc')->select();
            foreach ($category_list as $val){
                $return_list[]=['title'=>$val['cate_name'],'link_url'=>'/packageC/columnsList/columnsList?sid='.$val['id'].'&title='.$val['cate_name']];
            }
            unset($val);

            cache($tag_name,$return_list,60);
        }
        return $return_list;
    }

    /**
     * 认证中心链接
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private function _getRenzhengList(){
        $tag_name='renzheng_list_for_link_select';
        $return_list=cache($tag_name);
        if($return_list===false){
            $return_list=[
                ['title'=>'用户认证首页','link_url'=>'/packageC/renzheng/index'],
            ];
            $certification_cate_list=CertificationCate::where('status',1)->order('sort desc')->field('id,name')->select();
            foreach ($certification_cate_list as $val){
                $return_list[]=['title'=>$val['name'],'link_url'=>'/packageC/renzheng/detail?id='.$val['id']];
            }
            unset($val);
            cache($tag_name,$return_list,60);
        }
        return $return_list;
    }

    /**
     * 积分商城链接
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private function _getShopList(){
        $tag_name='shop_list_for_link_select';
        $return_list=cache($tag_name);
        if($return_list===false){
            $return_list=[
                ['title'=>'积分商城首页','link_url'=>'/packageC/points/index'],
                ['title'=>'兑换记录','link_url'=>'/packageC/points-record/index'],
            ];
            $shop_column_list=ShopColumn::where('status',1)->order('sort asc')->field('id,name')->select();
            foreach ($shop_column_list as $val){
                $return_list[]=['title'=>'栏目-'.$val['name'],'link_url'=>'/packageC/points/column-list?id='.$val['id']];
            }
            unset($val);
            cache($tag_name,$return_list,60);
        }
        return $return_list;
    }

    /**
     * 装修页面链接列表
     * @return array
     * @author 郑钟良(zzl@ourstu.com)
     * @date 2019-7
     */
    private function _getDefinedList(){
        return [
//            ['title'=>'装修页面1','link_url'=>''],
//            ['title'=>'装修页面2','link_url'=>''],
        ];
    }
}