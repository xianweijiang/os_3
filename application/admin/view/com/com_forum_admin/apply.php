{extend name="public/container"}
{block name="content"}
<style>
    .position {
        left: 145px !important;
    }
</style>
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
    </div>
    <div class="layui-row layui-col-space15" >
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">版主申请列表</div>
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        版主申请列表
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                        <a class="layui-btn layui-btn-xs" href="{:Url('user.user/index')}?uid={{d.uid}}" >
                            查看用户
                        </a>
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit position">
                            <li>
                                {if condition="in_array('osapi_comforum',$open_list)"}
                                <a lay-event='approved' href="javascript:void(0);" >
                                    审核通过
                                </a>
                                {else/}
                                <a lay-event='unable' href="javascript:void(0);" >
                                    审核通过
                                </a>
                                {/if}
                            </li>
                            <li>
                                <a  onclick="$eb.createModalFrame('编辑资讯内容','{:Url('reject_reason')}?id={{d.id}}')">
                                   驳回
                                </a>
                            </li>
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
    setTimeout(function () {
        $('.alert-info').hide();
    },3000);
    //实例化form
    layList.form.render();
    // layList.date({elem:'#start_time',theme:'#393D49',type:'datetime'});
    // layList.date({elem:'#end_time',theme:'#393D49',type:'datetime'});
    //加载列表
    layList.tableList('List',"{:Url('admin_apply_list')}",function (){
        return [
            {field: 'id', title: 'ID',width:'4%'},
            {field: 'uid', title: 'UID',width:'5%'},
            {field: 'avatar', title: '头像',templet:'#avatar'},
            {field: 'nickname', title: '用户名',width:'8%',templet:'#nickname'},
            {field: 'forum', title: '版块',templet:'#forum'},
            {field: 'level', title: '申请权限级别'},
            {field: 'content', title: '申请理由'},
            {field: 'create_time', title: '申请时间'},
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
            case 'approved':
                var url=layList.U({c:'com.com_forum_admin',a:'approved',q:{id:data.id}});
                var code = {title:"操作提示",text:"你确定要审核通过吗？",type:'info',confirm:'是的，通过'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            layList.reload();
                        }else
                            return Promise.reject(res.data.msg || '审核失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
            case 'reject':
                var url=layList.U({c:'com.com_forum_admin',a:'reject',q:{id:data.id}});
                var code = {title:"操作提示",text:"你确定要审核驳回吗？",type:'info',confirm:'是的，驳回'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            layList.reload();
                        }else
                            return Promise.reject(res.data.msg || '审核失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
            case 'unable':
                var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
                $eb.$swal('delete',function(){
                    $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
                }, code)
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

    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
</script>
{/block}
