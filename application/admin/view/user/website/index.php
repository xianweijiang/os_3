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
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">是否成功通知</label>
                                <div class="layui-input-block">
                                    <select name="status">
                                        <option value="3">全部</option>
                                        <option value="2">待请求重试</option>
                                        <option value="1">通知成功</option>
                                        <option value="0">通知失败</option>
                                        <option value="-1">通知异常删除</option>
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
                <div class="layui-card-header">第三方平台事件通知记录</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="user_info">
                        <p>昵称：{{d.user_info.nickname}}</p>
                        <p>UID：{{d.uid}}</p>
                        <p>user_token：</p>
                        <p>{{d.user_info.user_token}}</p>
                    </script>

                    <script type="text/html" id="to_user_info">
                        {{#  if(d.to_uid==0){ }}
                            <p>无</p>
                        {{#  } else { }}
                            <p>昵称：{{d.to_user_info.nickname}}</p>
                            <p>UID：{{d.to_uid}}</p>
                            <p>user_token：</p>
                            <p>{{d.to_user_info.user_token}}</p>
                        {{#  } }}
                    </script>

                    <script type="text/html" id="action_info">
                        <p>{{d.action}}</p>
                        <p>-</p>
                        <p>{{d.action_token}}</p>
                    </script>

                    <script type="text/html" id="notify_status">
                        {{#  if(d.notify_status==-1){ }}
                        <p>通知异常删除</p>
                        {{#  } else if(d.notify_status==0) { }}
                        <p>通知失败</p>
                        {{#  } else if(d.notify_status==1) { }}
                        <p>通知成功</p>
                        {{#  } else if(d.notify_status==2) { }}
                        <p>待请求重试</p>
                        {{#  } else { }}
                        <p>未知状态，异常</p>
                        {{#  } }}
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
  layList.tableList('List', "{:Url('notify_list')}", function () {
    switch (status) {
        case '0':
            var join = [
                {field: 'id', title: '编号', align: 'center'},
                {field: 'user_info', templet: '#user_info',title: '操作人信息', align: 'left', width: '15%'},
                {field: 'to_id', title: '操作对象id', align: 'center'},
                {field: 'to_user_info', templet: '#to_user_info', title: '操作对象所属用户信息', align: 'left', width: '15%'},
                {field: 'action_info', templet: '#action_info', title: '行为标识及唯一标识', align: 'center', width: '20%'},
                {field: 'num', title: '第n次通知', align: 'center'},
                {field: 'send_time_show', title: '通知时间', align: 'center'},
                {field: 'notify_status', templet: '#notify_status',title: '通知状态', align: 'center'},
                {field: 'false_reason', title: '通知失败原因', align: 'center'},
            ];
        break;
        default:
            var join = [
                {field: 'id', title: '编号', align: 'center'},
                {field: 'user_info', templet: '#user_info',title: '操作人信息', align: 'left', width: '15%'},
                {field: 'to_id', title: '操作对象id', align: 'center'},
                {field: 'to_user_info', templet: '#to_user_info', title: '操作对象所属用户信息', align: 'left', width: '15%'},
                {field: 'action_info', templet: '#action_info', title: '行为标识及唯一标识', align: 'center', width: '20%'},
                {field: 'num', title: '第n次通知', align: 'center'},
                {field: 'send_time_show', title: '通知时间', align: 'center'},
                {field: 'notify_status', templet: '#notify_status',title: '通知状态', align: 'center'},
                {field: 'false_reason', title: '通知失败原因', align: 'center'},
            ];
            break;
    }
    return join;
  });
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