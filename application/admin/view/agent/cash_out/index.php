{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
  <div class="layui-row layui-col-space15" id="app">
    <!--搜索条件-->
    <div class="layui-col-md12">
      <div class="layui-tab layui-tab-brief" lay-filter="tab">
        <ul class="layui-tab-title">
          <li lay-id="list" {eq name='status' value='-1'}class="layui-this" {/eq} >
          <a href="{eq name='status' value='-1'}javascript:;{else}{:Url('index',['status'=>'-1'])}{/eq}">全部</a>
          </li>
          <li lay-id="list" {eq name='status' value='0'}class="layui-this" {/eq}>
          <a href="{eq name='status' value='0'}javascript:;{else}{:Url('index',['status'=>0])}{/eq}">已驳回</a>
          </li>
          <li lay-id="list" {eq name='status' value='1'}class="layui-this" {/eq}>
          <a href="{eq name='status' value='1'}javascript:;{else}{:Url('index',['status'=>1])}{/eq}">待审核</a>
          </li>
          <li lay-id="list" {eq name='status' value='2'}class="layui-this" {/eq}>
          <a href="{eq name='status' value='2'}javascript:;{else}{:Url('index',['status'=>2])}{/eq}">已审核</a>
          </li>
          <li lay-id="list" {eq name='status' value='3'}class="layui-this" {/eq}>
          <a href="{eq name='status' value='3'}javascript:;{else}{:Url('index',['status'=>3])}{/eq}">已打款</a>
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
                  <div class="layui-col-lg12" style="display: flex;">
                    <div class="layui-inline">
                      <label class="layui-form-label">关键词：</label>
                      <div class="layui-input-inline" style="margin-left: 45px;">
                        <input type="text" name="real_name" v-model="where.keyword"
                               lay-verify="title" style="width: 100%" autocomplete="off"
                               placeholder="请输入昵称、电话、UID、订单号" class="layui-input">
                      </div>
                    </div>
                    <div class="layui-inline">
                      <label class="layui-form-label">提现方式:</label>
                        <div class="layui-input-inline">
                              <select name="out_way" v-model="where.out_way" lay-filter="out_way">
                                  <option value="all">全部</option>
                                  <option value="weixin">微信</option>
                                  <option value="alipay">支付宝</option>
                              </select>
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
          已提现金额
          <span class="layui-badge layuiadmin-badge">￥</span>
        </div>
        <div class="layui-card-body">
          <p class="layuiadmin-big-font">{$show_data.total_out_income}</p>
        </div>
      </div>
    </div>
    <div class="layui-col-sm2 layui-col-md2">
      <div class="layui-card">
        <div class="layui-card-header">
          待审核提现金额
          <span class="layui-badge layuiadmin-badge">急</span>
        </div>
        <div class="layui-card-body">
          <p class="layuiadmin-big-font">{$show_data.total_on_out_income}</p>
        </div>
      </div>
    </div>
    <div class="layui-col-sm2 layui-col-md2">
      <div class="layui-card">
        <div class="layui-card-header">
          佣金总金额
          <span class="layui-badge layuiadmin-badge">待</span>
        </div>
        <div class="layui-card-body">
          <p class="layuiadmin-big-font">{$show_data.total_income}</p>
        </div>
      </div>
    </div>
    <div class="layui-col-sm2 layui-col-md2">
      <div class="layui-card">
        <div class="layui-card-header" id="back-btn">
          未提现金额
          <span class="layui-badge layuiadmin-badge">待</span>
        </div>
        <div class="layui-card-body">
          <p class="layuiadmin-big-font">{$show_data.total_has_income}</p>
        </div>
      </div>
    </div>
    <!--enb-->
  </div>
  <!--列表-->
  <div class="layui-row layui-col-space15">
    <div class="layui-col-md12">
      <div class="layui-card">
        <div class="layui-card-header">分销提现列表</div>
        <div class="layui-card-body">
          <table class="layui-hide" id="List" lay-filter="List"></table>
          <script type="text/html" id="user_info">
            <div style="display: flex;align-items: center">
              {{#  if(d.user_info.avatar==''){ }}
              <div style="width: 50px;height: 50px;"></div>
              {{#  } else { }}
              <img style="cursor: pointer;margin-left: 0;margin-right: 5px;" onclick="javascript:$eb.openImage(this.src);"
                   src="{{d.user_info.avatar}}">
              {{#  } }}
              <div>
                <p>昵称:{{d.user_info.nickname}} </p>
                <p>电话:{{d.user_info.phone}} </p>
              </div>
            </div>
          </script>
          <script type="text/html" id="cash_mode">
            {{#  if(d.type=='weixin'){ }}
            <p>微信</p>
            <p>{{d.account}}</p>
            <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);"
                 src="{{d.image}}">
            <p>{{d.weixin_name}}</p>
            {{#  }else if(d.type=='alipay'){ }}
            <p>支付宝</p>
            <p>{{d.account}}</p>
            <p>{{d.image}}</p>
            {{#  } }}
          </script>
          <script type="text/html" id="status">
            {{#  if(d.status=='0'){ }}
            <p>已驳回</p>
            <div>驳回理由：{{d.fail_reason}}</div>
            {{#  }else if(d.status=='1'){ }}
            <p>待审核</p>
            {{#  }else if(d.status=='2'){ }}
            <p>已审核</p>
            {{#  }else if(d.status=='3'){ }}
            <p>已打款</p>
            {{#  } }}
          </script>
          <script type="text/html" id="time_show">
            <div>
              <p>申请时间:{{d.create_time}} </p>
              {{#  if(d.status=='3'){ }}
                <p>打款时间:{{d.finish_time}}</p>
              {{#  } }}
            </div>
          </script>
          <script type="text/html" id="act_band">
            {{#  if(d.status=='0'){ }}
            {{#  }else if(d.status=='1'){ }}
            <button type="button" class="layui-btn layui-btn-xs" lay-event='auditSuccess'>审核通过</button>
            <button type="button" class="layui-btn layui-btn-xs" lay-event='auditFalse'>驳回</button>
            {{#  }else if(d.status=='2'){ }}
            <!--<button type="button" class="layui-btn layui-btn-xs" lay-event='delstor'>线上打款</button>-->
            <button type="button" class="layui-btn layui-btn-xs" lay-event='auditFinish'>线下打款成功</button>
            <button type="button" class="layui-btn layui-btn-xs" lay-event='setRemark'>备注</button>
            {{#  }else if(d.status=='3'){ }}
            <button type="button" class="layui-btn layui-btn-xs" lay-event='setRemark'>备注</button>
            {{#  } }}
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

  layList.tableList('List', "{:Url('cash_out_list',['status'=>$status,'seller_uid'=>$seller_uid])}", function () {
    switch ('-1') {
      case '-1':
        var join = [
          {field: 'id', title: '编号', align: 'center', width: '7%'},
          {field: 'order_num', title: '订单编号', align: 'center', width: '15%'},
          {field: 'user_info', templet: '#user_info', align: 'center', title: '用户信息', width: '15%'},
          {field: 'out_num', title: '提现金额', align: 'center', width: '8%'},
          {field: 'user_info', templet: '#cash_mode', title: '提现方式', align: 'center', width: '10%'},
          {field: 'time_show', title: '申请/打款时间', align: 'center',templet: '#time_show', width: '15%'},
          {field: 'status', templet: '#status', align: 'center', title: '状态', width: '10%'},
          {field: 'remark', title: '备注', align: 'center', width: '10%'},
          {field: 'right', title: '操作', align: 'center', toolbar: '#act_band'},
        ];
        break;
    }
    return join;
  });
  layList.tool(function (event,data,obj) {
    switch (event) {
      case 'auditSuccess':
        var url=layList.U({c:'agent.cash_out',a:'auditSuccess',q:{uid:data.uid,id:data.id}});
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
      case 'auditFalse':
        var url=layList.U({c:'agent.cash_out',a:'auditFalse'});
        $eb.$alert('textarea',{title:'请输入驳回理由',placeholder:'最多三十个字',value:""},function (result) {
          if(result){
            if(result.length <= 30){
              $.ajax({
                url:url,
                data:{
                  uid:data.uid,
                  id:data.id,
                  reason:result
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
              $eb.$swal('error','最多只能输入30个字');
            }
          }else{
            $eb.$swal('error','请输入驳回理由');
          }
        });
        break;
      case 'auditFinish':
        var url=layList.U({c:'agent.cash_out',a:'auditFinish',q:{uid:data.uid,id:data.id}});
        var code = {title:"操作提示",text:"你确定提现打款成功了吗？",type:'info',confirm:'是的，我确定'};
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
      case 'setRemark':
        var url=layList.U({c:'agent.cash_out',a:'setRemark'});
        $eb.$alert('textarea',{title:'请输入备注',value:""},function (result) {
          if(result){
            $.ajax({
              url:url,
              data:{
                id:data.id,
                remark:result
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
            $eb.$swal('error','请输入备注');
          }
        });
        break;
    }
  })
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
          keyword:'',
          out_way:'all',
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
          location.href=layList.U({c:'agent.cash_out',a:'cash_out_list',q:this.where});
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
        layList.form.on("select(out_way)", function (data) {
          that.where.out_way = data.value;
        });
        layList.form.render();
      }
    })
  });
</script>
{/block}