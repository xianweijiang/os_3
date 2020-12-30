{extend name="public/container"}
{block name="content"}
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
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('get_system_vip_list')}",function (){
        return [
            {field: 'id', title: '编号', sort: true,event:'id',width:'3%'},
            {field: 'leixing', title: '任务类型',width:'7%'},
            {field: 'jifenflag', title: '积分标志',width:'7%'},
            {field: 'name', title: '任务名称' ,width:'7%'},
            {field: 'explain', title: '任务描述' ,width:'7%'},
            {field: 'icon', title: '图标'  , templet:'#icon',width:'7%'},
            {field: 'require', title: '完成要求' ,width:'7%'},
            {field: 'exp', title: '经验值',edit:'exp' ,width:'7%'},
            {field: 'fly', title: '社区积分',edit:'fly',width:'6%'},
            {field: 'gong', title: '贡献',edit:'gong' ,width:'8%'},
            {field: 'buy', title: '购物积分',edit:'buy',width:'6%'},
            {field: 'one', title: '自定义积分1',edit:'one',width:'6%'},
            {field: 'status', title: '状态',templet:'#is_show',width:'7%'},
            {field: 'right', title: '操作',toolbar:'#act',width:'15%'},
        ];
    });
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
            layList.baseGet(layList.Url({a:'set_show',p:{is_show:2,id:value}}),function (res) {
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
