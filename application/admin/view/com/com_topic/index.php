{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <!--搜索条件-->
        <div class="layui-col-md12" style="top: -20px">
            <div class="layui-tab layui-tab-brief" lay-filter="tab">
                <ul class="layui-tab-title" style="background-color: white;top: 10px;">
                    <li lay-id="list" {eq name='status' value=''}class="layui-this" {/eq} >
                    <a href="{eq name='status' value=''}javascript:;{else}{:Url('index',['status'=>''])}{/eq}">全部</a>
                    </li>
                    <li lay-id="list" {eq name='status' value='1'}class="layui-this" {/eq} >
                    <a href="{eq name='status' value='1'}javascript:;{else}{:Url('index',['status'=>1])}{/eq}">已审核</a>
                    </li>
                    <li lay-id="list" {eq name='status' value='2'}class="layui-this" {/eq}>
                    <a href="{eq name='status' value='2'}javascript:;{else}{:Url('index',['status'=>2])}{/eq}">未审核</a>
                    </li>
                    <li lay-id="list" {eq name='status' value='-1'}class="layui-this" {/eq}>
                    <a href="{eq name='status' value='-1'}javascript:;{else}{:Url('index',['status'=>-1])}{/eq}">回收站</a>
                    </li>
                </ul>
            </div>
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body" style="padding-bottom: 0px">
                    <form class="layui-form">
                        <div class="layui-carousel layadmin-carousel layadmin-shortcut" lay-anim="" lay-indicator="inside" lay-arrow="none" style="background:none">
                            <div class="layui-card-body" style="padding-bottom: 0px;">
                                <div class="layui-row layui-col-space10 layui-form-item">
                                    <div class="layui-col-lg12">
                                        <div class="layui-inline">
                                            <label class="layui-form-label">话题名称:</label>
                                            <div class="layui-input-inline" style="margin-left: 43px">
                                                <input type="text" name="title" v-model="where.title" lay-verify="title" autocomplete="off" placeholder="请输入话题标题" class="layui-input" style="width: 173px;padding-left: 5px">
                                            </div>
                                        </div>
                                        <div class="layui-inline">
                                            <label class="layui-form-label">发起人:</label>
                                            <div class="layui-input-inline"  style="margin-left: 56px">
                                                <input type="text" name="uid" v-model="where.uid" lay-verify="uid" style="width: 173px;padding-left: 5px" autocomplete="off" placeholder="请输入用户昵称或UID" class="layui-input">
                                            </div>
                                        </div>
                                        <div class="layui-inline">
                                            <label class="layui-form-label">话题分类:</label>
                                            <div class="layui-input-inline" style="width: 173px;margin-left: 43px">
                                                <select name="class" v-model="where.class" lay-filter="class">
                                                    <option value="">不限</option>
                                                    {volist name='class' id='vo'}
                                                    <option value="{$vo.id}">{$vo.name}</option>
                                                    {/volist}
                                                </select>
                                            </div>
                                        </div>
                                        <div class="layui-inline">
                                            <label class="layui-form-label">热门话题:</label>
                                            <div class="layui-input-block">
                                                <select name="is_hot" v-model="where.is_hot" lay-filter="is_hot">
                                                    <option value="">全部</option>
                                                    <option value="1">是</option>
                                                    <option value="0">否</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="layui-col-lg12">
                                        <label class="layui-form-label">发布时间:</label>
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
                                            <button @click="search" type="button" class="layui-btn layui-btn-sm layui-btn-normal">
                                                <i class="layui-icon layui-icon-search"></i>搜索</button>
                                            <!-- <button @click="excel" type="button" class="layui-btn layui-btn-warm layui-btn-sm export" type="button">
                                                <i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出</button> -->
                                            <button @click="refresh" type="reset" class="layui-btn layui-btn-primary layui-btn-sm">
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
        <!--end-->
        <!-- 中间详细信息-->
        <!--enb-->
    </div>
    <!--列表-->
    <div class="layui-row layui-col-space15" >
        <div class="layui-col-md12" style="top: -20px">
            <div class="layui-card">
                <div class="layui-card-header">话题列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container" style="margin-top: 10px">
                        {eq name="status" value="1"}
                        <button class="layui-btn layui-btn-sm" data-type="move">迁移内容</button>
                        {/eq}
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加话题</button>
                        {else/}
                        <button type="button" class="layui-btn layui-btn-sm" data-type="unable">添加话题</button>
                        {/if}


                        {eq name="status" value="2"}
                        <button class="layui-btn layui-btn-sm" data-type="verify">批量审核</button>
                        {/eq}

                        {neq name="status" value="-1"}
                        <button class="layui-btn layui-btn-sm" data-type="delete">批量删除</button>
                        {/neq}

                        {eq name="status" value="-1"}
                        <button class="layui-btn layui-btn-sm" data-type="restore">还原</button>
                        <button class="layui-btn layui-btn-sm" data-type="remove">清理</button>
                        {/eq}

                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="image">
                        {{#  if(d.image!=''){ }}
                        <img style="cursor: pointer;width: 120px!important;height: 50px!important;" onclick="javascript:$eb.openImage(this.src);" src="{{d.image}}">
                        {{#  } }}
                    </script>
                    <script type="text/html" id="title">
                        <div style="width:218px;display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 1;overflow: hidden;">{{d.title}}</div>
                    </script>
                    <script type="text/html" id="act_common">
                        {{# if(d.status > 0){ }}
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑话题','{:Url('edit')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        {{# } }}
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('话题详情','{:Url('view')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 详情
                        </button>
                        <a  class="layui-btn layui-btn-xs" href="{:Url('com.com_thread/index')}?oid={{d.id}}&is_weibo=1" >
                            帖子管理
                        </a>
                        {{# if(d.status == 1){ }}
                        {{# if(d.is_hot == 1){ }}
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a lay-event='set_hot' href="javascript:void(0);" >
                                    取消推荐
                                </a>
                            </li>
                        </ul>
                        {{# }else{ }}
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('热门推荐','{:Url('hot_time')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 热门推荐
                        </button>
                        {{# } }}
                        {{#  }else if(d.status==2){ }}
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a lay-event='verify' href="javascript:void(0);" >
                                 审核通过
                                </a>
                            </li>
                        </ul>
                        {{# } }}
                        {{# if(d.status > -1){ }}
                        <button class="layui-btn layui-btn-xs" lay-event='delstor'>
                            <i class="fa fa-warning"></i> 删除
                        </button>
                        {{# }else{ }}
                        <!--<button class="layui-btn layui-btn-xs" lay-event="restore"><i class="fa fa-paste"></i>还原</button>-->
                        <button class="layui-btn layui-btn-xs" lay-event="remove"><i class="fa fa-paste"></i>清理</button>
                        {{# } }}
                    </script>
                    <script type="text/html" id="view_count">
                        <p>关注数:{{d.follow_count}}</p>
                        <p>讨论数:{{d.post_count}} </p>
                        <p>阅读数:{{d.view_count}} </p>
                    </script>
                    <script type="text/html" id="create_time">
                        <p>创建时间：{{d.create_time}}</p>
                        {{# if(d.update_time!='1970-01-01 08:00:00'){ }}
                        <p>回帖时间：{{d.update_time}}</p>
                        {{# }else{ }}
                        <p>回帖时间：</p>
                        {{# } }}
                        {{# if(d.update_time!='1970-01-01 08:00'){ }}
                        <p>热门到期时间：{{d.hot_end_time}}</p>
                        {{# } }}
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
    var status = '<?= $status?>';
    layList.tableList('List',"{:Url('topic_list',['status'=>$status,'class_id'=>$class_id,'id'=>$id])}",function (){
        //switch(parseInt(status)){
        switch('1'){
            case '1':
            var join = [
                {type:'checkbox'},
                {field: 'id', title: 'ID', event:'id',width:'5%'},
                {field: 'title', title: '话题',templet:'#title',width:'15%'},
                {field: 'image', title: '封面',templet:'#image',width:'7%'},
                {field: 'nickname', title: '发起人',width:'7%'},
                {field: 'class', title: '分类',width:'7%'},
                {field: 'view_count', title: '数据统计',templet:'#view_count',width:'10%'},
                {field: 'hot', title: '是否热门',width:'5%',sort: true},
                {field: 'create_time', title: '更新时间',templet:'#create_time',sort: true},
                {field: 'status_name', title: '审核状态',width:'7%'},
                {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'12%'},
            ];
                break;
        }
        return join;
    });
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'set_hot':
                var url=layList.U({c:'com.com_topic',a:'quick_edit',q:{id:data.id, field:'is_hot', value:data.is_hot== 1? 0:1}});
                if(data.is_top){
                    var code = {title:"操作提示",text:"你确定要取消推荐吗？",type:'info',confirm:'是的，我要取消推荐'};
                }else{
                    var code = {title:"操作提示",text:"你确定要热门推荐吗？",type:'info',confirm:'是的，我要推荐'};
                }
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
            case 'verify':
                var url=layList.U({c:'com.com_topic',a:'quick_edit',q:{id:data.id, field:'status', value:1}});
                var code = {title:"操作提示",text:"你确定要审核通过吗？",type:'info',confirm:'是的，我要审核通过'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '审核失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
            case 'delstor':
                var url=layList.U({c:'com.com_topic',a:'quick_edit',q:{id:data.id, field:'status', value:-1}});
                var code = {title:"是否要删除该话题",text:"删除后可在回收站中还原",confirm:'是的，我要删除'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
            case 'remove':
                var code = {title:"操作提示",text:"清空后，数据将同步清空，无法恢复，请慎重考虑",type:'info',confirm:'确定'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_topic',a:'remove'}),{ids:data.id},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                }, code)
                break;
        }
    })

    //自定义方法
    var action={
        move: function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                $eb.createModalFrame('迁移话题', layList.Url({c:'com.com_topic',a:'move', p:{
                    ids:ids
                }}));
            }else{
                layList.msg('请选择要迁移的话题');
            }
        },
        // 批量删除
        delete:function(field,id,value){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定批量删除话题吗？",type:'info',confirm:'是的，删除'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_thread',a:'delete'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要删除的话题');
            }
        },
        // 审核
        verify:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定审核通过吗？",type:'info',confirm:'是的，审核通过'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_thread',a:'verify'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要审核的话题');
            }
        },
        // 清理
        remove:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"清空后，数据将同步清空，无法恢复，请慎重考虑",type:'info',confirm:'确定'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_thread',a:'remove'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要清理的话题');
            }
        },
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
                'top': - ($(that).parents('td').height() / 2 + $(that).height() + $(that).next('ul').height()/2),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }else{
            $(that).next('ul').css({
                'padding': 10,
                'top':$(that).parents('td').height() / 2 + $(that).height(),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }
    }
    var orderCount=<?=json_encode($orderCount)?>;
    require(['vue'],function(Vue) {
        new Vue({
            el: "#app",
            data: {
                orderType: [
                    {name: '全部', value: ''},
                    {name: '1.普通版式', value: 1,count:orderCount.general},
                    {name: '2.微博', value: 2,count:orderCount.pink},
                    {name: '3.朋友圈', value: 3,count:orderCount.seckill},
                    {name: '4.资讯', value: 4,count:orderCount.bargain},
                    {name: '5.活动', value: 4,count:orderCount.bargain},
                    {name: '6.视频横版（PGC为主）', value: 4,count:orderCount.bargain},
                    {name: '7.视频竖版（UGC为主）', value: 4,count:orderCount.bargain},

                ],
                orderStatus: [
                    {name: '全部', value: ''},
                    {name: '正常', value: 1,count:orderCount.wz},
                    {name: '禁用', value: 0,count:orderCount.wf,class:true},
                    {name: '待审核', value: 2,count:orderCount.ds},
                    {name: '已删除', value: -1,count:orderCount.dp},
                ],
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
                    class:'',
                    uid:'',
                    is_hot:'',
                    title:'',
                },
                showtime: false,
            },
            watch: {

            },
            methods: {
                setFid:function(value){
                    var that = this;
                    this.where.class = value;
                },
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
                    this.where.excel=0;
                    layList.reload(this.where,true);
                },
                refresh:function () {
                    $('[data-type="data"]').children(":first").click();
                    layList.reload();
                },
                excel:function () {
                    this.where.excel=1;
                    location.href=layList.U({c:'order.store_order',a:'order_list',q:this.where});
                },
                get_class:function(){
                    console.log(this.where.class);
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
                layList.form.on("select(class)", function (data) {
                    that.where.class = data.value;
                    if(data.value){
                        var url = layList.U({c:'com.com_topic',a:'getThreadClassByForum',q:{class:data.value}});
                        $.getJSON(url,function(result){
                            $('#cid').html('<option value="">全部</option>');
                            $.each(result.data, function(i, field){
                                $('#cid').append(`<option value="${field.value}">${field.label}</option>`);
                            });
                            layList.form.render('select');//最后记得渲染
                        });
                    }else{
                        $('#cid').html('<option value="">全部</option>');
                    }
                });
                layList.form.on("select(is_hot)", function (data) {
                    that.where.is_hot = data.value;
                });

            }
        })
    });
</script>
{/block}