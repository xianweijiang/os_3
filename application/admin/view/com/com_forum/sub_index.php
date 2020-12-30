{extend name="public/container"}
{block name="content"}
<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
    <div class="layui-row layui-col-space15" id="app">
        <!--版块列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <!-- <div class="alert alert-info" role="alert">
                        列表[版块描述],[排序]可进行快速修改,双击或者单击进入编辑模式,失去焦点可进行自动保存
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div> -->
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--图片-->
                    <script type="text/html" id="logo">
                        <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);" src="{{d.logo}}">
                    </script>
                    <script type="text/html" id="background">
                        <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);" src="{{d.background}}">
                    </script>
                    <!--上架|下架-->
                    <script type="text/html" id="checkboxstatus">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_verify' lay-text='启用|禁用'  {{ d.display == 1 ? 'checked' : '' }}>
                    </script>
                    <!--收藏-->
                    <script type="text/html" id="thread_count">
                         <a title="管理帖子" href="{:Url('thread')}?id={{d.id}}"><p><i class="layui-icon layui-icon-dialogue"></i> 共有<font color="red">{{d.thread_count}}</font>帖</p></a>
                    </script>
                    <!--点赞-->
                    <script type="text/html" id="collect">
                        <span><i class="layui-icon layui-icon-star"></i> {{d.collect}}</span>
                    </script>
                    <!--版块名称-->
                    <script type="text/html" id="forum_name">
                        <h4>{{d.name}}</h4>
                        <p>帖子数:{{d.post_count}} </p>
                        {{# if(d.pid!=''){ }}
                        <p>分享数:{{d.share_count}}</p>
                        {{# } }}

                    </script>
                    <script type="text/html" id="is_hot">
                        <h4>{{d.is_hot}}</h4>
                        <p>虚拟关注人数:{{d.false_num}} </p>
                    </script>
                    <script type="text/html" id="admin_users">
                        {{#  if(d.admin_uid!=0){ }}
                        <p>{{d.admin_users}}</p>
                        {{#  } else { }}
                        <p>暂无版主</p>
                        {{#  } }}
                    </script>
                    <!--操作-->
                    <!--<script type="text/html" id="act_common">
                        <button type="button" class="layui-btn layui-btn-xs btn-success" onclick="$eb.createModalFrame('{{d.name}}-版主设置','{:Url('set_admin')}?id={{d.id}}',{h:600,w:800})">
                            版主设置
                        </button>
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" class="" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content')}?id={{d.id}}')">
                                    <i class="fa fa-pencil"></i> 版块详情</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='set_hot'>
                                {{# if(d.is_hot== '是'){ }}
                                    取消推荐
                                {{# }else{ }}
                                    设为推荐
                                {{# } }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='delstor'>
                                    <i class="fa fa-trash"></i> 移到回收站
                                </a>
                            </li>
                        </ul>
                    </script>-->
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    var status= 1;
    var pid = <?=$pid?>;
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('forum_list',['status'=>1, 'pid'=>$pid])}",function (){
        var join=new Array();
        join=[
            {field: 'id', title: 'ID', event:'fid',width:'3%'},
            {field: 'name', title: '版块名称',templet:'#forum_name',width:'18%'},
            {field: 'logo', title: '版块logo',templet:'#logo',width:'9%'},
            {field: 'background', title: '版块背景图',templet:'#background',width:'9%'},
            {field: 'type_name', title: '版块类型',templet:'type_name',width:'8%'},
            {field: 'pid_name', title: '上级版块',templet:'#pid_name',width:'10%'},
            {field: 'admin_html',title:'版主',templet:'#admin_html',width:'10%'},
            {field: 'is_hot', title: '是否推荐',templet:'#is_hot',width:'10%'},
            // {field: 'summary', title: '版块描述',edit:'summary'},
            {field: 'thread_count', title: '主题帖数',templet:'#thread_count',width:'10%'},
            {field: 'sort', title: '排序',edit:'sort',width:'6%'},
            {field: 'status', title: '状态',templet:"#checkboxstatus",width:'8%'},
            // {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'14%'},
        ];

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
    //审核版块
    layList.switch('is_verify',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'com.com_forum',a:'set_verify',p:{display:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'com.com_forum',a:'set_verify',p:{display:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                console.log($eb);
                if(data.status == -1){
                    var url  = layList.U({c:'com.com_forum',a:'delete',q:{id:data.id}});
                    var code = {title:"操作提示",text:"你确定要清理该版块吗？",type:'info',confirm:'是的，我要删除'};
                }else{
                    var url  = layList.U({c:'com.com_forum',a:'set_forum',q:{id:data.id, field:'status', value: -1}});
                    var code = {title:"操作提示",text:"确定将该版块移入回收站吗？",type:'info',confirm:'是的，移入回收站'};
                }
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
            case 'set_hot':
                var url=layList.U({c:'com.com_forum',a:'set_forum',q:{id:data.id, field:'is_hot', value: data.is_hot == '是'?0:1}});
                if(data.is_hot == '是'){
                    var code = {title:"操作提示",text:"确定取消版块推荐操作吗？",type:'info',confirm:'是的，取消推荐该版块'};
                }else{
                    var code = {title:"操作提示",text:"确定将该版块设为推荐吗？",type:'info',confirm:'是的，设为推荐'};
                }
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success','');
                            layList.reload({},true,null,obj);
                        }else{
                            return Promise.reject(res.data.msg || '设置失败');
                        }
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code);
                break;
            case 'restore':
                var url=layList.U({c:'com.com_forum',a:'set_forum',q:{id:data.id, field:'status', value: 1}});
                var code = {title:"操作提示",text:"确定还原该版块吗？",type:'info',confirm:'是的，还原'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success','');
                            layList.reload({},true,null,obj);
                        }else{
                            return Promise.reject(res.data.msg || '失败');
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
        verify:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                layList.basePost(layList.Url({c:'com.com_forum',a:'forum_verify'}),{ids:ids},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要审核的版块');
            }
        },
        del: function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                layList.basePost(layList.Url({c:'com.com_forum',a:'del'}),{ids:ids},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要删除的版块');
            }
        },
        band:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                layList.basePost(layList.Url({c:'com.com_forum',a:'ban'}),{ids:ids},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要驳回的版块');
            }
        },
        restore:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                layList.basePost(layList.Url({c:'com.com_forum',a:'forum_verify'}),{ids:ids, status:1},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要还原的版块');
            }
        },
        remove:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                layList.basePost(layList.Url({c:'com.com_forum',a:'remove'}),{ids:ids},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要清理的版块');
            }
        }
    };

</script>
{/block}
