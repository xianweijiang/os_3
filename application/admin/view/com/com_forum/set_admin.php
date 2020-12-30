{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}formselects/formSelects-v4.css">
<script src="{__PLUG_PATH}formselects/formSelects-v4.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <form class="layui-form" action="" style="padding:20px;">
                        <fieldset><legend><a name="input">通过搜索昵称、UID快速选择用户</a></legend></fieldset>
                            <select name="uids" xm-select="user_select" xm-select-search="{:Url('get_users')}" xm-select-radio>
                                <option value="">请选择版主</option>
                                {volist name="admins_user" id="v"}
                                <option value="{$v.uid}" selected="selected" >{$v.user.nickname}</option>
                                {/volist}
                            </select>
                    <br/>
                <button class="btn btn-primary " data-url="{:Url('save_admin_uids')}" id="save" type="button"><i class="fa  fa-arrow-circle-o-right"></i> 设置版主
                </button>
                </form>

            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    var form = layui.form;
    form.render();
    var formSelects = layui.formSelects;
    formSelects.config('user_select', {
        type: 'get',                //请求方式: post, get, put, delete...
        searchName: 'nickname',      //自定义搜索内容的key值
        clearInput: true,          //当有搜索内容时, 点击选项是否清空搜索内容, 默认不清空
    }, false);


    $('#save').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        swal({
            title: "您确定要重新设置版主吗？",
            text:"该操作会重置版主，请谨慎操作！",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText:"是的，我要设置！",
            cancelButtonText:"让我再考虑一下…",
            closeOnConfirm: false,
            closeOnCancel: false
        }).then(function(){
            $eb.axios.get(url+'?uids='+$('[name=uids]').val()+"&fid={$d.id}").then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    swal(res.data.msg);
                    //关闭当前窗口
                    setTimeout(function () {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    },1500)
                }else
                    return Promise.reject(res.data.msg || '设置版主失败')
            }).catch(function(err){
                swal(err);
            });
        }).catch(console.log);
    });
</script>
{/block}
