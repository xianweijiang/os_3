{extend name="public/container"}
{block name="content"}
<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
    <div class="layui-tab layui-tab-brief" lay-filter="tab">
        <ul class="layui-tab-title">
            <li lay-id="list" {eq name='status' value='1'}class="layui-this" {/eq} >
            <a href="{eq name='status' value='1'}javascript:;{else}{:Url('index',['status'=>1])}{/eq}">商品列表({$onsale})</a>
            </li>
            <li lay-id="list" {eq name='status' value='-1'}class="layui-this" {/eq}>
            <a href="{eq name='status' value='-1'}javascript:;{else}{:Url('index',['status'=>-1])}{/eq}">商品回收站({$recycle})</a>
            </li>
        </ul>
    </div>
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">商品名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="store_name" class="layui-input" placeholder="请输入商品名称或商品id">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">状态</label>
                                <div class="layui-input-block">
                                    <select name="is_on">
                                        <option value="">全部</option>
                                        <option value="1">上架</option>
                                        <option value="0">下架</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">所属栏目</label>
                                <div class="layui-input-block">
                                    <select name="column">
                                        <option value="">全部</option>
                                        {volist name='column' id='vo'}
                                        <option value="{$vo.id}">{$vo.name}</option>
                                        {/volist}
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
        <!--商品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        列表[排序]，[库存]可进行快速修改,双击或者单击进入编辑模式,失去焦点可进行自动保存
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="layui-btn-container">
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}',{h:document.body.clientHeight,w:document.body.clientWidth})">添加商品</button>
                        {else/}
                        <button class="layui-btn layui-btn-sm" data-type="unable">添加商品</button>
                        {/if}

                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--图片-->
                    <script type="text/html" id="image">
                        <img style="cursor: pointer" lay-event="open_image" src="{{d.image}}">
                    </script>
                    <!--上架|下架-->
                    <script type="text/html" id="checkboxstatus">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_on' lay-text='上架|下架'  {{ d.is_on == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="store_info">
                        <h4>{{d.store_info}}</h4>
                    </script>
                    <!--商品名称-->
                    <script type="text/html" id="store_name">
                        <h4>{{d.store_name}}</h4>
                    </script>
                    <script type="text/html" id="cash_price">
                        <p>积分:<font color="red">{{d.score_price}}</font> </p>
                        <p>实付:<font color="red">{{d.cash_price}}</font> </p>
                    </script>
                    <!--操作-->
                    <script type="text/html" id="act">
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="$eb.createModalFrame('{{d.store_name}}-编辑','{:Url('edit')}?id={{d.id}}',{h:document.body.clientHeight,w:document.body.clientWidth})">
                            编辑
                        </button>
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" class="" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content')}?id={{d.id}}')">
                                    <i class="fa fa-pencil"></i> 商品详情</a>
                            </li>
                            {{# if(d.status == -1){ }}
                            <li>
                                <a href="javascript:void(0);" lay-event='delstor'>
                                    <i class="fa fa-trash"></i> 恢复商品
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='delall'>
                                    <i class="fa fa-trash"></i> 清空
                                </a>
                            </li>
                            {{# }else{ }}
                            <li>
                                <a href="javascript:void(0);" lay-event='delstor'>
                                    <i class="fa fa-trash"></i> 移到回收站
                                </a>
                            </li>
                            {{# } }}
                        </ul>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    var status=<?=$status?>;
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('product_list',['status'=>$status])}",function (){
        switch(parseInt(status)){
            case 1:
                var join=[
                    {field: 'id', title: 'ID', sort: true,event:'id',width:'6%'},
                    {field: 'image', title: '商品图片',templet:'#image',width:'8%'},
                    {field: 'store_name', title: '商品名称',templet:'#store_name'},
                    {field: 'store_info', title: '商品简介',templet:'#store_info'},
                    {field: 'cash_price', title: '价格',templet:'#cash_price',width:'8%'},
                    {field: 'column_name', title: '所属栏目',width:'6%'},
                    {field: 'sales', title: '兑换数量',sort: true,width:'5%'},
                    {field: 'stock', title: '库存',width:'5%',edit:'stock'},
                    {field: 'sort', title: '排序',edit:'sort',width:'5%'},
                    {field: 'status', title: '状态',templet:"#checkboxstatus",width:'6%'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'14%'},
                ];
                break;
            case -1:
                var join=[
                    {field: 'id', title: 'ID', sort: true,event:'id',width:'6%'},
                    {field: 'image', title: '商品图片',templet:'#image',width:'8%'},
                    {field: 'store_name', title: '商品名称',templet:'#store_name'},
                    {field: 'store_info', title: '商品简介',templet:'#store_info'},
                    {field: 'cash_price', title: '价格',templet:'#cash_price',width:'8%'},
                    {field: 'column_name', title: '所属栏目',width:'8%'},
                    {field: 'sales', title: '兑换数量',sort: true,width:'7%'},
                    {field: 'stock', title: '库存',width:'7%',edit:'stock'},
                    {field: 'sort', title: '排序',edit:'sort',width:'7%'},
                    {field: 'status', title: '状态',templet:"#checkboxstatus",width:'7%'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'14%'},
                ];
                break;
        }
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
                action.set_product('sort',id,value);
                break;
            case 'stock':
                action.set_product('stock',id,value);
                break;
        }
    });
    //上下加商品
    layList.switch('is_on',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'shop.shop_product',a:'set_on',p:{is_on:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'shop.shop_product',a:'set_on',p:{is_on:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'shop.shop_product',a:'delete',q:{id:data.id}});
                if(data.status==-1) var code = {title:"操作提示",text:"确定恢复商品操作吗？",type:'info',confirm:'是的，恢复该商品'};
                else var code = {title:"操作提示",text:"确定将该商品移入回收站吗？",type:'info',confirm:'是的，移入回收站'};
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
            case 'delall':
                var url=layList.U({c:'shop.shop_product',a:'delall',q:{id:data.id}});
                var code = {title:"操作提示",text:"确定将该商品彻底删除吗，删除以后无法找回？",type:'info',confirm:'是的，彻底删除'};
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
        }
    })
    //排序
    layList.sort(function (obj) {
        var type = obj.type;
        switch (obj.field){
            case 'id':
                layList.reload({order: layList.order(type,'id')},true,null,obj);
                break;
            case 'sales':
                layList.reload({order: layList.order(type,'sales')},true,null,obj);
                break;
        }
    });
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
    //自定义方法
    var action={
        set_product:function(field,id,value){
            layList.baseGet(layList.Url({c:'shop.shop_product',a:'set_product',q:{field:field,id:id,value:value}}),function (res) {
                layList.msg(res.msg);
            });
        },
        show:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                layList.basePost(layList.Url({c:'shop.shop_product',a:'product_show'}),{ids:ids},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要上架的商品');
            }
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
