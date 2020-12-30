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
                    <!--<div class="layui-btn-container">
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加积分类型</button>
                        <button class="layui-btn-success layui-btn-sm" onclick="$eb.createModalFrame('编辑等级说明','{:Url('admin/user.level/edit_content')}?type=3')">积分规则说明</button>
                    </div>-->

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
            limits:[10,20],
            height : "full-125",
            id:'List',
            cols : [

                [
                    {field: 'id', title: '编号', sort: true,event:'id' ,rowspan:2,width:55},
                    {field: 'module', title: '所属模块',rowspan:2},
                    {field: 'actionflag', title: '行为标识',rowspan:2},
                    {field: 'actiontype', title: '行为类型',rowspan:2},
                    {field: 'actionname', title: '行为名称',rowspan:2},
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
            if(value < 0){
                layer.msg('分值需大于0');
                return
            }
            $.post("/index.php/admin/user.guize/set_value", {id: data.id, field: field,value:value}, function (data) {
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






    //自定义方法
    var action= {
        set_value: function (field, id, value) {
            layList.baseGet(layList.Url({
                a: 'set_value',
                q: {field: field, id: id, value: value}
            }), function (res) {
                layList.msg(res.msg);
            });
        },
    }
    //查询
    layList.search('search',function(where){

        //layList.reload(where,true);
    });
    layList.switch('is_show',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({a:'set_show',p:{is_show:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({a:'set_show',p:{is_show:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    //快速编辑
    layList.edit(function (obj) {
        var id=obj.data.id,value=obj.value;

        action.set_value(obj.field,id,value);

    });
    //监听并执行排序
    layList.sort(['id','sort'],true);
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
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
</script>
{/block}
