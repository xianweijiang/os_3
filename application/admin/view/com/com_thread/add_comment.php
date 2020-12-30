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
                    <div class="form-group" style="display: flex">
                        <label  style="width: 100px;text-align: right">已选中帖子数:</label>
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-md-6">
                                    {$num}
                                    <input type="hidden" name="ids" id="ids" value="{$ids}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="display: flex">
                        <label  style="width: 111px;text-align: right">评论时间:</label>
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="radio" name="time"  value="24" validate="" checked/>24小时内
                                    <input type="radio" name="time"  value="48" validate="" />48小时内
                                    <input type="radio" name="time"  value="72" validate="" />72小时内
                                </div>
                                <div class="col-md-6">
                                    <span class="help-block m-b-none" style="color: #ccc"> 即设置每条评论的发布时间，建议尽量选择离当前时间点较近的时间段，系统将在此时间段内随时生成评论时间</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row" style="display: flex">
                        <label  style="width: 100px;text-align: right">评论数:</label>
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="num" id="num" value="1" validate="" style="width:50%"/>
                                </div>
                                <div class="col-md-6">
                                    <span class="help-block m-b-none" style="color: #ccc"> 即每条选中的帖子对应注入的评论数，建议1~3条，不宜过多</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row" style="display: flex">
                        <label  style="width: 100px;text-align: right">评论数:</label>
                        <div style="margin-left: 15px">
                            <div style="display: flex">
                                <input type="radio"  name="temp"  value="1"/><span>评论模板随机选择<span>
                            </div>
                            <div  style="display: flex;margin-top: 10px">
                                <input type="radio"  name="temp" value="2" /><span>自定义内容</span>
                            </div>
                            <div  style="display: flex;margin-top: 10px">
                                <textarea rows="3" name="temp_content" style="width: 400px"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="text-align: center;">
                        <div class="col-sm-4 col-sm-offset-2">
                            <div class="btn btn-primary" style="width: 500px" data-role="submit">提交</div>
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
        $(this).unbind('click');
        var data=$('#signupForm').serializeArray();
        $.post("{:Url('add_comment')}",data,function (res) {
            if(res.code==200){
                $eb.message('success',res.msg);
                setTimeout(function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
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
