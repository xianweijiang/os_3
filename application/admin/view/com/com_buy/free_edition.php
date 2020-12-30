{extend name="public/container"}
{block name="content"}
<link rel="stylesheet" href="/public/system/frame/css/buy.css">
<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
    <div class="layui-tab layui-tab-brief" lay-filter="tab">
        <ul class="layui-tab-title">
            <li lay-id="list" class="layui-this">
                <a href="#">授权信息</a>
            </li>
            <li lay-id="list">
                <a href="#">站点信息</a>
            </li>
        </ul>
    </div>
    <div class="buy-message">
        <img class="application-logo" src="/public/system/images/application_logo.png" alt="">
        <div>
            <p class="version-message">
                <span class="system-version">当前系统版本：</span>
                <span class="system-number">2019102901</span>
            </p>
            <p class="version-message">
                <span class="system-version">版本号：</span>
                <span class="system-number">1.4.1</span>
            </p>
        </div>
        <p style="margin: 20px 0 0 0">授权状态：<span class="free-edition">免费版</span>（请联系客户经理购买）</p>
        <button class="buy-now">点此购买</button>
        <div class="privilege">
            <p>商业版特权：</p>
            <p><i class="layui-icon layui-icon-ok"></i>购买商业版享受全部扩展插件免服务费</p>
            <p><i class="layui-icon layui-icon-ok"></i>可自主修改版权标志</p>
            <p><i class="layui-icon layui-icon-ok"></i>7*8小时技术服务</p>
        </div>
        <div class="attention-me">
            <div class="attention-qrcode">
                <div class="each-qrcode">
                    <img src="/public/system/images/operation_cat.png" alt="">
                    <div class="qrcode-description">
                        <p>运营喵</p>
                        <p class="qrcode-people">想天小番茄</p>
                    </div>
                </div>
                <div class="each-qrcode">
                    <img src="/public/system/images/product_director.png" alt="">
                    <div class="qrcode-description">
                        <p> 产品总监</p>
                        <p class="qrcode-people">想天Mr Chen</p>
                    </div>
                </div>
                <div class="each-qrcode">
                    <img src="/public/system/images/thissky_software.png" alt="">
                    <div class="qrcode-description">
                        <p> 想天软件</p>
                        <p class="qrcode-people read-color">想天社区服务号</p>
                    </div>
                </div>
            </div>
            <p style="margin-top: 50px">获取最新的产品更新动态，请关注想天科技公众号。</p>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
