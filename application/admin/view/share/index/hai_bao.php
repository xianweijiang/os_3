{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" id="app">
        <!--搜索条件-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item"  style="margin-top: 10px">
                            <div class="layui-inline">
                                <label class="layui-form-label">是否可用</label>
                                <div class="layui-input-block">
                                    <select name="status">
                                        <option value="2">全部</option>
                                        <option value="1">可用</option>
                                        <option value="0">禁用</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
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
                <div class="layui-card-header">分销海报列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container" style="margin-top: 10px">
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button class="layui-btn layui-btn-sm" data-type="add">添加数据</button>
                        {else/}
                        <button class="layui-btn layui-btn-sm" data-type="unable">添加数据</button>
                        {/if}
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="img">
                        {{#  if(d.url==''){ }}
                        {{#  } else { }}
                        <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);"
                             src="{{d.url}}">
                        {{#  } }}
                    </script>
                    <script type="text/html" id="use">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_show' lay-text='可用|禁用'  {{ d.status == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="act_band">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('edit_one')}?id={{d.id}}')"><i class="fa fa-paste"></i>编辑</button>
                        <button type="button" class="layui-btn layui-btn-xs" lay-event='delstor'><i class="fa fa-warning"></i>删除</button>
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
  layList.form.render();
  var status = '<?=$status?>';
  console.log(status)
  layList.tableList('List', "{:Url('hai_bao_list')}", function () {
    switch (status) {
      case '0':
        var join = [
          {field: 'id', title: '编号', align: 'center', width: '10%'},
          {field: 'title', title: '名称', align: 'center', width: '20%'},
          {field: 'img', templet: '#img', title: '背景图', align: 'center', width: '25%'},
          {field: 'use', templet: '#use', title: '是否可用', align: 'center', width: '20%'},
          {field: 'right', templet: '#act_band', align: 'center', title: '操作'},
        ];
        break;
      case '1':
        var join = [
          {field: 'id', title: '编号', align: 'center', width: '10%'},
          {field: 'title', title: '名称', align: 'center', width: '20%'},
          {field: 'img', templet: '#img', title: '背景图', align: 'center', width: '25%'},
          {field: 'use', templet: '#use', title: '是否可用', align: 'center', width: '20%'},
          {field: 'right', templet: '#act_band', align: 'center', title: '操作'},
        ];
        break;
      case '2':
        var join = [
          {field: 'id', title: '编号', align: 'center', width: '10%'},
          {field: 'title', title: '名称', align: 'center', width: '20%'},
          {field: 'img', templet: '#img', title: '背景图', align: 'center', width: '25%'},
          {field: 'use', templet: '#use', title: '是否可用', align: 'center', width: '20%'},
          {field: 'right', templet: '#act_band', align: 'center', title: '操作'},
        ];
        break;
    }
    return join;
  });
  layList.tool(function (event,data,obj) {
    switch (event) {
      case 'delstor':
        var url=layList.U({c:'share.index',a:'delete_one',q:{id:data.id}});
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
        })
        break;
    }
  })
  layList.switch('is_show',function (odj,value) {
    if(odj.elem.checked==true){
      layList.baseGet(layList.Url({c:'share.index',a:'change_status_one',p:{status:1,id:value}}),function (res) {
        layList.msg(res.msg);
      });
    }else{
      layList.baseGet(layList.Url({c:'share.index',a:'change_status_one',p:{status:0,id:value}}),function (res) {
        layList.msg(res.msg);
      });
    }
  });
  //自定义方法
  var action = {
    add: function () {
      $eb.createModalFrame('添加数据', layList.Url({c: 'share.index', a: 'createOne'}));
    },
      unable:function(){
          var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
          $eb.$swal('delete',function(){
              $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
          }, code)
      },
  };
  $('.layui-btn-container').find('button').each(function () {
    var type = $(this).data('type');
    $(this).on('click', function () {
      action[type] && action[type]();
    })
  });
  layList.search('search',function(where){
    layList.reload(where,true);
  });
</script>
{/block}