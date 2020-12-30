{extend name="public/container"}
{block name="content"}
<style>
    .backlog-body{
        padding: 10px 15px;
        background-color: #f8f8f8;
        color: #999;
        border-radius: 2px;
        transition: all .3s;
        -webkit-transition: all .3s;
        overflow: hidden;
        max-height: 84px;
    }
    .backlog-body h3{
        margin-bottom: 10px;
    }
    .right-icon{
        position: absolute;
        right: 10px;
    }
    .backlog-body p cite {
        font-style: normal;
        font-size: 17px;
        font-weight: 300;
        color: #009688;
    }
    .layuiadmin-badge, .layuiadmin-btn-group, .layuiadmin-span-color {
        position: absolute;
        right: 15px;
    }
    .layuiadmin-badge {
        top: 50%;
        margin-top: -9px;
        color: #01AAED;
    }
    .info-content{
        display: flex;
        flex-wrap: wrap;
        padding: 0 15px;
    }
    .info-content .info-line{
        display: flex;
        width: 50%;
    }
    .info-content .info-line .title{
        color: #333;
        width: 30%;
    }
    .info-content .info-line .text{
        color: #999;
        width: 70%;
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">会员详情</div>
                <div class="layui-card-body">
                    <div class="info-content">
                        <div class="info-line">
                            <div class="title">用户昵称</div>
                            <div class="text">{$userinfo.nickname}</div>
                        </div>
                        <div class="info-line">
                            <div class="title">手机号码</div>
                            <div class="text">{$userinfo.account}</div>
                        </div>
                        <div class="info-line">
                            <div class="title">用户姓名</div>
                            <div class="text"></div>
                        </div>
                        <div class="info-line">
                            <div class="title">性别</div>
                            <div class="text">{$userinfo.sex}</div>
                        </div>
                        <div class="info-line">
                            <div class="title">生日</div>
                            <div class="text">{$userinfo.birthday}</div>
                        </div>
                        <div class="info-line" style="width: 100%">
                            <div class="title" style="width: 15%">一句话简介</div>
                            <div class="text">{$userinfo.signature}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">积分详情</div>
                <div class="layui-card-body">
                    <div class="info-content">
                        <div class="info-line">
                            <div class="title">{$userinfo.exp_name}</div>
                            <div class="text">{$userinfo.exp}</div>
                        </div>
                        <div class="info-line">
                            <div class="title">{$userinfo.fly_name}</div>
                            <div class="text">{$userinfo.fly}</div>
                        </div>
                        <div class="info-line">
                            <div class="title">{$userinfo.buy_name}</div>
                            <div class="text">{$userinfo.buy}</div>
                        </div>
                        <div class="info-line">
                            <div class="title">{$userinfo.gong_name}</div>
                            <div class="text">{$userinfo.gong}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">等级信息</div>
                <div class="layui-card-body">
                    <div class="info-content">
                        <!--<div class="info-line">
                            <div class="title">会员等级</div>
                            <div class="text"></div>
                        </div>-->
                        <div class="info-line">
                            <div class="title">用户等级</div>
                            <div class="text">{$userinfo.user_grade}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">认证信息</div>
                <div class="layui-card-body">
                    <div class="info-content">
                        {volist name="userinfo.certification" id="vo"}
                        <div class="info-line">
                            <div class="title">{$vo}</div>
                            <div class="text"></div>
                        </div>
                        {/volist}
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">社区信息</div>
                <div class="layui-card-body">
                    <div class="total-num-content" style="display: flex;">
                        <div class="total-box" style="display: flex;justify-content: center;width: 25%">
                            <div style="text-align: center">
                                <div class="title" style="color: #0ca6f2">帖子</div>
                                <div class="num" style="color: #333;font-weight: 600;font-size: 20px;margin-top: 10px">{$userinfo.post_count+$userinfo.news_count+$userinfo.video_count}</div>
                            </div>
                            <div style="margin-top: 25px;margin-left: 10px;">
                                <div style="display: flex;line-height: 16px">
                                    <div style="width: 50px">帖子:</div>
                                    <div>{$userinfo.post_count}</div>
                                </div>
                                <div style="display: flex;line-height: 16px">
                                    <div style="width: 50px">资讯:</div>
                                    <div>{$userinfo.news_count}</div>
                                </div>
                                <div style="display: flex;line-height: 16px">
                                    <div style="width: 50px">视频:</div>
                                    <div>{$userinfo.video_count}</div>
                                </div>
                            </div>
                        </div>
                        <div class="total-box" style="text-align: center;width: 20%">
                            <div class="title" style="color: #0ca6f2">关注</div>
                            <div class="num" style="color: #333;font-weight: 600;font-size: 20px;margin-top: 10px">{$userinfo.follow}</div>
                        </div>
                        <div class="total-box" style="text-align: center;width: 20%">
                            <div class="title" style="color: #0ca6f2">粉丝</div>
                            <div class="num" style="color: #333;font-weight: 600;font-size: 20px;margin-top: 10px">{$userinfo.fans}</div>
                        </div>
                        <div class="total-box" style="text-align: center;width: 20%">
                            <div class="title" style="color: #0ca6f2">收藏</div>
                            <div class="num" style="color: #333;font-weight: 600;font-size: 20px;margin-top: 10px">{$userinfo.is_collect}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">商城信息</div>
                <div class="layui-card-body">
                    <div class="layui-row layui-col-space15">
                    {volist name='headerList' id='vo'}
                    <div class="layui-col-xs3" style="margin-bottom: 10px ">
                        <div class="layui-card">
                            <div class="layui-card-header">
                                {$vo.title}
                                <span class="layui-badge layuiadmin-badge {if isset($vo.class) && $vo.class}{$vo.class}{else}layui-bg-blue{/if}">{$vo.key}</span>
                            </div>
                            <div class="layui-card-body">
                                <p class="layuiadmin-big-font">{$vo.value}</p>
                            </div>
                        </div>
                    </div>
                    {/volist}
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">其它信息</div>
                <div class="layui-card-body">
                    <div class="info-content">
                        <div class="info-line" style="width: 100%;">
                            <div class="title" style="width: 15%">用户备注</div>
                            <div class="text">{}</div>
                        </div>
                        <div class="info-line" style="width: 100%;">
                            <div class="title" style="width: 15%">用户状态</div>
                            <div class="text">{$userinfo._status}</div>
                        </div>
                        <div class="info-line" style="width: 100%;">
                            <div class="title" style="width: 15%">分销权限</div>
                            {if condition="$userinfo.is_seller eq '0'"}
                            <div class="text">未开启</div>
                            {elseif condition="$userinfo.is_seller eq '1'"/}
                            <div class="text">已开启</div>
                            {/if}
                        </div>
                        <div class="info-line" style="width: 100%;">
                            <div class="title" style="width: 15%">首次登陆时间</div>
                            <div class="text">{$userinfo.add_time}</div>
                        </div>
                        <div class="info-line" style="width: 100%;">
                            <div class="title" style="width: 15%">最后登陆时间</div>
                            <div class="text">{$userinfo.last_time}</div>
                        </div>
                        <div class="info-line" style="width: 100%;">
                            <div class="title" style="width: 15%">最后登陆IP</div>
                            <div class="text">{$userinfo.last_ip}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">其他记录</div>
                <div class="layui-card-body">
                    <div class="layui-tab layui-tab-card">
                        <ul class="layui-tab-title">
                            <li class="layui-this">消费能力</li>
                            <li>积分明细</li>
                            <li>签到记录</li>
                            <li>持有优惠劵</li>
                            <li>余额变动记录</li>
                            <li>推广下线明细</li>
                        </ul>
                        <div class="layui-tab-content" id="content">
                            <div class="layui-tab-item layui-show">
                                <table class="layui-table" lay-skin="line" v-cloak="">
                                    <thead>
                                        <tr>
                                            <th>订单编号</th>
                                            <th>收货人</th>
                                            <th>商品数量</th>
                                            <th>商品总价</th>
                                            <th>实付金额</th>
                                            <th>交易完成时间</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in orderList">
                                            <td class="text-center">{{item.order_id}}
                                                <p>
                                                    <span class="layui-badge" :class="{'layui-bg-green':item.paid==1}" v-text="item.paid==1 ? '已支付': '未支付' ">正在加载</span>
                                                    <span class="layui-badge" :class="{'layui-bg-cyan':item.pay_type=='yue','layui-bg-blue':item.pay_type=='weixin'}" v-text="item.pay_type=='weixin' ? '微信支付': '余额支付' ">正在加载</span>
                                                    <span class="layui-badge layui-bg-black" v-show="item.pink_id!=0">拼团</span>
                                                    <span class="layui-badge layui-bg-blue" v-show="item.seckill_id!=0">秒杀</span>
                                                    <span class="layui-badge layui-bg-gray" v-show="item.bargain_id!=0">砍价</span>
                                                </p>
                                            </td>
                                            <td>{{item.real_name}}</td>
                                            <td>{{item.total_num}}</td>
                                            <td>{{item.total_price}}</td>
                                            <td>{{item.pay_price}}</td>
                                            <td>{{item.pay_time}}</td>
                                        </tr>
                                        <tr v-show="orderList.length<=0" style="text-align: center">
                                            <td colspan="6">暂无数据</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div ref="page_order" v-show="count.order_count > limit" style="text-align: right;"></div>
                            </div>
                            <div class="layui-tab-item">
                                <table class="layui-table" lay-skin="line" v-cloak="">
                                    <thead>
                                    <tr>
                                        <th>来源/用途</th>
                                        <th>积分变化</th>
                                        <th>变化后积分</th>
                                        <th>日期</th>
                                        <th>备注</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in integralList">
                                            <td>{{item.title}}</td>
                                            <td>{{item.number}}</td>
                                            <td>{{item.balance}}</td>
                                            <td>{{item.add_time}}</td>
                                            <td>{{item.mark}}</td>
                                        </tr>
                                        <tr v-show="integralList.length<=0" style="text-align: center">
                                            <td colspan="5">暂无数据</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div ref="integral_page" v-show="count.integral_count > limit" style="text-align: right;"></div>
                            </div>
                            <div class="layui-tab-item">
                                <table class="layui-table" lay-skin="line" v-cloak="">
                                    <thead>
                                    <tr>
                                        <th>动作</th>
                                        <th>获得积分</th>
                                        <th>签到时间</th>
                                        <th>备注</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in SignList">
                                            <td>{{item.title}}</td>
                                            <td>{{item.number}}</td>
                                            <td>{{item.add_time}}</td>
                                            <td>{{item.mark}}</td>
                                        </tr>
                                        <tr v-show="SignList.length<=0" style="text-align: center">
                                            <td colspan="4">暂无数据</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div ref="Sign_page" v-show="count.sign_count > limit" style="text-align: right;"></div>
                            </div>
                            <div class="layui-tab-item">
                                <table class="layui-table" v-cloak="">
                                    <thead>
                                    <tr>
                                        <th>优惠券名称</th>
                                        <th>面值</th>
                                        <th>有效期</th>
                                        <th>所需积分</th>
                                        <th>兑换时间</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in CouponsList">
                                            <td>{{item.coupon_title}}
                                                <p>
                                                    <span class="layui-badge" :class="{'layui-bg-green':item._type>=1}" v-text="item._type>=1 ? '可使用': '已过期' ">正在加载</span>
                                                </p>
                                            </td>
                                            <td>{{item.coupon_price}}</td>
                                            <td>{{item._add_time}}-{{item._end_time}}</td>
                                            <td>{{item.integral}}</td>
                                            <td>{{item._add_time}}</td>
                                        </tr>
                                        <tr v-show="CouponsList.length<=0" style="text-align: center">
                                            <td colspan="5">暂无数据</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div ref="copons_page" v-show="count.coupon_count > limit" style="text-align: right;"></div>
                            </div>
                            <div class="layui-tab-item">
                                <table class="layui-table" v-cloak="">
                                    <thead>
                                    <tr>
                                        <th>变动金额</th>
                                        <th>变动后</th>
                                        <th>类型</th>
                                        <th>创建时间</th>
                                        <th>备注</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in balanceChangList">
                                            <td>{{item.number}}
                                                <p v-show="item.pm==1">
                                                    <span class="layui-badge layui-bg-green" v-show="item.status==1">有效</span>
                                                    <span class="layui-badge layui-bg-orange" v-show="item.status==0">带确定</span>
                                                    <span class="layui-badge layui-bg-gray" v-show="item.status==-1">无效</span>
                                                </p>
                                            </td>
                                            <td>{{item.balance}}</td>
                                            <td>{{item._type}}</td>
                                            <td>{{item.add_time}}</td>
                                            <td>{{item.mark}}</td>
                                        </tr>
                                        <tr v-show="balanceChangList.length<=0" style="text-align: center">
                                            <td colspan="5">暂无数据</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div ref="balancechang_page" v-show="count.balanceChang_count > limit" style="text-align: right;"></div>
                            </div>
                            <!--推广人-->
                            <div class="layui-tab-item">
                                <table class="layui-table" v-cloak="">
                                    <thead>
                                    <tr>
                                        <th>昵称</th>
                                        <th>余额</th>
                                        <th>积分</th>
                                        <th>加入时间</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="item in SpreadList">
                                        <td>
                                            {{item.nickname}}
                                            <p v-show="item.is_vip">
                                                <span class="layui-badge layui-bg-orange" v-text="item.vip_name"></span>
                                            </p>
                                        </td>
                                        <td>{{item.now_money}}</td>
                                        <td>{{item.integral}}</td>
                                        <td>{{item.add_time}}</td>
                                    </tr>
                                    <tr v-show="balanceChangList.length<=0" style="text-align: center">
                                        <td colspan="4">暂无数据</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div ref="spread_page" v-show="count.spread_page > limit" style="text-align: right;"></div>
                            </div>
                            <!--end-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    var count=<?=json_encode($count)?>,
        $uid=<?=$uid?>;
    require(['vue'],function(Vue) {
        new Vue({
            el: "#content",
            data: {
                limit:10,
                uid:$uid,
                orderList:[],
                integralList:[],
                SignList:[],
                CouponsList:[],
                balanceChangList:[],
                SpreadList:[],
                count:count,
                page:{
                    order_page:1,
                    integral_page:1,
                    sign_page:1,
                    copons_page:1,
                    balancechang_page:1,
                    spread_page:1,
                },
            },
            watch:{
                'page.order_page':function () {
                    this.getOneorderList();
                },
                'page.integral_page':function () {
                    this.getOneIntegralList();
                },
                'page.sign_page':function () {
                    this.getOneSignList();
                },
                'page.copons_page':function () {
                    this.getOneCouponsList();
                },
                'page.balancechang_page':function () {
                    this.getOneBalanceChangList();
                },
                'page.spread_page':function () {
                    this.getSpreadList();
                }
            },
            methods:{
                getSpreadList:function(){
                    this.request('getSpreadList',this.page.spread_page,'SpreadList');
                },
                getOneorderList:function () {
                    this.request('getOneorderList',this.page.order_page,'orderList');
                },
                getOneIntegralList:function () {
                    this.request('getOneIntegralList',this.page.integral_page,'integralList');
                },
                getOneSignList:function () {
                    this.request('getOneSignList',this.page.sign_page,'SignList');
                },
                getOneCouponsList:function () {
                    this.request('getOneCouponsList',this.page.copons_page,'CouponsList');
                },
                getOneBalanceChangList:function () {
                    this.request('getOneBalanceChangList',this.page.balancechang_page,'balanceChangList');
                },
                request:function (action,page,name) {
                    var that=this;
                    layList.baseGet(layList.U({a:action,p:{page:page,limit:this.limit,uid:this.uid}}),function (res) {
                        that.$set(that,name,res.data)
                    });
                }
            },
            mounted:function () {
                this.getOneorderList();
                this.getOneIntegralList();
                this.getOneSignList();
                this.getOneCouponsList();
                this.getOneBalanceChangList();
                this.getSpreadList();
                var that=this;
                layList.laypage.render({
                    elem: that.$refs.page_order
                    ,count:that.count.order_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.order_page=obj.curr;
                    }
                });
                layList.laypage.render({
                    elem: that.$refs.integral_page
                    ,count:that.count.integral_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.integral_page=obj.curr;
                    }
                });
                layList.laypage.render({
                    elem: that.$refs.Sign_page
                    ,count:that.count.sign_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.sign_page=obj.curr;
                    }
                });
                layList.laypage.render({
                    elem: that.$refs.copons_page
                    ,count:that.count.coupon_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.copons_page=obj.curr;
                    }
                });
                layList.laypage.render({
                    elem: that.$refs.balancechang_page
                    ,count:that.count.balanceChang_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.balancechang_page=obj.curr;
                    }
                });

                layList.laypage.render({
                    elem: that.$refs.spread_page
                    ,count:that.count.spread_count
                    ,limit:that.limit
                    ,theme: '#1E9FFF',
                    jump:function(obj){
                        that.page.spread_page=obj.curr;
                    }
                });
            }
        });
    });
</script>
{/block}