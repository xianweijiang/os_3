{extend name="public/container"}
{block name="head_top"}
<link href="{__ADMIN_PATH}module/wechat/news/css/style.css" type="text/css" rel="stylesheet">
<link href="{__FRAME_PATH}css/plugins/chosen/chosen.css" rel="stylesheet">
<script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/jquery.min.js"></script>
<script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/template.min.js"></script>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.all.js"></script>

<script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>
<script src="{__ADMIN_PATH}plug/validate/jquery.validate.js"></script>
<script src="{__FRAME_PATH}js/plugins/chosen/chosen.jquery.js"></script>
<style>
    .common-input{
        margin-left: 10px;
        width: 300px;
        margin-right: 5px;
    }
    .layui-form-label{
        margin-right: 10px;
        font-weight: 600;
        color: #333;
        width: 116px;
        text-align: left;
    }

    .radio {
        margin: 0!important;
        padding-top: 8px;
    }
    .radio input[type="radio"] {
        position: absolute;
        opacity: 0;
    }
    .radio input[type="radio"] + .radio-label:before {
        content: '';
        background: #f4f4f4;
        border-radius: 100%;
        border: 1px solid #b4b4b4;
        display: inline-block;
        width: 1.4em;
        height: 1.4em;
        position: relative;
        top: -0.2em;
        margin-right: 1em;
        vertical-align: top;
        cursor: pointer;
        text-align: center;
        -webkit-transition: all 250ms ease;
        transition: all 250ms ease;
    }
    .radio input[type="radio"]:checked + .radio-label:before {
        background-color: #3197EE;
        box-shadow: inset 0 0 0 4px #f4f4f4;
    }
    .radio input[type="radio"]:focus + .radio-label:before {
        outline: none;
        border-color: #3197EE;
    }
    .radio input[type="radio"]:disabled + .radio-label:before {
        box-shadow: inset 0 0 0 4px #f4f4f4;
        border-color: #b4b4b4;
        background: #b4b4b4;
    }
    .radio input[type="radio"] + .radio-label:empty:before {
        margin-right: 0;
    }

</style>
{/block}
{block name="content"}
<div class="row" style="width: 100%;margin-left: 0;">
    <div class="col-sm-12" style="background-color: #fff">
        <div class="layui-card-header">版块管理</div>
        <div class="form" style="margin: 20px">
            <div class="input-box" style="display: flex;align-items: center;color: #333">
                <label class="layui-form-label" for="" style="margin-bottom: 0;width: 116px;">社区版块前端命名</label>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="forum1" type="radio" name="forum_name" value="版块" style="margin-top: 0;" checked>
                    <label for="forum1" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">版块</label>
                </div>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="forum2" type="radio" name="forum_name" value="圈子" style="margin-top: 0;">
                    <label for="forum2" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">圈子</label>
                </div>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="forum3" type="radio" name="forum_name" value="群组" style="margin-top: 0;">
                    <label for="forum3" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">群组</label>
                </div>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="forum4" type="radio" name="forum_name" value="部落" style="margin-top: 0;">
                    <label for="forum4" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">部落</label>
                </div>
            </div>
            <div style="display: flex;align-items: center;margin-left: 126px;margin-top: 20px">
                <div class="radio">
                    <input id="forum5" type="radio" name="forum_name" value="自定义" style="margin-top: 0;">
                    <label for="forum5" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 10px;font-weight: 500;line-height: 1;">自定义</label>
                </div>
                <input id="forum_name_input" class="common-input form-control valid" value="" type="text">
            </div>
            <div class="input-box" style="display: flex;align-items: center;margin-top: 20px;color: #333">
                <label class="layui-form-label" for="" style="margin-bottom: 0;width: 116px;">版主前端命名</label>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="admin1" type="radio" name="admin_name" value="版主" style="margin-top: 0;" checked>
                    <label for="admin1" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">版主</label>
                </div>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="admin2" type="radio" name="admin_name" value="圈主" style="margin-top: 0;">
                    <label for="admin2" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">圈主</label>
                </div>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="admin3" type="radio" name="admin_name" value="组长" style="margin-top: 0;">
                    <label for="admin3" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">组长</label>
                </div>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="admin4" type="radio" name="admin_name" value="酋长" style="margin-top: 0;">
                    <label for="admin4" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">酋长</label>
                </div>
            </div>
            <div style="display: flex;align-items: center;margin-left: 126px;margin-top: 20px">
                <div class="radio">
                    <input id="admin5" type="radio" name="admin_name" value="自定义" style="margin-top: 0;">
                    <label for="admin5" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 10px;font-weight: 500;line-height: 1;">自定义</label>
                </div>
                <input id="admin_name_input" class="common-input form-control valid" value="" type="text">
            </div>

            <div class="input-box" style="display: flex;align-items: center;margin-top: 20px;color: #333">
                <label class="layui-form-label" for="" style="margin-bottom: 0;width: 116px;">新帖图标提示</label>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="new1" type="radio" name="new_icon" value="1" style="margin-top: 0;" checked>
                    <label for="new1" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">开启</label>
                </div>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="new2" type="radio" name="new_icon" value="0" style="margin-top: 0;">
                    <label for="new2" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">关闭</label>
                </div>
            </div>
            <div class="input-box" style="display: flex;align-items: center;margin-top: 20px;color: #333">
                <label class="layui-form-label" for="" style="margin-bottom: 0;width: 116px;">热帖图标提示</label>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="hot1" type="radio" name="hot_icon" value="1" style="margin-top: 0;" checked>
                    <label for="hot1" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">开启</label>
                </div>
                <div class="radio" style="display: flex;align-items: center">
                    <input id="hot2" type="radio" name="hot_icon" value="0" style="margin-top: 0;">
                    <label for="hot2" class="radio-label" style="margin-bottom: 0;margin-left: 5px;margin-right: 50px;font-weight: 500;line-height: 1;">关闭</label>
                </div>
            </div>
            <div class="input-box" style="display: flex;align-items: center;margin-top: 20px;color: #333">
                <label class="layui-form-label" for="" style="margin-bottom: 0;width: 116px;">热帖评论数阈值</label>
                <input id="num_input" class="common-input form-control valid" value="{$data.threshold}" type="text" style="margin-left: 24px;">
            </div>
            <div class="btn" id="save_btn" style="background-color: #0092DC;color: #fff;margin: 40px 0 20px 116px;">
                保存
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $(function () {
        var forumName = "{$data.forum_name}";
        if(forumName === "版块"){
            $("#forum1").prop("checked",true);
        }else if(forumName === "圈子"){
            $("#forum2").prop("checked",true);
        }else if(forumName === "群组"){
            $("#forum3").prop("checked",true);
        }else if(forumName === "部落"){
            $("#forum4").prop("checked",true);
        }else {
            $("#forum5").prop("checked",true);
            $("#forum_name_input").val(forumName);
        }
        var adminName = "{$data.user_name}";
        if(adminName === "版主"){
            $("#admin1").prop("checked",true);
        }else if(adminName === "圈主"){
            $("#admin2").prop("checked",true);
        }else if(adminName === "组长"){
            $("#admin3").prop("checked",true);
        }else if(adminName === "酋长"){
            $("#admin4").prop("checked",true);
        }else {
            $("#admin5").prop("checked",true);
            $("#admin_name_input").val(adminName);
        }
        var newIcon = "{$data.new_on}";
        var hotIcon = "{$data.hot_on}";
        if(newIcon === "1"){
            $("#new1").prop("checked",true);
        }else {
            $("#new2").prop("checked",true);
        }
        if(hotIcon === "1"){
            $("#hot1").prop("checked",true);
        }else {
            $("#hot2").prop("checked",true);
        }
    });
    $("#save_btn").on("click",function () {
        var list = {};
        list.forum_name = checkForumName();
        list.user_name = checkAdminName();
        list.new_on = $("input[name='new_icon']:checked").val();
        list.hot_on = $("input[name='hot_icon']:checked").val();
        list.threshold = $("#num_input").val();
        if(list.forum_name === ""){
            $eb.message('error',"请输入社区版块前端命名");
            return;
        }
        if(list.user_name === ""){
            $eb.message('error',"请输入版主前端命名");
            return;
        }
        if(list.threshold === ""){
            $eb.message('error',"请输入热帖评论数阈值");
            return;
        }
        $.ajax({
            url:"{:Url('update')}",
            data:list,
            type:'post',
            dataType:'json',
            success:function(re){
                if(re.code == 200){
                    $eb.message('success',re.msg);
                }else{
                    $eb.message('error',re.msg);
                }
            }
        })
    });
    function checkForumName() {
        var name = $("input[name='forum_name']:checked").val();
        if(name === "自定义"){
            return $("#forum_name_input").val();
        }else {
            return name;
        }
    }
    function checkAdminName() {
        var name = $("input[name='admin_name']:checked").val();
        if(name === "自定义"){
            return $("#admin_name_input").val();
        }else {
            return name;
        }
    }
</script>
{/block}
