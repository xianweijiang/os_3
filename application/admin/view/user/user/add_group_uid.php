{extend name="public/container"}
{block name="head_top"}
<script src="{__FRAME_PATH}js/toast-js.js"></script>
<style>
    .content {
        background-color: #fff;
        padding: 20px;
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
    }
    .title{
        font-size: 16px;
        font-weight: 600;
    }
    .platform-content .checkbox-content{
        margin-top: 10px;
        display: flex;
    }
    .platform-content .checkbox-content label{
        display: flex;
        align-items: center;
        font-weight: 500;
        margin-right: 50px;
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
    .unable label{
        color: #d7d7d7;
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
    .label_show{
        line-height: 29px;
        width: 150px;
        text-align: right;
        margin: 15px 0;
        display: block;
    }
    .label_value{
        display: flex;
    }
    .gray-bg{
        background-color: #fff;
    }
</style>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="content">
            <input type="hidden" name="ids" id="ids" value="{$ids}">
<!--            <div class="platform-content">-->
<!--                <div class="title">-->
<!--                    默认所属用户组-->
<!--                </div>-->
<!--                <div class="checkbox-content" style="border-bottom: 0">-->
<!--                    <div>-->
<!--                        <div class="label_value"><span class="label_show">所属系统用户组</span></div>-->
<!--                        <div class="label_value"><span class="label_show">所属等级用户组</span></div>-->
<!--                        <div class="label_value"><span class="label_show">所属会员用户组</span></div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
            <div class="module-content">
                <form name="signForm" id="signForm">
                    <div class="title" style="margin: 10px 0;border-top: 1px solid  #CCCCCC;color: #ccc">
                        扩展所属用户组
                    </div>
                    <div style="display: flex">
                        <div class="add_user_show" data-count="0">
                            <div class="checkbox-content row" style="display: flex">
                                <label class="col-md-6" style="line-height: 29px;width: 150px;text-align: right">用户组1:</label>
                                <div class="col-md-6">
                                    <select  id="user_1" name="user_1" class="layui-input module-input" style="width:241px;">
                                        {volist name='group' id='v'}
                                            <option value="{$v.id}">{$v.name}</option>
                                        {/volist}
                                    </select>
                                </div>
                            </div>
                            <div class="checkbox-content row" style="display: flex">
                                <label class="col-md-6" style="line-height: 29px;width: 150px;text-align: right">有效期:</label>
                                <div class="col-md-6">
                                    <input type="text" id="time_1" name="time_1" class="layui-input module-input"   style="width:241px;" placeholder="">
                                </div>
                            </div>
                            <script>
                                layui.use('laydate', function(){
                                    var laydate = layui.laydate;
                                    //执行一个laydate实例
                                    laydate.render({
                                        elem: "#time_1", //指定元素
                                        type:'date',
                                        value:'',//初始时间
                                        range: ''
                                    });
                                });
                            </script>
                        </div>
                        <div style="margin-left: 20px;color: #ccc">
                            注：如需设定当前用户组的有效期，请输入用户组截止日期，留空则默认为按系统规则判断或不自动过期
                        </div>
                    </div>
                </form>
                <div class="add_user" onclick="add_user_group()" style="width: 120px;height: 30px;line-height: 30px;text-align: center;border: 1px dotted #ccc;margin: 20px auto;cursor: pointer">
                    +添加扩展用户组
                </div>

            </div>
            </div>
        </div>
    </div>
    <div class="btn-content">
        <div class="submit-btn" data-role="submit" id="submit_btn">提交</div>
    </div>
</div>
</div>
{/block}
{block name="script"}
<script>
    function add_user_group() {
        var count=$('.add_user_show').attr('data-id');
        $.post("{:Url('add_user_group')}",{count:count},function (res) {
            $('.add_user_show').append(res.data.html).attr('data-id',res.data.count);
        })
    }
    $('[data-role="submit"]').click(function () {
        $('[data-role="submit"]').unbind('click')
        var data=$('#signForm').serializeArray();
        var count=data.length;
        var user=new Array();
        var time=new Array();
        var j=0;
        for(var i=0;i<count;i+=2){
            user[j]=data[i]['value'];
            time[j]=data[i+1]['value'];
            j++;
        }
        var ids=$('#ids').val();
        $.post("{:Url('add_group_uid')}",{user:user,time:time,is_post:1,ids:ids},function (res) {
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
