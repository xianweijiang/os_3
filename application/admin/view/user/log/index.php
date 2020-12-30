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
                <div class="layui-card-body">
                    <form class="layui-form">


                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <input type="text" class="layui-input searchVal" placeholder="请输入用户昵称">
                                </div>
                                <div class="layui-input-inline">
                                    <input type="text" class="layui-input" id="test1" placeholder="日期">
                                </div>
                                <a class="layui-btn search_btn" data-type="reload" style="width: 80px;height: 30px;line-height: 30px">搜索</a>
                            </div>

                    </form>
                    <table class="layui-hide" id="List" lay-filter="List"></table>

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
            limit:20,
            limits:[20,40],
            id:'List',
            cols : [
                {$head}
            ]
        });


        laydate.render({
            elem: '#test1' //指定元素
        });


        //搜索【此功能需要后台配合，所以暂时没有动态效果演示】
        $(".search_btn").on("click",function(){
            if($(".searchVal").val() != ''  || $("#test1").val() != '' ){
                table.reload("List",{
                    page: {
                        curr: 1 //重新从第 1 页开始
                    },
                    where: {
                        nickname: $(".searchVal").val(),  //搜索的关键字
                        date: $("#test1").val()  //搜索的关键字
                    }
                })
            }else{
                layer.msg("请输入搜索的内容");
            }
        });





    })
</script>
{/block}
