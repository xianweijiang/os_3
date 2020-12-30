{extend name="public/container"}
{block name="content"}
<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
    <div class="layui-tab layui-tab-brief" lay-filter="tab">
        <ul class="layui-tab-title">
            <li lay-id="list" {eq name='status' value='1'}class="layui-this" {/eq} >
            <a href="{eq name='status' value='1'}javascript:;{else}{:Url('index',['status'=>1])}{/eq}">已发布({$common})</a>
            </li>
            <li lay-id="list" {eq name='status' value='0'}class="layui-this" {/eq}>
            <a href="{eq name='status' value='0'}javascript:;{else}{:Url('index',['status'=>0])}{/eq}">未发布({$band})</a>
            </li>
            <li lay-id="list" {eq name='status' value='-1'}class="layui-this" {/eq}>
            <a href="{eq name='status' value='-1'}javascript:;{else}{:Url('index',['status'=>-1])}{/eq}">回收站({$recycle})</a>
            </li>
        </ul>
    </div>
    <div class="layui-row layui-col-space15" id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                </div>
            </div>
        </div>
        <!--版块列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        公告管理
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="layui-btn-container">
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create_message_news')}',{h:document.body.clientHeight,w:document.body.clientWidth})">创建营销消息</button>
                        {else/}
                        <button class="layui-btn layui-btn-sm" data-type="unable">创建营销消息</button>
                        {/if}
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
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" lay-event='delstor'>
                                    <i class="fa fa-trash"></i> 删除
                                </a>
                            </li>
                        </ul>
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('edit')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        {else/}
                        <button class="layui-btn layui-btn-xs" data-type="unable">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        {/if}

                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('详情','{:Url('view')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 详情
                        </button>
                    </script>
                    <script type="text/html" id="title">
                        <div style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">{{d.title}}</div>
                    </script>
                    <script type="text/html" id="summary">
                        <div style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 3;overflow: hidden;">{{d.summary}}</div>
                    </script>
                    <script type="text/html" id="act_tre">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" lay-event='open'>
                                    <i class="fa fa-trash"></i> 推送
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='delstor'>
                                    <i class="fa fa-trash"></i> 删除
                                </a>
                            </li>
                        </ul>
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('edit')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('详情','{:Url('view')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 详情
                        </button>
                    </script>
                    <script type="text/html" id="act_two">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" lay-event='open'>
                                    <i class="fa fa-trash"></i> 还原
                                </a>
                            </li>
                        </ul>
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('edit')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('详情','{:Url('view')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 详情
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
    layList.tableList('List',"{:Url('message_news_list',['status'=>$status])}",function (){
        var join=new Array();
        switch (parseInt(status)){
            // 禁用
            case 0:
                join=[
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', event:'fid',width:'5%'},
                    {field: 'title', title: '标题',width:'10%',templet:'#title'},
                    {field: 'summary', title: '摘要',width:'16%',templet:'#summary'},
                    {field: 'logo', title: '海报',templet:'#logo',width:'6%'},
                    {field: 'from_uid', title: '发布人',width:'10%'},
                    {field: 'status', title: '状态',width:'5%'},
                    {field: 'create_time', title: '创建时间',width:'8%'},
                    {field: 'send_time', title: '推送时间',width:'8%'},
                    {field: 'to_uid', title: '推送人群',width:'8%'},
                    {field: 'view', title: '阅读量',templet:'type_name',width:'6%'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_tre',width:'15%'},
                ];
                break;
            case 1:
                join=[
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', event:'fid',width:'5%'},
                    {field: 'title', title: '标题',width:'10%',templet:'#title'},
                    {field: 'summary', title: '摘要',width:'16%',templet:'#summary'},
                    {field: 'logo', title: '海报',templet:'#logo',width:'6%'},
                    {field: 'from_uid', title: '发布人',width:'10%'},
                    {field: 'status', title: '状态',width:'5%'},
                    {field: 'create_time', title: '创建时间',width:'8%'},
                    {field: 'send_time', title: '推送时间',width:'8%'},
                    {field: 'to_uid', title: '推送人群',width:'8%'},
                    {field: 'view', title: '阅读量',templet:'type_name',width:'6%'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_one',width:'15%'},
                ];
                break;
            case -1:
                join=[
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', event:'fid',width:'5%'},
                    {field: 'title', title: '标题',width:'10%',templet:'#title'},
                    {field: 'summary', title: '摘要',width:'16%',templet:'#summary'},
                    {field: 'logo', title: '海报',templet:'#logo',width:'6%'},
                    {field: 'from_uid', title: '发布人',width:'10%'},
                    {field: 'status', title: '状态',width:'5%'},
                    {field: 'create_time', title: '创建时间',width:'8%'},
                    {field: 'send_time', title: '推送时间',width:'8%'},
                    {field: 'to_uid', title: '推送人群',width:'8%'},
                    {field: 'view', title: '阅读量',templet:'type_name',width:'6%'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_two',width:'15%'},
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
        },
        unable:function(){
            var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
            $eb.$swal('delete',function(){
                $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
            }, code)
        },
    };

    //多选事件绑定
    $('body').find('button').each(function () {
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
