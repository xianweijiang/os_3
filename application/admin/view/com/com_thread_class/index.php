{extend name="public/container"}
{block name="content"}

<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12" style="margin-top: -20px">
            <div class="layui-tab layui-tab-brief" lay-filter="tab">
                <ul class="layui-tab-title" style="background-color: white; top: 10px">
                    <li lay-id="list" {eq name='status' value=''}class="layui-this" {/eq} >
                        <a href="{eq name='status' value=''}javascript:;{else}{:Url('index',['fid'=>$fid])}{/eq}">分类列表</a>
                    </li>
                    <li lay-id="list" {eq name='status' value='-1'}class="layui-this" {/eq}>
                        <a href="{eq name='status' value='0'}javascript:;{else}{:Url('index',['status'=>-1,'fid'=>$fid])}{/eq}">分类回收站</a>
                    </li>
                </ul>
            </div>
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="" style="margin-top: 10px">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">所有分类</label>
                                <div class="layui-input-block">
                                    <select name="fid">
                                        <option value="">所有主题分类</option>
                                        {volist name="cate" id="vo"}
                                        <option value="{$vo.id}">{$vo.html}{$vo.name}</option>
                                        {/volist}
                                    </select>
                                </div>
                            </div>
                            {eq name="status" value="-1"}
                           <!--  <div class="layui-inline">
                                <label class="layui-form-label">删除时间</label>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="start_time" placeholder="开始时间" id="start_time" class="layui-input">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="end_time" placeholder="结束时间" id="end_time" class="layui-input">
                                </div>
                            </div> -->
                            {else /}
                            <div class="layui-inline">
                                <label class="layui-form-label">分类状态</label>
                                <div class="layui-input-block">
                                    <select name="status">
                                        <option value="">全部</option>
                                        <option value="1">启用</option>
                                        <option value="0">禁用</option>
                                    </select>
                                </div>
                            </div>
                            {/eq}
                            <div class="layui-inline">
                                <label class="layui-form-label">主题分类名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="name" class="layui-input" placeholder="请输入主题分类名称">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
                                    <button onclick="javascript:layList.reload();" type="reset" class="layui-btn layui-btn-primary layui-btn-sm">
                                            <i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">分类列表</div>
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        注:分类名称和排序可进行快速编辑;
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    {eq name="status" value="-1"}
                    <div class="layui-btn-container" style="margin-bottom: 10px;margin-top: 10px">
                        <button class="layui-btn layui-btn-sm" data-type="remove">清理</button>
                        <button class="layui-btn layui-btn-sm" data-type="restore">还原</button>
                    </div>
                    {else /}
                    <div class="layui-btn-container" style="margin-bottom: 2px;margin-top: 10px">
                        <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加主题分类</button>
                    </div>
                    {/eq}
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="icon">
                        <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);" src="{{d.icon}}">
                    </script>
                    <script type="text/html" id="moderators">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='moderators' lay-text='仅管理员可发布|任何人可发布'  {{ d.moderators == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="status">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='status' lay-text='启用|禁用'  {{ d.status == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="pid">
                        <a href="{:Url('index')}?pid={{d.id}}">查看</a>
                    </script>
                    <script type="text/html" id="act">
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('edit')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        <button class="layui-btn layui-btn-xs" lay-event='delstor'>
                            <i class="fa fa-warning"></i> 删除
                        </button>
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
    var status = '<?=$status?>';
    setTimeout(function () {
        $('.alert-info').hide();
    },3000);
    //实例化form
    layList.form.render();
    // layList.date({elem:'#start_time',theme:'#393D49',type:'datetime'});
    // layList.date({elem:'#end_time',theme:'#393D49',type:'datetime'});
    //加载列表
    layList.tableList('List',"{:Url('class_list',['fid'=>$fid, 'status'=>$status])}",function (){
        if(status == '-1'){
            return [
                {type:'checkbox'},
                {field: 'id', title: 'ID', event:'id',width:'4%'},
                {field: 'fid_name', title: '所属版块'},
                {field: 'name', title: '分类名称',edit:'name'},
                //{field: 'moderators', title: '是否仅管理员可发布',templet:'#moderators'},
                {field: 'create_time', title: '创建时间',edit:'create_time'},
                {field: 'update_time', title: '删除时间',edit:'update_time'},
                // {field: 'icon', title: '分类图标',templet:'#icon'},
                // {field: 'thread_count', title: '创建时间'},
                // {field: 'summary', title: '分类描述',edit:'#summary'},
                // {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'14%'},
            ];
        }else{
            return [
                {field: 'id', title: 'ID', event:'id',width:'4%'},
                {field: 'fid_name', title: '所属版块'},
                {field: 'name', title: '分类名称',edit:'name'},
                // {field: 'icon', title: '分类图标',templet:'#icon'},
                // {field: 'summary', title: '分类描述',edit:'#summary'},
                //{field: 'moderators', title: '是否仅管理员可发布',templet:'#moderators'},
                {field: 'sort', title: '排序',sort: true,event:'sort',edit:'sort',width:'8%'},
                {field: 'status', title: '状态',templet:'#status',width:'8%'},
                {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'14%'},
            ];
        }
    });
    //自定义方法
    var action= {
        set_Class: function (field, id, value) {
            layList.baseGet(layList.Url({
                c: 'com.com_thread_class',
                a: 'set_class',
                q: {field: field, id: id, value: value}
            }), function (res) {
                layList.msg(res.msg);
            });
        },
        remove:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"清空分类后，该分类下的所有帖子数据将同步清空，无法恢复，请慎重考虑。 ",type:'info',confirm:'确定'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_thread_class',a:'delete'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择分类');
            }
        },
        restore:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定还原吗？",type:'info',confirm:'是的，还原'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_thread_class',a:'restore'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要还原的版块');
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
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
    layList.switch('status',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'com.com_thread_class',a:'set_status',p:{status:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'com.com_thread_class',a:'set_status',p:{status:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    layList.switch('moderators',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'com.com_thread_class',a:'set_moderators',p:{moderators:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'com.com_thread_class',a:'set_moderators',p:{moderators:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    //快速编辑
    layList.edit(function (obj) {
        var id=obj.data.id,value=obj.value;
        switch (obj.field) {
            case 'name':
                action.set_Class('name',id,value);
                break;
            case 'sort':
                action.set_Class('sort',id,value);
                break;
            case 'summary':
                action.set_Class('summary',id,value);
                break;
        }
    });
    //监听并执行排序
    // layList.sort(['id','sort'],true);
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'com.com_thread_class',a:'set_status',q:{id:data.id,status:-1}});
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                })
                break;
        }
    })
</script>
{/block}
