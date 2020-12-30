{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">昵称/ID</label>
                                <div class="layui-input-block">
                                    <input type="text" name="nickname" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">时间范围</label>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="start_time" id="start_time" placeholder="开始时间" autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="end_time" id="end_time" placeholder="结束时间" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">邀请记录</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="userList" lay-filter="userList"></table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    require(['vue'],function(Vue) {
        new Vue({
            el: "#app",
            data: {
                badge:[],
            },
            mounted:function () {
                layList.form.render();
                layList.tableList('userList',"{:Url('userInviteList')}",function () {
                    return [
                        {field: 'id', title: 'ID', sort: true,event:'uid',width:'8%'},
                        {field: 'user', title: '被邀请人' },
                        {field: 'father_user', title: '邀请人'},
                        {field: 'code', title: '邀请码'},
                        {field: 'create_time', title: '邀请时间'},
                    ];
                });
                layList.date({elem:'#start_time',theme:'#393D49',type:'datetime'});
                layList.date({elem:'#end_time',theme:'#393D49',type:'datetime'});
                var that=this;
                layList.search('search',function(where){
                    if(where.start_time!=''){
                        if(where.end_time==''){
                            layList.msg('请选择结束时间');
                            return;
                        }
                    }
                    if(where.end_time!=''){
                        if(where.start_time==''){
                            layList.msg('请选择开始时间');
                            return;
                        }
                    }
                    layList.reload(where,true);
                });
            }
        })
    });

</script>
{/block}
