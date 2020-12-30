{extend name="public/container"}
{block name="content"}
<style>
    .text-line{
        display: flex;
        margin-left: 10px;
        margin-top: 10px;
    }
    .title{
        width: 60px;
        margin-right: 20px;
        text-align: right;
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="text-line">
                        <div class="title">话题标题:</div>
                        <div class="text">#{$topic.title}#</div>
                    </div>
                    <div class="text-line">
                        <div class="title">简介:</div>
                        <div class="text">{$topic.summary}</div>
                    </div>
                    <div class="text-line">
                        <div class="title">分类:</div>
                        <div class="text">{$topic.class_name}</div>
                    </div>
                    <div class="text-line">
                        <div class="title">发起人:</div>
                        <div class="text">{$topic.nickname}</div>
                    </div>
                    <div class="text-line">
                        <div class="title">创建时间:</div>
                        <div class="text">{$topic.create_time}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}