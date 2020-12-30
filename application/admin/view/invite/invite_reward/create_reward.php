{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}formselects/formSelects-v4.css">
<script src="{__PLUG_PATH}formselects/formSelects-v4.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }
    .layui-form-selected .layui-anim-upbit{
        height: 288px;
    }
</style>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <form class="layui-form" action="" style="padding:20px;">
                    <div style="display: flex;align-items: center">
                        <label>等级名称</label>
                        <div style="width: 450px;margin-left: 20px;">
                            {if condition="$style eq 'create'"}
                            <input type="text" name="title" readonly placeholder="等级{$level}" class="layui-input">
                            {elseif condition="$style eq 'edit'"/}
                            <input type="text" name="title" readonly placeholder="等级{$reward.level}" class="layui-input">
                            {/if}
                        </div>
                    </div>
                    <div style="margin-top: 20px;display: flex;align-items: center">
                        <label for="condition" style="width: 52px;">条件</label>
                        <div style="width: 450px;margin-left: 20px;">
                            <select id="condition">
                                <option value="">注册成功</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 20px;display: flex;align-items: center">
                        <label>推荐人数</label>
                        <div style="width: 450px;margin-left: 20px;display: flex;align-items: center">
                            {if condition="$style eq 'create'"}
                            <input type="number" id="reward_num" name="title" class="layui-input"
                                   style="margin-right: 10px;"><span>名</span>
                            {elseif condition="$style eq 'edit'"/}
                            <input type="number" id="reward_num" value="{$reward.num}" name="title" class="layui-input"
                                   style="margin-right: 10px;"><span>名</span>
                            {/if}
                        </div>
                    </div>
                    <div style="margin-top: 20px;display: flex;align-items: center">
                        <label>奖励内容</label>
                        <div style="width: 300px;margin-left: 20px;">
                            <button style="" class="btn btn-primary" id="add_reward" type="button">+添加积分奖励</button>
                        </div>
                    </div>
                    <div id="reward_list" style="margin-left: 72px;">
                        {if condition="$style eq 'edit'"}
                        {volist name="reward.reward" key="k" id="data"}
                        <div class="reward-box" style="margin-top: 20px;display: flex;align-items: center">
                            <label style="width: 70px">积分奖励{$k}</label>
                            <div style="width: 184px;">
                                <select class="score-name">
                                    <option value="">请选择积分类型</option>
                                    {volist name="score" id="v"}
                                    {if condition="$v.flag eq $data.flag"}
                                    <option value="{$v.flag}" selected>{$v.name}</option>
                                    {else/}
                                    <option value="{$v.flag}">{$v.name}</option>
                                    {/if}
                                    {/volist}
                                </select>
                            </div>
                            <div style="width: 184px;margin-left: 10px;">
                                <input type="number" value="{$data.value}" placeholder="请输入分值" name="title" class="layui-input score-num">
                            </div>
                            <div class="delete-btn" style="color: #0ca6f2;font-size: 14px;text-decoration: underline;margin-left: 5px;cursor: pointer">删除</div>
                        </div>
                        {/volist}
                        {/if}
                    </div>
                    <button style="margin-top: 20px;" class="btn btn-primary" id="save" type="button">提交</button>
                </form>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    var rewardNum = 1;
    $("body").on("click",".delete-btn",function () {
        $(this).parent().remove()
    })
</script>
{if condition="$style eq 'create'"}
<script>
    var flag = false;
    $("#save").on("click", function () {
        if (flag) {
            return;
        }
        flag = !flag;
        var list = {};
        var rewardData = [];
        $(".reward-box").each(function () {
            var scoreName = $(this).find(".score-name").val();
            var scoreNum = $(this).find(".score-num").val();
            rewardData.push({flag: scoreName, value: scoreNum});
        });
        list.type = "注册成功";
        list.reward_type = "积分奖励";
        list.num = $("#reward_num").val();
        list.reward = rewardData;
        list.level = '{$level}';
        $.ajax({
            url: "{:Url('add_reward')}",
            data: list,
            type: 'post',
            dataType: 'json',
            success: function (re) {
                if (re.code === 200) {
                    $eb.message('success', re.msg);
                    setTimeout(function () {
                        parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    }, 1500)
                } else {
                    $eb.message('success', re.msg);
                    flag = !flag;
                }
            }
        })
    })
</script>
{elseif condition="$style eq 'edit'"/}
<script>
    var flag = false;
    $("#save").on("click", function () {
        if (flag) {
            return;
        }
        flag = !flag;
        var list = {};
        var rewardData = [];
        $(".reward-box").each(function () {
            var scoreName = $(this).find(".score-name").val();
            var scoreNum = $(this).find(".score-num").val();
            rewardData.push({flag: scoreName, value: scoreNum});
        });
        list.type = "注册成功";
        list.reward_type = "积分奖励";
        list.num = $("#reward_num").val();
        list.reward = rewardData;
        list.id = '{$reward.id}';
        $.ajax({
            url: "{:Url('edit')}",
            data: list,
            type: 'post',
            dataType: 'json',
            success: function (re) {
                if (re.code === 200) {
                    $eb.message('success', re.msg);
                    setTimeout(function () {
                        parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    }, 1500)
                } else {
                    $eb.message('success', re.msg);
                    flag = !flag;
                }
            }
        })
    })
    rewardNum = {:count($reward.reward)} + 1;
</script>
{/if}
<script>
    var form = layui.form, layer = layui.layer;
    form.render();
    $("#add_reward").on("click", function () {
        $("#reward_list").append('<div class="reward-box" style="margin-top: 20px;display: flex;align-items: center">\n' +
            '                            <label style="width: 70px">积分奖励' + rewardNum + '</label>\n' +
            '                            <div style="width: 184px;">\n' +
            '                                <select class="score-name">\n' +
            '                                    <option value="">请选择积分类型</option>\n' +
            '                                    {volist name="score" id="v"}\n' +
            '                                    <option value="{$v.flag}">{$v.name}</option>\n' +
            '                                    {/volist}\n' +
            '                                </select>\n' +
            '                            </div>\n' +
            '                            <div style="width: 184px;margin-left: 10px;">\n' +
            '                                <input type="number" placeholder="请输入分值" name="title" class="layui-input score-num">\n' +
            '                            </div>\n' +
            '                            <div class="delete-btn" style="color: #0ca6f2;font-size: 14px;text-decoration: underline;margin-left: 5px;cursor: pointer">删除</div>\n' +
            '                        </div>')
        form.render();
        rewardNum++
    });
</script>
{/block}
