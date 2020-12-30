{extend name="public/container"}

{block name="content"}
<style>
    li{
        margin-top: 10px;
        display: flex;
    }
    .title{
        width: 100px;display: block;text-align: center;
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="token">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">token值</div>
                <div class="layui-card-body">
                    <ul style="font-size: 16px">
                        <li>
                            <span class="title">平台</span>
                            <span>token值</span>
                        </li>
                        {volist name="data" id="vo"}
                            <li>
                                <span  class="title">{$key}:</span>
                                <span>{$vo}</span>
                            </li>
                        {/volist}
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
</script>
{/block}
