{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}formselects/formSelects-v4.css">
<script src="{__PLUG_PATH}formselects/formSelects-v4.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
<script src="/public/static/plug/vue/dist/vue.min.js"></script>
<link href="/public/static/plug/iview/dist/styles/iview.css" rel="stylesheet">
<script src="/public/static/plug/iview/dist/iview.min.js"></script>
<script src="/public/static/plug/jquery/jquery.min.js"></script>
<script src="/public/static/plug/form-create/province_city.js"></script>
<script src="/public/static/plug/form-create/form-create.min.js"></script>
<link rel="stylesheet" href="{__PLUG_PATH}formselects/formSelects-v4.css">
<script src="{__PLUG_PATH}formselects/formSelects-v4.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
<style>
    /*弹框样式修改*/
    .ivu-modal-body{padding: 5;}
    .ivu-modal-confirm-footer{display: none;}
    .ivu-date-picker {display: inline-block;line-height: normal;width: 280px;}

    /**链接选择器组件选择范围优化 css**/
    .ivu-icon-ios-close.ivu-input-icon div:before{content: '';}
    .ivu-icon-link-select{width: 100%;text-align: right;}
    .ivu-icon-link-select div{width: 80px;float: right;background: #eeeeee;color: #848484;text-align: center;height: 30px;margin-top: 1px;margin-right: 1px;border-bottom-right-radius: 4px;border-top-right-radius: 4px;font-size: 14px; }
    .ivu-icon-link-select div:before{content: '选择地址';}
    .ivu-icon-link-select:hover div{background: #d2cece;}
    /**链接选择器组件选择范围优化 end**/
    .message_show{
        margin-left: 125px;
        display: flex;
        justify-content: space-around;
    }
    .info_show{
        width: 250px;
        color: #ccc;
        line-height: 18px;
    }
    .label_show{
        margin-top: 25px;
    }
    .ivu-form-item-content{
        margin-left: 125px;
        display:flex;
        justify-content: space-between
    }
    .layui-form-radio{
        margin-top: 0;
    }
    .gray-bg{
        background-color: #fff;
    }
</style>
<script>
    /**链接选择器组件选择范围优化 js**/
    $(function () {
        $('.ivu-icon-ios-close.ivu-input-icon').html('<div></div>');
        $('.ivu-icon-link-select').html('<div></div>');
    })
    /**链接选择器组件选择范围优化 end**/
</script>
<style id="form-create-style">.form-create{padding:25px;} .fc-upload-btn,.fc-files{display: inline-block;width: 58px;height: 58px;text-align: center;line-height: 58px;border: 1px solid #c0ccda;border-radius: 4px;overflow: hidden;background: #fff;position: relative;box-shadow: 2px 2px 5px rgba(0,0,0,.1);margin-right: 4px;box-sizing: border-box;}.__fc_h{display:none;}.__fc_v{visibility:hidden;} .fc-files>.ivu-icon{vertical-align: middle;}.fc-files img{width:100%;height:100%;display:inline-block;vertical-align: top;}.fc-upload .ivu-upload{display: inline-block;}.fc-upload-btn{border: 1px dashed #c0ccda;}.fc-upload-btn>ivu-icon{vertical-align:sub;}.fc-upload .fc-upload-cover{opacity: 0; position: absolute; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,.6); transition: opacity .3s;}.fc-upload .fc-upload-cover i{ color: #fff; font-size: 20px; cursor: pointer; margin: 0 2px; }.fc-files:hover .fc-upload-cover{opacity: 1; }.fc-hide-btn .ivu-upload .ivu-upload{display:none;}.fc-upload .ivu-upload-list{margin-top: 0;}.fc-spin-icon-load{animation: ani-fc-spin 1s linear infinite;} @-webkit-keyframes ani-fc-spin{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}50%{-webkit-transform:rotate(180deg);transform:rotate(180deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@keyframes ani-fc-spin{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}50%{-webkit-transform:rotate(180deg);transform:rotate(180deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}</style></head>
{/block}
{block name="content"}
<form autocomplete="off" class="ivu-form ivu-form-label-right form-create" id="signupForm">
    <div class="ivu-row">
        <div class="ivu-col ivu-col-span-24">
            <div class="ivu-form-item">
                <input type="hidden" name="id" id="id" value="{$id}">
                <!-- 访问审核-->
                <label for="fc_visit" class="ivu-form-item-label" style="width: 125px;">访问审核</label>
                <div class="ivu-form-item-content">
                    <div  class="ivu-radio-group">
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.audit==0'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.audit eq 0'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="audit" value="0">
                                </span>
                                关闭审核
                        </label>
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.audit eq 1'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.audit eq 1'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="audit" value="1">
                                </span>
                               开启审核
                        </label>
                    </div>
                    <div class="info_show">开启后，用户访问或关注该版块时，必须提交申请，并通过审核才可以访问到该版块。审核通过后即默认关注该版块。</div>
                </div>
                <!-- 版块权限审核-->
                <label for="fc_visit" class="ivu-form-item-label label_show" style="width: 125px;">版块访问权限</label>
                <div class="ivu-form-item-content label_show" style="margin-left: 125px;">
                    <div  class="ivu-radio-group">
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.visit eq 0'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.visit eq 0'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="visit" value="0">
                                </span>
                            公开,允许所有用户访问
                        </label></br>
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.visit eq 1'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.visit eq 1'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="visit" value="1">
                                </span>
                            仅关注版块的用户访问
                        </label></br>
                        {if condition="in_array('user_power',$open_list)"}
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.visit eq 2'}ivu-radio-wrapper-checked{/if}" data-show="show" data-id="visit">
                                <span class="ivu-radio {if condition='$data.visit eq 2'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="visit" data-show="show" value="2">
                                </span>
                                指定用户组访问
                                <div style="margin-top: 10px;{if condition='$data.visit eq 2'}display: flex;{else/}display: none;{/if}" id="choose_group_box_visit">
                                    <input type="text" id="visit" class="layui-input ivu-input" value="{$data['visit_name']}">
                                    <input type="hidden" id="visit_id" name="visit_id" class="layui-input ivu-input" value="{$data['visit_id']}">
                                    <button onclick="$eb.createModalFrame(this.innerText,'{:Url('user_select',['name'=>'visit','id'=>$data['visit_group_id']])}',{h:500,w:650})" type="button" class="layui-btn ivu-btn ivu-btn-primary ivu-btn-long ivu-btn-large" style="width: 100px;height: 32px;">选择用户组</button>
                                </div>
                        </label>
                        {/if}
                    </div>
                    <div class="info_show">此处发帖，指所有支持前端发布的社区内容形式。如果关闭访问审核，则用户视同已关注版块。</div>
                </div>
                <!-- 版块权限审核-->
                <label for="fc_visit" class="ivu-form-item-label label_show" style="width: 125px;">版块内发帖权限</label>
                <div class="ivu-form-item-content label_show" style="margin-left: 125px;">
                    <div  class="ivu-radio-group">
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.send_thread eq 0'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.send_thread eq 0'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="send_thread" value="0">
                                </span>
                            同版块访问权限
                        </label></br>
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.send_thread eq 1'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.send_thread eq 1'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="send_thread" value="1">
                                </span>
                            仅允许关注版块的用户发帖
                        </label></br>
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.send_thread eq 2'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.send_thread eq 2'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="send_thread" value="2">
                                </span>
                            仅版主发帖（该版块版主及超级版主）
                        </label></br>
                        {if condition="in_array('user_power',$open_list)"}
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.send_thread eq 3'}ivu-radio-wrapper-checked{/if}" data-show="show" data-id="send_thread">
                                <span class="ivu-radio {if condition='$data.send_thread eq 3'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="send_thread" data-show="show" value="3">
                                </span>
                            指定用户组发帖
                            <div style="margin-top: 10px;{if condition='$data.send_thread eq 3'}display: flex;{else/}display: none;{/if}" id="choose_group_box_send_thread">
                                <input type="text" id="send_thread" class="layui-input ivu-input" value="{$data['send_thread_name']}">
                                <input type="hidden" id="send_thread_id" name="send_thread_id" class="layui-input ivu-input" value="{$data['send_thread_id']}">
                                <button onclick="$eb.createModalFrame(this.innerText,'{:Url('user_select',['name'=>'send_thread','id'=>$data['send_thread_group_id']])}',{h:500,w:650})" type="button" class="layui-btn ivu-btn ivu-btn-primary ivu-btn-long ivu-btn-large" style="width: 100px;height: 32px;">选择用户组</button>
                            </div>
                        </label>
                        {/if}
                    </div>
                </div>
                <!-- 版块内评论权限-->
                <label for="fc_visit" class="ivu-form-item-label label_show" style="width: 125px;">版块内评论权限</label>
                <div class="ivu-form-item-content label_show" style="margin-left: 125px;">
                    <div  class="ivu-radio-group">
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.send_comment eq 0'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.send_comment eq 0'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="send_comment" value="0">
                                </span>
                            同版块访问权限
                        </label></br>
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.send_comment eq 1'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.send_comment eq 1'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="send_comment" value="1">
                                </span>
                            仅允许关注版块的用户评论
                        </label></br>
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.send_comment eq 2'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.send_comment eq 2'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="send_comment" value="2">
                                </span>
                            仅作者及版主评论（该版块版主及超级版主）
                        </label></br>
                        {if condition="in_array('user_power',$open_list)"}
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.send_comment eq 3'}ivu-radio-wrapper-checked{/if}" data-show="show" data-id="send_comment">
                                <span class="ivu-radio {if condition='$data.send_comment eq 3'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="send_comment" data-show="show" value="3">
                                </span>
                            仅作者及指定用户组评论
                            <div style="margin-top: 10px;{if condition='$data.send_comment eq 3'}display: flex;{else/}display: none;{/if}" id="choose_group_box_send_comment">
                                <input type="text" id="send_comment" class="layui-input ivu-input" value="{$data['send_comment_name']}">
                                <input type="hidden" id="send_comment_id" name="send_comment_id" class="layui-input ivu-input" value="{$data['send_comment_id']}">
                                <button onclick="$eb.createModalFrame(this.innerText,'{:Url('user_select',['name'=>'send_comment','id'=>$data['send_comment_group_id']])}',{h:500,w:650})" type="button" class="layui-btn ivu-btn ivu-btn-primary ivu-btn-long ivu-btn-large" style="width: 100px;height: 32px;">选择用户组</button>
                            </div>
                        </label>
                        {/if}
                    </div>
                </div>
                <!-- 帖子浏览权限-->
                <label for="fc_visit" class="ivu-form-item-label label_show" style="width: 125px;">版块内帖子浏览权限</label>
                <div class="ivu-form-item-content label_show" style="margin-left: 125px;">
                    <div  class="ivu-radio-group">
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.browse eq 0'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.browse eq 0'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="browse" value="0">
                                </span>
                            同版块访问权限
                        </label></br>
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.browse eq 1'}ivu-radio-wrapper-checked{/if}">
                                <span class="ivu-radio {if condition='$data.browse eq 1'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="browse" value="1">
                                </span>
                            仅作者及版主浏览（该版块版主及超级版主）
                        </label></br>
                        {if condition="in_array('user_power',$open_list)"}
                        <label class="ivu-radio-wrapper ivu-radio-group-item {if condition='$data.browse eq 2'}ivu-radio-wrapper-checked{/if}" data-show="show" data-id="browse">
                                <span class="ivu-radio {if condition='$data.browse eq 2'}ivu-radio-checked{/if}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="browse" data-show="show" value="2">
                                </span>
                            仅作者及指定用户组可见
                            <div style="margin-top: 10px;{if condition='$data.browse eq 2'}display: flex;{else/}display: none;{/if}" id="choose_group_box_browse">
                                <input type="text" id="browse" class="layui-input ivu-input" value="{$data['browse_name']}">
                                <input type="hidden" id="browse_id" name="browse_id" class="layui-input ivu-input" value="{$data['browse_id']}"">
                                <button onclick="$eb.createModalFrame(this.innerText,'{:Url('user_select',['name'=>'browse','id'=>$data['browse_group_id']])}',{h:500,w:650})" type="button" class="layui-btn ivu-btn ivu-btn-primary ivu-btn-long ivu-btn-large" style="width: 100px;height: 32px;">选择用户组</button>
                            </div>
                        </label>
                        {/if}
                    </div>
                    <div class="info_show">浏览权限设置说明：</br>
                        1、该权限一般常用于投诉、举报等私密版块，常规版块不建议设置；</br>
                        2、浏览权限设置仅对普通用户发布的帖子有效，对管理员、版主、超级版主发布的帖子无效；</br></div>
                </div>
                <!-- 不受限制的用户组-->
                {if condition="in_array('user_power',$open_list)"}
                <label for="fc_visit" class="ivu-form-item-label label_show" style="width: 125px;">不受限制的用户组</label>
                <div class="ivu-form-item-content label_show" style="margin-left: 125px;">
                    <div  class="ivu-radio-group">
                        <div style="display: flex">
                            <input type="text" id="forum" class="layui-input ivu-input" value="{$data.forum_id_name}">
                            <input type="hidden" id="forum_id" class="layui-input ivu-input" name="forum_id" value="{$data.forum_id_id}">
                            <button onclick="$eb.createModalFrame(this.innerText,'{:Url('user_select',['name'=>'forum','id'=>$data['forum_id_group_id']])}',{h:500,w:650})" type="button" class="layui-btn ivu-btn ivu-btn-primary ivu-btn-long ivu-btn-large" style="width: 100px;height: 32px;">选择用户组</button>
                        </div>
                    </div>
                    <div class="info_show">不受限制用户组，指以上版块设置对该用户组无效，按用户组自身权限为准，非必填</div>
                </div>
                {/if}
            </div>
        </div>
        <div class="ivu-col ivu-col-span-24" >
            <div class="ivu-col ivu-col-span-24">
                <button type="button" class="ivu-btn ivu-btn-primary ivu-btn-long ivu-btn-large" data-role="submit">
                    <i class="ivu-icon ivu-icon-ios-upload"></i>
                    <span>提交</span>
                </button>
                <p style="color: red;text-align: center;margin-top: 10px">修改版块权限后，需要去维护->刷新缓存->清除缓存,前端才能生效。</p>
            </div>

        </div>
    </div>
</form>
{/block}
{block name="script"}
<script>
    $('.ivu-radio-wrapper').click(function () {
        $(this).addClass('ivu-radio-wrapper-checked').children().addClass('ivu-radio-checked').children().attr("checked","checked");
        $(this).siblings().removeClass('ivu-radio-wrapper-checked').children().removeClass('ivu-radio-checked').children().remove('checked');
        var id=$(this).attr('data-id');
        var show=$(this).attr('data-show');
        if(show === "show"){
            $("#choose_group_box_"+id).css("display","flex");
        }else{
            $("#choose_group_box_"+id).css("display","none");
        }
    });
    window.addEventListener("storage", function (e) {
//        var reg = new RegExp(",","g");//g,表示全部替换。
//        var text = e.newValue.replace(reg,"、");
        $('#'+e.key).val(e.newValue);
        window.localStorage.removeItem(e.key);
    });
    $('[data-role="submit"]').click(function () {
        var data=$('#signupForm').serializeArray();
        $.post("{:Url('edit_forum_power')}",data,function (res) {
            if(res.code==200){
                $eb.message('success',res.msg);
                setTimeout(function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                    parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
//                    console.log($(".page-tabs-content .active").index());
//                    window.frames[$(".page-tabs-content .active").index()].location.reload();
                },1500)
            }else{
                $eb.message('error',res.msg);
            }
        })

    });
</script>
{/block}