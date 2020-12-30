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
                <div class="layui-card-header">积分规则编辑</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container">
                        <!--<button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加积分类型</button>-->
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame('签到积分规则说明','{:Url('admin/user.level/edit_content')}?type=4')" style="margin-top: 10px">签到积分规则说明</button>
                    </div>

                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="icon" >
                        <img style="cursor: pointer" lay-event='open_icon' src="{{d.icon}}">
                    </script>
                    <script type="text/html" id="image">
                        <img style="cursor: pointer" lay-event='open_image' src="{{d.image}}">
                    </script>

                    <script type="text/html" id="act">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0)" onclick="$eb.createModalFrame(this.innerText,'{:Url(\'create\')}?id={{d.id}}')">
                                    <i class="fa fa-paste"></i> 编辑{{#console.log(d)}}
                                </a>
                            </li>
                            {{#  if(d.leixing == '自定义积分'){ }}
                                <li>
                                    <a lay-event='delete' href="javascript:void(0)" >
                                        <i class="fa fa-paste"></i> 删除
                                    </a>
                                </li>
                            {{#  } }}

                        </ul>
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
                    {field: 'id', title: '编号', sort: true,event:'id' ,rowspan:2,width:55},
                    { field: 'name',  title: '第几天' ,rowspan:2},
                    {$str}


                ],
                {$secnod}
            ]
        });



        table.on('edit(List)',function (obj) {
            console.log(obj.data)
            var value = obj.value //得到修改后的值
                ,data = obj.data //得到所在行所有键值
                ,field = obj.field; //得到字段
            $.post("/index.php/admin/user.jifen/set_value", {id: data.id, field: field,value:value}, function (data) {
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


</script>
{/block}
