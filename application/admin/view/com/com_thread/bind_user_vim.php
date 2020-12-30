{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}formselects/formSelects-v4.css">
<script src="{__PLUG_PATH}formselects/formSelects-v4.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
<style>
    .content_all{
        display: flex;
    }
    .gray-bg{
        background: #fff;
    }
    .wrapper{
        margin-top: 0;
        padding-top: 0;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .ibox-content{
        margin-top: 0;
        padding-top: 0;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .content_all .left{
        /* width: 80px; */
        border-right: 4px solid #ccc;
    }
    .content_all .right{
        flex:1;
    }
    .bind-tips{
        font-size:16px;
        border:none;
        color:#333;
        text-decoration: none;
        cursor:normal;
    }
    .bind-tips:hover{
        color:#333;
    }
    .user-lists{
        display:flex;
        margin-top:20px;
    }
    .tab_show{
        padding:0 10px;
        line-height: 40px;
        text-align: center;
        margin-top: 10px;
        font-size: 14px;
        cursor: pointer;
    }
    .active{
        background-color: #00CCFF;
        color: #fff;
    }
    .often-user{
        margin-top:20px;
    }
    .often-user-lists{
        display:flex;
        flex-wrap:wrap;
        flex-direction:row;
    }
    .often-user-lists .user-name{
        margin:10px;
        padding:10px;
        border:none;
        background-color:#fff;
        outline:none;
    }
    .often-user-lists .user-name:hover{
        color:#FFF;
        background-color:#1E9FFF
    }
</style>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12" style="margin: 0;padding: 0;">
        <div class="ibox">
            <div class="ibox-content">
                <div class="content_all" style="height: 440px">
                    <div class="left" style="padding-right:20px">
                        <div data-role="user" class="tab_show active" data-value="0">普通用户</div>
                        <div data-role="user" class="tab_show" data-value="1">马甲用户</div>
                    </div>
                    <div class="right">
                        <div>
                            <form class="layui-form" action="" style="padding:20px;">
                                <a class="bind-tips" name="input">通过搜索昵称、UID、手机号快速选择用户</a>
                                <div class="user-lists">
                                    <div style="flex:1">
                                        <select name="uids" id="bind_select" xm-select-skin="normal" xm-select-height="38px" xm-select="user_select" xm-select-search="{:Url('find_users')}" xm-select-radio>
                                            <option value="">请选择绑定用户</option>
                                        </select>
                                    </div>
                                    <!-- <br/> -->
                                    <button class="btn btn-primary layui-btn" id="save" style="margin-left:20px" type="button">
                                        <i class="fa  fa-arrow-circle-o-right"></i>
                                        绑定用户
                                    </button>
                                </div>
                                <div class="often-user">
                                    <p style="font-size:16px">常用用户<span style="font-size:14px;color:#999">（近30天绑定过的用户）</span></p>
                                    <hr class="layui-bg-gray">
                                    <div class="often-user-lists" id="bind_log">
<!--                                        <button type="button" class="user-name">小番茄001</button>-->
<!--                                        <button type="button" class="user-name">小番茄001</button>-->
<!--                                        <button type="button" class="user-name">小番茄001</button>-->
<!--                                        <button type="button" class="user-name">小番茄001</button>-->
<!--                                        <button type="button" class="user-name">小番茄001</button>-->
<!--                                        <button type="button" class="user-name">小番茄001</button>-->
<!--                                        <button type="button" class="user-name">小番茄001</button>-->
                                    </div>
                                </div>
                            </form>
                        </div>
                    <div>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script src="{__FRAME_PATH}js/toast-js.js"></script>
<script>
    var formSelects = layui.formSelects;
    $.ajax({
        url:"{:Url('get_user')}",
        data:{},
        type:'get',
        dataType:'json',
        success:function(res){
            if(res.code == 200){
                var selectHtml = '<option value='+res.data.uid+' selected>'+res.data.nickname+'</option>';
                if(res.data){
                    $("#bind_select").append(selectHtml);
                    var form = layui.form;
                    form.render();
                    formSelects.config('user_select', {
                        type: 'get',                //请求方式: post, get, put, delete...
                        searchName: 'nickname',      //自定义搜索内容的key值
                        clearInput: true,          //当有搜索内容时, 点击选项是否清空搜索内容, 默认不清空
                    }, false);
                }
            }else{
                Toast.error(res.msg);
            }
        }
    });
    $('#save').on('click',function(){
        var selectVal = formSelects.value('user_select', 'val')[0];
        var selectName = formSelects.value('user_select', 'name')[0];
        $.ajax({
            url:"{:Url('bind_user')}",
            data:{
                uid:selectVal
            },
            type:'post',
            dataType:'json',
            success:function(res){
                if(res.code == 200){
                    Toast.success(res.msg);
                    window.localStorage.setItem("bind_username",selectName);
                    window.localStorage.setItem("bind_userId",selectVal);
                    setTimeout(function (e) {
                        parent.layer.close(parent.layer.getFrameIndex(window.name));
                    },600)
                }else{
                    Toast.error(res.msg);
                }
            }
        });
    });

    $('[data-role="user"]').click(function () {
        var is_vest=$(this).attr('data-value');
        $(this).addClass('active').siblings().removeClass('active');
        add_bind_log(is_vest);
    });
    add_bind_log(0);
    function add_bind_log(is_vest) {
        $.post("{:Url('get_bind_log')}",{is_vest:is_vest},function (res) {
            $('#bind_log').empty().append(res.data.html);
            choose();
        })
    }
var choose=function(){
    $('[data-role="choose_uid"]').unbind('click');
    $('[data-role="choose_uid"]').click(function () {
        var formSelects = layui.formSelects;
        var uid=$(this).attr('data-uid');
        $.ajax({
            url:"{:Url('get_user')}",
            data:{uid:uid},
            type:'get',
            dataType:'json',
            success:function(res){
                if(res.code == 200){
                    $('.xm-select-parent').remove();
                    var selectHtml = '<option value='+res.data.uid+' selected>'+res.data.nickname+'</option>';
                    if(res.data){
                        $("#bind_select").empty().append(selectHtml);
                        var form = layui.form;
                        form.render();
                        formSelects.config('user_select', {
                            type: 'get',                //请求方式: post, get, put, delete...
                            searchName: 'nickname',      //自定义搜索内容的key值
                            clearInput: true,          //当有搜索内容时, 点击选项是否清空搜索内容, 默认不清空
                        }, false);
                    }
                }else{
                    Toast.error(res.msg);
                }
            }
        });
    });
};
</script>
{/block}
