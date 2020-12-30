{extend name="public/container"}

{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">推荐用户列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container">
                        <button type="button" class="layui-btn layui-btn-sm"  onclick="$eb.createModalFrame('推荐用户设置','{:Url('set_recommend')}',{h:document.body.clientHeight,w:document.body.clientWidth})">
                            添加推荐
                        </button>
                        <button class="layui-btn layui-btn-sm" data-type="remove">取消推荐</button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="act">
                        <button class="layui-btn layui-btn-xs" lay-event='delstor'>取消推荐</button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    setTimeout(function () {
        $('.alert-info').hide();
    },3000);
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('user_list')}",function (){
        return [
            {type:'checkbox'},
            {field: 'id', title: 'ID'},
            {field: 'nickname', title: '用户名'},
            //{field: 'reason', title: '推荐原因'},
            {field: 'sort', title: '推荐排序',edit:'sort'},
            {field: 'create_time', title: '推荐时间'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act'},
        ];
    });
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'user.user_recommend',a:'quick_edit',q:{id:data.id, field:'status', value:0}});
                var code = {title:"是否要取消推荐",text:"确定要取消推荐吗",confirm:'确定'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '取消失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
        }
    })
    //自定义方法
    var action= {
        quick_edit:function(field, id, value){
            layList.baseGet(layList.Url({c:'user.user_recommend',a:'quick_edit',q:{field:field,id:id,value:value}}),function (res) {
                layList.msg(res.msg);
            });
        },
        remove:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定要取消推荐吗？ ",type:'info',confirm:'确定'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'user.user_recommend',a:'del_recommend'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要取消推荐的用户');
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
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });

    layList.switch('status',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'com.com_adv',a:'quick_edit',p:{value:1,field:'status',id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'com.com_adv',a:'quick_edit',p:{value:0,field:'status', id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });

    //快速编辑
    layList.edit(function (obj) {
        var id=obj.data.id,value=obj.value;
        switch (obj.field) {
            case 'name':
                action.quick_edit('name',id,value);
                break;
            case 'sort':
                action.quick_edit('sort',id,value);
                break;
            case 'url':
                action.quick_edit('url',id,value);
                break;
        }
    });
    //监听并执行排序
    // layList.sort(['id','sort'],true);
    //点击事件绑定
 /*   layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'com.com_adv',a:'delete',q:{id:data.id}});
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
    })*/
</script>
{/block}
