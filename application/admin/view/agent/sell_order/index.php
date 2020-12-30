{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
  <div class="layui-row layui-col-space15" id="app">
    <!--搜索条件-->
    <div class="layui-col-md12">
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
                          <label class="layui-form-label" style="margin-right: 42px">订单状态:</label>
                          <div class="layui-input-inline">
                              <select name="order_status" v-model="where.order_status" lay-filter="order_status">
                                  <option value="">全部</option>
                                  <option value="-1">已退款</option>
                                  <option value="0">待发货</option>
                                  <option value="1">待收货</option>
                                  <option value="2">已收货</option>
                                  <option value="3">已评价</option>
                                  <option value="4">待付款</option>
                              </select>
                          </div>
                      </div>
                      <div class="layui-inline">
                          <label class="layui-form-label">分销状态:</label>
                          <div class="layui-input-inline">
                              <select name="back_status" v-model="where.back_status" lay-filter="back_status">
                                  <option value="">全部</option>
                                  <option value="1">已结算</option>
                                  <option value="2">未结算</option>
                                  <option value="0">已失效</option>
                              </select>
                          </div>
                      </div>
                      <div class="layui-inline">
                          <div class="layui-input-inline" style="margin-right: 0;width: 130px;">
                              <select name="keywords_type" v-model="where.keywords_type" lay-filter="keywords_type">
                                  <option value="order_id">订单号</option>
                                  <option value="user">用户名</option>
                                  <option value="product">商品名称</option>
                              </select>
                          </div>
                          <div class="layui-input-inline" style="">
                              <input type="text" name="keywords" v-model="where.keywords"
                                     lay-verify="title" style="width: 100%" autocomplete="off"
                                     placeholder="" class="layui-input">
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
    <!--enb-->
  </div>
  <!--列表-->
  <div class="layui-row layui-col-space15">
    <div class="layui-col-md12">
      <div class="layui-card">
        <div class="layui-card-header">分销订单列表</div>
        <div class="layui-card-body">
          <table class="layui-hide" id="List" lay-filter="List"></table>
          <script type="text/html" id="user_info">
              <p>{{d.user_info.nickname}} </p>
          </script>
            <script type="text/html" id="father_user_info">
                {{#  if(d.father1_info=={}){ }}
                <p>无</p>
                {{#  } else { }}
                <p>{{d.father1_info.nickname}}</p>
                {{#  } }}
            </script>
            <script type="text/html" id="product_info">
                {{#  layui.each(d.goods_info_list, function(index, item){ }}
                <div style="display: flex;align-items: center;margin-top: 5px;">
                    <div><img style="width: 30px;height: 30px;margin:0;cursor: pointer;" src="{{item.image}}"></div>
                    <div style="margin-left: 5px;">
                        {{item.store_name}}
                        {{#  if(item.sku==""){ }}
                        {{#  } else { }}
                        <span>({{item.sku}})</span>
                        {{#  } }}
                        ×{{item.cart_num}}
                    </div>
                </div>
                {{#  }); }}
            </script>
          <script type="text/html" id="father_back_info">
              {{#  if(d.father1!="0"){ }}
                <p>一级：{{d.father1_info.nickname}} {{d.father1_back}}元</p>
              {{#  } else { }}
              <p>一级：无归属 {{d.father1_back}}元</p>
              {{#  } }}
              {{#  if(d.father2!="0"){ }}
              <p>二级：{{d.father2_info.nickname}} {{d.father2_back}}元</p>
              {{#  } else { }}
              <p>二级：无归属 {{d.father2_back}}元</p>
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
  layList.tableList('List', "{:Url('order_list')}", function () {
    switch ('-1') {
      case '-1':
        var join = [
          {field: 'order_id', title: '订单号', align: 'center', width: '12%'},
          {field: 'user_info', templet: '#user_info', align:'center', title: '用户信息', width: '8%'},
          {field: 'father_user_info', align:'center',templet: '#father_user_info', title: '推荐人信息', width: '8%'},
          {field: 'product_info', templet: '#product_info', title: '商品信息', width: '18%'},
          {field: 'pay_money', title: '实付金额', align: 'center', width: '10%'},
          {field: 'order_status_show', align: 'center', title: '订单状态', width: '10%'},
          {field: 'create_time_show', title: '下单时间', align: 'center', width: '12%'},
          {field: 'back_status_show', title: '分销状态', align: 'center', width: '8%'},
          {field: 'father_back_info',templet: '#father_back_info', title: '分销情况'},
        ];
        break;
    }
    return join;
  });

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
          order_status:'',
          back_status:'',
          keywords_type:'order_id',
          keywords:'',
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
          location.href=layList.U({c:'agent.sell_order',a:'order_list',q:this.where});
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
        layList.form.on("select(order_status)", function (data) {
          that.where.order_status = data.value;
        });
        layList.form.on("select(back_status)", function (data) {
          that.where.back_status = data.value;
        });
        layList.form.on("select(keywords_type)", function (data) {
          that.where.keywords_type = data.value;
        });
        layList.form.render();
      }
    })
  });
</script>
{/block}