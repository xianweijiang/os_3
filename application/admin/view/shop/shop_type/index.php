{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">

        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">积分类型列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container">
<!--                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加积分类型</button>-->
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame('积分商城兑换类型','{:Url('edit_score_type')}')" style="margin-top: 10px">积分商城兑换类型</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
{/block}
