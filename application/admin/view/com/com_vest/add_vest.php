{extend name="public/container"}
{block name="head_top"}
<script src="{__FRAME_PATH}js/toast-js.js"></script>
<style>
    .content {
        background-color: #fff;
        padding: 20px;
    }
    .platform-content{
        border-bottom: 1px solid #eee;
    }
    .module-content{
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }
    .unit-content{
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }
    .unit-box-content{
        margin-top: 10px;
    }
    .unit-box-content .checkbox-content{
        margin-left: 10px;
        height: 30px;
    }
    .title{
        font-size: 20px;
        font-weight: 600;
        margin: 15px 0;
    }
    .platform-content .checkbox-content{
        margin-top: 10px;
        display: flex;
        border-bottom: 1px solid #ccc;
        font-size: 18px;
    }
    .platform-content .checkbox-content label{
        display: flex;
        align-items: center;
        font-weight: 500;
        min-width: 60px;
        margin-left: 20px;
        height: 30px;
        font-size: 18px;
    }
    .choose-box{
        display: flex;
        align-items: center;
        margin-top: 15px;
    }
    .choose-box .layui-input{
        width: 200px;
        border: none;
        outline: none;
        height: 16px;
    }
    .choose-box label{
        display: flex;
        align-items: center;
        font-weight: 500;
        margin-bottom: 0;
    }
    .time-label{
        margin-left: 20px;
        color: #0ca6f2;
        text-decoration: underline;
    }
    .btn-content{
        margin-top: 20px;
    }
    .submit-btn{
        margin: 0 auto;
        cursor: pointer;
        color: #fff;
        background-color: #169bd5;
        width: 150px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        border-radius: 10px;
    }
</style>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="content">
            <div class="platform-content">
                <form  method="post" class="form-horizontal" id="signupForm" action="{:Url('edit')}">
                    <input type="hidden" class="form-control" name="is_post" value="1" validate="" style="width:100%"/>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">注入马甲数量:</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="number" class="form-control" name="number" value="number" validate="" style="width:100%"/>
                                </div>
                                <div class="col-md-6">
                                    <span class="help-block m-b-none"> 个<span style="color: red;margin-left: 15px">备注:建议一次性注入5-8个为宜</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">马甲号关注数区间:</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6" style="display: flex">
                                    <input type="number" class="form-control" name="min_number" value="number" validate="" style="width:45%"/>
                                    <div style="width: 10%;text-align: center;padding: 5px">—</div>
                                    <input type="number" class="form-control" name="max_number" value="number" validate="" style="width:45%"/>
                                </div>
                                <div class="col-md-6">
                                    <span class="help-block m-b-none"> <i class="fa fa-info-circle"></i>批量导入的马甲号将随机关注（“1~10”自定义数值）个系统用户</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12 col-sm-offset-2">
                            <div class="btn btn-primary" style="width: 500px" data-role="submit">提交</div>
                            <span style="color: red;margin-left: 154px;line-height: 30px">注："添加成功"后再进行其他操作</span>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

{/block}
{block name="script"}
<script>
    $('[data-role="submit"]').click(function () {
        $('[data-role="submit"]').unbind('click');
        var data=$('#signupForm').serializeArray();
        $.post("{:Url('add_vest')}",data,function (res) {
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
