{extend name="public/container"}
{block name="head_top"}
<script src="{__PLUG_PATH}city.js"></script>
<style>
    .layui-btn-xs{margin-left: 0px !important;}
    legend{
        width: auto;
        border: none;
        font-weight: 700 !important;
    }
    .site-demo-button{
        padding-bottom: 20px;
        padding-left: 10px;
    }
    .layui-form-label{
        width: auto;
    }
    .layui-input-block input{
        width: 50%;
        height: 34px;
    }
    .layui-form-item{
        margin-bottom: 0;
    }
    .layui-input-block .time-w{
        width: 200px;
    }
    .layui-table-body{overflow-x: hidden;}
    .layui-btn-group button i{
        line-height: 30px;
        margin-right: 3px;
        vertical-align: bottom;
    }
    .back-f8{
        background-color: #F8F8F8;
    }
    .layui-input-block button{
        border: 1px solid #e5e5e5;
    }
    .avatar{width: 50px;height: 50px;}
    .position {
        top: 26.375px !important;
    }
    .layui-table-body {
        overflow: initial !important;
    }
</style>
{/block}
{block name="content"}
<div class="layui-row layui-col-space15"  id="app">
    <!--搜索条件-->
    <div class="layui-col-md12">
        <div class="layui-card">
            <div class="row">
                <div class="col-sm-12">
                    <div class="layui-tab layui-tab-brief" lay-filter="tab">
                        <ul class="layui-tab-title">
                            <li lay-id="list" {eq name='is_deal' value=''}class="layui-this" {/eq} >
                            <a href="{eq name='is_deal' value=''}javascript:;{else}{:Url('index',['is_deal'=>''])}{/eq}">全部</a>
                            </li>
                            <li lay-id="list" {eq name='is_deal' value='1'}class="layui-this" {/eq} >
                            <a href="{eq name='is_deal' value='1'}javascript:;{else}{:Url('index',['is_deal'=>1])}{/eq}">已处理</a>
                            </li>
                            <li lay-id="list" {eq name='is_deal' value='0'}class="layui-this" {/eq}>
                            <a href="{eq name='is_deal' value='2'}javascript:;{else}{:Url('index',['is_deal'=>0])}{/eq}">待处理</a>
                            </li>
                        </ul>
                    </div>
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>举报帖子列表</h5>
                            <div class="ibox-tools">
                                <a class="collapse-link">
                                    <i class="fa fa-chevron-up"></i>
                                </a>
                            </div>
                        </div>
                        <div class="ibox-content" style="display: block;padding-bottom: 0px;">
                            <form class="layui-form">
                                <div class="layui-form-item">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">处理方式：</label>
                                        <div class="layui-input-inline">
                                            <select name="deal_type" id='deal_type' lay-verify="deal_type">
                                                <option value="">全部</option>
                                                {volist name="deal_type" id="vo" key="k"}
                                                <option value="{$vo.id}">{$vo.name}</option>
                                                {/volist}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">举报类型：</label>
                                        <div class="layui-input-inline">
                                            <select name="type" id='type' lay-verify="type">
                                                <option value="">全部</option>
                                                {volist name="report_type" id="vo" key="k"}
                                                <option value="{$vo.id}">{$vo.name}</option>
                                                {/volist}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">举报原因：</label>
                                        <div class="layui-input-inline">
                                            <select name="reason" id='reason' lay-verify="reason">
                                                <option value="">全部</option>
                                                {volist name="reason" id="vo" key="k"}
                                                    <option value="{$key}">{$vo}</option>
                                                {/volist}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-col-lg12">
                                    <label class="layui-form-label">提交时间:</label>
                                    <input type="hidden" name="data" style="width: 50%" v-model="where.data">
                                    <div class="layui-input-block" data-type="data" v-cloak="" style="margin-left: 77px">
                                        <button class="layui-btn layui-btn-sm" type="button" v-for="item in dataList" @click="setData(item)" :class="{'layui-btn-primary':where.data!=item.value}">{{item.name}}</button>
                                        <button class="layui-btn layui-btn-sm" type="button" ref="create_time" @click="setData({value:'zd',is_zd:true})" :class="{'layui-btn-primary':where.data!='zd'}">自定义</button>
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" v-show="showtime==true" ref="date_time">{$year.0} - {$year.1}</button>
                                    </div>
                                </div>
                                <div class="layui-col-lg12" style="display:flex">
                                    <!-- <label class="layui-form-label">被举报人:</label> -->
                                    <div style="width:80px;display:inline-block">
                                        <select name="user_type" id='user_type' lay-verify="user_type">
                                            <option value="to_uid">被举报人</option>
                                            <option value="uid">举报人</option>
                                        </select>
                                    </div>
                                    <div style="flex:1;margin-left:10px">
                                        <input type="text" name="real_name" style="width: 50%" v-model="where.real_name" placeholder="请输入被举报人用户名 ID" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item" style="padding-top:10px">
                                    <label class="layui-form-label">
                                        <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="" lay-filter="search" >
                                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索</button>
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="layui-row layui-col-space15">
    <!--搜索条件-->
    <div class="layui-col-md12">
        <div class="layui-card">
            <div class="row">
                <div class="col-sm-12">
                    <div class="ibox">
                        <div class="ibox-content">
                            <div class="table-responsive">
<!--                                <div class="layui-btn-group conrelTable">-->
<!--                                     <button class="layui-btn layui-btn-sm" data-type="delete">批量删除</button>-->
<!--                                     <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="refresh"><i class="layui-icon layui-icon-refresh" ></i>刷新</button>-->
<!--                                 </div> <div class="layui-btn-group conrelTable">-->
<!--                                     <button class="layui-btn layui-btn-sm" data-type="delete">批量删除</button>-->
<!--                                     <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="refresh"><i class="layui-icon layui-icon-refresh" ></i>刷新</button>-->
<!--                                 </div>-->
                                 <table class="layui-hide" id="userList" lay-filter="userList">

                                 </table>
                                <script type="text/html" id="operation_uid">
                                    {{# if(d.operation_uid>0){ }}
                                    <p>{{d.operation_nickname}}</p>
                                    <p>{{d.operation_identity}}</p>
                                    {{# }else{ }}
                                    {{# } }}
                                </script>
                                 <script type="text/html" id="checkboxstatus">
                                     <input type='checkbox' name='status' lay-skin='switch' value="{{d.uid}}" lay-filter='status' lay-text='正常|禁止'  {{ d.status == 1 ? 'checked' : '' }}>
                                 </script>
                                 <script type="text/html" id="barDemo">
                                     {{#  if(d.is_deal!=='已处理'){ }}
                                     <button type="button" class="layui-btn layui-btn-xs show-btn" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                                        <ul class="layui-nav-child layui-anim layui-anim-upbit position" id="control">
                                            <li lay-event="delete_content">
                                                删除内容
                                            </li>
                                            <li onclick="$eb.createModalFrame('账号禁言','{:Url('choose_prohibit')}?id={{d.id}}')">
                                                账号禁言
                                            </li>
                                            <li lay-event="delete_user">
                                                账号禁用
                                            </li>
                                            <li lay-event="no_deal">
                                                无效举报
                                            </li>
                                        </ul>
                                     {{#  } }}
                                     {{#  if(d.prohibit>0){ }}
                                     <button type="button" class="layui-btn layui-btn-xs show-btn" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                                        <ul class="layui-nav-child layui-anim layui-anim-upbit position" id="delete">
                                            <li lay-event="no_deal_user">
                                                解除禁言
                                            </li>
                                        </ul>
                                     {{#  } }}
                                     <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('详情','{:Url('forum_view')}?id={{d.id}}')">
                                         <i class="fa fa-paste"></i> 详情
                                     </button>
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
 <script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
 {/block}
 {block name="script"}
 <script>
     $('#province-div').hide();
     $('#city-div').hide();
     layList.select('country',function (odj,value,name) {
         var html = '';
         $.each(city,function (index,item) {
             html += '<option value="'+item.label+'">'+item.label+'</option>';
         })
         if(odj.value == 'domestic'){
             $('#province-div').show();
             $('#city-div').show();
             $('#province-top').siblings().remove();
             $('#province-top').after(html);
             $('#province').val('');
             layList.form.render('select');
         }else{
             $('#province-div').hide();
             $('#city-div').hide();
         }
         $('#province').val('');
         $('#city').val('');
     });
     layList.select('province',function (odj,value,name) {
         var html = '';
         $.each(city,function (index,item) {
             if(item.label == odj.value){
                 $.each(item.children,function (indexe,iteme) {
                     html += '<option value="'+iteme.label+'">'+iteme.label+'</option>';
                 })
                 $('#city').val('');
                 $('#city-top').siblings().remove();
                 $('#city-top').after(html);
                 layList.form.render('select');
             }
         })
     });
     var is_deal='<?=$is_deal?>';
     layList.form.render();
     layList.tableList('userList',"{:Url('get_forum_report_list',['is_deal'=>$is_deal])}",function () {
         return [
             {type:'checkbox'},
             {field: 'id', title: 'ID', width:'4%',event:'id'},
             {field: 'to_nickname', title: '被举报用户',width:'6%'},
             {field: 'nickname', title: '举报用户',width:'6%'},
             {field: 'content_show', title: '举报内容'},
             {field: 'report_type', title: '举报类型',width:'8%'},
             {field: 'plate_cate', title: '所属版块及分类',width:'6%'},
             {field: 'create_time', title: '时间',align:'center',width:'10%'},
             {field: 'reason_show', title: '举报原因',align:'center',width:'7%'},
             {field: 'is_deal', title: '处理状态',width:'6%'},
             {field: 'deal_type', title: '处理方式',width:'6%'},
             {field: 'operation_uid', title: '操作人',templet:'#operation_uid',width:'6%'},
             {fixed: 'right', title: '操作', toolbar: '#barDemo',width:'14%'}
         ];
     });
    //  layList.date('create_time');
     //监听并执行 uid 的排序
     layList.sort(function (obj) {
         var layEvent = obj.field;
         var type = obj.type;
         switch (layEvent){
             case 'uid':
                 layList.reload({order: layList.order(type,'u.uid')},true,null,obj);
                 break;
             case 'now_money':
                 layList.reload({order: layList.order(type,'u.now_money')},true,null,obj);
                 break;
             case 'integral':
                 layList.reload({order: layList.order(type,'u.integral')},true,null,obj);
                 break;
         }
     });
     //监听并执行 uid 的排序
     layList.tool(function (event,data) {
         var layEvent = event;
         switch (layEvent){
             case 'edit':
                 $eb.createModalFrame('编辑',layList.Url({a:'edit',p:{uid:data.uid}}));
                 break;
             case 'see':
                 $eb.createModalFrame(data.nickname+'-会员详情',layList.Url({a:'see',p:{uid:data.uid}}));
                 break;
             case 'delete_content':
                 var url=layList.U({c:'com.com_report',a:'delete_content',q:{id:data.id,status:1}});
                 var code = {title:"操作提示",text:"确定删除吗？",type:'info',confirm:'是的'};
                 $eb.$swal('delete',function(){
                     $eb.axios.get(url).then(function(res){
                         if(res.status == 200 && res.data.code == 200) {
                             layList.reload();
                             $eb.$swal('success',res.data.msg);
                         }else
                             return Promise.reject(res.data.msg || '删除失败')
                     }).catch(function(err){
                         $eb.$swal('error',err);
                     });
                 },code)
                 break;
             case 'delete_user':
                 var url=layList.U({c:'com.com_report',a:'user_delete',q:{id:data.id,status:1}});
                 var code = {title:"操作提示",text:"确定禁用用户吗？",type:'info',confirm:'是的'};
                 $eb.$swal('delete',function(){
                     $eb.axios.get(url).then(function(res){
                         if(res.status == 200 && res.data.code == 200) {
                             layList.reload();
                             $eb.$swal('success','禁用成功');
                         }else
                             return Promise.reject( '禁用失败')
                     }).catch(function(err){
                         $eb.$swal('error',err);
                     });
                 },code)
                 break;
             case 'no_deal':
                 var url=layList.U({c:'com.com_report',a:'no_deal',q:{id:data.id,status:1}});
                 var code = {title:"操作提示",text:"确定是无效举报吗？",type:'info',confirm:'是的'};
                 $eb.$swal('delete',function(){
                     $eb.axios.get(url).then(function(res){
                         if(res.status == 200 && res.data.code == 200) {
                             layList.reload();
                             $eb.$swal('success','操作成功');
                         }else
                             return Promise.reject( '操作失败')
                     }).catch(function(err){
                         $eb.$swal('error',err);
                     });
                 },code)
                 break;
             case 'no_deal_user':
                 var url=layList.U({c:'com.com_report',a:'no_deal',q:{id:data.id,status:1}});
                 var code = {title:"操作提示",text:"确定是解除禁言吗？",type:'info',confirm:'是的'};
                 $eb.$swal('delete',function(){
                     $eb.axios.get(url).then(function(res){
                         if(res.status == 200 && res.data.code == 200) {
                             layList.reload();
                             $eb.$swal('success','操作成功');
                         }else
                             return Promise.reject( '操作失败')
                     }).catch(function(err){
                         $eb.$swal('error',err);
                     });
                 },code)
                 break;
         }
     });
     //    layList.sort('uid');
     //监听并执行 now_money 的排序
     // layList.sort('now_money');
     //监听 checkbox 的状态
     layList.switch('status',function (odj,value,name) {
         if(odj.elem.checked==true){
             layList.baseGet(layList.Url({a:'set_status',p:{status:1,uid:value}}),function (res) {
                 layList.msg(res.msg);
             });
         }else{
             layList.baseGet(layList.Url({a:'set_status',p:{status:0,uid:value}}),function (res) {
                 layList.msg(res.msg);
             });
         }
     });
     layList.search('search',function(where){
         // if(where['user_time_type'] != '' && where['user_time'] == '') return layList.msg('请选择选择时间');
         // if(where['user_time_type'] == '' && where['user_time'] != '') return layList.msg('请选择访问情况');
         if(where['user_time'] != ''){
             where['user_time_type'] = 'add_time';
         }
         layList.reload(where,true);
     });

     //自定义方法
     var action={
         // 批量删除
         delete:function(field,id,value){
             var ids=layList.getCheckData().getIds('  ');
             alert(ids);
             if(ids.length){
                 var code = {title:"操作提示",text:"确定批量删除这些举报吗？",type:'info',confirm:'是的，删除'};
                 $eb.$swal('delete',function(){
                     layList.basePost(layList.Url({c:'com.com_report',a:'delete_forum'}),{ids:ids,status:-1},function (res) {
                         layList.msg(res.msg);
                         layList.reload();
                     });
                 },code);
             }else{
                 layList.msg('请选择要删除的帖子');
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
     require(['vue'],function(Vue) {
         new Vue({
             el: "#app",
             data: {
                //  badge: [],
                 orderStatus: [
                     {name: '全部', value: ''},
                     {name: '待审核', value: 2},
                     {name: '已审核', value: 1},
                     {name: '驳回', value: 0},
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
                     status:'',
                     type:'',
                     real_name: '',
                     excel:0,
                     user_type:"uid"
                 },
                 showtime: false,
             },
             watch: {

             },
             methods: {
                 setData(item){
                     var that=this;
                     if(item.is_zd==true){
                         that.showtime=true;
                         this.where.data=this.$refs.date_time.innerText;
                     }else{
                         this.showtime=false;
                         this.where.data=item.value;
                     }
                 },
                //  getBadge:function() {
                //      var that=this;
                //      layList.basePost(layList.Url({c:'auth.index',a:'get_user_message_list'}),this.where,function (rem) {
                //          that.badge=rem.data;
                //      });
                //  },
                 search:function () {
                     this.where.excel=0;
                    //  this.getBadge();
                     layList.reload(this.where,true);
                 },
                 refresh:function () {
                     layList.reload();
                    //  this.getBadge();
                 },
                 excel:function () {
                     this.where.excel=1;
                     location.href=layList.U({c:'order.store_order',a:'order_list',q:this.where});
                 }
             },
             mounted:function () {
                 var that=this;
                //  that.getBadge();
                 layList.laydate.render({
                     elem:this.$refs.date_time,
                     trigger:'click',
                     eventElem:this.$refs.create_time,
                     range:true,
                     change:function (value){
                         that.where.data=value;
                     }
                 });
                 layList.form.on("select(type)", function (data) {
                     that.where.cate = data.value;
                 });
                 layList.form.on("select(status)", function (data) {
                     that.where.support = data.value;
                 });
                 layList.form.on("select(deal_type)", function (data) {
                     that.where.user_property = data.value;
                 });
                 layList.form.on("select(reason)", function (data) {
                     that.where.version = data.value;
                 });
                 
                layList.form.render();
             }
         })
     });
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
    $('body').click(function(e) {
       $('.show-btn').next('ul').hide()
    })
 </script>
 {/block}
