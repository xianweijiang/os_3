{extend name="public/container"}
{block name="content"}

<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-tab layui-tab-brief" lay-filter="tab">
            </div>
            <div class="layui-card">
            </div>
        </div>
        <!--产品列表-->
        <div class="layui-col-md12" style="top: -23px">
            <div class="layui-card">
                <div class="layui-card-header">话题分类</div>
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        话题分类
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="layui-btn-container" style="margin-top: 10px">
                        <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加分类</button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="act">
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑话题','{:Url('edit')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        <a  class="layui-btn layui-btn-xs" href="{:Url('com.com_topic/index')}?class_id={{d.id}}" >
                            话题管理
                        </a>
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
    setTimeout(function () {
        $('.alert-info').hide();
    },3000);
    //实例化form
    layList.form.render();
    // layList.date({elem:'#start_time',theme:'#393D49',type:'datetime'});
    // layList.date({elem:'#end_time',theme:'#393D49',type:'datetime'});
    //加载列表
    layList.tableList('List',"{:Url('class_list')}",function (){
        return [
            {field: 'id', title: 'ID',width:'4%'},
            {field: 'name', title: '分类名称'},
            {field: 'sort', title: '排序'},
            {field: 'topic_count', title: '话题数'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'14%'},
        ];
    });

    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });

    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'com.com_topic_class',a:'del',q:{id:data.id}});
                var code = {title:"你确定要删除这条信息吗",text:"删除即取删除信息，无法恢复，请慎重操作！",confirm:'是的，我要删除'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            layList.reload();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                        layList.reload();
                    });
                },code)
                break;
            case 'open':
                var url=layList.U({c:'com.com_forum_admin',a:'open',q:{id:data.id}});
                var code = {title:"是否开启改版主",text:"开启后可再次禁用",confirm:'是的，我要开启'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            layList.reload();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                        layList.reload();
                    });
                },code)
                break;
            case 'close':
                var url=layList.U({c:'com.com_forum_admin',a:'close',q:{id:data.id}});
                var code = {title:"是否禁用该版主",text:"禁用后可再次开启",confirm:'是的，我要禁用'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            layList.reload();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                        layList.reload();
                    });
                },code)
                break;
        }
    })
</script>
{/block}
