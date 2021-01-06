<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:88:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/index/index.php";i:1597214754;s:89:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/style.php";i:1597214754;}*/ ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <title>OSX管理系统</title>
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="/public/system/frame/css/bootstrap.min.css" rel="stylesheet">
    <link href="/public/system/frame/css/font-awesome.min.css" rel="stylesheet">
    <link href="/public/system/frame/css/animate.min.css" rel="stylesheet">
    <!-- <link href="/public/system/frame/css/style.min.css" rel="stylesheet"> -->
    <!-- 复制style.min.css并修改的文件 -->
    <link href="/public/system/frame/css/style.copy.css" rel="stylesheet">
    <style>
        .plus-content{
            width: 72px;
            display: flex;
            flex-direction: column;
            background-color: #0ca6f2;
            align-items: center;
        }
        .plus-box{
            margin-top: 30px;
            display: block;
            text-align: center;
            color: #fff;
            opacity: 0.7;
        }
        .plus-box:hover{
            opacity: 1;
        }
        .plus-content .active-plus-box{
            opacity: 1;
        }
        .plus-img{
            width: 32px;
            height: 32px;
        }
        .plus-text{
            margin-top: 3px;
            color: #fff;
            font-size: 12px!important;
        }
        .slimScrollDiv{
            width: 100%!important;
        }
        .new-ul{
            display: none;
        }
        .active-ul{
            display: block;
        }
        .message-box{
            position: fixed;
            right: 12px;
            bottom: 50px;
            width: 300px;
            height: 300px;
            z-index: 9999;
        }
        .close-msg{
            position: absolute;
            left: 0;
            top: -20px;
            padding: 0 5px;
            display: inline-block;
            height: 20px;
            color: #fff;
            background-color: #0ca6f2;
            cursor: pointer;
        }
        .icon {
            display: inline-block;
            width: 12px;
            height: 12px;
        }
        .icon img {
            width: 100%;
            height: 100%;
            position: relative;
            top: -1px;
        }
        /* 点击查询的样式 */
        .circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            position: absolute;
        }
        .animated-circles {
            position: absolute;
            right: 100px;
            bottom: 100px;
            cursor: pointer;
        }
        .zixun {
            z-index: 20;
        }
        .c-1 {
            animation: 8s scaleToggleOne infinite;
        }
        .c-2 {
            animation: 8s scaleToggleTwo infinite;

        }
        .c-3 {
            animation: 8s scaleToggleThree infinite;
        }
        /* 点击查询框的动画 */
        @keyframes scaleToggleOne { 
            0% {
            transform:scale(1);
            -webkit-transform:scale(1)
            }
            12% {
            transform:scale(2);
            -webkit-transform:scale(2)
            }
            25% {
            transform:scale(1);
            -webkit-transform:scale(1)
            }
        }
        @keyframes scaleToggleTwo { 
    0% {
    transform:scale(1);
    -webkit-transform:scale(1)
    }
    5% {
    transform:scale(1);
    -webkit-transform:scale(1)
    }
    15% {
    transform:scale(2);
    -webkit-transform:scale(2)
    }
    25% {
    transform:scale(1);
    -webkit-transform:scale(1)
    }
    }
    @keyframes scaleToggleThree { 
    0% {
    transform:scale(1);
    -webkit-transform:scale(1)
    }
    7% {
    transform:scale(1);
    -webkit-transform:scale(1)
    }
    15% {
    transform:scale(2);
    -webkit-transform:scale(2)
    }
    25% {
    transform:scale(1);
    -webkit-transform:scale(1)
    }
    }
    .zixun img {
        width: 100%;
        height: 100%;
    }
    .msg {
        width: 115px;
        height: 30px;
        background-color: rgba(18,150,219);
        position: absolute;
        left: -135px;
        top: 10px;
        display: none;

    }
    .msg span {
        font-size: 16px;
        color: white;
        line-height: 30px;
        margin-left: 20px;
    }
    .small-arrow {
        width: 0px;
        border: 10px solid transparent;
        border-left-color: rgba(18,150,219);
        position: absolute;
        right: -20px;
        top: 5px;
    }
    </style>
</head>
<body class="fixed-sidebar full-height-layout gray-bg" style="overflow:hidden">
    <div id="wrapper" >
        <!-- 点击查询框 -->
        <a href="https://dct.zoosnet.net/lr/chatpre.aspx?id=dct70858541&lng=cn&e=hezuo&r=&rf1=http%3a//192.168.31.131/p/product&rf2=.php&p=http%3a//192.168.31.131/p/index.php&cid=1518162243949518404080&sid=1520306073574298250017" target="_blank">
            <div class="animated-circles" style="box-shadow: 4px 5px 5px  #DADADA;">
                <div class="msg"><span>点击咨询</span><div class="small-arrow"></div></div>
                <div class="zixun circle" style="background-color: #fefefe"><img src="/public/system/images/zixun1.png" alt=""></div>
                <div class="circle c-1" style="background-color: rgba(18,150,219,.25);"></div>
                <div class="circle c-2" style="background-color: rgba(18,150,219,.25);"></div>
                <div class="circle c-3" style="background-color: rgba(18,150,219,.25);"></div>
            </div>
        </a>
        <!--左侧导航开始-->
        <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="nav-close"><i class="fa fa-times-circle"></i>
            </div>

            <div style="display: flex;height: 100%">
                <div class="plus-content">
                    <div class="roll-user-nav" style="position: static;margin-top: 10px;">
                        <!-- 用户 -->
                        <a data-toggle="dropdown" class="dropdown-toggle user-msgs" style="display: block" href="#">
                            <img src="/public/system/images/001.png" alt="">
                        </a>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li><a class="J_menuItem admin_close" href="<?php echo Url('setting.systemAdmin/adminInfo'); ?>">个人资料</a>
                            </li>
                            <li><a class="admin_close" target="_blank" href="http://www.thisky.com/">联系我们</a>
                            </li>
                            <li class="divider"></li>
                            <li><a href="<?php echo Url('Login/logout'); ?>">安全退出</a>
                            </li>
                        </ul>
                    </div>
                    <a class="plus-box J_menuItem active-plus-box" id="you_web" href="/admin/index/main.html" data-index="100" style="margin-top: 40px">
                        <img class="plus-img" src="/public/system/frame/img/nav7.png" alt="">
                        <div class="plus-text">网站管理</div>
                    </a>

                    <?php if(is_array($website_menu) || $website_menu instanceof \think\Collection || $website_menu instanceof \think\Paginator): $i = 0; $__LIST__ = $website_menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$firstMenu): $mod = ($i % 2 );++$i;if($firstMenu['name'] == '短说官网'): ?>
                        <a class="plus-box " href="<?php echo $firstMenu['url']; ?>" target="_blank" id="<?php echo $firstMenu['id1']; ?>">
                            <img class="plus-img" src="<?php echo $firstMenu['icon']; ?>" alt="">
                            <div class="plus-text"><?php echo $firstMenu['name']; ?></div>
                        </a>
                    <?php else: ?>
                        <a class="plus-box J_menuItem" href="<?php echo $firstMenu['url']; ?>" id="<?php echo $firstMenu['id1']; ?>">
                            <img class="plus-img" src="<?php echo $firstMenu['icon']; ?>" alt="">
                            <div class="plus-text"><?php echo $firstMenu['name']; ?></div>
                        </a>
                    <?php endif; endforeach; endif; else: echo "" ;endif; ?>
                </div>
                <div class="sidebar-collapse" style="width: 100%">
                    <li class="nav-header" style="list-style:none">
                        <div class="dropdown profile-element admin_open">
                    <span>
                        <a href="http://osx.opensns.cn/h-col-139.html?mf06" target="_blank">
                            <img alt="image" class="imgbox" src="<?php echo $site_logo; ?>" onerror="javascript:this.src='/public/system/images/admin_logo.png';" />
                        </a>
                    </span>
                        </div>
                        <div class="logo-element">短说
                        </div>
                    </li>
                    <ul class="nav nav-first" style="padding-left: 0;position: relative;" id="side-menu">
                        <!--  菜单  -->
                        <ul class="nav nav-first active-ul new-ul" id="you_ul">
                            <?php if(is_array($menuList) || $menuList instanceof \think\Collection || $menuList instanceof \think\Paginator): $i = 0; $__LIST__ = $menuList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;if (isset($menu['child']) && count($menu['child']) > 0 && $menu['is_show']==1) { ?>
                              <li>
                                  <a href="#"><i class="fa fa-<?php echo $menu['icon']; ?>"></i> <span class="nav-label"><?php echo $menu['menu_name']; ?></span><span class="fa arrow"></span></a>
                                  <ul class="nav nav-second-level">
                                      <?php if(is_array($menu['child']) || $menu['child'] instanceof \think\Collection || $menu['child'] instanceof \think\Paginator): $i = 0; $__LIST__ = $menu['child'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$child): $mod = ($i % 2 );++$i;?>
                                      <li>
                                        <?php if (isset($child['child']) && count($child['child']) > 0  && $child['is_show']==1) { ?>
                                            <a href="#"><i class="fa fa-<?php echo $child['icon']; ?>"></i><?php echo $child['menu_name']; ?><span class="fa arrow"></span></a>
                                            <ul class="nav nav-third-level">
                                                <?php if(is_array($child['child']) || $child['child'] instanceof \think\Collection || $child['child'] instanceof \think\Paginator): $i = 0; $__LIST__ = $child['child'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$song): $mod = ($i % 2 );++$i;if ( $song['is_show']==1) { ?>
                                                  <li><a class="J_menuItem" href="<?php echo $song['url']; ?>"><i class="fa fa-<?php echo $song['icon']; ?>"></i> <?php echo $song['menu_name']; ?></a></li>
                                              <?php } endforeach; endif; else: echo "" ;endif; ?>
                                            </ul>
                                        <?php } elseif($child['is_show']==1) { ?>
                                            <a class="J_menuItem" href="<?php echo $child['url']; ?>"><i class="fa fa-<?php echo $child['icon']; ?>"></i><?php echo $child['menu_name']; ?></a>
                                        <?php } ?>
                                      </li>
                                      <?php endforeach; endif; else: echo "" ;endif; ?>
                                  </ul>
                              </li>
                          <?php } endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                        <?php if(is_array($website_menu) || $website_menu instanceof \think\Collection || $website_menu instanceof \think\Paginator): $i = 0; $__LIST__ = $website_menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$firstUl): $mod = ($i % 2 );++$i;?>
                            <ul class="nav nav-first new-ul" id="<?php echo $firstUl['id2']; ?>">
                            <?php if(is_array($firstUl['child']) || $firstUl['child'] instanceof \think\Collection || $firstUl['child'] instanceof \think\Paginator): $i = 0; $__LIST__ = $firstUl['child'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                            <li>
                                <a href="<?php echo $v['url']; ?>" class="J_menuItem"><img style="width: 16px;height: 16px;margin-right: 6px;" src="<?php echo $v['icon']; ?>" alt=""><span class="nav-label"><?php echo $v['pid']; ?></span><?php if(count($v['menu']) != 0): ?><span class="fa arrow"></span><?php endif; ?></a>
                                <?php if(count($v['menu']) != 0): ?>
                                <ul class="nav nav-third-level">
                                    <?php if(is_array($v['menu']) || $v['menu'] instanceof \think\Collection || $v['menu'] instanceof \think\Paginator): $i = 0; $__LIST__ = $v['menu'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$son): $mod = ($i % 2 );++$i;?>
                                    <li><a class="J_menuItem" href="<?php echo $son['url']; ?>"><i class="fa"></i><?php echo $son['name']; ?></a></li>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                        <?php endforeach; endif; else: echo "" ;endif; ?>

                    </ul>
                </div>
            </div>

        </nav>
        <!--左侧导航结束-->
        <!--右侧部分开始-->
        <div id="page-wrapper" class=" dashbard-1 ">
            <div class="row all-width content-tabs" @touchmove.prevent>
                <button class="roll-nav roll-left navbar-minimalize" style="padding: 0;margin: 0; border-radius: 7px"><i class="fa fa-bars"></i></button>
                <nav class="page-tabs nav-width J_menuTabs">
                    <div class="page-tabs-content">
                        <a href="javascript:;" class="active J_menuTab" data-id="<?php echo Url('Index/main'); ?>">首页</a>
                    </div>
                </nav>
                <button class="roll-nav roll-right J_tabLeft"  style="right: 350px;"><i class="fa fa-backward"></i></button>
                <button class="roll-nav roll-right J_tabRight" style="right: 310px;"><i class="fa fa-forward"></i></button>

                <a href="javascript:void(0);" class="roll-nav roll-right J_tabReply" title="返回"><i class="fa fa-reply"></i> </a>
                <a href="javascript:void(0);" class="roll-nav roll-right J_tabRefresh" title="刷新"><i class="fa fa-refresh"></i> </a>
                <a href="/admin/system.clear/index.html" class="roll-nav roll-right J_tabClearCache J_menuItem" title="清除缓存" data-index="168" style="overflow: hidden;"><i class="icon fa fa-" style="margin-right: 20px;margin-left: 13px;"><img src="/public/system/images/clear.png" alt=""></i>刷新缓存 </a>
                <a href="javascript:void(0);" class="roll-nav roll-right J_tabFullScreen" title="全屏"><i class="fa fa-arrows"></i> </a>
                <a href="javascript:void(0);" class="roll-nav roll-right J_notice" data-toggle="dropdown" aria-expanded="true" title="消息"><i class="fa fa-bell"></i> <span class="badge badge-danger" id="msgcount">0</span></a>
                <ul class="dropdown-menu dropdown-alerts dropdown-menu-right dropdown-menu-news">
                    <li>
                        <a class="J_menuItem" href="<?php echo Url('order.store_order/index',array('status'=>1)); ?>">
                            <div>
                                <i class="fa fa-building-o"></i> 待发货
                                <span class="pull-right text-muted small" id="ordernum">0个</span>
                            </div>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a class="J_menuItem" href="<?php echo Url('store.store_product/index',array('type'=>5)); ?>">
                            <div>
                                <i class="fa fa-pagelines"></i> 库存预警 <span class="pull-right text-muted small" id="inventory">0个</span>
                            </div>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a class="J_menuItem" href="<?php echo Url('store.store_product/index',array('type'=>1)); ?>">
                            <div>
                                <i class="fa fa-pagelines"></i> 待补货 <span class="pull-right text-muted small" id="replenishment">0个</span>
                            </div>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a class="J_menuItem" href="<?php echo Url('store.store_product_reply/index'); ?>">
                            <div>
                                <i class="fa fa-comments-o"></i> 新评论 <span class="pull-right text-muted small" id="commentnum">0个</span>
                            </div>
                        </a>
                    </li>
                    <!--<li class="divider"></li>
                    <li>
                        <a class="J_menuItem" href="<?php echo Url('finance.user_extract/index'); ?>">
                            <div>
                                <i class="fa fa-cny"></i> 申请提现 <span class="pull-right text-muted small" id="reflectnum">0个</span>
                            </div>
                        </a>
                    </li>-->
                </ul>
                <a href="javascript:void(0);" class="roll-nav roll-right J_tabSetting right-sidebar-toggle" title="更多"><i class="fa fa-tasks"></i></a>
                <div class="btn-group roll-nav roll-right" style="right: 260px;">
                    <button class="dropdown J_tabClose" data-toggle="dropdown">关闭<span class="caret"></span>
                    </button>
                    <ul role="menu" class="dropdown-menu dropdown-menu-right">
                        <li class="J_tabShowActive"><a>定位当前选项卡</a>
                        </li>
                        <li class="divider"></li>
                        <li class="J_tabCloseAll"><a>关闭全部选项卡</a>
                        </li>
                        <li class="J_tabCloseOther"><a>关闭其他选项卡</a>
                        </li>
                    </ul>
                </div>
                <!--<div class="roll-user-nav" style="width: 100px;">

                    <a data-toggle="dropdown" class="dropdown-toggle user-msgs" href="#">
                    <span class="clear">
                        <span class="block"><strong class="font-bold"><?php echo $_admin['real_name']; ?></strong></span>
                        <span class="text-muted text-xs block"><?php echo !empty($role_name['role_name'])?$role_name['role_name'] : '管理员'; ?><b class="caret"></b></span>
                    </span>

                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a class="J_menuItem admin_close" href="<?php echo Url('setting.systemAdmin/adminInfo'); ?>">个人资料</a>
                        </li>
                        <li><a class="admin_close" target="_blank" href="http://www.thisky.com/">联系我们</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="<?php echo Url('Login/logout'); ?>">安全退出</a>
                        </li>
                    </ul>
                </div>-->
            </div>

            <!--内容展示模块-->
            <div class="row J_mainContent" id="content-main">
                <iframe class="J_iframe" name="iframe_opensnsx_main" width="100%" height="100%" src="<?php echo Url('Index/main'); ?>" frameborder="0" data-id="<?php echo Url('Index/main'); ?>" seamless></iframe>
            </div>
            <!--底部版权-->
            <div class="footer" @touchmove.prevent>
                        本产品由想天软件提供技术支持
<!--                <div id="message_box" class="message-box" style="display: none">-->
<!--                    <span id="close_msg" class="close-msg">隐藏>></span>-->
<!--                    <div id="message_content" ></div>-->
<!--                </div>-->
            </div>
        </div>
    </div>
    <!--右侧部分结束-->
    <!--右侧边栏开始-->
    <div id="right-sidebar">
        <div class="sidebar-container">
            <ul class="nav nav-tabs navs-3">
                <li class="active">
                    <a data-toggle="tab" href="#tab-1">
                        <i class="fa fa-bell"></i>通知
                    </a>
                </li>
                <li class="">
                    <a data-toggle="tab" href="#tab-2">
                        <i class="fa fa-gear"></i> 设置
                    </a>
                </li>

            </ul>
            <div class="tab-content">
                <div id="tab-1" class="tab-pane active">
                    <div class="sidebar-title">
                        <h3><i class="fa fa-comments-o"></i> 最新通知</h3>
                        <small><i class="fa fa-tim"></i> 您当前有0条未读信息</small>
                    </div>
                    <div>
                        <!--<div class="sidebar-message">
                            <a href="#">
                                <div class="pull-left text-center">
                                    <img alt="image" class="img-circle message-avatar" src="http://ozwpnu2pa.bkt.clouddn.com/a1.jpg">
                                    <div class="m-t-xs">
                                        <i class="fa fa-star text-warning"></i> <i class="fa fa-star text-warning"></i>
                                    </div>
                                </div>
                                <div class="media-body">

                                    据天津日报报道：瑞海公司董事长于学伟，副董事长董社轩等10人在13日上午已被控制。 <br>
                                    <small class="text-muted">今天 4:21 <a class="J_menuItem admin_close" href="/admin/setting.system_admin/admininfo.html" data-index="0">【查看】</a></small>
                                </div>
                            </a>
                        </div>-->
                    </div>
                </div>
                <div id="tab-2" class="tab-pane ">
                    <div class="sidebar-title">
                        <h3><i class="fa fa-comments-o"></i> 提示</h3>
                        <small><i class="fa fa-tim"></i> 你可以从这里选择和预览主题的布局和样式，这些设置会被保存在本地，下次打开的时候会直接应用这些设置。</small>
                    </div>
                    <div class="skin-setttings">
                        <div class="title">设置</div>
                        <div class="setings-item">
                            <span>收起左侧菜单</span>
                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="collapsemenu" class="onoffswitch-checkbox" id="collapsemenu">
                                    <label class="onoffswitch-label" for="collapsemenu">
                                        <span class="onoffswitch-inner"></span> <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                      <!--  <div class="setings-item">
                            <span>固定宽度</span>
                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="boxedlayout" class="onoffswitch-checkbox" id="boxedlayout">
                                    <label class="onoffswitch-label" for="boxedlayout">
                                        <span class="onoffswitch-inner"></span> <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>-->
                        <div class="setings-item">
                            <span>菜单点击刷新</span>
                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="refresh" class="onoffswitch-checkbox" id="refresh">
                                    <label class="onoffswitch-label" for="refresh">
                                        <span class="onoffswitch-inner"></span> <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                      <!--  <div class="title">皮肤选择</div>
                        <div class="setings-item blue-skin nb">
                            <span class="skin-name ">
                                <a href="#" class="s-skin-1">
                                    默认皮肤
                                </a>
                            </span>
                        </div>
                        <div class="setings-item default-skin nb">
                            <span class="skin-name ">
                                <a href="#" class="s-skin-0">
                                    黑色主题
                                </a>
                            </span>
                        </div>
                        <div class="setings-item yellow-skin nb">
                            <span class="skin-name ">
                                <a href="#" class="s-skin-3">
                                    黄色/紫色主题
                                </a>
                            </span>
                        </div>-->
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!--右侧边栏结束-->
    </div>
    <!--vue调用不能删除-->
    <div id="vm"></div>
    <script src="/public/system/frame/js/jquery.min.js"></script>
    <script src="/public/system/frame/js/bootstrap.min.js"></script>
    <script src="/public/system/frame/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/public/system/frame/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="/public/system/frame/js/plugins/layer/layer.min.js"></script>
    <script src="/public/system/frame/js/hplus.min.js"></script>
    <script src="/public/system/frame/js/contabs.min.js"></script>
    <script src="/public/system/frame/js/plugins/pace/pace.min.js"></script>
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
    <script src="/public/system/js/index.js"></script>
    <script>
        $(function() {
            // 隐藏消息框
            var flag = true;
            $("#close_msg").click(function () {
                if (flag) {
                    $("#message_box").css({"right":"-300px"});
                    $(this).css({"left":"-64px"});
                    $(this).html("显示<<");
                    flag = false;
                }else{
                    $("#message_box").css({"right":"12px"});
                    $(this).css({"left":"0"});
                    $(this).html("隐藏>>");
                    flag = true;
                }
            })
            // 消息内容开始
            $.ajax({
                url:"https://osxbe.demo.opensns.cn/auth/Notice/getNotice",
                data:{site_code:"<?php echo $site_code; ?>"},
                type:'post',
                dataType:'json',
                success:function (res) {
                    var data=res.data.data;
                    if(res.code == 200&&data.status==1) {
                        document.getElementById("message_content").innerHTML = data.html[0].html
                    }else{
                        $("#message_box").css({"right":"-300px"});
                        $("#close_msg").css({"left":"-50px"}).html("显示<<");
                        flag = false;
                    }
                }
            })
            // 消息内容结束
            function getnotice() {
                $.getJSON("<?php echo Url('Jnotice'); ?>", function(res) {
                    var info = eval("(" + res + ")");
                    var data = info.data;
                    $('#msgcount').html(data.msgcount);
                    $('#ordernum').html(data.ordernum + '个');
                    $('#inventory').html(data.inventory + '个');
                    $('#replenishment').html(data.replenishment + '个');
                    $('#commentnum').html(data.commentnum + '个');
                    $('#reflectnum').html(data.reflectnum + '个');
                });
            }
            getnotice();
            setInterval(getnotice, 600000);
        });

        // 点击查询 框的交互
        $('.animated-circles').mouseenter(function() {
            $('.msg').css('display','block')
        })
        $('.animated-circles').mouseleave(function() {
            $('.msg').css('display','none')
        })
    </script>
    <script>
        $(function () {
            $('.nav-width').width($('.all-width').width()-540);
        })
    </script>
    <script>
        $(".plus-box").on("click",function () {
            $(".active-plus-box").removeClass("active-plus-box");
            $(this).addClass("active-plus-box");
        });
        $("#you_web").on("click",function () {
            $(".active-ul").removeClass("active-ul");
            $("#you_ul").addClass("active-ul");
        });
        $("#shop_our").on("click",function () {
            $(".active-ul").removeClass("active-ul");
            $("#shop_ul").addClass("active-ul");
        });
        $("#our_web").on("click",function () {
            $(".active-ul").removeClass("active-ul");
            $("#our_web_ul").addClass("active-ul");
        });
        $("#our_community").on("click",function () {
            $(".active-ul").removeClass("active-ul");
            $("#our_community_ul").addClass("active-ul");
        });
        $("#look_auth").on("click",function () {
            $(".active-ul").removeClass("active-ul");
            $("#auth_ul").addClass("active-ul");
        });
    </script>
</body>

</html>