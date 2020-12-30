{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <div class="layui-tab layui-tab-brief" lay-filter="tab">
                    <ul class="layui-tab-title">
                        <li lay-id="list" {eq name='status' value=''}class="layui-this" {/eq} >
                            <a href="{eq name='status' value=''}javascript:;{else}{:Url('index',['status'=>''])}{/eq}">全部</a>
                        </li>
                        <li lay-id="list" {eq name='status' value='0'}class="layui-this" {/eq}>
                            <a href="{eq name='status' value='0'}javascript:;{else}{:Url('index',['status'=>0])}{/eq}">未审核</a>
                        </li>
                        <li lay-id="list" {eq name='status' value='1'}class="layui-this" {/eq}>
                            <a href="{eq name='status' value='1'}javascript:;{else}{:Url('index',['status'=>1])}{/eq}">已通过</a>
                        </li>
                        <li lay-id="list" {eq name='status' value='-1'}class="layui-this" {/eq}>
                            <a href="{eq name='status' value='-1'}javascript:;{else}{:Url('index',['status'=>-1])}{/eq}">已驳回</a>
                        </li>
                    </ul>
                </div>
                <div class="ibox-tools">

                </div>
            </div>
            
                <div class="layui-row layui-col-space15"  id="app">
                 <!--搜索条件-->
                    <div class="layui-col-md12">
                        <div class="layui-card">
                            <div class="layui-card-header">搜索条件</div>
                            <div class="layui-card-body">
                                <form class="layui-form">
                                <div class="layui-carousel layadmin-carousel layadmin-shortcut" lay-anim="" lay-indicator="inside" lay-arrow="none" style="background:none">
                                    <div class="layui-card-body">
                                        <div class="layui-row layui-col-space10 layui-form-item">
                                            
                                            <div class="layui-col-lg12">
                                                <label class="layui-form-label">时间选择:</label>
                                                <div class="layui-input-block" data-type="data" v-cloak="">
                                                    <button class="layui-btn layui-btn-sm" type="button" v-for="item in dataList" @click="setData(item)" :class="{'layui-btn-primary':where.select_date!=item.value}" style="margin-top: 0px">{{item.name}}</button>
                                                    <button class="layui-btn layui-btn-sm" type="button" ref="time" @click="setData({value:'zd',is_zd:true})" :class="{'layui-btn-primary':where.data!='zd'}" style="margin-top: 0px">自定义</button>
                                                    <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" v-show="showtime==true" ref="date_time" style="margin-top: 0px">{$year.0} - {$year.1}</button>
                                                </div>
                                            </div>
                                            <!-- <div class="layui-col-lg12">
                                                <label class="layui-form-label">创建时间:</label>
                                                <div class="layui-input-block" data-type="data" v-cloak="">
                                                    <button class="layui-btn layui-btn-sm" type="button" v-for="item in dataList" @click="setData(item)" :class="{'layui-btn-primary':where.data!=item.value}">{{item.name}}</button>
                                                    <button class="layui-btn layui-btn-sm" type="button" ref="time" @click="setData({value:'zd',is_zd:true})" :class="{'layui-btn-primary':where.data!='zd'}">自定义</button>
                                                    <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" v-show="showtime==true" ref="date_time" >{$year.0} - {$year.1}</button>
                                                </div>
                                                <input v-show="showtime==false" type="hidden" name="create_time"  id="create_time"/>
                                                <input v-show="showtime==true" type="hidden" name="create_time_between"  id="create_time_between"/>
                                            </div> -->

                                            <div class="layui-col-lg12">
                                                <label class="layui-form-label">认证类别:</label>
                                                <div class="layui-input-block" style="width: 50%;">
                                                    <select v-model="where.cate_id" name="cate_id" lay-filter="cate_id">
                                                        <option value="">全部</option>
                                                        {volist name='cates' id='vo'}
                                                        <option value="{$vo.value}">{$vo.name}</option>
                                                        {/volist}
                                                    </select>
                                                </div>
                                               
                                            </div>
                                            <div class="layui-col-lg12">
                                                <label class="layui-form-label">关键字:</label>
                                                <div class="layui-input-block" style="width: 50%" >
                                                    <input type="text" name="keyword" v-model="where.keyword" placeholder="请输入姓名、电话、UID" class="layui-input">
                                                </div>
                                            </div>
                                            <div class="layui-col-lg12">
                                                <label class="layui-form-label">用户名:</label>
                                                <div class="layui-input-block" style="width: 50%" >
                                                    <input type="text" name="nickname" v-model="where.nickname" placeholder="请输入用户名" class="layui-input">
                                                </div>
                                            </div>
                                            <div class="layui-col-lg12">
                                                <div class="layui-input-block">
                                                    <!-- <button type="submit" class="layui-btn layui-btn-sm layui-btn-normal">
                                                        <i class="layui-icon layui-icon-search"></i>搜索</button> -->
                                                        <button @click="search" type="button"
                                                            class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top: 0px">
                                                        <i class="layui-icon layui-icon-search"></i>搜索
                                                    </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end-->
             <!--列表-->
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-body">
                            <!-- <div class="alert alert-info" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div> -->
                            
                            <table class="layui-hide" id="List" lay-filter="List"></table>
                            
                            <script type="text/html" id="avatar">
                                {{#  if(d.avatar==''){ }}
                                {{#  } else { }}
                                <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);"
                                     src="{{d.avatar}}">
                                {{#  } }}
                            </script>

                            <script type="text/html" id="user_info">
                                <p>昵称:{{d.nickname}} </p>
                                {{#  if(d.truename==null){ }}
                                {{#  } else { }}
                                <p>姓名:{{d.truename}} </p>
                                {{#  } }}
                                <p>电话:{{d.phone}} </p>
                            </script>

                            <script type="text/html" id="cate">
                                <p>{{d.cate.name}} </p>
                            </script>

                            <script type="text/html" id="time">
                                <p style="font-size: 12px">申请时间:{{getMyDate(d.create_time*1000)}}</p>
                                {{#  if(d.status=='1' && d.approve_time !='0'){ }}
                                <p style="font-size: 12px">审核时间:{{getMyDate(d.approve_time*1000)}} </p>
                                {{#  }else if(d.status=='-1' && d.reject_time !='0'){ }}
                                <p style="font-size: 12px">驳回时间:{{getMyDate(d.reject_time*1000)}} </p>
                                {{#  } }}
                            </script>

                            <script type="text/html" id="status">
                                {{#  if(d.status=='1'){ }}
                                <p>已通过</p>
                                {{#  }else if(d.status=='0'){ }}
                                <p>未审核</p>
                                {{#  }else if(d.status=='-1'){ }}
                                <p>已驳回</p>驳回理由：{{d.reject_note}}
                                {{#  }else if(d.status=='-2'){ }}
                                <p>取消认证</p>
                                {{#  } }}
                            </script>
                            
                            <!--操作-->
                            <script type="text/html" id="act">
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame(this.innerText,'{:Url('view')}?id={{d.id}}')"><i class="fa fa-paste"></i>  查看详情</button>
                                {{# if(d.rztx_edit == 1){ }}
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame(this.innerText,'{:Url('rztx')}?id={{d.id}}')"><i class="fa fa-paste"></i>  编辑头衔</button>
                                {{# } }}
                                {{# if(d.status === 0){ }}
                                <button class="btn btn-success btn-xs" lay-event='pass' oclick="$eb.createModalFrame(this.innerText,'{:Url('approve')}?id={{d.id}}&status=1')" type="button"><i class="fa fa-success"></i> 审核通过
                                </button>
                                <button class="btn btn-warning btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('approve')}?id={{d.id}}&status=-1')" type="button"><i class="fa fa-warning"></i> 驳回
                                </button>
                                {{# }else{ }}
                                {{# } }}
                                {{# if(d.status === 1){ }}
                                <button class="btn btn-warning btn-xs" lay-event='cancel' oclick="$eb.createModalFrame(this.innerText,'{:Url('approve')}?id={{d.id}}&status=-2')" type="button"><i class="fa fa-warning"></i> 取消认证
                                </button>
                                {{# }else{ }}
                                {{# } }}

                            </script>
                        </div>
                    </div>
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
    $('.btn-warning').on('click',function(){
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                console.log(res);
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                    _this.parents('tr').remove();
                }else
                    return Promise.reject(res.data.msg || '删除失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        })
    });
    
    //实例化form
    layList.form.render();
    //加载列表 edit:'sort',
    layList.tableList('List',"{:Url('list',['status'=>$status])}",function (){
        var join=new Array();
        join=[
            {field: 'id', title: 'ID', sort: true,event:'id',width:'6%'},
            {field: 'avatar', templet: '#avatar',title: '头像',width: '6%'},
            {field: 'user_info', templet: '#user_info', title: '用户信息'},
            {field: 'cate', templet: '#cate',title: '认证类别'},
            {field: 'rztx',title: '认证头衔'},
            {field: 'create_time', templet: '#time', title: '时间', width: '20%'},
            {field: 'status',templet: '#status', title: '审核状态', width: '10%'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'20%'},
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
                action.set('sort',id,value);
                break;
        }
    });
    
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'certification.entity',a:'delete',q:{id:data.id}});
                var code = {title:"操作提示",text:"确定删除吗？",type:'info',confirm:'是的'};
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
            case 'pass':
                var url=layList.U({c:'certification.entity',a:'approve',q:{id:data.id,status:1}});
                var code = {title:"操作提示",text:"确定审核通过吗？",type:'info',confirm:'是的'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            layList.reload();
                            $eb.$swal('success',res.data.msg);
                        }else
                            return Promise.reject(res.data.msg || '通过失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
            case 'cancel':
                var url=layList.U({c:'certification.entity',a:'approve',q:{id:data.id,status:-2}});
                var code = {title:"操作提示",text:"确定取消认证吗？",type:'info',confirm:'是的'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            layList.reload();
                            $eb.$swal('success',res.data.msg);
                        }else
                            return Promise.reject(res.data.msg || '取消认证失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
            case 'open_image':
                $eb.openImage(data.image);
                break;
        }
    })
    //排序
    layList.sort(function (obj) {
        var type = obj.type;
        switch (obj.field){
            case 'id':
                layList.reload({order: layList.order(type,'p.id')},true,null,obj);
                break;
        }
    });

    // //查询
    // layList.search('search',function(where){
    //     layList.reload(where,true);
    // });
    //自定义方法
    var action={
        set:function(field,id,value){
            layList.baseGet(layList.Url({c:'certification.entity',a:'set',q:{field:field,id:id,value:value}}),function (res) {
                layList.msg(res.msg);
            });
        },
    };
    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });
    function getMyDate(str){
        var oDate = new Date(str),
          oYear = oDate.getFullYear(),
          oMonth = oDate.getMonth()+1,
          oDay = oDate.getDate(),
          oHour = oDate.getHours(),
          oMin = oDate.getMinutes(),
          oSen = oDate.getSeconds(),
          oTime = oYear +'-'+ getzf(oMonth) +'-'+ getzf(oDay) +' '+ getzf(oHour) +':'+ getzf(oMin) +':'+getzf(oSen);//最后拼接时间
        return oTime;
    };
    //补0操作
    function getzf(num){
    if(parseInt(num) < 10){
          num = '0'+num;
        }
        return num;
    }
    var status='<?=$status?>';
    var keyword='<?=$params["keyword"]?>';
    var cates=<?=json_encode($cates)?>;
    var nickname='<?=$params["nickname"]?>';

    console.log(cates);
    require(['vue'],function(Vue) {
        new Vue({
            el: "#app",
            data: {
                badge: [],
                //cates:cates,
                cates: [
                    {name: '全部', value: ''},
                    {name: '昨天', value: 'yesterday'},
                    {name: '今天', value: 'today'},
                    {name: '本周', value: 'week'},
                    {name: '本月', value: 'month'},
                    {name: '本季度', value: 'quarter'},
                    {name: '本年', value: 'year'},
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
                    data:'',
                    status:status || '',
                    select_date:'',
                    cate_id:'',
                    keyword:keyword || '',
                    nickname:nickname || '',
                    //create_time:'',
                    //create_time_between:'',
                },
                showtime: false,
            },
            watch: {

            },
            created(){
                this.where.cate_id = this.cates[0].value;
            },
            methods: {
                // setData:function(item){
                //     var that=this;
                //     if(item.is_zd==true){
                //         that.showtime=true;
                //         this.where.data=this.$refs.date_time.innerText;
                //     }else{
                //         this.showtime=false;
                //         this.where.data=item.value;
                //         $("#create_time_between").val('');
                //         $("#create_time").val(this.where.data);
                //     }
                    
                //     console.log(this.where.data);
                // }
                setData:function(item){
                  var that=this;
                  if(item.is_zd==true){
                    that.showtime=true;
                    this.where.select_date=this.$refs.date_time.innerText;
                  }else{
                    this.showtime=false;
                    this.where.select_date=item.value;
                  }
                },
                search:function () {
                  layList.reload(this.where,true);
                },
                refresh:function () {
                  $('[data-type="data"]').children(":first").click();
                  layList.reload();
                },
                getSelected(){
                    console.log(this.where.cate_id);
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
                    that.where.select_date=value;
                  }
                });
                layList.form.on("select(cate_id)", function (data) {
                  that.where.cate_id = data.value;
                });
                layList.form.render();
                // var that=this;
                // layList.laydate.render({
                //     elem:this.$refs.date_time,
                //     trigger:'click',
                //     eventElem:this.$refs.time,
                //     range:true,
                //     change:function (value){
                //         that.where.data=value;
                //         $("#create_time_between").val(that.where.data);
                //         $("#create_time").val('');
                //         console.log(that.where.data);
                //     }
                // });
            }
        })
    });

</script>
{/block}
