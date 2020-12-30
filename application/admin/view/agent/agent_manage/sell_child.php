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
                                            <button class="layui-btn layui-btn-sm" type="button" v-for="item in dataList" @click="setData(item)" :class="{'layui-btn-primary':where.select_date!=item.value}">{{item.name}}</button>
                                            <button class="layui-btn layui-btn-sm" type="button" ref="time" @click="setData({value:'zd',is_zd:true})" :class="{'layui-btn-primary':where.data!='zd'}">自定义</button>
                                            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" v-show="showtime==true" ref="date_time">{$year.0} - {$year.1}</button>
                                        </div>
                                    </div>
                                    <div class="layui-col-lg12">
                                        <label class="layui-form-label">推广类型:</label>
                                        <div class="layui-input-block" data-type="data" v-cloak="">
                                            <button class="layui-btn layui-btn-sm" type="button" v-for="item in type" @click="setType(item)" :class="{'layui-btn-primary':where.type!=item.value}">{{item.name}}</button>
                                        </div>
                                    </div>
                                    <div class="layui-col-lg12">
                                        <div class="layui-inline">
                                            <label class="layui-form-label">关键词：</label>
                                            <div class="layui-input-inline" style="margin-left: 45px;">
                                                <input type="text" name="real_name" v-model="where.keywords"
                                                       lay-verify="title" style="width: 100%" autocomplete="off"
                                                       placeholder="请输入昵称、电话、UID" class="layui-input">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="layui-col-lg12">
                                        <div class="layui-input-block">
                                            <button @click="search" type="button"
                                                    class="layui-btn layui-btn-sm layui-btn-normal">
                                                <i class="layui-icon layui-icon-search"></i>搜索
                                            </button>
                                            <!--<button @click="excel" type="button"
                                                    class="layui-btn layui-btn-warm layui-btn-sm export">
                                                <i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出
                                            </button>-->
                                            <button @click="refresh" type="reset"
                                                    class="layui-btn layui-btn-primary layui-btn-sm">
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
                <div class="layui-card-header">推广人列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="avatar">
                        {{#  if(d.user_info.avatar==''){ }}
                        <div style="width: 50px;height: 50px;"></div>
                        {{#  } else { }}
                            <img style="cursor: pointer;" onclick="javascript:$eb.openImage(this.src);"
                                 src="{{d.user_info.avatar}}">
                        {{#  } }}
                    </script>
                    <script type="text/html" id="user_info">
                        <div>昵称：{{d.user_info.nickname}}</div>
                        <div style="margin-top: 5px;">电话：{{d.user_info.phone}}</div>
                    </script>
                    <script type="text/html" id="is_seller">
                        {{#  if(d.is_seller=='1'){ }}
                        <p>是</p>
                        {{#  } else { }}
                        <p>否</p>
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
  layList.tableList('List', "{:Url('sell_child_list',['seller_uid'=>$seller_uid])}", function () {
    switch ('1') {
      case '1':
        var join = [
          {field: 'uid', title: 'UID',align: 'center', width: '10%'},
          {field: 'avatar', templet: '#avatar', title: '头像',align: 'center', width: '8%'},
          {field: 'user_info', templet: '#user_info',align: 'center', title: '用户信息', width: '20%'},
          {field: 'is_seller', templet: '#is_seller', title: '是否推广员',align: 'center', width: '10%'},
          {field: 'child_num', title: '推广人数',align: 'center', sort: true, width: '10%'},
          {field: 'order_num', title: '订单数',align: 'center', sort: true, width: '10%'},
          {field: 'create_time', title: '创建时间',align: 'center'},
        ];
        break;
    }
    return join;
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
        type:[
          {name:"全部",value:"all"},
          {name:"一级推广人",value:"level1"},
          {name:"二级推广人",value:"level2"},
        ],
        where:{
          select_date:'',
          type:'',
          keywords:'',
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
        setType:function(item){
          this.where.type=item.value;
        },
        search:function () {
          layList.reload(this.where,true);
        },
        refresh:function () {
          $('[data-type="data"]').children(":first").click();
          layList.reload();
        },
        excel:function () {
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