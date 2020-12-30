{extend name="public/container"}

{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" style="margin-top: -27px">
        <div class="layui-col-md12">
            <div class="layui-tab layui-tab-brief" lay-filter="tab">
                <ul class="layui-tab-title" style="background-color: white;top: 10px">
                    <li lay-id="list" {eq name='status' value='2'}class="layui-this" {/eq}>
                    <a href="{eq name='status' value='2'}javascript:;{else}{:Url('index',['status'=>2, 'type'=>$type])}{/eq}">待审核</a>
                    </li>
                    <li lay-id="list" {eq name='status' value='1'}class="layui-this" {/eq} >
                    <a href="{eq name='status' value='1'}javascript:;{else}{:Url('index',['status'=>1, 'type'=>$type])}{/eq}">已审核</a>
                    </li>
                    <li lay-id="list" {eq name='status' value='0'}class="layui-this" {/eq}>
                    <a href="{eq name='status' value='0'}javascript:;{else}{:Url('index',['status'=>0, 'type'=>$type])}{/eq}">已驳回</a>
                    </li>
                </ul>
            </div>
            <div class="layui-card" id="app">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body" style="padding: 0; margin-top: 12px">
                    <form class="layui-form">
                        <div class="layui-carousel layadmin-carousel layadmin-shortcut" lay-anim="" lay-indicator="inside" lay-arrow="none" style="background:none">
                            <div class="layui-card-body ">
                                <div class="layui-row layui-col-space10 layui-form-item">
                                    <div class="layui-col-lg12">

                                        <div class="layui-inline" >
                                            <label class="layui-form-label">版块名称:</label>
                                            <div class="layui-input-inline" style="width: 173px;margin-left: 42px">
                                                <select name="fid" v-model="where.fid" lay-filter="fid">
                                                    <option value="">全部</option>
                                                    {volist name='cate' id='vo'}
                                                    <option value="{$vo.id}" {eq name="vo.pid" value="0"} disabled{/eq}>{$vo.html}{$vo.name}</option>
                                                    {/volist}
                                                </select>
                                            </div>
                                        </div>

                                        <div class="layui-inline">
                                            <label class="layui-form-label" style="margin-left: -26px">申请人:</label>
                                            <div class="layui-input-inline" style="margin-left: 56px">
                                                <input type="text" name="real_name" v-model="where.real_name" lay-verify="title" autocomplete="off" placeholder="填写申请人昵称或uid" class="layui-input" style="width: 173px;padding-left: 5px">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="layui-col-lg12">
                                        <label class="layui-form-label">申请时间:</label>
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
                                            <button @click="search" type="button" class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top: 0px">
                                                <i class="layui-icon layui-icon-search"></i>搜索</button>
                                            <!-- <button @click="excel" type="button" class="layui-btn layui-btn-warm layui-btn-sm export" type="button">
                                                <i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出</button> -->
                                            <button @click="refresh" type="reset" class="layui-btn layui-btn-primary layui-btn-sm" style="margin-top: 0px">
                                                <i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                                        </div>
                                    </div>
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
                <div class="layui-card">
                    <div class="layui-card-header">用户列表</div>
                    <div class="layui-card-body">
                        <div class="layui-btn-container" style="display:flex;justify-content: space-between;align-items:center;margin-top: 10px">
                            <div>
                                <button class="layui-btn layui-btn-sm" onclick="pass_forum()">批量通过</button>
                                <button class="layui-btn layui-btn-sm" onclick="delete_forum()">批量驳回</button>
                            </div>
                        </div>
                        <table class="layui-hide" id="List" lay-filter="List"></table>
                        <script type="text/html" id="act_common">
                            <button type="button" class="layui-btn layui-btn-xs" lay-event="see"><i class="fa fa-paste"></i>详情</button>
                        </script>
                        <script type="text/html" id="img">
                           <img src="{{d.avatar}}">
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    layList.tableList('List',"{:Url('thread_list',['status'=>$status,'real_name'=>$real_name])}",function (){
        var join = [
            {type:'checkbox'},
            {field: 'id', title: 'ID', event:'id',width:'3%'},
            {field: 'nickname', title: '申请人',templet:'#title',width:'13%'},
            {field: 'avatar', title: '头像',width:'10%',toolbar:'#img'},
            {field: 'f_name', title: '申请版块',width:'10%'},
            {field: 'reason', title: '申请理由',width:'10%'},
            {field: 'count', title: '申请次数',width:'6%'},
            {field: 'time', title: '时间',width:'18%',},
            {field: 'status', title: '审核状态',width:'9%'},
            {field: 'audit_name', title: '审核人',width:'6%'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'9%'},
        ];
        return join;
    });
    //自定义方法
    var action={
        // 批量删除
        delete:function(field,id,value){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                layList.basePost(layList.Url({c:'com.com_post',a:'delete'}),{ids:ids},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要删除的评论');
            }
        },
        // 清理
        remove:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                layList.basePost(layList.Url({c:'com.com_post',a:'remove'}),{ids:ids},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要清理的评论');
            }
        },
        restore:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                layList.basePost(layList.Url({c:'com.com_post',a:'restore'}),{ids:ids},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要还原的评论');
            }
        }
    };
    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });

    layList.switch('status',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'com.com_post',a:'quick_edit',p:{value:1,field:'status',id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'com.com_post',a:'quick_edit',p:{value:0,field:'status', id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });

    //监听并执行排序
    // layList.sort(['id','sort'],true);
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'see':
                $eb.createModalFrame(data.nickname+'-会员详情',layList.Url({c:'user.user',a:'see',p:{uid:data.uid}}));
                break;
        }
    })
    // 批量驳回
    function delete_forum(){
        var ids=layList.getCheckData().getIds('id');
        if(ids.length){
            var str='';
            for(var i=0;i<ids.length;i++){
                str+=ids[i]+',';
            }
            if (str.length > 0) {
                str = str.substr(0, str.length - 1);
            }
            $eb.createModalFrame('驳回理由',"{:Url('set_reason')}?id="+str);
        }else{
            layList.msg('请选择要批量驳回的申请');
        }
    }
    // 批量通过
    function pass_forum(){
        var ids=layList.getCheckData().getIds('id');
        if(ids.length){
            var str='';
            for(var i=0;i<ids.length;i++){
                str+=ids[i]+',';
            }
            if (str.length > 0) {
                str = str.substr(0, str.length - 1);
            }
            var url=layList.U({c:'group.visit_audit',a:'set_audit',q:{id:str, field:'status', value:1}});
            var code = {title:"操作提示",text:"你确定要通过这些用户的申请吗？",type:'info',confirm:'是的,我要审核通过'};
            $eb.$swal('delete',function(){
                $eb.axios.get(url).then(function(res){
                    if(res.status == 200 && res.data.code == 200) {
                        $eb.$swal('success','审核成功');
//                        obj.del();
                        setTimeout(function () {
//                            var index = parent.layer.getFrameIndex(window.name);
//                            parent.layer.close(index);
//                            parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
//                            console.log($(".page-tabs-content .active").index());
                            window.frames[$(".page-tabs-content .active").index()].location.reload();
                        },1500)
                    }else
                        return Promise.reject('审核失败')
                }).catch(function(err){
                    $eb.$swal('error',err);
                });
            }, code)
        }else{
            layList.msg('请选择要批量通过的申请');
        }
    }
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
                    fid:'',
                    type:'',
                    name:'',
                    uid:''
                },
                showtime: false,
            },
            watch: {

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
                search:function () {
                    layList.reload(this.where,true);
                },
                refresh:function () {
                    layList.reload();
                }
            },
            mounted:function () {
                var that=this;
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
                layList.form.on("select(fid)", function (data) {
                    that.where.fid = data.value;
                });
                layList.form.on("select(type)", function (data) {
                    that.where.type = data.value;
                });
            }
        })
    });
</script>
{/block}
