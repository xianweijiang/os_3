{extend name="public/container"}

{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" style="margin-top: -27px">
        <div class="layui-col-md12">
            <div class="layui-tab layui-tab-brief" lay-filter="tab">
                <ul class="layui-tab-title" style="background-color: white;top: 10px">
                    {eq name="status" value="-1"}
                    <li lay-id="list">
                        <a href="{:Url('index')}">评论列表</a>
                    </li>
                    <li lay-id="list" class="layui-this">
                        <a href="javascript:;">评论回收站</a>
                    </li>
                    {else /}
                    <li lay-id="list" class="layui-this">
                        <a href="javascript:;">评论列表</a>
                    </li>
                    <li lay-id="list">
                        <a href="{:Url('index',['status'=>-1,])}">评论回收站</a>
                    </li>
                    {/eq}
                </ul>
            </div>
            <div class="layui-card" id="app">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body" style="padding-bottom: 20px">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                            <div class="layui-inline">
                                <label class="layui-form-label">所有分类:</label>
                                <div class="layui-input-block">
                                    <select name="fid" v-model="where.fid" lay-filter="fid">
                                        <option value="">所有主题分类</option>
                                        {volist name="cate" id="vo"}
                                        <option value="{$vo.id}">{$vo.html}{$vo.name}</option>
                                        {/volist}
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">主题内容类型:</label>
                                <div class="layui-input-block">
                                    <select name="type" v-model="where.type" lay-filter="type">
                                        <option value="">全部</option>
                                        <option value="1">帖子</option>
                                        <option value="2">动态</option>
                                        <option value="4">资讯</option>
                                        <option value="6">视频</option>
                                        <option value="8">聚合</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">评论内容:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="name" v-model="where.name" lay-filter="name" class="layui-input" placeholder="请输入内容">
                                </div>
                            </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">作者:</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="uid" v-model="where.uid" lay-filter="uid" class="layui-input" placeholder="请输入昵称或uid">
                                    </div>
                                </div>
                                <div class="layui-col-lg12" style="display: flex;align-items: baseline">
                                    <label class="layui-form-label">创建时间:</label>
                                    <div class="layui-input-block" data-type="data" v-cloak="" style="margin-left: 13px;">
                                        <button class="layui-btn layui-btn-sm" type="button" v-for="item in dataList" @click="setData(item)" :class="{'layui-btn-primary':where.data!=item.value}">{{item.name}}</button>
                                        <button class="layui-btn layui-btn-sm" type="button" ref="time" @click="setData({value:'zd',is_zd:true})" :class="{'layui-btn-primary':where.data!='zd'}">自定义</button>
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" v-show="showtime==true" ref="date_time">{$year.0} - {$year.1}</button>
                                    </div>
                                </div>
                            <div class="layui-col-lg12" style="float: left;left: 123px;top: 10px;">
                                <div class="layui-input-inline">
                                    <button @click="search" type="button"
                                            class="layui-btn layui-btn-sm layui-btn-normal">
                                        <i class="layui-icon layui-icon-search"></i>搜索
                                    </button>
                                    <button @click="refresh" type="reset" class="layui-btn layui-btn-primary layui-btn-sm">
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
                <div class="layui-card-header">评论列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container" style="margin-top: 10px">
                        {eq name="status" value="-1"}
                        <button class="layui-btn layui-btn-sm" data-type="restore">还原</button>
                        <button class="layui-btn layui-btn-sm" data-type="remove">清理</button>
                        {else /}
                        <button class="layui-btn layui-btn-sm" data-type="delete">批量删除</button>
                        {/eq}
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="status">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='status' lay-text='启用|禁用'  {{ d.status == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="content">
                        <div style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">{{d.content}}</div>
                    </script>
                    <script type="text/html" id="thread_content">
                        <a href="javascript:void(0);" onclick="$eb.createModalFrame('编辑帖子内容','{:Url('admin/com.com_thread/edit')}?id={{d.thread_id}}')">
                        <div style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">{{d.thread_title}}</div>
                        </a>
                    </script>
                    <script type="text/html" id="fid_name">
                        <div style="font-weight:bold">{{d.fid_name}}</div>
                        <div>—{{d.class_name}}</div>
                    </script>
                    <script type="text/html" id="time">
                        <div>创建时间：{{d.create_time}}</div>
                        {{# if (d.status == -1) { }}
                        <div>删除时间：{{d.del_time}}</div>
                        {{# } }}
                    </script>
                    <script type="text/html" id="count">
                        <div>赞：{{d.support_count}}</div>
                    </script>
                    <script type="text/html" id="act">
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('详情','{:Url('view')}?id={{d.id}}')">
                            详情
                        </button>
                        {{# if (d.status == 2) { }}
                        <button class="layui-btn layui-btn-xs" lay-event='verify'>
                            审核通过
                        </button>
                        {{# } }}
                        {{# if (d.status != -1) { }}
                        <button class="layui-btn layui-btn-xs" lay-event='delstor'>
                            <i class="fa fa-warning"></i> 删除
                        </button>
                        {{# } }}
                        {{# if (d.status == -1) { }}
                        <button class="layui-btn layui-btn-xs" lay-event='reduction'>
                            <i class="fa fa-warning"></i> 还原
                        </button>
                        {{# } }}
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
    //实例化form
    // layList.date({elem:'#start_time',theme:'#393D49',type:'datetime'});
    // layList.date({elem:'#end_time',theme:'#393D49',type:'datetime'});
    //加载列表
    layList.tableList('List',"{:Url('post_list',[ 'status'=>$status,'tid'=>$tid,'is_vest'=>$is_vest])}",function (){
        if(status == '-1'){
            var join = [
                {type:'checkbox'},
                {field: 'id', title: 'ID', event:'id',width:'5%'},
                {field: 'content', title: '评论内容',templet:'#content',width:'16%'},
                {field: 'author_info', title: '作者',width:'9%'},
                {field: 'fid_name', title: '所属版块/分类',templet:'#fid_name'},
                {field: 'thread_title', title: '主题内容',templet:'#thread_content',width:'16%'},
                {field: 'type', title: '主题类型',width:'6%'},
                {field: 'time', title: '时间',templet:'#time',width:'13%'},
                {field: 'count', title: '数据统计',templet:'#count',width:'8%'},
                {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'14%'},
            ];
        }else{
            var join = [
                {type:'checkbox'},
                {field: 'id', title: 'ID', event:'id',width:'5%'},
                {field: 'content', title: '评论内容',templet:'#content',width:'16%'},
                {field: 'author_info', title: '作者',width:'9%'},
                {field: 'fid_name', title: '所属版块/分类',templet:'#fid_name'},
                {field: 'thread_title', title: '主题内容',templet:'#thread_content',width:'16%'},
                {field: 'type', title: '主题类型',width:'6%'},
                {field: 'time', title: '时间',templet:'#time',width:'13%'},
                {field: 'count', title: '数据统计',templet:'#count',width:'8%'},
                {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'14%'},
            ];
        }
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
            case 'verify':
                var url=layList.U({c:'com.com_post',a:'quick_edit',q:{id:data.id, field:'status', value:1}});
                var code = {title:"操作提示",text:"你确定要审核通过该版块吗？",type:'info',confirm:'是的，我要审核通过'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '审核失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
            case 'delstor':
                var url=layList.U({c:'com.com_post',a:'quick_edit',q:{id:data.id, field:'status', value:-1}});
                var code = {title:"是否要删除该评论",text:"删除后可在回收站中还原",confirm:'是的，我要删除'};
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
            case 'reduction':
                var url=layList.U({c:'com.com_post',a:'quick_edit',q:{id:data.id, field:'status', value:1}});
                var code = {title:"是否要还原该评论",text:"还原后可在前台正常显示",confirm:'是的，我要还原'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '还原失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
        }
    })
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
