{extend name="public/container"}
{block name="content"}
<style>
    .gray-bg{
        background-color: #fff;
    }
</style>
    <div style="background-color: #fff">
        <h2 style="text-align: center">{$forum['title']}</h2>
        <div style="margin-top: 20px">{$forum['content']}</div>
    </div>
{/block}
{block name="script"}
{/block}
