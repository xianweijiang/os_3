{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" id="app" style="margin-top: -25px;">
        <!--搜索条件-->
        <div class="layui-col-md12">
            <div class="layui-tab layui-tab-brief" lay-filter="tab">
                <ul class="layui-tab-title" style="background-color: white; top: 10px">
                    <li lay-id="list" {eq name='status' value='10'}class="layui-this" {/eq} >
                    <a href="{eq name='status' value='3'}javascript:;{else}{:Url('agent',['status'=>''])}{/eq}">全部</a>
                    </li>
                    <li lay-id="list" {eq name='status' value='2'}class="layui-this" {/eq}>
                    <a href="{eq name='status' value='2'}javascript:;{else}{:Url('agent',['status'=>2])}{/eq}">未审核</a>
                    </li>
                    <li lay-id="list" {eq name='status' value='1'}class="layui-this" {/eq}>
                    <a href="{eq name='status' value='1'}javascript:;{else}{:Url('agent',['status'=>1])}{/eq}">已通过</a>
                    </li>
                    <li lay-id="list" {eq name='status' value='3'}class="layui-this" {/eq}>
                    <a href="{eq name='status' value='3'}javascript:;{else}{:Url('agent',['status'=>3])}{/eq}">已驳回</a>
                    </li>
                    <li lay-id="list" {eq name='status' value='0'}class="layui-this" {/eq}>
                    <a href="{eq name='status' value='0'}javascript:;{else}{:Url('agent',['status'=>0])}{/eq}">已禁用</a>
                    </li>
                </ul>
            </div>
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form">
                        <div class="layui-carousel layadmin-carousel layadmin-shortcut" lay-anim=""
                             lay-indicator="inside" lay-arrow="none" style="background:none">
                            <div class="layui-card-body ">
                                <div class="layui-row layui-col-space10 layui-form-item">
                                    <div class="layui-col-lg12">
                                        <label class="layui-form-label">时间选择:</label>
                                        <div class="layui-input-block" data-type="data" v-cloak="">
                                            <button class="layui-btn layui-btn-sm" type="button" v-for="item in dataList" @click="setData(item)" :class="{'layui-btn-primary':where.select_date!=item.value}" style="margin-top: 0px;">{{item.name}}</button>
                                            <button class="layui-btn layui-btn-sm" type="button" ref="time" @click="setData({value:'zd',is_zd:true})" :class="{'layui-btn-primary':where.data!='zd'}" style="margin-top: 0px;">自定义</button>
                                            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" v-show="showtime==true" ref="date_time" style="margin-top: 0px;">{$year.0} - {$year.1}</button>
                                        </div>
                                    </div>
                                    <div class="layui-col-lg12">
                                        <div class="layui-inline">
                                            <label class="layui-form-label">关键词：</label>
                                            <div class="layui-input-inline" style="margin-left: 45px;">
                                                <input type="text" name="real_name" v-model="where.key_word"
                                                       lay-verify="title" style="width: 100%" autocomplete="off"
                                                       placeholder="请输入昵称、电话、UID" class="layui-input">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="layui-col-lg12">
                                        <div class="layui-input-block">
                                            <button @click="search" type="button"
                                                    class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top: 0px;">
                                                <i class="layui-icon layui-icon-search"></i>搜索
                                            </button>
                                            <button @click="excel" type="button"
                                                    class="layui-btn layui-btn-warm layui-btn-sm export" style="margin-top: 0px;">
                                                <i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出
                                            </button>
                                            <button @click="refresh" type="reset"
                                                    class="layui-btn layui-btn-primary layui-btn-sm" style="margin-top: 0px;">
                                                <i class="layui-icon layui-icon-refresh"></i>刷新
                                            </button>
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
        <div class="layui-col-sm2 layui-col-md2">
            <div class="layui-card">
                <div class="layui-card-header">
                    分销人员数
                    <span class="layui-badge layuiadmin-badge">人</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$show_data.seller_num}</p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm2 layui-col-md2">
            <div class="layui-card">
                <div class="layui-card-header">
                    分销订单数
                    <span class="layui-badge layuiadmin-badge">单</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$show_data.order_num}</p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm2 layui-col-md2">
            <div class="layui-card">
                <div class="layui-card-header">
                    订单金额
                    <span class="layui-badge layuiadmin-badge">元</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$show_data.order_money}</p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm2 layui-col-md2">
            <div class="layui-card">
                <div class="layui-card-header">
                    提现金额
                    <span class="layui-badge layuiadmin-badge">元</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$show_data.out_money}</p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm2 layui-col-md2">
            <div class="layui-card">
                <div class="layui-card-header">
                    返利金额
                    <span class="layui-badge layuiadmin-badge">元</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$show_data.order_back_money}</p>
                </div>
            </div>
        </div>
        <!--enb-->
    </div>
    <!--列表-->
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">分销员列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="avatar">
                        {{#  if(d.user_info.avatar==''){ }}
                        {{#  } else { }}
                        <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);"
                             src="{{d.user_info.avatar}}">
                        {{#  } }}
                    </script>
                    <script type="text/html" id="user_info">
                        <div>昵称:{{d.user_info.nickname}} </div>
                        <div>电话:{{d.user_info.phone}} </div>
                    </script>
                    <script type="text/html" id="child_num">
                        <p>一级:{{d.child1_num}} </p>
                        <p>二级:{{d.child2_num}} </p>
                    </script>
                    <script type="text/html" id="father1_info">
                        {{#  if(d.father1_info){ }}
                        <p>{{d.father1_info.nickname}} </p>
                        {{#  } else { }}
                        <p>暂无</p>
                        {{#  } }}
                    </script>
                    <script type="text/html" id="status">
                        {{#  if(d.status=='2'){ }}
                        <p>未审核</p>
                        {{#  }else if(d.status=='1'){ }}
                        <p>已通过</p>
                        {{#  }else if(d.status=='3'){ }}
                        <p>已驳回</p>
                        {{#  }else if(d.status=='0'){ }}
                        <p>已禁用</p>
                        {{#  } }}
                    </script>
                    <script type="text/html" id="time">
                        <p style="font-size: 12px">申请时间:{{d.create_time}} </p>
                        {{#  if(d.status=='1' && d.audit_time !='0'){ }}
                        <p style="font-size: 12px">审核时间:{{d.audit_time}} </p>
                        {{#  }else if(d.status=='3' && d.audit_time !='0'){ }}
                        <p style="font-size: 12px">驳回时间:{{d.audit_time}} </p>
                        {{#  }else if(d.status=='0' && d.audit_time !='0'){ }}
                        <p style="font-size: 12px">禁用时间:{{d.audit_time}} </p>
                        {{#  } }}
                    </script>
                    <script type="text/html" id="act_band">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span
                                    class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            {{# if(d.status=='1'){ }}
                            <li>
                                <a href="{:Url('agent.agent_manage/sell_child')}?seller_uid={{d.uid}}" class="">查看推广人列表</a>
                            </li>
                            <li>
                                <a href="{:Url('agent.sell_order/sell_order')}?seller_uid={{d.uid}}" class="">查看推广订单</a>
                            </li>
                            <li>
                                <a href="{:Url('agent.cashOut/index')}?seller_uid={{d.user_info.uid}}" class="" onclick="">提现明细</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='cancel_verify'>禁用分销权限</a>
                            </li>
                            {{# }else if(d.status=='3'){ }}
                            <li>
                                <a href="javascript:void(0);" lay-event='look_verify'>查看驳回理由</a>
                            </li>
                            {{# }else if(d.status=='2'){ }}
                            <li>
                                <a href="javascript:void(0);" lay-event='verify'>审核通过</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='refuse_verify'>驳回</a>
                            </li>
                            {{# }else if(d.status=='0'){ }}
                            <li>
                                <a href="javascript:void(0);" lay-event='back_verify'>恢复分销权限</a>
                            </li>
                            {{#  } }}
                        </ul>
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
  layList.tableList('List', "{:Url('agent_list',['status'=>$status])}", function () {
    switch (status) {
      case '3':
        var join = [
          {field: 'uid', title: 'UID', width: '5%'},
          {field: 'avatar', templet: '#avatar', title: '头像', width: '5%'},
          {field: 'user_info', templet: '#user_info', title: '用户信息', width: '9%'},
          {field: 'child_num', templet: '#child_num', title: '推广用户数量', sort: true, width: '8%'},
          {field: 'order_num', title: '订单数量', width: '6%'},
          {field: 'order_money', title: '推广订单金额', sort: true, width: '8%'},
          {field: 'total_income', title: '佣金金额', sort: true, width: '6%'},
          {field: 'out_income', title: '已提现金额', sort: true, width: '7%'},
          {field: 'out_num', title: '提现次数', width: '6%'},
          {field: 'has_income', title: '未提现金额', sort: true, width: '7%'},
          {field: 'father1_info', templet: '#father1_info', title: '上级推广人', width: '8%'},
          {field: 'status',templet: '#status', title: '审核状态', width: '6%'},
          {field: 'time', templet: '#time', title: '时间', width: '13%'},
          {field: 'right', title: '操作', align: 'center', toolbar: '#act_band'},
        ];
        break;
      case '2':
        var join = [
          {field: 'uid', title: 'UID', width: '5%'},
          {field: 'avatar', templet: '#avatar', title: '头像', width: '5%'},
          {field: 'user_info', templet: '#user_info', title: '用户信息', width: '9%'},
          {field: 'child_num', templet: '#child_num', title: '推广用户数量', sort: true, width: '8%'},
          {field: 'order_num', title: '订单数量', width: '6%'},
          {field: 'order_money', title: '推广订单金额', sort: true, width: '8%'},
          {field: 'total_income', title: '佣金金额', sort: true, width: '6%'},
          {field: 'out_income', title: '已提现金额', sort: true, width: '7%'},
          {field: 'out_num', title: '提现次数', width: '6%'},
          {field: 'has_income', title: '未体现金额', sort: true, width: '7%'},
          {field: 'father1_info', templet: '#father1_info', title: '上级推广人', width: '8%'},
          {field: 'status',templet: '#status', title: '审核状态', width: '6%'},
          {field: 'time', templet: '#time', title: '时间', width: '13%'},
          {field: 'right', title: '操作', align: 'center', toolbar: '#act_band'},
        ];
        break;
      case '1':
        var join = [
          {field: 'uid', title: 'UID', width: '5%'},
          {field: 'avatar', templet: '#avatar', title: '头像', width: '5%'},
          {field: 'user_info', templet: '#user_info', title: '用户信息', width: '9%'},
          {field: 'child_num', templet: '#child_num', title: '推广用户数量', sort: true, width: '8%'},
          {field: 'order_num', title: '订单数量', width: '6%'},
          {field: 'order_money', title: '推广订单金额', sort: true, width: '8%'},
          {field: 'total_income', title: '佣金金额', sort: true, width: '6%'},
          {field: 'out_income', title: '已提现金额', sort: true, width: '7%'},
          {field: 'out_num', title: '提现次数', width: '6%'},
          {field: 'has_income', title: '未体现金额', sort: true, width: '7%'},
          {field: 'father1_info', templet: '#father1_info', title: '上级推广人', width: '8%'},
          {field: 'status',templet: '#status', title: '审核状态', width: '6%'},
          {field: 'time', templet: '#time', title: '时间', width: '13%'},
          {field: 'right', title: '操作', align: 'center', toolbar: '#act_band'},
        ];
        break;
      case '0':
        var join = [
          {field: 'uid', title: 'UID', width: '5%'},
          {field: 'avatar', templet: '#avatar', title: '头像', width: '5%'},
          {field: 'user_info', templet: '#user_info', title: '用户信息', width: '9%'},
          {field: 'child_num', templet: '#child_num', title: '推广用户数量', sort: true, width: '8%'},
          {field: 'order_num', title: '订单数量', width: '6%'},
          {field: 'order_money', title: '推广订单金额', sort: true, width: '8%'},
          {field: 'total_income', title: '佣金金额', sort: true, width: '6%'},
          {field: 'out_income', title: '已提现金额', sort: true, width: '7%'},
          {field: 'out_num', title: '提现次数', width: '6%'},
          {field: 'has_income', title: '未体现金额', sort: true, width: '7%'},
          {field: 'father1_info', templet: '#father1_info', title: '上级推广人', width: '8%'},
          {field: 'status',templet: '#status', title: '审核状态', width: '6%'},
          {field: 'time', templet: '#time', title: '时间', width: '13%'},
          {field: 'right', title: '操作', align: 'center', toolbar: '#act_band'},
        ];
        break;
        case '10':
            var join = [
                {field: 'uid', title: 'UID', width: '5%'},
                {field: 'avatar', templet: '#avatar', title: '头像', width: '5%'},
                {field: 'user_info', templet: '#user_info', title: '用户信息', width: '9%'},
                {field: 'child_num', templet: '#child_num', title: '推广用户数量', sort: true, width: '8%'},
                {field: 'order_num', title: '订单数量', width: '6%'},
                {field: 'order_money', title: '推广订单金额', sort: true, width: '8%'},
                {field: 'total_income', title: '佣金金额', sort: true, width: '6%'},
                {field: 'out_income', title: '已提现金额', sort: true, width: '7%'},
                {field: 'out_num', title: '提现次数', width: '6%'},
                {field: 'has_income', title: '未体现金额', sort: true, width: '7%'},
                {field: 'father1_info', templet: '#father1_info', title: '上级推广人', width: '8%'},
                {field: 'status',templet: '#status', title: '审核状态', width: '6%'},
                {field: 'time', templet: '#time', title: '时间', width: '13%'},
                {field: 'right', title: '操作', align: 'center', toolbar: '#act_band'},
            ];
            break;
    }
    return join;
  });
  layList.tool(function (event,data,obj) {
    switch (event) {
      case 'verify':
        var url=layList.U({c:'agent.agent_manage',a:'auditSeller',q:{uid:data.uid,statue:1}});
        var code = {title:"操作提示",text:"你确定要审核通过吗？",type:'info',confirm:'是的，我要审核通过'};
        $eb.$swal('delete',function(){
          $eb.axios.get(url).then(function(res){
            if(res.status == 200 && res.data.code == 200) {
              $eb.$swal('success', '');
              window.location.reload();
            }else
              return Promise.reject(res.data.msg || '审核失败')
          }).catch(function(err){
            $eb.$swal('error',err);
          });
        }, code)
        break;
      case 'refuse_verify':
        var url=layList.U({c:'agent.agent_manage',a:'auditSeller'});
        $eb.$alert('textarea',{title:'请输入驳回理由',value:""},function (result) {
          if(result){
            $.ajax({
              url:url,
              data:{
                uid:data.uid,
                status:3,
                fail_reason:result
              },
              type:'post',
              dataType:'json',
              success:function (res) {
                if(res.code == 200) {
                  $eb.$swal('success',res.msg);
                  window.location.reload();
                }else
                  $eb.$swal('error',res.msg);
              }
            })
          }else{
            $eb.$swal('error','请输入驳回理由');
          }
        });
        break;
      case 'cancel_verify':
        var url=layList.U({c:'agent.agent_manage',a:'delSeller',q:{uid:data.uid}});
        var code = {title:"操作提示",text:"你确定要取消该用户分销商权限吗？",type:'info',confirm:'是的，我要取消'};
        $eb.$swal('delete',function(){
          $eb.axios.get(url).then(function(res){
            if(res.status == 200 && res.data.code == 200) {
              $eb.$swal('success', '');
              window.location.reload();
            }else
              return Promise.reject(res.data.msg || '取消失败')
          }).catch(function(err){
            $eb.$swal('error',err);
          });
        }, code)
        break;
      case 'look_verify':
        $eb.$alert('textarea',{title:'查看驳回理由',value:data.fail_reason,readOnly:true});
        break;
        case 'back_verify':
            var url=layList.U({c:'agent.agent_manage',a:'auditSeller',q:{uid:data.uid,statue:1}});
            var code = {title:"操作提示",text:"你确定要恢复该用户分销权限吗？",type:'info',confirm:'是的，我要恢复'};
            $eb.$swal('delete',function(){
                $eb.axios.get(url).then(function(res){
                    if(res.status == 200 && res.data.code == 200) {
                        $eb.$swal('success', '');
                        window.location.reload();
                    }else
                        return Promise.reject(res.data.msg || '恢复失败')
                }).catch(function(err){
                    $eb.$swal('error',err);
                });
            }, code)
            break;
    }
  })
  $(document).click(function (e) {
    $('.layui-nav-child').hide();
  })
  function dropdown(that) {
    var oEvent = arguments.callee.caller.arguments[0] || event;
    oEvent.stopPropagation();
    var offset = $(that).offset();
    var top = offset.top - $(window).scrollTop();
    var index = $(that).parents('tr').data('index');
    $('.layui-nav-child').each(function (key) {
      if (key != index) {
        $(this).hide();
      }
    })
    if ($(document).height() < top + $(that).next('ul').height()) {
      $(that).next('ul').css({
        'padding': 10,
        'top': -($(that).parents('td').height() / 2 + $(that).height() + $(that).next('ul').height() / 2),
        'min-width': 'inherit',
        'position': 'absolute'
      }).toggle();
    } else {
      $(that).next('ul').css({
        'padding': 10,
        'top': $(that).parents('td').height() / 2 + $(that).height(),
        'min-width': 'inherit',
        'position': 'absolute'
      }).toggle();
    }
  }
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
        dataList: [
          {name: '全部', value: ''},
          {name: '昨天', value: 'yesterday'},
          {name: '今天', value: 'today'},
          {name: '本周', value: 'week'},
          {name: '本月', value: 'month'},
          {name: '本年', value: 'year'},
        ],
        where:{
          select_date:'',
          status:'',
          key_word:'',
          excel:0,
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
        excel:function () {
          this.where.excel=1;
          location.href=layList.U({c:'agent.agent_manage',a:'agent_list',q:this.where});
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
        layList.form.render();
      }
    })
  });
</script>
{/block}