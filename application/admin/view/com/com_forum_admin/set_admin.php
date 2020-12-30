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
                    <div style="display: flex;align-items: center">
                        <label for="user_select">设置对象</label>
                        <div style="width: 300px;margin-left: 20px;">
                            <select id="user_select" name="uids" xm-select="user_select" xm-select-search="{:Url('com.com_forum/get_users')}" xm-select-radio>
                                <option value="">输入昵称搜索用户</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 20px;display: flex;align-items: center">
                        <label for="level_select">权限级别</label>
                        <div style="width: 300px;margin-left: 20px;">
                            <select id="level_select" name="level" lay-filter="level">
                                <option value="">请选择权限级别</option>
                                <option value="1">版主</option>
                                <option value="2">超级版主</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 20px;display: flex;align-items: center">
                        <label for="fid_select">管理版块</label>
                        <div style="width: 300px;margin-left: 20px;">
                            <select id="fid_select">
                                <option value="">请先选择权限级别</option>
                            </select>
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
    var form = layui.form, layer = layui.layer;
    form.render();
    var formSelects = layui.formSelects;
    formSelects.config('user_select', {
        type: 'get',                //请求方式: post, get, put, delete...
        searchName: 'nickname',      //自定义搜索内容的key值
        clearInput: true          //当有搜索内容时, 点击选项是否清空搜索内容, 默认不清空
    }, false);

    form.on('select(level)', function(data){
        var type = $("#level_select").val();
        $.ajax({
            url:"{:Url('select_class')}",
            data:{type:type},
            type:'post',
            dataType:'json',
            success:function(re){
                if(re.code == 200){
                    $('#fid_select').html("");
                    $.each(re.data, function (index, item) {
                        $('#fid_select').append(new Option(item.name, item.id));
                    });
                    layui.form.render("select");
                }
            }
        })
    });

    var flag = false;
    $('#save').on('click',function(){
        if(flag){
            return;
        }
        flag = !flag;
        var list = {};
        list.uid = $('[name=uids]').val();
        list.level = $("#level_select").val();
        list.fid = $("#fid_select").val();
        if(list.uid === ""){
            $eb.message('error',"请选择设置对象");
        }
        if(list.level === ""){
            $eb.message('error',"请选择权限级别");
        }
        if(list.fid === ""){
            $eb.message('error',"请选择管理版块");
        }
        $.ajax({
            url:"{:Url('create')}",
            data:list,
            type:'post',
            dataType:'json',
            success:function(re){
                if(re.code == 200){
                    $eb.message('success',re.msg);
                    setTimeout(function () {
                        parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    },1500)
                }else {
                    $eb.message('success',re.msg);
                    flag = !flag;
                }
            }
        })
    });
</script>
{/block}
