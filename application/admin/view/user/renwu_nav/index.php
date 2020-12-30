{extend name="public/container"}

{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">

        </div>

        <!--产品列表-->
        <div class="layui-col-md12">

            <div class="layui-card">
                <div class="layui-card-header">导航列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="icon">
                        <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);" src="{{d.image}}">
                    </script>
                    <script type="text/html" id="status">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='status' lay-text='启用|禁用'  {{ d.status == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="act">
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
    layList.tableList('List',"{:Url('nav_list',['status'=>$status])}",function (){
        return [
            {field: 'id', title: 'ID', event:'id',width:'6%'},
            {field: 'title', title: '标题'},
            {field: 'content', title: '描述'},
            {field: 'image', title: '图片',templet:'#icon'},
            {field: 'link', title: '跳转路径',edit:'#url'},
            {field: 'sort', title: '排序',sort: true,event:'sort',edit:'sort'},
            {field: 'create_time', title: '创建时间'},
            {field: 'update_time', title: '更新时间'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'14%'},
        ];
    });
    //自定义方法
    var action= {
        quick_edit:function(field, id, value){
            layList.baseGet(layList.Url({c:'user.renwu_nav',a:'quick_edit',q:{field:field,id:id,value:value}}),function (res) {
                layList.msg(res.msg);
            });
        },
        remove:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                layList.basePost(layList.Url({c:'user.renwu_nav',a:'delete'}),{ids:ids},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择分类');
            }
        }
    };
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });


    //快速编辑
    layList.edit(function (obj) {
        var id=obj.data.id,value=obj.value;
        switch (obj.field) {
            case 'sort':
                action.quick_edit('sort',id,value);
                break;
        }
    });

</script>
{/block}
