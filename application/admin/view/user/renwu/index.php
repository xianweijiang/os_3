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
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame('自定义任务','{:Url('admin/user.renwu/create')}?type=2')" style="margin-top: 10px">新增自定义任务</button>
                        {else/}
                        <button class="layui-btn layui-btn-sm" data-type="unable" style="margin-top: 10px">新增自定义任务</button>
                        {/if}
                    </div>

                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="icon" >
                        <img style="cursor: pointer" lay-event='open_icon' src="{{d.icon}}">
                    </script>
                    <script type="text/html" id="image">
                        <img style="cursor: pointer" lay-event='open_image' src="{{d.image}}">
                    </script>
                    <script type="text/html" id="is_show">
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_show' lay-text='启用|禁用'  {{ d.status == 1 ? 'checked' : '' }}>
                        {else/}
                        <input type='checkbox' name='id' lay-skin='switch' disabled value="{{d.id}}" lay-filter='is_show' lay-text='启用|禁用'  {{ d.status == 1 ? 'checked' : '' }}>
                        {/if}
                    </script>
                    <script type="text/html" id="act">
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url(\'create\')}?id={{d.id}}&type={{d.type}}')"><i class="fa fa-paste"></i> 编辑</button>
                        {else/}
                        <button class="btn btn-info btn-xs" type="button" lay-event='unable'><i class="fa fa-paste"></i> 编辑</button>
                        {/if}
                        {{#  if(d.type == '2'){ }}
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
            height : 800,
            limit:10,
            limits:[10,20],
            id:'List',
            cols : [
                        [
                            {field: 'id', title: '编号', sort: true,event:'id' ,rowspan:2,width:55},
                            {field: 'leixing', title: '任务类型' ,rowspan:2},
                            {field: 'jifenflag', title: '积分标志',rowspan:2},
                            {field: 'name', title: '任务名称'  ,rowspan:2},
                            {field: 'explain', title: '任务描述'  ,rowspan:2},
                            {field: 'require', title: '完成要求' ,rowspan:2 },
                            {field: 'icon', title: '图标'  , templet:'#icon',rowspan:2 },
                            { title: '积分奖励',colspan: {$count},align:'center'},
                            {field: 'status', title: '状态',templet:'#is_show',rowspan:2,width: 100 },
                            {field: 'right', title: '操作',toolbar:'#act' ,rowspan:2,width: 140}
                        ],
                         {$head}
            ]
        });



        table.on('edit(List)',function (obj) {
            console.log(obj.data)
            var value = obj.value //得到修改后的值
                ,data = obj.data //得到所在行所有键值
                ,field = obj.field; //得到字段
            $.post("/index.php/admin/user.renwu/set_value", {id: data.id, field: field,value:value}, function (data) {
                layer.msg(data.msg );

            });
        })



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
            // var index = layer.msg('设置中，请稍后', {icon: 16, time: false, shade: 0.8});

            setTimeout(function () {

                var status = data.elem.checked ?  1: 2;
                id = data.elem.value;

                $.post("/index.php/admin/user.renwu/set_value", {id: id, field: 'status',value:status}, function (data) {

                    layer.msg(data.msg );

                });
            }, 500);
        });


        //列表操作
        table.on('tool(List)', function(obj){
            var layEvent = obj.event, data = obj.data;

            switch (layEvent) {
                case 'delete':
                    console.log(333)
                    var url=layList.U({a:'delete',q:{id:data.id}});
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
                case 'open_image':
                    $eb.openImage(data.image);
                    break;
                case 'open_icon':
                    $eb.openImage(data.icon);
                    break;
            }
        });

    })
    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });
    var action={
        set_column:function(field,id,value){
            layList.baseGet(layList.Url({c:'shop.shop_column',a:'set_column',q:{field:field,id:id,value:value}}),function (res) {
                layList.msg(res.msg);
            });
        },
        unable:function(){
            var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
            $eb.$swal('delete',function(){
                $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
            }, code)
        },
    };
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'unable':
                var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
                $eb.$swal('delete',function(){
                    $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
                }, code)
                break;
        }
    })
</script>
{/block}
