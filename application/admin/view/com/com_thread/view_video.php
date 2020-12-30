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
    margin-top: 5px;
    color: black;
}
a {
    color: #02A7F0;
    display: inline-block;
    margin-top: 10px;
    text-decoration: underline;
}
.body {
    margin-top: 20px;
}
.play-video {
    display: none;
}
</style>
{/block}
{block name="content"}
{if condition="$type eq 1"}
                <div class="container">
                    <video src="{$info.video_url}" autoplay controls loop></video>
                </div>
            {else}
            <div class="container">
                <div class="header">
                    <div class="user-head-img"><img src="{$info.user.avatar}" alt=""></div>
                    <div class="detail">
                        <p class="username">{$info.user.nickname}</p>
                        <p class="time">{$info.create_time}</p>
                    </div>
                </div>
                <h1>{$info.title}</h1>
                <div class="body">
                    <p class="content"></p>
                    <a href="javascript:;" class="a-btn">点击播放视频→</a>
                    <div class="play-video">
                        <video src="{$info.video_url}" controls autoplay></video>
                    </div>
                </div>
            </div>
{/if}
{/block}
{block name="script"}
<script>
var oldContentHtml = $('.content').html({$info.content}).html();
var newContentHtml = unescape(oldContentHtml.replace(/\\u/g, '%u'));
$('.content').html(newContentHtml);
$('.body').on('click', '.a-btn', function() {
    $('.play-video').css('display', 'block');
})
</script>
{/block}