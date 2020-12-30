<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <title>登录 - Powered by OSX!</title>
    <meta name="generator" content="opensnsx! v2.5" />
    <meta name="author" content="opensnsx! Team and opensnsx UI Team" />
    <link href="{__FRAME_PATH}css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
    <link href="{__FRAME_PATH}css/font-awesome.min.css?v=4.3.0" rel="stylesheet">
    <link href="{__FRAME_PATH}css/animate.min.css" rel="stylesheet">
    <link href="{__FRAME_PATH}css/login_iconfont.css" rel="stylesheet">
    <!-- <link href="{__FRAME_PATH}css/style.min.css?v=3.0.0" rel="stylesheet"> -->
    <!-- 复制style.min.css并修改的文件 -->
    <link href="{__FRAME_PATH}css/style.copy.css" rel="stylesheet">
    <!-- 消息弹框样式 -->
    <link href="{__FRAME_PATH}css/toast-style.css" rel="stylesheet">
    <!-- <link href="{__STATIC_PATH}css/toast-style.css"> -->
    <link ref="stylesheet" href="path/to/swiper/dist/css/swiper.css"/>

    <link rel="stylesheet" href="https://unpkg.com/swiper/css/swiper.css">  
    <link rel="stylesheet" href="https://unpkg.com/swiper/css/swiper.min.css">    
    <script src="https://unpkg.com/swiper/js/swiper.js"> </script>  
    <script src="https://unpkg.com/swiper/js/swiper.min.js"> </script>

    <script type="text/javascript" src="path/to/swiper.js"></script>
    <script type="text/javascript" src="path/to/vue.min.js"></script>
    <script type="text/javascript" src="path/to/dist/vue-awesome-swiper.js"></script>
    <script type="text/javascript">
      Vue.use(window.VueAwesomeSwiper)
    </script>
    
    <script>
        top != window && (top.location.href = location.href);
    </script>
    <!-- 引入layui.css -->
    <link href="{__PLUG_PATH}layui2.5.5/css/layui.css" rel="stylesheet">
    <style>
        /* 设置下图片样式 */
        .swiper-slide img{
            width: 400px;
            height: 556px;
        }
        /* 轮播图的样式 */
        .layui-carousel {
            overflow: hidden;
            padding-right: 40px;
        }
        .logo {
            position: absolute;
            left: 30px;
            top: 15px;
        }
        .logo img{
            width: 60px;
        }
        .logo span{
            font-size: 32px;
            color: #000000;
            font-weight: bold;
            vertical-align: middle;
            margin-left: 10px;
        }
        .foot-bar {
            position: absolute;
            bottom: 40px;
            width: 580px;
            margin: 0 auto;
        }
        .ad {
            display: flex;
            margin: 0 auto;
            width: 306px;
            justify-content: space-between;
        }
        .line {
            width: 45px;
            height: 0.25px;
            margin-top: 9.5px;
            background-color: #666666;
        }
        .content {
            font-size: 14px;
            color: #666666;
        }
        .ICP {
            text-align: center;
            margin-top: 20px;
        }
        .ICP span {
            display: inline-block;
            font-size: 13px;
            color: #aaaaaa;
            width: 100%;
        }
    </style>
</head>

<body class="gray-bg login-bg">
    <div class="login-background" style="background-color: #f5f6f9">
        <a href="http://osx.opensns.cn/" style="display: inline-block"><div class="logo"><img src="/public/system/images/LOGO1024.png" alt=""><span>短说</span></div></a>
        <div class="middle-box text-center loginscreen  animated fadeInDown new-login-box" style="padding: 0;padding-right: 40px;height: 556px">
            <!-- <div class="layui-carousel" id="carousel" style="background-color: white;padding-right: 0px;margin-right: 40px;">
                <div carousel-item class="logo-fish-img">
                </div>
            </div> -->
            <div class="swiper-container" style="width: 400px;margin-right: 40px;border-top-left-radius: 7px;border-bottom-left-radius: 7px"">
                <div class="swiper-wrapper">
                    
                </div>
                <!-- 如果需要分页器 -->
                <div class="swiper-pagination"></div>

                <!-- 如果需要导航按钮 -->
                <!-- <div class="swiper-button-prev"></div> -->
                <!-- <div class="swiper-button-next"></div> -->

                <!-- 如果需要滚动条 -->
                <!-- <div class="swiper-scrollbar"></div> -->
            </div>
            <div class="login-group" style="padding: 40px 0">
              
                <p class="login-tips"><span>欢迎回来，请输入工作账号密码。</span><span>当前IP：{$this_client_ip}</span></p>
                <form id="login_form" role="form" action_url="{:url('verify')}" method="post">
                    <div class="form-group">
                        <p class="input-group-title">用户名</p>
                        <div class="input-group m-b"><span class="input-group-addon"><i class="iconfont icon-user-s"></i> </span>
                            <input type="text" id="account" name="account" placeholder="用户名" placeholder="用户名" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <p class="input-group-title">密码</p>
                        <div class="input-group m-b"><span class="input-group-addon"><i class="iconfont icon-suo"></i> </span>
                            <input type="password" class="form-control" id="pwd" name="pwd" placeholder="密码" required="">
                        </div>

                    </div>
                    <div class="form-group">
                        <p class="input-group-title">验证码</p>
                        <div class="input-group"><span class="input-group-addon"><i class="iconfont icon-yanzhengma"></i> </span>
                            <input type="text" class="form-control" id="verify" name="verify" placeholder="验证码" required="">
                            <span class="input-group-btn verification-code" style="padding: 0;margin: 0;">
                                <img id="verify_img" src="{:Url('captcha')}" alt="验证码" style="padding: 0;height: 34px;margin: 0;">
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <strong>
                            <p class="text-danger" id="err"></p>
                        </strong>
                    </div>
                    <button type="button" data-role="login-button" class="btn btn-primary block m-b login-button">登 录</button>
                    <?php /*  <p class="text-muted text-center"> <a href="{:url('./forgetpwd')}"><small>忘记密码了？</small></a> | <a href="{:url('./register')}">注册一个新账号</a>
              </p>  */ ?>
                </form>
            </div>
        </div>
        <div class="foot-bar" style="background-color: #f5f6f9">
            <div class="ad">
                <div class="line"></div>
                <div class="content">短说整合运营系统，运营不再难</div>
                <div class="line"></div>
            </div>
            <div class="ICP">
                <span>Copyright©2014-2020 嘉兴想天科技信息有限公司 Powered by 短说OSX</span>
            </div>
        </div>
    </div>
    <!--<div class="footer" style=" position: fixed;bottom: 0;width: 100%;left: 0;margin: 0;opacity: 0.8;">

    </div>-->
    </div>


    <!-- 全局js -->
    <script src="{__FRAME_PATH}js/jquery.min.js"></script>
    <script src="{__FRAME_PATH}js/bootstrap.min.js?v=3.4.0"></script>
    <script src="{__MODULE_PATH}login/ios-parallax.js"></script>
    <script src="{__PLUG_PATH}layui/layui.js"></script>
    <!-- <script src="{__PLUG_PATH}layer/layer.js"></script> -->
    <script src="{__ADMIN_PATH}js/layuiList.js"></script>
    <!-- 封装的消息弹框 -->
    <script src="{__FRAME_PATH}js/toast-js.js"></script>

    <!--加密代码-->
    <script src="{__PLUG_PATH}crypt/aes.js"></script>
    <script src="{__PLUG_PATH}crypt/pad-zeropadding.js"></script>
    <script src="{__PLUG_PATH}crypt/openssl.js"></script>
    <!--加密代码 end-->

    <script src="{__MODULE_PATH}login/index.js"></script>

    <script>
        $('#verify').keypress(function (e) {
            if(e.which=='13') {
                $("[data-role=login-button]").click();
            }
        })
    </script>
    <!--统计代码，可删除-->
    <!--点击刷新验证码-->
    <script>
    var arr = [];
    // 请求数据
    $.ajax({
        url: 'https://osxbe.demo.opensns.cn/auth/index/get_login_adv',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            arr = res.data.adv
            // for (let i = 0; i < arr.length; i++) {
            //     var tag = '<div class="fish-image"><a class="carousel-img-a" href="' + arr[i].link_url.url +'"><img style="border-top-left-radius: 7px;border-bottom-left-radius: 7px" src="'+ arr[i].pic +'" alt="" class="carousel-img"></a></div>';
            //     $('.logo-fish-img').append(tag)
            // }
            for (let i = 0; i < arr.length; i++) {
                var tag = '<div class="swiper-slide"><a class="carousel-img-a" href="' + arr[i].link_url.url +'"><img style="border-top-left-radius: 7px;border-bottom-left-radius: 7px" src="'+ arr[i].pic +'" alt="" class="carousel-img"></a></div>';
                $('.swiper-wrapper').append(tag)
            }
            // 登陆首页广告位优化为轮播图
            // layui.use('carousel', function(){
            // var carousel = layui.carousel;
            // 建造实例
            // carousel.render({
            //     elem: '#carousel',
            //     width: '400px' ,     // 设置容器宽度
            //     height: '556px' ,    // 设置容器宽度       
            //     anim: 'default',     // 切换动画方式    
            //     arrow: 'none',       // 悬浮箭头的显隐
            //     interval: 3000,      // 轮播图切换时间
            //     indicator: 'none'    // 圆点指示器的显隐
            //     });
            // });

            var mySwiper = new Swiper ('.swiper-container', {
                loop: true, // 循环模式选项
                speed:300,
                width: 400, 
                height: 556, 
                autoplay : {
                  delay:3000
                },
                // 如果需要分页器
                pagination :{
	                el: '.swiper-pagination',
	                clickable :true,
                },

                // 如果需要前进后退按钮
                // navigation: {
                //   nextEl: '.swiper-button-next',
                //   prevEl: '.swiper-button-prev',
                // },

                // 如果需要滚动条
                // scrollbar: {
                //   el: '.swiper-scrollbar',
                // },
            })        
            
        }
    })
    </script>
</body>

</html>