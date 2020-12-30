{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}formselects/formSelects-v4.css">
<script src="{__PLUG_PATH}formselects/formSelects-v4.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
<style>
    .layui-form-radio{
        margin-top: 0;
    }
</style>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content" style="border-radius: 0">
                <form class="layui-form" action="" id="signupForm">
                    <input type="hidden" name="id" id="id" value="{$id}">
                    <div style="margin-top: 20px;display: flex;align-items: center">
                        <label for="level_select" style="width: 78px;text-align: right">分区名称</label>
                        <div style="width: 500px;margin-left: 20px;">
                            <input type="text" name="name" id="name" class="layui-input" value="{$forum['name']}">
                        </div>
                    </div>
                    <div style="margin-top: 20px;display: flex;align-items: center">
                        <label for="level_select" style="width: 78px;text-align: right">分区描述</label>
                        <div style="width: 500px;margin-left: 20px;">
                            <textarea name="summary" id="summary" class="layui-textarea" style="resize: none;" value="{$forum['summary']}">{$forum['summary']}</textarea>
                        </div>
                    </div>
                    <div style="margin-top: 20px;display: flex">
                        <label for="level_select">分区访问权限</label>
                        <div style="width: 500px;margin-left: 20px;display: flex;flex-direction: column">
                            <input type="radio" name="jurisdiction" lay-filter="test" value="hide" title="公开，允许所有用户访问" {if condition="!$forum['group']"}checked{/if}>
                            {if condition="in_array('osapi_forum_power',$open_list)"}
                            <input type="radio" name="jurisdiction" lay-filter="test" value="show" title="私密，仅限指定用户组访问" {if condition="$forum['group']"}checked{/if}>
                            {/if}
                            <div style="margin-top: 10px;{if condition="!$forum['group']"}display:none{else/}display:flex{/if} " id="choose_group_box">
                                <input type="text" id="group_input" class="layui-input" value="{$forum['g_name']}">
                                <input type="hidden" id="group" name="group" class="layui-input" value="{$forum['group']}">
                                <button onclick="$eb.createModalFrame(this.innerText,'{:Url('user_select',['name'=>'group','id'=>$id,'type'=>1])}',{h:500,w:650})" type="button" class="layui-btn" style="height: 32px;line-height: 32px;">选择用户组</button>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 20px;display: flex;align-items: center">
                        <label for="level_select" style="width: 78px;text-align: right">排序</label>
                        <div style="width: 500px;margin-left: 20px;">
                            <input type="number" name="sort" id="sort" class="layui-input" value="{$forum['sort']}">
                        </div>
                    </div>
                    <button style="margin-top: 20px;" class="btn btn-primary"  id="save" type="button">提交</button>
                </form>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    var form = layui.form, layer = layui.layer;
    form.render();
    var idData = [];
    window.addEventListener("storage", function (e) {
        console.log(e);
        if (e.key === "group") {
            var reg = new RegExp(",","g");//g,表示全部替换。
            var text = e.newValue.replace(reg,"、");
            $("#group_input").val(text);
            window.localStorage.removeItem("group")
        }else if(e.key === "group_id"){
            idData = e.newValue;
            $("#group").val(idData);
        }
    });
    form.on('radio(test)', function(data){
        var val = data.value;
        if(val === "show"){
            $("#choose_group_box").css("display","flex");
        }else if(val === "hide"){
            $("#choose_group_box").css("display","none");
        }
    });
    $('#save').click(function () {
        var url="<?php echo $url ?>";
        var data=$('#signupForm').serializeArray();
        $.post(url,data,function (res) {
            if(res.code==200){
                $eb.message('success',res.msg);
                setTimeout(function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                    parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                    console.log($(".page-tabs-content .active").index());
                    window.frames[$(".page-tabs-content .active").index()].location.reload();
                },1500)
            }else{
                $eb.message('error',res.msg);
            }
        })

    });
</script>
{/block}
