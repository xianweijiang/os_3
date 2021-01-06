<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:87:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/index/main.php";i:1597214754;s:93:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/container.php";i:1597214754;s:94:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/frame_head.php";i:1597214754;s:89:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/style.php";i:1597214754;s:96:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/frame_footer.php";i:1597214754;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if(empty($is_layui) || (($is_layui instanceof \think\Collection || $is_layui instanceof \think\Paginator ) && $is_layui->isEmpty())): ?>
    <link href="/public/system/frame/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
    <?php endif; ?>
    <link href="/public/static/plug/layui/css/layui.css" rel="stylesheet">
    <link href="/public/system/css/layui-admin.css" rel="stylesheet"></link>
    <link href="/public/system/frame/css/font-awesome.min.css?v=4.3.0" rel="stylesheet">
    <link href="/public/system/frame/css/animate.min.css" rel="stylesheet">
    <link href="/public/system/frame/css/style.min.css?v=3.0.0" rel="stylesheet">
    <!--专栏Column新增图标-->
    <link href="https://at.alicdn.com/t/font_1685608_u3j40gwewag.css" rel="stylesheet">
    <script src="/public/system/frame/js/jquery.min.js"></script>
    <script src="/public/system/frame/js/bootstrap.min.js"></script>
    <script src="/public/static/plug/layui/layui.all.js"></script>
    <script>
        $eb = parent._mpApi;
        window.controlle="<?php echo strtolower(trim(preg_replace("/[A-Z]/", "_\\0", think\Request::instance()->controller()), "_"));?>";
        window.module="<?php echo think\Request::instance()->module();?>";
    </script>



    <title></title>
    
<!-- 全局js -->
<script src="/public/static/plug/echarts/echarts.common.min.js"></script>
<script src="/public/static/plug/echarts/theme/macarons.js"></script>
<script src="/public/static/plug/echarts/theme/westeros.js"></script>
<link rel="stylesheet" href="/public/static/plug/formselects/formSelects-v4.css">
<script src="/public/static/plug/formselects/formSelects-v4.min.js"></script>

    <!--<script type="text/javascript" src="/static/plug/basket.js"></script>-->
<script type="text/javascript" src="/public/static/plug/requirejs/require.js"></script>
<?php /*  <script type="text/javascript" src="/static/plug/requirejs/require-basket-load.js"></script>  */ ?>
<script>
    var hostname = location.hostname;
    if(location.port) hostname += ':' + location.port;
    requirejs.config({
        map: {
            '*': {
                'css': '/public/static/plug/requirejs/require-css.js'
            }
        },
        shim:{
            'iview':{
                deps:['css!iviewcss']
            },
            'layer':{
                deps:['css!layercss']
            }
        },
        baseUrl:'//'+hostname+'/public/',
        paths: {
            'static':'static',
            'system':'system',
            'vue':'static/plug/vue/dist/vue.min',
            'axios':'static/plug/axios.min',
            'iview':'static/plug/iview/dist/iview.min',
            'iviewcss':'static/plug/iview/dist/styles/iview',
            'lodash':'static/plug/lodash',
            'layer':'static/plug/layer/layer',
            'layercss':'static/plug/layer/theme/default/layer',
            'jquery':'static/plug/jquery/jquery.min',
            'moment':'static/plug/moment',
            'sweetalert':'static/plug/sweetalert2/sweetalert2.all.min'

        },
        basket: {
            excludes:['system/js/index','system/util/mpVueComponent','system/util/mpVuePackage']
//            excludes:['system/util/mpFormBuilder','system/js/index','system/util/mpVueComponent','system/util/mpVuePackage']
        }
    });
</script>
<script type="text/javascript" src="/public/system/util/mpFrame.js"></script>
    
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <?php if(!$is_free_ban): ?>
    <div style="background-color: #FBE0E3;margin-top: 10px;color: #D9001B;padding: 10px 40px 10px 20px">
        <div style="display: flex;justify-content: space-between;align-items: center">
            <span>未开通该功能使用权限，如需开通，请联系客服！</span>
            <a id="contact_service" style="color: #D9001B;display: block;border: 1px solid #D9001B;padding: 7px 24px;border-radius: 5px;font-size: 13px">联系客服</a>
        </div>
    </div>
    <script>
        $("#contact_service").on("click",function () {
            $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
        })
    </script>
    <?php endif; if(!$is_end_ban): ?>
    <div style="background-color: #FBE0E3;margin-top: 10px;color: #D9001B;padding: 10px 40px 10px 20px">
        <div style="display: flex;justify-content: space-between;align-items: center">
            <span>功能已到期，请续费后继续使用该功能！</span>
            <a id="contact_service" style="color: #D9001B;display: block;border: 1px solid #D9001B;padding: 7px 24px;border-radius: 5px;font-size: 13px">联系客服</a>
        </div>
    </div>
    <script>
        $("#contact_service").on("click",function () {
            $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
        })
    </script>
    <?php endif; ?>

<div id="app">
    <div class="top-choose-content">
        <div class="left">
            概况 / <span>应用概况</span>
        </div>
        <div class="right">
            <span>平台选择:</span>
            <span class="tab top-active-tab top-tab" data-value="all">全部</span>
            <span class="tab top-tab" data-value="android">Android</span>
            <span class="tab top-tab" data-value="ios">ios</span>
            <span class="tab top-tab" data-value="h5">H5</span>
            <span class="tab top-tab" data-value="mini_program">微信小程序</span>
            <!--<span data-value="alipay_mini_program">支付宝小程序</span>
            <span data-value="headline_mini_program">头条小程序</span>-->
        </div>
    </div>
    <div style="margin-top: 10px;" class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title data-box">
                    <h5>应用概况</h5>
                    <!--当前获取的全部平台的数据，可调用admin/index/getSystemCountData   post  place 获取各平台的数据-->
                    <!--平台place有 all：全部；android：安卓；ios：苹果；h5：手机网页；mini_program：微信小程序；alipay_mini_program：支付宝小程序；headline_mini_program：头条小程序-->
                    <!--<form class="layui-form" action="">
                        <div style="width: 200px;float: right;margin-top: 5px;">
                            <select name="place" id="place_val" lay-filter="place">
                                <option value="all">全部</option>
                                <option value="android">安卓</option>
                                <option value="ios">苹果</option>
                                <option value="h5">手机网页</option>
                                <option value="mini_program">微信小程序</option>
                                <option value="alipay_mini_program">支付宝小程序</option>
                                <option value="headline_mini_program">头条小程序</option>
                            </select>
                        </div>
                    </form>-->
                    <div class="ibox-content data-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="layui-table" lay-skin="nob" lay-size="lg">
                                    <colgroup>
                                        <col width="150">
                                        <col width="200">
                                        <col>
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th class="center-box question-box">新增用户
                                            <img class="question-img" src="/public/system/images/question.png" alt="">
                                            <div class="tip-box" style="right: -11px;">
                                                首次访问应用的用户数(以设备为判断标准，去重)
                                            </div>
                                        </th>
                                        <th class="center-box question-box">活跃用户
                                            <img class="question-img" src="/public/system/images/question.png" alt="">
                                            <div class="tip-box">
                                                启动过应用的用户(以设备为标准去重)
                                            </div>
                                        </th>
                                        <th class="center-box question-box">访问次数
                                            <img class="question-img" src="/public/system/images/question.png" alt="">
                                            <div class="tip-box">
                                                访问过应用内任意页面总次数，多个页面之间跳转、同一页面的重复访问计为多次访问
                                            </div>
                                        </th>
                                        <th class="center-box question-box">总访客数
                                            <img class="question-img" src="/public/system/images/question.png" alt="">
                                            <div class="tip-box">
                                                从添加统计到当前选择时间的总访客数(去重)
                                            </div>
                                        </th>
                                        <th class="center-box question-box">分享次数
                                            <img class="question-img" src="/public/system/images/question.png" alt="">
                                            <div class="tip-box">
                                                点击分享次数统计
                                            </div>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody id="system_data_box">
                                    <tr>
                                        <td>今日</td>
                                        <td class="center-box today"><?php echo $system_count['today']['new_count']; ?></td>
                                        <td class="center-box today"><?php echo $system_count['today']['active_count']; ?></td>
                                        <td class="center-box today"><?php echo $system_count['today']['view_count']; ?></td>
                                        <td class="center-box today"><?php echo $system_count['today']['total_count']; ?></td>
                                        <td class="center-box today"><?php echo $system_count['today']['share_count']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>昨日</td>
                                        <td class="center-box"><?php echo $system_count['yesterday']['new_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['yesterday']['active_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['yesterday']['view_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['yesterday']['total_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['yesterday']['share_count']; ?></td>
                                    </tr>
                                    <tr class="first-box">
                                        <td>每日平均</td>
                                        <td class="center-box"><?php echo $system_count['average']['new_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['average']['active_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['average']['view_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['average']['total_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['average']['share_count']; ?></td>
                                    </tr>
                                    <tr class="first-box">
                                        <td>历史峰值</td>
                                        <td class="center-box"><?php echo $system_count['max']['new_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['max']['active_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['max']['view_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['max']['total_count']; ?></td>
                                        <td class="center-box"><?php echo $system_count['max']['share_count']; ?></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <img class="down-img" id="first_down" src="/public/system/images/down.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title data-box">
                    <h5>社区概述</h5>
                    <div class="ibox-content data-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="layui-table" lay-skin="nob" lay-size="lg">
                                    <colgroup>
                                        <col width="150">
                                        <col width="200">
                                        <col>
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th class="center-box">发帖</th>
                                        <th class="center-box">评论</th>
                                        <th class="center-box">点赞</th>
                                        <!--<th class="center-box">分享</th>-->
                                        <th class="center-box">打赏</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>今日</td>
                                        <td class="center-box">{{getCensus.today&&getCensus.today.forum}}</td>
                                        <td class="center-box">{{getCensus.today&&getCensus.today.comment}}</td>
                                        <td class="center-box">{{getCensus.today&&getCensus.today.support}}</td>
                                        <!--<td class="center-box">{{getCensus.today&&getCensus.today.share}}</td>-->
                                        <td class="center-box">{{getCensus.today&&getCensus.today.reward}}</td>
                                    </tr>
                                    <tr>
                                        <td>昨日</td>
                                        <td class="center-box">{{getCensus.yesterday&&getCensus.yesterday.forum}}</td>
                                        <td class="center-box">{{getCensus.yesterday&&getCensus.yesterday.comment}}</td>
                                        <td class="center-box">{{getCensus.yesterday&&getCensus.yesterday.support}}</td>
                                        <!--<td class="center-box">{{getCensus.yesterday&&getCensus.yesterday.share}}</td>-->
                                        <td class="center-box">{{getCensus.yesterday&&getCensus.yesterday.reward}}</td>
                                    </tr>
                                    <tr class="second-box">
                                        <td>每日平均</td>
                                        <td class="center-box">{{getCensus.average&&getCensus.average.forum}}</td>
                                        <td class="center-box">{{getCensus.average&&getCensus.average.comment}}</td>
                                        <td class="center-box">{{getCensus.average&&getCensus.average.support}}</td>
                                        <!--<td class="center-box">{{getCensus.average&&getCensus.average.share}}</td>-->
                                        <td class="center-box">{{getCensus.average&&getCensus.average.reward}}</td>
                                    </tr>
                                    <tr class="second-box">
                                        <td>历史峰值</td>
                                        <td class="center-box">{{getCensus.max&&getCensus.max.forum}}</td>
                                        <td class="center-box">{{getCensus.max&&getCensus.max.comment}}</td>
                                        <td class="center-box">{{getCensus.max&&getCensus.max.support}}</td>
                                        <!--<td class="center-box">{{getCensus.max&&getCensus.max.share}}</td>-->
                                        <td class="center-box">{{getCensus.max&&getCensus.max.reward}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-lg-12">
                                <div class="statis-content">
                                    <div class="statis-tab-box-content">
                                        <div v-for="(item,index) in statisType" v-on:click="changeStatisType(index,item.field)" :class="index === activeType ? 'active-tab':''" class="tab-box">
                                            {{item.name}}
                                        </div>
                                    </div>
                                    <div class="select">
                                        <select name="type" ref="chartDay" v-on:change="communityInfo">
                                            <option class="option" v-for="(item,index) in chartDays" :value="item">最近{{item}}天</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flot-chart">
                                    <div class="flot-chart-content" ref="community_echart" id="flot-dashboard-chart3"></div>
                                </div>
                            </div>
                            <!--<div class="col-lg-12">
                                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief" style="padding: 0 20px">
                                    <ul class="layui-tab-title">
                                        <li class="layui-this" v-on:click="getCommunitys('one')">昨天</li>
                                        <li v-on:click="getCommunitys('seven')">最近7天</li>
                                        <li v-on:click="getCommunitys('thirty')">最近30天</li>
                                        <li v-on:click="getCommunitys('ninety')">最近90天</li>
                                    </ul>
                                    <div class="col-lg-12 layui-tab-content">
                                        <div class="col-lg-6 community-tables">
                                            <h3>TOP用户发帖</h3>
                                            <div style="font-weight: 600;height: 20px">
                                                <p class="col-lg-3">排名</p>
                                                <p class="col-lg-3">用户名</p>
                                                <p class="col-lg-3">UID</p>
                                                <p class="col-lg-3">发帖数</p>
                                            </div>
                                            <ul>
                                                <li v-for="(item,index) in communityList.thread_census" :key="index"
                                                    style="margin-top: 10px;height: 20px">
                                                    <p class="col-lg-3">{{index+1}}</p>
                                                    <p class="col-lg-3">{{item.user}}</p>
                                                    <p class="col-lg-3">{{item.uid}}</p>
                                                    <p class="col-lg-3">{{item.data}}</p>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-lg-6 community-tables">
                                            <h3>TOP用户评论</h3>
                                            <div style="font-weight: 600;height: 20px">
                                                <p class="col-lg-3">排名</p>
                                                <p class="col-lg-3">用户名</p>
                                                <p class="col-lg-3">UID</p>
                                                <p class="col-lg-3">发帖数</p>
                                            </div>
                                            <ul>
                                                <li v-for="(item,index) in communityList.comment_census" :key="index"
                                                    style="margin-top: 10px;height: 20px">
                                                    <p class="col-lg-3">{{index+1}}</p>
                                                    <p class="col-lg-3">{{item.user}}</p>
                                                    <p class="col-lg-3">{{item.uid}}</p>
                                                    <p class="col-lg-3">{{item.data}}</p>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-lg-6 community-tables">
                                            <h3>TOP热帖</h3>
                                            <div style="font-weight: 600;height: 20px">
                                                <p class="col-lg-2">排名</p>
                                                <p class="col-lg-6">帖子标题/内容</p>
                                                <p class="col-lg-2">发帖人</p>
                                                <p class="col-lg-2">评论数</p>
                                            </div>
                                            <ul>
                                                <li v-for="(item,index) in communityList.hot_census" :key="index"
                                                    style="margin-top: 10px;height: 20px">
                                                    <p class="col-lg-2">{{index+1}}</p>
                                                    <p class="col-lg-6">{{item.forum}}</p>
                                                    <p class="col-lg-2">{{item.user}}</p>
                                                    <p class="col-lg-2">{{item.data}}</p>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-lg-6 community-tables">
                                            <h3>版块排行</h3>
                                            <div style="font-weight: 600;height: 20px">
                                                <p class="col-lg-2">排名</p>
                                                <p class="col-lg-2">版块名称</p>
                                                <p class="col-lg-2">帖子数</p>
                                                <p class="col-lg-2">评论数</p>
                                                <p class="col-lg-2">浏览数</p>
                                                <p class="col-lg-2">关注人数</p>
                                            </div>
                                            <ul>
                                                <li v-for="(item,index) in communityList.forum_census" :key="index"
                                                    style="margin-top: 10px;height: 20px">
                                                    <p class="col-lg-2">{{index+1}}</p>
                                                    <p class="col-lg-2">{{item.forum}}</p>
                                                    <p class="col-lg-2">{{item.data}}</p>
                                                    <p class="col-lg-2">{{item.data_comment}}</p>
                                                    <p class="col-lg-2">{{item.data_view}}</p>
                                                    <p class="col-lg-2">{{item.data_member}}</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>-->
                        </div>
                        <img class="down-img" id="second_down" src="/public/system/images/down.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div style="padding-left: 15px;">
            <h5 style="margin: 10px 0 15px 15px;font-size: 18px;color: #000">商城概述</h5>
        </div>
        <div class="col-sm-3 ui-sortable">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">急</span>
                    <h5>订单</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $topData['orderDeliveryNum']; ?></h1>
                    <small><a href="<?php echo Url('order.store_order/index'); ?>">待发货</a></small>
                </div>
            </div>
        </div>
        <div class="col-sm-3 ui-sortable">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">待</span>
                    <h5>订单</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $topData['orderRefundNum']; ?></h1>
                    <small><a href="<?php echo Url('order.store_order/index'); ?>">退换货</a></small>
                </div>
            </div>
        </div>
        <div class="col-sm-3 ui-sortable">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">急</span>
                    <h5>商品</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $topData['stockProduct']; ?></h1>
                    <small><a href="<?php echo Url('store.store_product/index',array('type'=>5)); ?>">库存预警</a></small>
                </div>
            </div>
        </div>
        <div class="col-sm-3 ui-sortable">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">待</span>
                    <h5>待提现</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $topData['treatedExtract']; ?></h1>
                    <small><a href="<?php echo Url('finance.user_extract/index'); ?>">待提现</a></small>
                </div>
            </div>
        </div>
        <div class="col-sm-3 ui-sortable">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">今</span>
                    <h5>订单</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $first_line['d_num']['data']; ?></h1>
                    <div class="stat-percent font-bold text-navy">
                        <?php echo $first_line['d_num']['percent']; ?>%
                        <?php if($first_line['d_num']['is_plus'] >= 0): ?><i
                                class="fa <?php if($first_line['d_num']['is_plus'] == 1): ?>fa-level-up<?php else: ?>fa-level-down<?php endif; ?>"></i><?php endif; ?>
                    </div>
                    <small>今日订单数</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3 ui-sortable">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">今</span>
                    <h5>交易</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $first_line['d_price']['data']; ?></h1>
                    <div class="stat-percent font-bold text-info">
                        <?php echo $first_line['d_price']['percent']; ?>%
                        <?php if($first_line['d_price']['is_plus'] >= 0): ?><i
                                class="fa <?php if($first_line['d_price']['is_plus'] == 1): ?>fa-level-up<?php else: ?>fa-level-down<?php endif; ?>"></i><?php endif; ?>
                    </div>
                    <small>今日交易额</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3 ui-sortable">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">今</span>
                    <h5>粉丝</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $first_line['day']['data']; ?></h1>
                    <div class="stat-percent font-bold text-info">
                        <?php echo $first_line['day']['percent']; ?>%
                        <?php if($first_line['day']['is_plus'] >= 0): ?><i
                                class="fa <?php if($first_line['day']['is_plus'] == 1): ?>fa-level-up<?php else: ?>fa-level-down<?php endif; ?>"></i><?php endif; ?>
                    </div>
                    <small>今日新增粉丝</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3 ui-sortable">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">月</span>
                    <h5>粉丝</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $first_line['month']['data']; ?></h1>
                    <div class="stat-percent font-bold text-info">
                        <?php echo $first_line['month']['percent']; ?>%
                        <?php if($first_line['month']['is_plus'] >= 0): ?><i
                                class="fa <?php if($first_line['month']['is_plus'] == 1): ?>fa-level-up<?php else: ?>fa-level-down<?php endif; ?>"></i><?php endif; ?>
                    </div>
                    <small>本月新增粉丝</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>订单</h5>
                    <div class="pull-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-white"
                                    :class="{'active': active == 'thirtyday'}" v-on:click="getlist('thirtyday')">30天
                            </button>
                            <button type="button" class="btn btn-xs btn-white" :class="{'active': active == 'week'}"
                                    v-on:click="getlist('week')">周
                            </button>
                            <button type="button" class="btn btn-xs btn-white" :class="{'active': active == 'month'}"
                                    v-on:click="getlist('month')">月
                            </button>
                            <button type="button" class="btn btn-xs btn-white" :class="{'active': active == 'year'}"
                                    v-on:click="getlist('year')">年
                            </button>
                        </div>
                    </div>
                </div>
               <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="flot-chart-content echarts" ref="order_echart" id="flot-dashboard-chart1"></div>
                        </div>
                        <div class="col-lg-3">
                            <ul class="stat-list">
                                <li>
                                    <h2 class="no-margins ">{{pre_cycleprice}}</h2>
                                    <small>{{precyclename}}销售额</small>
                                </li>
                                <li>
                                    <h2 class="no-margins ">{{cycleprice}}</h2>
                                    <small>{{cyclename}}销售额</small>
                                    <div class="stat-percent text-navy" v-if='cycleprice_is_plus ===1'>
                                        {{cycleprice_percent}}%
                                        <i class="fa fa-level-up"></i>
                                    </div>
                                    <div class="stat-percent text-danger" v-else-if='cycleprice_is_plus === -1'>
                                        {{cycleprice_percent}}%
                                        <i class="fa fa-level-down"></i>
                                    </div>
                                    <div class="stat-percent" v-else>
                                        {{cycleprice_percent}}%
                                    </div>
                                    <div class="progress progress-mini">
                                        <div :style="{width:cycleprice_percent+'%'}" class="progress-bar box"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins ">{{pre_cyclecount}}</h2>
                                    <small>{{precyclename}}订单总数</small>
                                </li>
                                <li>
                                    <h2 class="no-margins">{{cyclecount}}</h2>
                                    <small>{{cyclename}}订单总数</small>
                                    <div class="stat-percent text-navy" v-if='cyclecount_is_plus ===1'>
                                        {{cyclecount_percent}}%
                                        <i class="fa fa-level-up"></i>
                                    </div>
                                    <div class="stat-percent text-danger" v-else-if='cyclecount_is_plus === -1'>
                                        {{cyclecount_percent}}%
                                        <i class="fa fa-level-down"></i>
                                    </div>
                                    <div class="stat-percent " v-else>
                                        {{cyclecount_percent}}%
                                    </div>
                                    <div class="progress progress-mini">
                                        <div :style="{width:cyclecount_percent+'%'}" class="progress-bar box"></div>
                                    </div>
                                </li>


                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>新增用户</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="flot-chart">
                                <div class="flot-chart-content" ref="user_echart" id="flot-dashboard-chart2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<style scoped>
    .top-choose-content{
        display: flex;
        justify-content: space-between;
        background-color: #fff;
        height: 50px;
        align-items: center;
        padding-left: 15px;
        padding-right: 20px;
        border-radius: 7px;
    }
    .top-choose-content .left{
        font-size: 18px;
        color: #000;
        font-weight: 600;
    }
    .top-choose-content .left span{
        font-size: 18px;
        color: #999;
        font-weight: 500;
    }
    .top-choose-content .right{
        font-size: 14px;
        color: #333;
        display: flex;
    }
    .top-choose-content .right span{
        display: block;
        margin: 0 10px;
    }
    .top-choose-content .right .tab{
        cursor: pointer;
        position: relative;
    }
    .top-choose-content .right .top-active-tab{
        color: #0ca6f2;
    }
    .top-choose-content .right .top-active-tab:before{
        content: '';
        position: absolute;
        width: 100%;
        height: 2px;
        background-color: #0ca6f2;
        bottom: -3px;
    }
    .statis-content{
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 60px 0 30px;
    }
    .statis-tab-box-content{
        display: flex;
    }
    .statis-tab-box-content .tab-box{
        width: 85px;
        height: 35px;
        line-height: 33px;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        text-align: center;
        font-size: 12px;
        cursor: pointer;
        position: relative;
    }
    .statis-tab-box-content .tab-box:before{
        content: '';
        position: absolute;
        height: 25px;
        width: 1px;
        background-color: #eee;
        right: 0;
        top: 5px;
    }
    .statis-tab-box-content .tab-box:first-child{
        border-left: 1px solid #eee;
        border-top-left-radius: 6px;
        border-bottom-left-radius: 6px;
    }
    .statis-tab-box-content .tab-box:last-child{
        border-right: 1px solid #eee;
        border-top-right-radius: 6px;
        border-bottom-right-radius: 6px;
    }
    .statis-tab-box-content .tab-box:last-child:before{
        content: none;
    }
    .statis-tab-box-content .active-tab{
        border: 1px solid #0ca6f2!important;
        color: #0ca6f2;
        border-radius: 6px;
    }
    .statis-tab-box-content .active-tab:before{
        content: none;
    }

    .data-content .down-img{
        position: absolute;
        cursor: pointer;
        width: 15px;
        top: 140px;
        right: 50px;
    }
    .first-box{
        display: none;
    }
    .second-box{
        display: none;
    }
    .box {
        width: 0px;
    }

    .community-tables {
        margin: 10px;
        width: 48%;
        border: 1px solid #f2f2f2;
    }

    .community-tables p {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .data-box{
        background-color: inherit;
        padding: 0;
    }
    .data-box h5{
        margin-top: 10px;
        margin-left: 15px;
        font-size: 18px;
        margin-bottom: 15px;
        color: #000;
    }
    .data-content{
        border-radius: 7px;
        padding: 0;
        position: relative;
    }
    tr{
        background-color: #fff!important;
    }
    .center-box{
        text-align: center!important;
    }
    .today{
        font-size: 22px!important;
        color: #0ca6f2;
    }
    .question-box img{
        width: 15px!important;
        height: 15px!important;
        margin-left: 0;
        margin-bottom: 3px;
    }
    .question-box{
        position: relative;
    }
    .question-box .tip-box{
        position: absolute;
        background-color: rgba(0,0,0,0.7);
        color: #fff;
        font-size: 12px;
        width: 150px;
        z-index: 1;
        border-radius: 5px;
        padding: 0 3px;
        text-align: left;
        top: 40px;
        right: 42px;
        display: none;
    }
    .tip-box:before{
        content: '';
        width: 0;
        height: 0;
        position: absolute;
        border-bottom: 6px solid rgba(0,0,0,0.7);
        border-right: 6px solid transparent;
        border-left: 6px solid transparent;
        top: -6px;
        left: 68px;
    }

    .select {
        display: inline-block;
        width: 200px;
        height: 32px;
        position: relative;
        vertical-align: middle;
        padding: 0;
        overflow: hidden;
        background-color: #fff;
        color: #555;
        border: 1px solid #e6e6e6;
        text-shadow: none;
        border-radius: 2px;
        transition: box-shadow 0.25s ease;
        z-index: 2;
        margin-left: 20px;
    }

    .select:hover {
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
    }

    .select:before {
        content: "";
        position: absolute;
        width: 0;
        height: 0;
        border: 6px solid transparent;
        border-top-color: #ccc;
        top: 12px;
        right: 10px;
        cursor: pointer;
        z-index: -2;
    }
    .select select {
        padding-left: 10px;
        cursor: pointer;
        line-height: 30px;
        width: 100%;
        border: none;
        background: transparent;
        background-image: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        height: 32px;
    }
    .select select:focus {
        outline: none;
    }
    .option {
        padding:0 30px 0 10px;
        min-height:40px;
        display:flex;
        align-items:center;
        background:#fff;
        border-top:#222 solid 1px;
        position:absolute;
        top:0;
        width: 100%;
        pointer-events:none;
        order:2;
        z-index:1;
        transition:background .4s ease-in-out;
        box-sizing:border-box;
        overflow:hidden;
        white-space:nowrap;

    }
    .option:hover {
        background:#f2f2f2;
    }
    .option:active {
        background:#0092DC;
    }
</style>
<script>
    var firstState = false;
    var secondState = false;
     require(['vue','axios','layer'],function(Vue,axios,layer){
        new Vue({
            el:"#app",
            data:{
                option:{},
                getCensus:{},
                communityList:{},
                myChart:{},
                active:'thirtyday',
                cyclename:'最近30天',
                precyclename:'上个30天',
                cyclecount:0,
                cycleprice:0,
                cyclecount_percent:0,
                cycleprice_percent:0,
                cyclecount_is_plus:0,
                cycleprice_is_plus:0,
                pre_cyclecount:0,
                pre_cycleprice:0,
                chartDays:[7,30,60],
                statisType:[{name:"发帖数",field:"forum"},{name:"评论数",field:"comment"},{name:"点赞数",field:"support"}/*,{name:"分享数",field:"share"}*/,{name:"打赏数",field:"reward"}],
                activeType:0
            },
            methods:{
                info:function () {
                    var that=this;
                    axios.get("<?php echo Url('userchart'); ?>").then((res)=>{
                        that.myChart.user_echart.setOption(that.userchartsetoption(res.data.data));
                    });
                },
                getCensusMessage:function(){
                    var that=this;
                    axios.get("<?php echo Url('admin/community.index/get_census_message'); ?>").then((res)=>{
                        var data = res.data.data
                        that.getCensus = {
                            today:{
                                forum:data.today.forum,
                                comment:data.today.comment,
                                support:data.today.support,
                                share:data.today.share,
                                reward:data.today.reward
                            },
                            yesterday:{
                                forum:data.yesterday.forum,
                                comment:data.yesterday.comment,
                                support:data.yesterday.support,
                                share:data.yesterday.share,
                                reward:data.yesterday.reward
                            },
                            average:{
                                forum:data.average.forum,
                                comment:data.average.comment,
                                support:data.average.support,
                                share:data.average.share,
                                reward:data.average.reward
                            },
                            max:{
                                forum:data.max.forum,
                                comment:data.max.comment,
                                support:data.max.support,
                                share:data.max.share,
                                reward:data.max.reward
                            }
                        }
                    });
                },
                communityInfo:function () {
                    var index = this.$refs.chartDay.value
                    var that=this;
                    axios.post("<?php echo Url('admin/community.index/get_census'); ?>",{limit: index}).then((res)=>{
                        console.log(res.data.data.series)
                        console.log(Math.max.apply(null, res.data.data.series))
                        that.myChart.community_echart.setOption(that.communitychartsetoption(res.data.data,'发帖数'));
                    });
                },
                changeStatisType:function (index,type){
                    var limit = this.$refs.chartDay.value;
                    this.activeType = index;
                    var name = this.statisType[index].name
                    var that = this;
                    axios.post("<?php echo Url('admin/community.index/get_census'); ?>",{limit: limit,field:type}).then((res)=>{
                        console.log(res.data.data.series)
                        console.log(Math.max.apply(null, res.data.data.series))
                        that.myChart.community_echart.setOption(that.communitychartsetoption(res.data.data,name));
                    });
                },
                getlist:function (e) {
                    var that=this;
                    var cycle = e!=null ? e :'thirtyday';
                    axios.get("<?php echo Url('orderchart'); ?>?cycle="+cycle).then((res)=>{
                            that.myChart.order_echart.clear();
                            that.myChart.order_echart.setOption(that.orderchartsetoption(res.data.data));
                            that.active = cycle;
                            switch (cycle){
                                case 'thirtyday':
                                    that.cyclename = '最近30天';
                                    that.precyclename = '上个30天';
                                    break;
                                case 'week':
                                    that.precyclename = '上周';
                                    that.cyclename = '本周';
                                    break;
                                case 'month':
                                    that.precyclename = '上月';
                                    that.cyclename = '本月';
                                    break;
                                case 'year':
                                    that.cyclename = '去年';
                                    that.precyclename = '今年';
                                    break;
                                default:
                                    break;
                            }
                            var data=res.data.data;
                            if(data.length) {
                                that.cyclecount = data.cycle.count.data;
                                that.cyclecount_percent = data.cycle.count.percent;
                                that.cyclecount_is_plus = data.cycle.count.is_plus;
                                that.cycleprice = data.cycle.price.data;
                                that.cycleprice_percent = data.cycle.price.percent;
                                that.cycleprice_is_plus = data.cycle.price.is_plus;
                                that.pre_cyclecount = data.pre_cycle.count.data;
                                that.pre_cycleprice = data.pre_cycle.price.data;
                            }
                    });
                },
                getCommunitys:function(num){
                    var that = this
                    axios.post("<?php echo Url('admin/community.index/census_rank'); ?>",{order: num}).then((res)=>{
                        that.communityList = res.data.data
                    })
                },
                orderchartsetoption:function(data){

                        this.option = {
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross',
                                    crossStyle: {
                                        color: '#999'
                                    }
                                }
                            },
                            toolbox: {
                                feature: {
                                    dataView: {show: true, readOnly: false},
                                    magicType: {show: true, type: ['line', 'bar']},
                                    restore: {show: false},
                                    saveAsImage: {show: true}
                                }
                            },
                            legend: {
                                data:data.legend
                            },
                            grid: {
                                x: 70,
                                x2: 50,
                                y: 60,
                                y2: 50
                            },
                            xAxis: [
                                {
                                    type: 'category',
                                    data: data.xAxis,
                                    axisPointer: {
                                        type: 'shadow'
                                    },
                                    axisLabel:{
                                        interval: 0,
                                        rotate:40
                                    }


                                }
                            ],
                            yAxis:[{type : 'value'}],
//                            yAxis: [
//                                {
//                                    type: 'value',
//                                    name: '',
//                                    min: 0,
//                                    max: data.yAxis.maxprice,
////                                    interval: 0,
//                                    axisLabel: {
//                                        formatter: '{value} 元'
//                                    }
//                                },
//                                {
//                                    type: 'value',
//                                    name: '',
//                                    min: 0,
//                                    max: data.yAxis.maxnum,
//                                    interval: 5,
//                                    axisLabel: {
//                                        formatter: '{value} 个'
//                                    }
//                                }
//                            ],
                            series: data.series
                        };
                    return  this.option;
                },
                userchartsetoption:function(data){
                    this.option = {
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross',
                                crossStyle: {
                                    color: '#999'
                                }
                            }
                        },
                        toolbox: {
                            feature: {
                                dataView: {show: false, readOnly: false},
                                magicType: {show: true, type: ['line', 'bar']},
                                restore: {show: false},
                                saveAsImage: {show: false}
                            }
                        },
                        legend: {
                            data:data.legend
                        },
                        grid: {
                            x: 70,
                            x2: 50,
                            y: 60,
                            y2: 50
                        },
                        xAxis: [
                            {
                                type: 'category',
                                data: data.xAxis,
                                axisPointer: {
                                    type: 'shadow'
                                }
                            }
                        ],
                        yAxis: [
                            {
                                type: 'value',
                                name: '人数',
                                min: 0,
                                max: data.yAxis.maxnum,
                                interval: 50,
                                axisLabel: {
                                    formatter: '{value} 人'
                                }
                            }
                        ],
//                        series: data.series
                        series : [ {
                            name : '人数',
                            type : 'bar',
                            barWidth : '50%',
                            itemStyle: {
                                normal: {
                                    label: {
                                        show: true, //开启显示
                                        position: 'top', //在上方显示
                                        textStyle: { //数值样式
                                            color: '#666',
                                            fontSize: 12
                                        }
                                    }
                                }
                            },
                            data : data.series
                        } ]

                    };
                    return  this.option;
                },
                communitychartsetoption:function(data,name){
                    this.option = {
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross',
                                crossStyle: {
                                    color: '#999'
                                }
                            }
                        },
                        toolbox: {
                            feature: {
                                dataView: {show: false, readOnly: false},
                                magicType: {show: true, type: ['line', 'bar']},
                                restore: {show: false},
                                saveAsImage: {show: false}
                            }
                        },
                        legend: {
                            data:data.legend
                        },
                        grid: {
                            x: 70,
                            x2: 50,
                            y: 60,
                            y2: 50
                        },
                        xAxis: [
                            {
                                type: 'category',
                                data: data.xAxis,
                                axisPointer: {
                                    type: 'shadow'
                                }
                            }
                        ],
                        yAxis: [
                            {
                                type: 'value',
                                name: name,
                                min: 0,
                                max: Math.max.apply(null, data.series),
                                interval: 100000,
                                axisLabel: {
                                    formatter: '{value}'
                                }
                            }
                        ],
//                        series: data.series
                        series : [ {
                            name : name,
                            type : 'bar',
                            barWidth : '50%',
                            itemStyle: {
                                normal: {
                                    label: {
                                        show: true, //开启显示
                                        position: 'top', //在上方显示
                                        textStyle: { //数值样式
                                            color: '#666',
                                            fontSize: 12
                                        }
                                    }
                                }
                            },
                            data : data.series
                        } ]

                    };
                    return  this.option;
                },
                setChart:function(name,myChartname){
                    this.myChart[myChartname] = echarts.init(name,'macarons');//初始化echart
                }
            },
            mounted:function () {
                const self = this;
                this.setChart(self.$refs.order_echart,'order_echart');//订单图表
                this.setChart(self.$refs.user_echart,'user_echart');//用户图表
                this.setChart(self.$refs.community_echart,'community_echart');//用户图表
                this.info();
                this.getlist();
                this.getCensusMessage();
                this.communityInfo();
                this.getCommunitys('one');

                layui.use('form', function () {
                    var form = layui.form;
                    form.render();
                    form.on('select(place)', function (data) {
                        var type = $("#place_val").val();
                        $.ajax({
                            url: "<?php echo Url('getSystemCountData'); ?>",
                            data: {place: type},
                            type: 'post',
                            dataType: 'json',
                            success: function (re) {
                                var res = JSON.parse(re)
                                $("#system_data_box").html('<tr>\n' +
                                    '                                        <td>今日</td>\n' +
                                    '                                        <td class="center-box today">' + res.data.today.new_count + '</td>\n' +
                                    '                                        <td class="center-box today">' + res.data.today.active_count + '</td>\n' +
                                    '                                        <td class="center-box today">' + res.data.today.view_count + '</td>\n' +
                                    '                                        <td class="center-box today">' + res.data.today.total_count + '</td>\n' +
                                    '                                        <td class="center-box today">' + res.data.today.share_count + '</td>\n' +
                                    '                                    </tr>\n' +
                                    '                                    <tr>\n' +
                                    '                                        <td>昨日</td>\n' +
                                    '                                        <td class="center-box">' + res.data.yesterday.new_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.yesterday.active_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.yesterday.view_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.yesterday.total_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.yesterday.share_count + '</td>\n' +
                                    '                                    </tr>\n' +
                                    '                                    <tr>\n' +
                                    '                                        <td>每日平均</td>\n' +
                                    '                                        <td class="center-box">' + res.data.average.new_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.average.active_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.average.view_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.average.total_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.average.share_count + '</td>\n' +
                                    '                                    </tr>\n' +
                                    '                                    <tr>\n' +
                                    '                                        <td>历史峰值</td>\n' +
                                    '                                        <td class="center-box">' + res.data.max.new_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.max.active_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.max.view_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.max.total_count + '</td>\n' +
                                    '                                        <td class="center-box">' + res.data.max.share_count + '</td>\n' +
                                    '                                    </tr>')
                            }
                        })
                    });
                });
                $(".question-img").hover(function(){
                    $(this).next().show()
                },function(){
                    $(this).next().hide()
                });
                $("#first_down").on("click",function () {
                    if(firstState){
                        $(this).attr("src","/public/system/images/down.png")
                    }else {
                        $(this).attr("src","/public/system/images/up.png")
                    }
                    firstState = !firstState;
                    $(".first-box").toggle();
                });
                $("#second_down").on("click",function () {
                    if(secondState){
                        $(this).attr("src","/public/system/images/down.png")
                    }else {
                        $(this).attr("src","/public/system/images/up.png")
                    }
                    secondState = !secondState;
                    $(".second-box").toggle();
                })

                $(".top-tab").on("click",function () {
                    var type = $(this).data("value");
                    $(".top-active-tab").removeClass("top-active-tab");
                    $(this).addClass("top-active-tab");
                    secondState = false;

                    $.ajax({
                        url: "<?php echo Url('getSystemCountData'); ?>",
                        data: {place: type},
                        type: 'post',
                        dataType: 'json',
                        success: function (re) {
                            var res = JSON.parse(re)
                            $("#system_data_box").html('<tr>\n' +
                                '                                        <td>今日</td>\n' +
                                '                                        <td class="center-box today">' + res.data.today.new_count + '</td>\n' +
                                '                                        <td class="center-box today">' + res.data.today.active_count + '</td>\n' +
                                '                                        <td class="center-box today">' + res.data.today.view_count + '</td>\n' +
                                '                                        <td class="center-box today">' + res.data.today.total_count + '</td>\n' +
                                '                                        <td class="center-box today">' + res.data.today.share_count + '</td>\n' +
                                '                                    </tr>\n' +
                                '                                    <tr>\n' +
                                '                                        <td>昨日</td>\n' +
                                '                                        <td class="center-box">' + res.data.yesterday.new_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.yesterday.active_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.yesterday.view_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.yesterday.total_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.yesterday.share_count + '</td>\n' +
                                '                                    </tr>\n' +
                                '                                    <tr class="first-box">\n' +
                                '                                        <td>每日平均</td>\n' +
                                '                                        <td class="center-box">' + res.data.average.new_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.average.active_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.average.view_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.average.total_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.average.share_count + '</td>\n' +
                                '                                    </tr>\n' +
                                '                                    <tr class="first-box">\n' +
                                '                                        <td>历史峰值</td>\n' +
                                '                                        <td class="center-box">' + res.data.max.new_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.max.active_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.max.view_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.max.total_count + '</td>\n' +
                                '                                        <td class="center-box">' + res.data.max.share_count + '</td>\n' +
                                '                                    </tr>')
                        }
                    })
                })
            }
        });
    });
</script>


</div>
</body>
</html>
