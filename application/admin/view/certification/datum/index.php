{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
             <div class="layui-row layui-col-space15"  id="app">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-body">
                            <form class="layui-form layui-form-pane" action="">
                                <div class="layui-form-item">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">关键词</label>
                                        <div class="layui-input-block">
                                            <input type="text" name="keyword" class="layui-input" placeholder="请输入关键词/名称/标识">
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">状态</label>
                                        <div class="layui-input-block">
                                            <select name="status">
                                                <option value="1">开启</option>
                                                <option value="0">关闭</option>
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
                <!--列表-->
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-body">
                            <!-- <div class="alert alert-info" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div> -->
                            <div class="layui-btn-container">
                                {if condition="$is_free_ban AND $is_end_ban"}
                                <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{$addurl}')">添加资料项</button>
                                {else/}
                                <button type="button" class="btn btn-w-m btn-primary" data-type="unable">添加资料项</button>
                                {/if}
                            </div>
                            <table class="layui-hide" id="List" lay-filter="List"></table>
                            <!--开启|关闭-->
                            <script type="text/html" id="checkboxstatus">
                                <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='status' lay-text='开启|关闭'  {{ d.status == 1 ? 'checked' : '' }}>
                            </script>
                            
                            <!--操作-->
                            <script type="text/html" id="act">
                                {{# if(d.field != 'rztx'){ }}
                                <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="$eb.createModalFrame('{{d.name}}-编辑','{:Url('edit')}?id={{d.id}}',{h:document.body.clientHeight,w:document.body.clientWidth})">
                                    编辑
                                </button>
                                {{# } }}
                                 {{# if(d.built_in == 0){ }}
                                  <a href="javascript:void(0);" lay-event='delstor'>
                                            <i class="fa fa-trash"></i> 删除
                                        </a>
                                
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
    layList.tableList('List',"{:Url('list')}",function (){
        var join=new Array();
        join=[
            {field: 'id', title: 'ID', sort: true,event:'id',width:'6%'},
            {field: 'field', title: '标识'},
            {field: 'name', title: '名称'},
            {field: 'input_tips', title: '备注说明'},
            {field: 'form_type', title: '样式'},
            {field: 'setting', title: '参数'},
            {field: 'type_id', title: '所属认证类型'},
            {field: 'status', title: '状态',templet:"#checkboxstatus",width:'8%'},
            {field: 'sort', title: '排序',width:'6%'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'10%'},
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
    //状态
    layList.switch('status',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'certification.datum',a:'set_status',p:{status:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'certification.datum',a:'set_status',p:{status:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'certification.datum',a:'delete',q:{id:data.id}});
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
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
    //自定义方法
    var action={
        set:function(field,id,value){
            layList.baseGet(layList.Url({c:'certification.datum',a:'set',q:{field:field,id:id,value:value}}),function (res) {
                layList.msg(res.msg);
            });
        },
        unable:function(){
            var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
            $eb.$swal('delete',function(){
                $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
            }, code)
        },
    };
    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });
</script>
{/block}
