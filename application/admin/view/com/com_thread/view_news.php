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

h1 {
    margin-top: 10px;
    color: black;
}
.remark {
    display: flex;
    padding: 25px 0 15px 0;
}
.remark-font, .body {
    color: black;
}
img {
    max-width: 100%;
    height: auto!important;
    width: auto\9!important;
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
    <h1>{$info.title}</h1>
    <div class="remark"><div class="remark-font">摘要：</div><div class="remark-content"></div></div>
    <div class="body">
    </div>
</div>
{/block}
{block name="script"}
<script>
// unicode的转码
var remarkHtml = $('.remark-content').html({$info.content}).html();
var oldRemarkHtml = unescape(remarkHtml.replace(/\\u/g, '%u'));
var newRemarkHtml = oldRemarkHtml.slice(0,20);
var contentHtml = $('.body').html({$info.content}).html();
var oldContentHtml = unescape(contentHtml.replace(/\\u/g, '%u'));
$('.remark-content').html(newRemarkHtml);
$('.body').html(oldContentHtml);
</script>
{/block}