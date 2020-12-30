<!DOCTYPE html>
<html lang="zh-CN">
<head>
    {include file="public/frame_head" /}
    <title>{block name="title"}{/block}</title>
    {block name="head_top"}{/block}
    {include file="public/style" /}
    {block name="head"}{/block}
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    {if condition="!$is_free_ban"}
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
    {/if}
    {if condition="!$is_end_ban"}
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
    {/if}
{block name="content"}{/block}
{block name="foot"}{/block}
{block name="script"}{/block}
{include file="public/frame_footer" /}
</div>
</body>
</html>
