{extend name="public/container"}
{block name="content"}
<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
    <div class="layui-row layui-col-space15" id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                    </form>
                </div>
            </div>
        </div>
        <!--版块列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        榜单列表
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="layui-btn-container">
                        <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加数据</button>
                        <button class="layui-btn layui-btn-sm" data-type="del_all">批量下榜</button>
                        <button class="layui-btn layui-btn-sm" data-type='clear'>刷新缓存</button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="end_time">
                        {{#  if(d.end_time=='1970-01-01 08:00'){ }}
                        {{#  } else { }}
                        {{d.end_time}}
                        {{#  } }}
                    </script>
                    <script type="text/html" id="act">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit')}?id={{d.id}}')">编辑</button>
                        <button class="layui-btn layui-btn-xs" lay-event='delstor'>下榜</button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script src="{__FRAME_PATH}js/toast-js.js"></script>
<script>
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('rank_search_list')}",function (){
        join=[
            {type:'checkbox'},
            {field: 'id', title: 'ID',width:'4%'},
            {field: 'keyword', title: '热搜词'},
            {field: 'type', title: '属性'},
            {field: 'end_time', title: '到期时间',templet:'#end_time'},
            {field: 'sort', title: '排序',width:'5%',sort:true,event:'sort'},
            {field: 'num', title: '搜索量',sort:true,event:'num'},
            {field: 'status', title: '是否可用',templet:'#status',width:'6%'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'10%'},
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
            case 'sort':
                action.set_column('sort',id,value);
                break;
        }
    });
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'rank.rank_search',a:'rank_del',q:{id:data.id}});
                var code = {title:"操作提示",text:"确定要下榜吗？下榜以后不会再上榜，请谨慎操作！",type:'info',confirm:'是的，下榜'};
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
                },code)
                break;
        }
    })
    //自定义方法
    var action={
        //批量下榜
        del_all:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定要下榜吗？下榜以后不会再上榜，请谨慎操作！",type:'info',confirm:'是的，下榜'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'rank.rank_search',a:'del_all'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要审核的版块');
            }
        },
        clear:function () {
            var url=layList.U({c:'rank.rank_search',a:'clear',q:{}});
            var code = {title:"操作提示",text:"确定要刷新吗，刷新会更新前台榜单！",type:'info',confirm:'是的，刷新'};
            $eb.$swal('delete',function(){
                $eb.axios.get(url).then(function(res){
                    $eb.$swal('success',res.data.msg);
                }).catch(function(err){
                    $eb.$swal('error',err);
                });
            },code)
        }
    };
    //排序
    layList.sort(function (obj) {
        var type = obj.type;
        switch (obj.field){
            case 'num':
                layList.reload({order: layList.order(type,'num')},true,null,obj);
                break;
            case 'sort':
                layList.reload({order: layList.order(type,'sort')},true,null,obj);
                break;
        }
    });
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
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
