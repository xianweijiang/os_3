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
</style>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>举报帖子列表</h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content" style="display: block;">
                <form class="layui-form">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">状　　态：</label>
                            <div class="layui-input-inline">
                                <select name="status" lay-verify="status">
                                    <option value="">全部</option>
                                    <option value="1">正常</option>
                                    <option value="0">删除</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">举报原因：</label>
                            <div class="layui-input-inline">
                                <select name="reason" lay-verify="pay_count">
                                    <option value="">全部</option>
                                    {volist name="reason" id="vo" key="k"}
                                        <option value="{$key}">{$vo}</option>
                                    {/volist}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
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
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="table-responsive">
                    <div class="layui-btn-group conrelTable">
                        <!--                        <button class="layui-btn layui-btn-sm layui-btn-danger" type="button" data-type="set_status_f"><i class="fa fa-ban"></i>封禁</button>-->
                        <!--                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="set_status_j"><i class="fa fa-check-circle-o"></i>解封</button>-->
                        <!-- <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="set_grant"><i class="fa fa-check-circle-o"></i>发送优惠券</button>
                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="set_custom"><i class="fa fa-check-circle-o"></i>发送客服图文消息</button>-->
                        <!--                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="set_template"><i class="fa fa-check-circle-o"></i>发送模板消息</button>-->
                        <!--  <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="set_info"><i class="fa fa-check-circle-o"></i>发送站内消息</button>-->
                         <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="refresh"><i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                     </div>
                     <table class="layui-hide" id="userList" lay-filter="userList">

                     </table>
                     <script type="text/html" id="checkboxstatus">
                         <input type='checkbox' name='status' lay-skin='switch' value="{{d.uid}}" lay-filter='status' lay-text='正常|禁止'  {{ d.status == 1 ? 'checked' : '' }}>
                     </script>
                     <script type="text/html" id="barDemo">
                         <button type="button" class="layui-btn layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i>编辑</button>
                         <button type="button" class="layui-btn layui-btn-xs" lay-event="see"><i class="layui-icon layui-icon-edit"></i>详情</button>
                     </script>
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
     layList.form.render();
     layList.tableList('userList',"{:Url('get_user_report_list')}",function () {
         return [
             {type:'checkbox'},
             {field: 'id', title: '编号', width:'4%',event:'uid'},
             {field: 'nickname', title: '举报用户'},
             {field: 'to_nickname', title: '被举报用户'},
             {field: 'create_time', title: '举报时间',align:'center',width:'10%'},
             {field: 'reason_show', title: '举报理由',align:'center',width:'10%'},
             {field: 'total_count', title: '历史举报数量'},

             {field: 'status', title: '状态',templet:"#checkboxstatus",width:'5%'},
             {field: 'is_deal', title: '处理状态',width:'5%'},
             {fixed: 'right', title: '操作', width: '10%', align: 'center', toolbar: '#barDemo'}
         ];
     });
     layList.date('create_time');
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
 </script>
 {/block}
