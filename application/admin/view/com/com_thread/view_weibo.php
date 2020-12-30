<!-- 查看资讯详情 -->
{extend name="public/modal-frame"}
{block name="head_top"}
<script src="{__FRAME_PATH}js/plugins/chosen/chosen.jquery.js"></script>
<style>
.header {
    display: flex;
    width: 100%;
    height: 60px;
}
.detail {
    flex: 1;
    padding-left: 8px;
    padding-top: 3px;
}

.user-head-img {
    height: 50px;
    width: 50px;
    border-radius: 50%; 
    border: solid 1px #fafafa;
    overflow: hidden;
}
.user-head-img img {
    width: 100%;
    height: 100%;
}
.detail p:first-of-type {
    font-size: 18px;
    margin-bottom: 3px;
    font-weight: 600;
}

.detail p:last-of-type {
    font-size: 14px;
}

.body {
    color: black;
}
.img-box {
    margin-top: 10px;
}
.img-box img {
    width: 30%;
}

</style>
{/block}
{block name="content"}
<div class="container">
    <div class="header">
        <div class="user-head-img"><img src="{$info.user.avatar}" alt=""></div>
        <div class="detail">
            <p class="username">{$info.user.nickname}</p>
            <p class="time">{$info.create_time}</p>
        </div>
    </div>
    <div class="body">
     {$info.content}
    </div>
    <div class="img-box">
     {volist name="info.image" id="vo"}
        <img src="{$vo}" alt="">
     {/volist}
    </div>
</div>
{/block}

{block name="script"}
<script>

</script>
{/block}