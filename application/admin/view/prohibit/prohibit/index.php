{extend name="public/container"}
{block name="head_top"}
<script src="{__FRAME_PATH}js/plugins/chosen/chosen.jquery.js"></script>
<style>
    .layui-table-cell p{
        height: auto !important;
        line-height: !important;
    }
    .layui-form input[type=checkbox], .layui-form input[type=radio], .layui-form select {
        display: none !important;
    }
    .layui-inline:first-of-type {
        margin-right: 40px !important;
    }
</style>
{/block}
{block name="content"}
<div class="layui-fluid" >
    <div class="layui-row layui-col-space15" id="app">
        <!--搜索条件-->
        <div class="layui-col-md12" style="margin-top: -20px">
            <div class="layui-tab layui-tab-brief" lay-filter="tab">
                <ul class="layui-tab-title" style="background-color: #fff;
                    position: relative;
                    top: 10px;">
                    <li lay-id="list" {eq name='type' value='1'}class="layui-this" {/eq} >
                    <a href="{eq name='type' value='1'}javascript:;{else}{:Url('index',['type'=>1])}{/eq}">版块禁言</a>
                    </li>
                    <li lay-id="list" {eq name='type' value='2'}class="layui-this" {/eq} >
                    <a href="{eq name='type' value='2'}javascript:;{else}{:Url('index',['type'=>2])}{/eq}">全站禁言</a>
                    </li>
                </ul>
            </div>
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body" style="padding: 0; margin-top: 12px">
                    <form class="layui-form">
                        <div class="layui-carousel layadmin-carousel layadmin-shortcut" lay-anim="" lay-indicator="inside" lay-arrow="none" style="background:none">
                            <div class="layui-card-body ">
                                <div class="layui-row layui-col-space10 layui-form-item">
                                    <div class="layui-col-lg12">
                                        {if condition="$type eq 1"}
                                        <div class="layui-inline">
                                            <label class="layui-form-label">状态:</label>
                                            <div class="layui-input-block" style="width: 173px">
                                                <select name="status" v-model="where.status" lay-filter="status">
                                                    <option value="">全部</option>
                                                    <option value="1">禁言中</option>
                                                    <option value="2">已解禁</option>
                                                    <option value="0">失效禁言</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="layui-inline" style="margin-left: -26px">
                                            <label class="layui-form-label">禁言原因:</label>
                                            <div class="layui-input-inline" style="width: 173px;margin-left: 42px">
                                                <select name="reason" v-model="where.reason" lay-filter="reason">
                                                    <option value="">全部</option>
                                                    {volist name='reason' id='vo'}
                                                    <option value="{$vo.id}">{$vo.name}</option>
                                                    {/volist}
                                                </select>
                                            </div>
                                        </div>
                                        <div class="layui-inline">
                                            <label class="layui-form-label">禁言时长:</label>
                                            <div class="layui-input-inline" style="width: 173px;margin-left: 42px">
                                                <select name="time" v-model="where.time" lay-filter="time">
                                                    <option value="">全部</option>
                                                    {volist name='time' id='vo'}
                                                    <option value="{$vo.id}">{$vo.num}{$vo.time_type}</option>
                                                    {/volist}
                                                </select>
                                            </div>
                                        </div>
                                        <div class="layui-inline">
                                            <label class="layui-form-label">执行人:</label>
                                            <div class="layui-input-block" style="width: 173px">
                                                <select name="identity" v-model="where.identity" lay-filter="identity">
                                                    <option value="">全部</option>
                                                    <option value="1">后台管理员</option>
                                                    <option value="2">超级版主</option>
                                                    <option value="3">版主</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="layui-col-lg12">
                                            <label class="layui-form-label">时间:</label>
                                            <div class="layui-input-block" data-type="data" v-cloak="">
                                                <button class="layui-btn layui-btn-sm" type="button" v-for="item in dataList" @click="setData(item)" :class="{'layui-btn-primary':where.data!=item.value}" style="margin-top: 0px">{{item.name}}</button>
                                                <button class="layui-btn layui-btn-sm" type="button" ref="time" @click="setData({value:'zd',is_zd:true})" :class="{'layui-btn-primary':where.data!='zd'}" style="margin-top: 0px">自定义</button>
                                                <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" v-show="showtime==true" ref="date_time" style="margin-top: 0px">{$year.0} - {$year.1}</button>
                                            </div>
                                        </div>
                                        <div class="layui-col-lg12">
                                            <div class="layui-input-block">
                                                <!-- <div class="layui-col-lg12 " style="margin-bottom: 10px">
                                                   <input type="checkbox" name="more" lay-skin="primary" title="更多选项">
                                                </div> -->
                                                <button @click="search" type="button" class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top: 8px">
                                                    <i class="layui-icon layui-icon-search"></i>搜索</button>
                                                <!-- <button @click="excel" type="button" class="layui-btn layui-btn-warm layui-btn-sm export" type="button">
                                                    <i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出</button> -->
                                                <button @click="refresh" type="reset" class="layui-btn layui-btn-primary layui-btn-sm" style="margin-top: 8px">
                                                    <i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                                            </div>
                                        </div>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--end-->

    </div>
    <!--列表-->
    <div class="layui-row layui-col-space15" >
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">禁言列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container" style="display:flex;justify-content: space-between;align-items:center;margin-top: 10px">
                        <div>
                            {if condition="$type eq 1"}
                            <button class="layui-btn layui-btn-sm" data-type="relieve_all">批量解除</button>
                            {/if}
                        </div>
                    </div>
                    <table class="layui-hide" id="userList" lay-filter="userList"></table>
                    <script type="text/html" id="avatar">
                        <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);" src="{{d.avatar}}">
                    </script>
                    <script type="text/html" id="operation_uid">
                        {{# if(d.operation_uid>0){ }}
                        <p>{{d.operation_nickname}}</p>
                        <p>{{d.operation_identity}}</p>
                        {{# }else{ }}
                        {{# } }}
                    </script>
                    <script type="text/html" id="relieve_uid">
                        {{# if(d.relieve_uid>0){ }}
                        <p>{{d.relieve_nickname}}</p>
                        <p>{{d.relieve_identity}}</p>
                        {{# }else{ }}
                        {{# } }}
                    </script>
                    <script type="text/html" id="act_common">
                        {if condition="$type eq 1"}
                        {{# if(d.status==1){ }}
                        <button class="layui-btn layui-btn-xs" lay-event='relieve'>解除禁言</button>
                        {{# } }}
                        {/if}
                    </script>
                </div>
            </div>
        </div>
    </div>
    <!--end-->
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
layList.tableList('userList',"{:Url('prohibit_list',['type'=>$type])}",function () {
    var join = [
            {type:'checkbox'},
            {field: 'id', title: 'ID', event:'id',width:'6%'},
            {field: 'avatar', title: '头像',templet:'#avatar',width:'6%'},
            {field: 'nickname', title: '用户名',width:'6%'},
            {field: 'forum_name', title: '禁言版块',width:'6%'},
            {field: 'create_time', title: '禁言时间',width:'8%'},
            {field: 'time', title: '禁言时长',width:'6%'},
            {field: 'reason', title: '禁言原因'},
            {field: 'count', title: '历史累计次数',width:'9%'},
            {field: 'status_type', title: '用户状态',width:'6%'},
            {field: 'operation_uid', title: '禁言人',templet:'#operation_uid'},
            {field: 'relieve_uid', title: '解禁人',templet:'#relieve_uid'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'10%'},
        ];
    return join
});

    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'relieve':
                var url=layList.Url({c:'prohibit.prohibit',a:'relieve',q:{id:data.id}});
                var code = {title:"操作提示",text:"你确定要解除禁言吗？",type:'info',confirm:'是的,解除'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '解除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
        }
    })

    //自定义方法
    var action={
        // 审核
        relieve_all:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定解除禁言吗？",type:'info',confirm:'是的，解除禁言'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'prohibit.prohibit',a:'relieve_all'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要审核的版块');
            }
        },
    };
    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });

    require(['vue'],function(Vue) {
        new Vue({
            el: "#app",
            data: {
                badge: [],
                dataList: [
                    {name: '全部', value: ''},
                    {name: '昨天', value: 'yesterday'},
                    {name: '今天', value: 'today'},
                    {name: '本周', value: 'week'},
                    {name: '本月', value: 'month'},
                    {name: '本季度', value: 'quarter'},
                    {name: '本年', value: 'year'},
                ],
                where:{
                    data:'',
                    identity:'',
                    time:'',
                    status:'',
                    reason:'',
                    excel: 2
                },
                showtime: false,
            },
            methods: {
                setData:function(item){
                    var that=this;
                    if(item.is_zd==true){
                        that.showtime=true;
                        this.where.data=this.$refs.date_time.innerText;
                    }else{
                        this.showtime=false;
                        this.where.data=item.value;
                    }
                },
                getBadge:function() {
                    var that=this;
                    layList.basePost(layList.Url({c:'order.store_order',a:'getBadge'}),this.where,function (rem) {
                        that.badge=rem.data;
                    });
                },
                search:function () {
                    this.where.excel=0;
                    this.getBadge();
                    layList.reload(this.where,true);
                },
                refresh:function () {
                    layList.reload();
                    this.getBadge();
                },
                excel:function () {
                    this.where.excel=1;
                    location.href=layList.Url({c:'order.store_order',a:'order_list',q:this.where});
                }
            },
            mounted:function () {
                var that=this;
                that.getBadge();
                layList.laydate.render({
                    elem:this.$refs.date_time,
                    trigger:'click',
                    eventElem:this.$refs.time,
                    range:true,
                    change:function (value){
                        that.where.data=value;
                    }
                });
                layList.form.render();
            }
        })
    });

</script>
{/block}