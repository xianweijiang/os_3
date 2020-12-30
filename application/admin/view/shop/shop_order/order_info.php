{extend name="public/container"}
{block name="content"}
<div class="ibox-content order-info">

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    收货信息
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-12" >用户昵称: {$userInfo.nickname}</div>
                        <div class="col-xs-12">收货人: {$orderInfo.real_name}</div>
                        <div class="col-xs-12">联系电话: {$orderInfo.user_phone}</div>
                        <div class="col-xs-12">收货地址: {$orderInfo.user_address}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    订单信息
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-6" >订单编号: {$orderInfo.order_id}</div>
                        <div class="col-xs-6" style="color: #8BC34A;">订单状态:
                            {if condition="$orderInfo['paid'] eq 1 && $orderInfo['status'] eq 1"}
                            未发货
                            {elseif condition="$orderInfo['paid'] eq 1 && $orderInfo['status'] eq 2"/}
                            待收货
                            {elseif condition="$orderInfo['paid'] eq 1 && $orderInfo['status'] eq 3"/}
                            交易完成
                            {elseif condition="$orderInfo['paid'] eq 1 && $orderInfo['status'] eq -2"/}
                            已退款
                            {/if}
                        </div>
                        <div class="col-xs-6">商品总数: {$orderInfo.total_num}</div>
                        <div class="col-xs-6">支付总数: {$orderInfo.pay_price}</div>
                        <div class="col-xs-6">支付积分: {$orderInfo.pay_score}</div>
                        <div class="col-xs-6">支付金额: ￥{$orderInfo.pay_cash}</div>
                        <div class="col-xs-6">支付邮费: ￥{$orderInfo.pay_postage}</div>
                        <div class="col-xs-6">创建时间: {$orderInfo.add_time|date="Y/m/d H:i",###}</div>
                        <div class="col-xs-6">支付方式:
                            {if condition="$orderInfo['paid'] eq 1"}
                            {if condition="$orderInfo['pay_type'] eq 'weixin'"}
                            微信支付
                            {elseif condition="$orderInfo['pay_type'] eq 'yue'"}
                            余额支付
                            {else/}
                            其他支付
                            {/if}
                            {else/}
                            未支付
                            {/if}
                        </div>
                        {notempty name="orderInfo.pay_time"}
                        <div class="col-xs-6">支付时间: {$orderInfo.pay_time|date="Y/m/d H:i",###}</div>
                        {/notempty}
                        <div class="col-xs-6" style="color: #733b5c">备注: {$orderInfo.remark?:'无'}</div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    物流信息
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-6" >快递公司: {$orderInfo.delivery_name}</div>
                        <div class="col-xs-6">快递单号: {$orderInfo.delivery_id} </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
{/block}
{block name="script"}

{/block}
