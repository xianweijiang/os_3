{extend name="public/container"}
{block name="content"}
<!--<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
    <div class="layui-row layui-col-space15" id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-card-body">
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label">状态</label>
                                    <div class="layui-input-block">
                                        <select name="display">
                                            <option value="">全部</option>
                                            <option value="1">已发布</option>
                                            <option value="0">未发布</option>
                                            <option value="-1">已关闭</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!--<div class="layui-row layui-col-space10 layui-form-item">
                                <div class="layui-col-lg12">
                                    <div class="layui-input-block">
                                        <div class="layui-inline">
                                            <div class="layui-input-inline">
                                                <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                                    <i class="layui-icon layui-icon-search"></i>搜索</button>
                                                <button onclick="javascript:layList.reload();" type="reset" class="layui-btn layui-btn-primary layui-btn-sm">
                                                    <i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>-->
        <!--版块列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        以下用户协议、隐私协议用于登陆注册相关页面及设置列表展示、供用户查看阅读。
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="checkboxstatus">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_verify' lay-text='启用|禁用'  {{ d.display == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="logo">
                        {{#  if(d.logo==''){ }}
                        {{#  } else { }}
                        <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);" src="{{d.logo}}">
                        {{#  } }}
                    </script>
                    <!--操作-->
                    <script type="text/html" id="act_one">
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('edit')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    var status=<?=$status?>;
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('agreement_list',['status'=>$status])}",function (){
        var join=new Array();
        switch (parseInt(status)){
            case 1:
                join=[
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', event:'fid',width:'5%'},
                    {field: 'name', title: '协议名称',width:'8%'},
                    {field: 'content', title: '内容'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_one',width:'15%'},
                ];
                break;
        }
        return join;
    })

    //下拉框
    $(document).click(function (e) {
        $('.layui-nav-child').hide();
    })
    function dropdown(that){
        var oEvent = arguments.callee.caller.arguments[0] || event;
        oEvent.stopPropagation();
        var offset = $(that).offset();
        var top=offset.top-$(window).scrollTop();
        var index = $(that).parents('tr').data('index');
        $('.layui-nav-child').each(function (key) {
            if (key != index) {
                $(this).hide();
            }
        })
        if($(document).height() < top+$(that).next('ul').height()){
            $(that).next('ul').css({
                'padding': 10,
                'top': - ($(that).parent('td').height() / 2 + $(that).height() + $(that).next('ul').height()/2),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }else{
            $(that).next('ul').css({
                'padding': 10,
                'top':$(that).parent('td').height() / 2 + $(that).height(),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }
    }
    //快速编辑
    layList.edit(function (obj) {
        var id=obj.data.id,value=obj.value;
        switch (obj.field) {
            case 'summary':
                action.set_forum('summary',id,value);
                break;
            case 'sort':
                action.set_forum('sort',id,value);
                break;
        }
    });
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                console.log($eb);
                var url  = layList.U({c:'com.com_message_news',a:'delete',q:{id:data.id}});
                var code = {title:"操作提示",text:"你确定要删除该消息吗？",type:'info',confirm:'是的，删除该消息'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success','');
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
            case 'open':
                var url=layList.U({c:'com.com_message_news',a:'open',q:{id:data.id}});
                var code = {title:"操作提示",text:"你确定推送该消息吗？",type:'info',confirm:'是的，推送该消息'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success','');
                            layList.reload({},true,null,obj);
                        }else{
                            return Promise.reject(res.data.msg || '推送失败');
                        }
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code);
                break;
            case 'close':
                var url=layList.U({c:'com.com_message_news',a:'close',q:{id:data.id}});
                var code = {title:"操作提示",text:"你确定关闭该消息吗？",type:'info',confirm:'是的，关闭该消息'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success','');
                            layList.reload({},true,null,obj);
                        }else{
                            return Promise.reject(res.data.msg || '关闭失败');
                        }
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code);
                break;
        }
    })
    //排序
    layList.sort(function (obj) {
        var type = obj.type;
        switch (obj.field){
            case 'id':
                // layList.reload({order: layList.order(type,'p.id')},true,null,obj);
                break;
            case 'sales':
                layList.reload({order: layList.order(type,'p.sales')},true,null,obj);
                break;
        }
    });
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
    //自定义方法
    var action={
        set_forum:function(field,id,value){
            layList.baseGet(layList.Url({c:'com.com_forum',a:'set_forum',q:{field:field,id:id,value:value}}),function (res) {
                layList.msg(res.msg);
            });
        },
        set_verify:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定提交审核版块吗？",type:'info',confirm:'是的，提交审核'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_forum',a:'forum_verify'}),{ids:ids, status:2},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要提交审核的版块');
            }
        },
        verify:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定审核通过版块吗？",type:'info',confirm:'是的，审核通过'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_forum',a:'forum_verify'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要审核的版块');
            }
        },
        del: function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定批量删除版块吗？",type:'info',confirm:'是的，删除'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_forum',a:'del'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要删除的版块');
            }
        },
        band:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定批量驳回版块吗？",type:'info',confirm:'是的，驳回'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_forum',a:'ban'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要驳回的版块');
            }
        },
        restore:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定还原吗？",type:'info',confirm:'是的，还原'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_forum',a:'forum_verify'}),{ids:ids, status:1},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要还原的版块');
            }
        },
        remove:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"清空版块后，该版块下的所有分类、帖子数据将同步清空，无法恢复，请慎重考虑。 ",type:'info',confirm:'确定'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_forum',a:'remove'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要清理的版块');
            }
        }
    };

    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });

    layList.laydate.render({
        elem:'#date_time',
        trigger:'click',
        eventElem:'#zd',
        range:true,
        change:function (value){
            $('#data').val(value);
            $('#date_time').text(value);
        }
    });

    var setData = function(val, ele){
        var $data = $('#data');
        $data.val(val);
        $(ele).parent().find('button').addClass('layui-btn-primary');
        $(ele).removeClass('layui-btn-primary');
        if(val == 'zd'){
            $('#date_time').show();
        }else{
            $('#date_time').hide();
        }
    }


</script>
{/block}
