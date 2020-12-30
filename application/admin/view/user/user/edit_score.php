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
    .layui-form-radio{
        margin-top: 0;
    }
    label{
        width: 117px;
        text-align: right;
    }
    .delete-img{
        width: 18px;
        margin-left: 15px;
        cursor: pointer;
    }
</style>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <form class="layui-form" action="" style="padding:20px;">
                    <div style="margin-top: 20px;display: flex;align-items: center">
                        <label>处理方式</label>
                        <div style="margin-left: 20px;">
                            <input type="radio" name="type" value="1" title="加分" checked>
                            <input type="radio" name="type" value="0" title="减分">
                        </div>
                    </div>
                    <div style="margin-top: 20px;display: flex;">
                        <label style="padding-top: 7px;">积分类型及分值</label>
                        <div style="margin-left: 20px;">
                            <div id="reward_list" style="">
                                <div class="reward-box" style="margin-bottom: 20px;display: flex;align-items: center">
                                    <div style="width: 184px;">
                                        <select class="score-name">
                                            <option value="">请选择积分类型</option>
                                            {volist name="score" id="v"}
                                            <option value="{$v.flag}">{$v.name}</option>
                                            {/volist}
                                        </select>
                                    </div>
                                    <div style="width: 184px;margin-left: 10px;">
                                        <input type="number" placeholder="请输入分值" name="title" class="layui-input score-num">
                                    </div>
                                    <img style="display: none;" class="delete-img" src="{__ADMIN_PATH}images/delete.png" alt="">
                                </div>
                            </div>
                            <div style="width: 300px;">
                                <button style="" class="btn btn-primary" id="add_reward" type="button">添加积分</button>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 20px;display: flex;">
                        <label>修改用户积分的理由</label>
                        <div style="width: 450px;margin-left: 20px;">
                            <textarea style="resize: none;" id="content" required lay-verify="required" placeholder="" class="layui-textarea"></textarea>
                        </div>
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
    var flag = false;
    var form = layui.form, layer = layui.layer;
    form.render();

    $("#add_reward").on("click", function () {
        $("#reward_list").append('<div class="reward-box" style="margin-bottom: 20px;display: flex;align-items: center">\n' +
            '                                    <div style="width: 184px;">\n' +
            '                                        <select class="score-name">\n' +
            '                                            <option value="">请选择积分类型</option>\n' +
            '                                            {volist name="score" id="v"}\n' +
            '                                            <option value="{$v.flag}">{$v.name}</option>\n' +
            '                                            {/volist}\n' +
            '                                        </select>\n' +
            '                                    </div>\n' +
            '                                    <div style="width: 184px;margin-left: 10px;">\n' +
            '                                        <input type="number" placeholder="请输入分值" name="title" class="layui-input score-num">\n' +
            '                                    </div>\n' +
            '                                    <img class="delete-img" src="{__ADMIN_PATH}images/delete.png" alt="">\n' +
            '                                </div>');
        form.render();
    });

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
        list.uids = '{$uids}'
        list.score = rewardData;
        list.type = $("[name='type']:checked").val();
        list.content = $("#content").val();
        if(list.content === ''){
            $eb.message('error', '请输入理由');
            return;
        }
        $.ajax({
            url: "{:Url('update_score')}",
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
                    $eb.message('error', re.msg);
                    flag = !flag;
                }
            }
        })
    });

    $("body").on("click",".delete-img",function () {
        var index = $('.delete-img').index(this);
        console.log(index)
        $(this).parent().remove();
    })
</script>
{/block}
