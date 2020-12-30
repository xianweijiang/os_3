{extend name="public/container"}
{block name="content"}

<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-tab layui-tab-brief" lay-filter="tab">
            </div>
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">选择版块</label>
                                <div class="layui-input-block">
                                    <select name="fid">
                                        <option value="">所有版块</option>
                                        {volist name='forum' id='vo'}
                                        <option value="{$vo.id}">{$vo.html}{$vo.name}</option>
                                        {/volist}
                                    </select>
                                </div>
                            </div>
                            <!--<div class="layui-inline" style="margin-left: -26px">
                                <label class="layui-form-label">所属版块:</label>
                                <div class="layui-input-inline" style="width: 173px;margin-left: 42px">
                                    <select name="fid" v-model="where.fid" lay-filter="fid">
                                        <option value="">全部</option>
                                        {volist name='forum' id='vo'}
                                        <option value="{$vo.id}" {eq name="vo.pid" value="0"} disabled{/eq}>{$vo.html}{$vo.name}</option>
                                        {/volist}
                                    </select>
                                </div>
                            </div>-->
                            <div class="layui-inline">
                                <label class="layui-form-label">权限级别</label>
                                <div class="layui-input-block">
                                    <select name="level">
                                        <option value="">全部</option>
                                        <option value="1">版主</option>
                                        <option value="2">超级版主</option>
                                    </select>
                                </div>
                            </div>

                            <div class="layui-inline">
                                <label class="layui-form-label">关键词</label>
                                <div class="layui-input-block">
                                    <input type="text" name="uid" class="layui-input" placeholder="请输入用户昵称或者UID">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
                                    <button onclick="javascript:layList.reload();" type="reset" class="layui-btn layui-btn-primary layui-btn-sm">
                                            <i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">版主列表</div>
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        版主列表
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="layui-btn-container">
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('set_admin')}')" style="margin-top: 10px">版主设置</button>
                        {else/}
                        <button type="button" class="layui-btn layui-btn-sm" data-type="unable" style="margin-top: 10px">版主设置</button>
                        {/if}
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="avatar">
                        <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);" src="{{d.user.avatar}}">
                    </script>
                    <script type="text/html" id="top">
                        <p>置顶:{{d.top}} </p>
                        <p>加精:{{d.essence}}</p>
                        <p>推荐:{{d.recommend}}</p>
                        <p>标题加粗:{{d.light}}</p>
                        <p>删帖:{{d.del}}</p>
                    </script>
                    <script type="text/html" id="forum">
                        <p>{{d.forum.name}} </p>
                        <p>帖子数:{{d.forum.post_count}}</p>
                        <p>关注数:{{d.forum.member_count}}</p>
                    </script>
                    <script type="text/html" id="nickname">
                        <p>{{d.user.nickname}} </p>
                    </script>
                    <script type="text/html" id="admin">
                        <p>{{d.admin}} </p>
                        <p>管理员</p>
                    </script>
                    <script type="text/html" id="status">
                        {{# if(d.status == 1){ }}
                        <i class="fa fa-check text-navyr"></i>
                        {{# }else{ }}
                        <i class="fa fa-close text-danger"></i>
                        {{# } }}
                    </script>
                    <script type="text/html" id="act">
                        {{# if(d.status == 1){ }}
                        <button class="layui-btn layui-btn-xs" lay-event='close'>
                            <i class="fa fa-warning"></i> 禁用
                        </button>
                        {{# }else{ }}
                        <button class="layui-btn layui-btn-xs" lay-event='open'>
                            <i class="fa fa-warning"></i> 启用
                        </button>
                        {{# } }}
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
    layList.tableList('List',"{:Url('admin_list')}",function (){
        return [
            {field: 'id', title: 'ID',width:'4%'},
            {field: 'uid', title: 'UID',width:'5%'},
            {field: 'avatar', title: '头像',templet:'#avatar'},
            {field: 'nickname', title: '昵称',width:'8%',templet:'#nickname'},
            {field: 'level', title: '权限级别'},
            {field: 'forum', title: '管理版块',templet:'#forum'},
            {field: 'admin', title: '操作人',templet:'#admin'},
            {field: 'create_time', title: '时间'},
            {field: 'top', title: '数据统计',templet:'#top'},
            {field: 'status', title: '状态',templet:'#status',width:'4%'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'14%'},
        ];
    });

    var action={
        unable:function(){
            var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
            $eb.$swal('delete',function(){
                $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
            }, code)
        },
    };
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
                var url=layList.U({c:'com.com_forum_admin',a:'del',q:{id:data.id}});
                var code = {title:"你确定要删除这条信息吗",text:"删除即取消权限并删除历史记录，无法恢复，请慎重操作！",confirm:'是的，我要删除'};
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
                var code = {title:"是否开启该版主",text:"开启后可再次禁用",confirm:'是的，我要开启'};
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

    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
</script>
{/block}
