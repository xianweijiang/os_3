{extend name="public/container"}
{block name="content"}
<style>
    .layui-table-body {
        position: relative;
        overflow: auto;
        margin-right: -1px;
        margin-bottom: -1px;
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">

        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">任务列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container">
                        <!--<button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加积分类型</button>-->
                        <!--<button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame('系统任务','{:Url('admin/user.renwu/create')}?type=1')">新增系统任务</button>-->
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame('自定义任务','{:Url('admin/user.renwu/create')}?type=2')">新增自定义任务</button>
                    </div>

                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="icon" >
                        <img style="cursor: pointer" lay-event='open_icon' src="{{d.icon}}">
                    </script>
                    <script type="text/html" id="image">
                        <img style="cursor: pointer" lay-event='open_image' src="{{d.image}}">
                    </script>
                    <script type="text/html" id="is_show">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_show' lay-text='启用|禁用'  {{ d.status == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="act">
                        <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url(\'create\')}?id={{d.id}}&type={{d.type}}')"><i class="fa fa-paste"></i> 编辑</button>
                        {{#  if(d.type == '1'){ }}
                        <button class="btn btn-warning btn-xs del_config_tab"   lay-event='delete' type="button"   ><i class="fa fa-warning"></i> 删除</button>
                        {{#  } }}

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
    layui.use(['form','layer','laydate','table','laytpl'],function(){
        var form = layui.form,
            layer = parent.layer === undefined ? layui.layer : top.layer,
            $ = layui.jquery,
            laydate = layui.laydate,
            laytpl = layui.laytpl,
            table = layui.table;

        //用户列表
        var tableIns = table.render({
            elem: '#List',
            url : "{:Url('get_system_vip_list')}",
            cellMinWidth : 65,
            page : true,
            limit:10,
            limits:[5,10,15,20],
            height : "full-125",
            id:'List',
            cols : [
                        [
                            {field: 'id', title: '编号', sort: true,event:'id' ,rowspan:2,width:75},
                            {field: 'leixing', title: '任务类型' ,rowspan:2},
                            {field: 'jifenflag', title: '积分标志',rowspan:2},
                            {field: 'name', title: '任务名称'  ,rowspan:2},
                            {field: 'explain', title: '任务描述'  ,rowspan:2},
                            {field: 'icon', title: '图标'  , templet:'#icon',rowspan:2 },
                            {field: 'require', title: '完成要求' ,rowspan:2 },
                            {field: 'exp', title: '经验值',edit:'exp' ,rowspan:2 },
                            { title: '积分奖励',colspan: 4,align:'center'},
                            {field: 'status', title: '状态',templet:'#is_show',rowspan:2 },
                            {field: 'right', title: '操作',toolbar:'#act' ,rowspan:2}
                        ],
                        [
                            {field: 'fly', title: '社区积分',edit:'fly' },
                            {field: 'gong', title: '贡献',edit:'gong'  },
                            {field: 'buy', title: '购物积分',edit:'buy' },
                            {field: 'one', title: '自定义积分1',edit:'one' },
                        ]
            ]
        });







        //搜索【此功能需要后台配合，所以暂时没有动态效果演示】
        $(".search_btn").on("click",function(){
            if($(".searchVal").val() != ''){
                table.reload("usersListTable",{
                    page: {
                        curr: 1 //重新从第 1 页开始
                    },
                    where: {
                        key: $(".searchVal").val()  //搜索的关键字
                    }
                })
            }else{
                layer.msg("请输入搜索的内容");
            }
        });
        //修改用户组状态
        form.on('switch(is_show)', function (data) {
            var index = layer.msg('设置中，请稍后', {icon: 16, time: false, shade: 0.8});
            console.log(123)
            setTimeout(function () {
                layer.close(index);
                var status = data.elem.checked ?  1: 2;
                id = data.elem.value;
                //alert(status);
                $.post("/index.php/admin/user.renwu/set_value", {id: id, field: 'status',value:status}, function (data) {
                    console.log(data)
                    var icon = 5;
                    if (data.code) {
                        icon = 6;
                    }
                    layer.msg(data.msg, {icon: icon, time: 1500});
                    setTimeout(function(){ location.reload() },500);
                });
            }, 500);
        });
        //添加用户
        function add(){
            var index = layui.layer.open({
                title : "添加模板消息",
                type : 2,
                content : "/admin/che_edit.html",
                success : function(layero, index){
                    var body = layui.layer.getChildFrame('body', index);
                    setTimeout(function(){
                        layui.layer.tips('点击此处返回列表', '.layui-layer-setwin .layui-layer-close', {
                            tips: 3
                        });
                    },500)
                }
            })
            layui.layer.full(index);
            window.sessionStorage.setItem("index",index);
            //改变窗口大小时，重置弹窗的宽高，防止超出可视区域（如F12调出debug的操作）
            $(window).on("resize",function(){
                layui.layer.full(window.sessionStorage.getItem("index"));
            })
        }
        //编辑用户
        function edit(uid){
            var index = layui.layer.open({
                title : "编辑用户",
                type : 2,
                content : "/admin/che_edit.html"+'?id='+uid,
                success : function(layero, index){
                    var body = layui.layer.getChildFrame('body', index);
                    setTimeout(function(){
                        layui.layer.tips('点击此处返回列表', '.layui-layer-setwin .layui-layer-close', {
                            tips: 3
                        });
                    },500)
                }
            })
            layui.layer.full(index);
            window.sessionStorage.setItem("index",index);
            //改变窗口大小时，重置弹窗的宽高，防止超出可视区域（如F12调出debug的操作）
            $(window).on("resize",function(){
                layui.layer.full(window.sessionStorage.getItem("index"));
            })
        }


        //查看记录
        function edit1(uid){
            var index = layui.layer.open({
                title : "查看记录",
                type : 2,
                content : "/admin/che_jilu.html"+'?id='+uid,
                success : function(layero, index){
                    var body = layui.layer.getChildFrame('body', index);
                    setTimeout(function(){
                        layui.layer.tips('点击此处返回列表', '.layui-layer-setwin .layui-layer-close', {
                            tips: 3
                        });
                    },500)
                }
            })
            layui.layer.full(index);
            window.sessionStorage.setItem("index",index);
            //改变窗口大小时，重置弹窗的宽高，防止超出可视区域（如F12调出debug的操作）
            $(window).on("resize",function(){
                layui.layer.full(window.sessionStorage.getItem("index"));
            })
        }

        $(".adduser_btn").click(function(){
            add();
        })

        //列表操作
        table.on('tool(usersList)', function(obj){
            var layEvent = obj.event, data = obj.data;
            if(layEvent === 'edit'){ //编辑
                edit(data.id);
            } else if(layEvent === 'del'){ //删除
                /* layer.confirm('确定删除操作？',{icon:3, title:'提示信息'},function(index){
                     $.post("/admin/delete.html",{uid:data.uid},function(data){
                         var icon=5;
                         if(data.code){
                             icon=6;
                         }
                         layer.msg(data.msg, {icon:icon,time: 1500}, function () {
                             if(data.code){
                                 obj.del();
                             }
                         });
                     })
                 });*/
                deleteAmTables('che',data.id)
            }
            else if(layEvent === 'edit_jl'){ //查看记录
                edit1(data.id);
            }
        });

    })
</script>
{/block}
