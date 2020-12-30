{extend name="public/container"}
{block name="content"}
<link rel="stylesheet" href="/public/system/frame/css/authorize.css">
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
    <div class="authorize-content">
        <div class="authorize-imgs">
            <img src="/public/system/images/bear-icon.png" alt="">
            <div class="mutual-arrow">
                <img src="/public/system/images/link-icon.png" alt="">
                <img src="/public/system/images/arrow-icon.png" alt="">
            </div>
            <img class="application-logo" src="/public/system/images/application_logo.png" alt="">
        </div>
        <div class="authorize-infos">
            <h3 class="basic-title"><span class="title-num">1</span>基本信息</h3>
            <ul class="infos-lists">
                <li class="each-info-list">
                    <p class="list-title">站点号：</p>
                    <div class="list-content">CBAS2123412</div>
                </li>
                <li class="each-info-list">
                    <p class="list-title">网站名：</p>
                    <div class="list-content">想天官方社区</div>
                </li>
                <li class="each-info-list">
                    <p class="list-title">通讯密钥：</p>
                    <div class="list-content">
                        <p>ADBXCWIE******WD <button class="show-key">查看秘钥</button></p>
                    </div>
                </li>
                <li class="each-info-list">
                    <p class="list-title">网站URL：</p>
                    <div class="list-content">http://www.thisky.com</div>
                </li>
                <li class="each-info-list">
                    <p class="list-title">IP：</p>
                    <div class="list-content">127.0.0.4</div>
                </li>
                <li class="each-info-list">
                    <p class="list-title">当前版本号：</p>
                    <div class="list-content">1.1.0  20191030</div>
                </li>
                <li class="each-info-list">
                    <p class="list-title">技术支持：</p>
                    <div class="all-single-choose list-content">
                        <div class="single-choose">
                            <input type="radio" name="support" value="free" title="免费版">
                            <p><span style="color: #d9001b">免费版</span>(<span style="color: #02a7f0">社区</span>支持)</p>
                        </div>
                        <div class="single-choose">
                            <input type="radio" name="support" value="more" title="7*8小时">
                            <p>7*8小时</p>
                        </div>
                        <div class="single-choose">
                            <input type="radio" name="support" value="best" title="7*24小时">
                            <p>7*24小时</p>
                        </div>
                        <div>购买商业版后，可享受全部扩展免服务费！<button class="buy-now">点此购买</button></div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="authorize-infos">
            <h3 class="basic-title"><span class="title-num">2</span>套餐信息</h3>
            <ul class="package-lists">
                <li class="each-package-lists">
                    <p class="options-title">社区</p>
                    <div class="each-option">
                        <input type="checkbox" name="" title="H5" lay-skin="primary">
                        H5
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="PC" lay-skin="primary">
                        PC
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="APP" lay-skin="primary">
                        APP
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="微信小程序" lay-skin="primary">
                        微信小程序
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="支付宝小程序" lay-skin="primary">
                        支付宝小程序
                    </div>
                </li>
                <li class="each-package-lists">
                    <p class="options-title">商城</p>
                    <div class="each-option">
                        <input type="checkbox" name="" title="H5" lay-skin="primary">
                        H5
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="PC" lay-skin="primary">
                        PC
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="APP" lay-skin="primary">
                        APP
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="微信小程序" lay-skin="primary">
                        微信小程序
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="支付宝小程序" lay-skin="primary">
                        支付宝小程序
                    </div>
                </li>
                <li class="each-package-lists">
                    <p class="options-title">知识付费</p>
                    <div class="each-option">
                        <input type="checkbox" name="" title="H5" lay-skin="primary">
                        H5
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="PC" lay-skin="primary">
                        PC
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="APP" lay-skin="primary">
                        APP
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="微信小程序" lay-skin="primary">
                        微信小程序
                    </div>
                    <div class="each-option">
                        <input type="checkbox" name="" title="支付宝小程序" lay-skin="primary">
                        支付宝小程序
                    </div>
                </li>
            </ul>
            <div class="expansion-infos">
                <h3>扩展模块</h3>
                <ul class="expansion-lists">
                    <li class="each-expansion-lists">
                        <p class="expansion-title">社区</p>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="小打卡" lay-skin="primary">
                            小打卡
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="马甲" lay-skin="primary">
                            马甲
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="推广中心" lay-skin="primary">
                            推广中心
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="版主管理面板" lay-skin="primary">
                            版主管理面板
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="社区会员" lay-skin="primary">
                            社区会员
                        </div>
                    </li>
                    <li class="each-expansion-lists">
                        <p class="expansion-title">商城</p>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="拼团" lay-skin="primary">
                            拼团
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="砍价" lay-skin="primary">
                            砍价
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="秒杀" lay-skin="primary">
                            秒杀
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="商城会员" lay-skin="primary">
                            商城会员
                        </div>
                    </li>
                    <li class="each-expansion-lists">
                        <p class="expansion-title">知识付费</p>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="线下活动(票务)" lay-skin="primary">
                            线下活动(票务)
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="创作者平台" lay-skin="primary">
                            创作者平台
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="课后作业" lay-skin="primary">
                            课后作业
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="拼团" lay-skin="primary">
                            拼团
                        </div>
                    </li>
                    <li class="each-expansion-lists">
                        <p class="expansion-title">公共模块</p>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="全站DIY" lay-skin="primary">
                            全站DIY
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="数据管家服务" lay-skin="primary">
                            数据管家服务
                        </div>
                        <div class="expansion-option">
                            <input type="checkbox" name="" title="分销" lay-skin="primary">
                            分销
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
