{extend name="public/container"}

{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">{$name}</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container">
                        {if condition="$group_type eq 6"}
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit')}?type={$group_type}',{h:document.body.clientHeight,w:document.body.clientWidth})">添加{$name}</button>
                        {else/}
                        <button class="layui-btn layui-btn-sm" data-type="unable">添加{$name}</button>
                        {/if}
                        {/if}
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="act_common">
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button class="btn btn-success btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit')}?id={{d.id}}',{h:document.body.clientHeight,w:document.body.clientWidth})" type="button"><i class="fa fa-warning"></i> 编辑
                        </button>
                        {else/}
                        <button class="btn btn-success btn-xs" lay-event='unable' type="button"><i class="fa fa-warning"></i> 编辑
                        </button>
                        {/if}

                        {if condition="$group_type eq 1"}
                            {if condition="$is_free_ban AND $is_end_ban"}
                            <button class="btn btn-success btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_manage_power')}?g_id={{d.id}}&&manage_type=1')" type="button"><i class="fa fa-warning"></i> 管理权限
                            </button>
                            {else/}
                            <button class="btn btn-success btn-xs" lay-event='unable' type="button"><i class="fa fa-warning"></i> 管理权限
                            </button>
                            {/if}
                        {/if}

                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button class="btn btn-success btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_manage_power')}?g_id={{d.id}}&&manage_type=2')" type="button"><i class="fa fa-warning"></i> 基本权限
                        </button>
                        {else/}
                        <button class="btn btn-success btn-xs" lay-event='unable' type="button"><i class="fa fa-warning"></i> 基本权限
                        </button>
                        {/if}

                        {{#  if(d.id!=7){ }}

                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button class="btn btn-success btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('admin/user.user/group_uid_index')}?g_id={{d.id}}')" type="button"><i class="fa fa-warning"></i> 查看用户
                        </button>
                        {else/}
                        <button class="btn btn-success btn-xs" lay-event='unable' type="button"><i class="fa fa-warning"></i> 查看用户
                        </button>
                        {/if}

                        {{#  } }}
                        {if condition="$group_type eq 6"}
                            <button class="btn btn-success btn-xs"  type="button"  lay-event='delete'><i class="fa fa-warning"></i> 删除
                            </button>
                        {/if}
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    setTimeout(function () {
        $('.alert-info').hide();
    },3000);
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('get_group_list',['group_type'=>$group_type])}",function (){
        var join = [
            {type:'checkbox'},
            {field: 'id', title: 'ID', event:'id'},
            {field: 'name', title: '用户组名称'},
            {field: 'remark', title: '角色描述'},
            {field: 'level', title: '级别'},
            {field: 'cate', title: '类型'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act_common'},
        ];
        return join;
    });
    //自定义方法
    var action={
        unable:function(){
            var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
            $eb.$swal('delete',function(){
                $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
            }, code)
        },
    };
    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });

    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });

    layList.switch('status',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'com.com_post',a:'quick_edit',p:{value:1,field:'status',id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'com.com_post',a:'quick_edit',p:{value:0,field:'status', id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });

    //监听并执行排序
    // layList.sort(['id','sort'],true);
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delete':
                var url=layList.U({c:'group.group_power',a:'del_version',q:{id:data.id,status:-1}});
                var code = {title:"操作提示",text:"确定删除吗？",type:'info',confirm:'是的'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            layList.reload();
                            $eb.$swal('success','删除成功');
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
            case 'unable':
                var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
                $eb.$swal('delete',function(){
                    $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
                }, code)
                break;
        }
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
                'top': - ($(that).parents('td').height() / 2 + $(that).height() + $(that).next('ul').height()/2),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }else{
            $(that).next('ul').css({
                'padding': 10,
                'top':$(that).parents('td').height() / 2 + $(that).height(),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }
    }
</script>
{/block}
