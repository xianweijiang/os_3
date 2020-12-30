{extend name="public/container"}
{block name="content"}
<div class="layui-fluid" style="background: #fff;margin-top: 7px;">
    <div class="layui-row layui-col-space15" id="app">

        <!--版块列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        榜单列表
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="summary">
                        <div style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 3;overflow: hidden;">{{d.summary}}</div>
                    </script>
                    <script type="text/html" id="image">
                        {{#  if(d.image==''){ }}
                        {{#  } else { }}
                        <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);" src="{{d.image}}">
                        {{#  } }}
                    </script>
                    <script type="text/html" id="status">
                        {{# if(d.status == 1){ }}
                        <i class="fa fa-check text-navyr"></i>
                        {{# }else{ }}
                        <i class="fa fa-close text-danger"></i>
                        {{# } }}
                    </script>
                    <script type="text/html" id="act">
                        {if condition="$is_free_ban AND $is_end_ban"}
                            {{# if(d.id ==1){ }}
                            <button type="button" class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_rank_thread')}?id={{d.id}}')">编辑</button>
                            <a  class="layui-btn layui-btn-xs" href="{:Url('rank.rank_thread/index')}?type=1" >
                                查看榜单
                            </a>
                            {{# } }}
                            {{# if(d.id ==2){ }}
                            <button type="button" class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_rank_thread')}?id={{d.id}}')">编辑</button>
                            <a  class="layui-btn layui-btn-xs" href="{:Url('rank.rank_thread/index')}?type=2" >
                                查看榜单
                            </a>
                            {{# } }}
                            {{# if(d.id ==3){ }}
                            <button type="button" class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_rank_thread')}?id={{d.id}}')">编辑</button>
                            <a  class="layui-btn layui-btn-xs" href="{:Url('rank.rank_thread/index')}?type=3" >
                                查看榜单
                            </a>
                            {{# } }}
                            {{# if(d.id ==4){ }}
                            <button type="button" class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_rank_thread')}?id={{d.id}}')">编辑</button>
                            <a  class="layui-btn layui-btn-xs" href="{:Url('rank.rank_thread/index')}?type=4" >
                                查看榜单
                            </a>
                            {{# } }}
                            {{# if(d.id ==5){ }}
                            <button type="button" class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_rank')}?id={{d.id}}')">编辑</button>
                            <a  class="layui-btn layui-btn-xs" href="{:Url('rank.rank_topic/index')}" >
                                查看榜单
                            </a>
                            {{# } }}
                            {{# if(d.id ==6){ }}
                            <button type="button" class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_rank_user')}?id={{d.id}}')">编辑</button>
                            <a  class="layui-btn layui-btn-xs" href="{:Url('rank.rank_user/index')}" >
                                查看榜单
                            </a>
                            {{# } }}
                            {{# if(d.id ==7){ }}
                            <button type="button" class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_rank')}?id={{d.id}}')">编辑</button>
                            <a  class="layui-btn layui-btn-xs" href="{:Url('rank.rank_search/index')}" >
                                查看榜单
                            </a>
                            {{# } }}
                        {else/}
                        <button type="button" class="layui-btn layui-btn-xs" data-type="unable">编辑</button>
                        <a class="layui-btn layui-btn-xs" data-type="unable">
                            查看榜单
                        </a>
                        {/if}
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script src="{__FRAME_PATH}js/toast-js.js"></script>
<script>
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('rank_list')}",function (){
        join=[
            {field: 'id', title: 'ID'},
            {field: 'title_one', title: '一级榜单'},
            {field: 'title_two', title: '二级榜单'},
            {field: 'summary', title: '说明',templet:'#summary',width:'16%'},
            {field: 'frequency', title: '更新频率'},
            {field: 'image', title: '背景图',templet:'#image'},
            {field: 'sort', title: '排序'},
            {field: 'status', title: '是否显示',templet:'#status'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'14%'},
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
                action.set_column('sort',id,value);
                break;
        }
    });
    layList.switch('is_status',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'shop.shop_column',a:'set_on',p:{is_on:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'shop.shop_column',a:'set_on',p:{is_on:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    //自定义方法
    var action={
        set_column:function(field,id,value){
            layList.baseGet(layList.Url({c:'shop.shop_column',a:'set_column',q:{field:field,id:id,value:value}}),function (res) {
                layList.msg(res.msg);
            });
        },
    };
    $('body').on("click",'[data-type="unable"]',function () {
        var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
        $eb.$swal('delete',function(){
            $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
        }, code)
    })


    //排序
    layList.sort(function (obj) {
        var type = obj.type;
        switch (obj.field){
            case 'id':
                // layList.reload({order: layList.order(type,'p.id')},true,null,obj);
                break;
            case 'sales':
                layList.reload({order: layList.order(type,'p.sales')},true,null,obj);
                break;
        }
    });
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });

    layList.laydate.render({
        elem:'#date_time',
        trigger:'click',
        eventElem:'#zd',
        range:true,
        change:function (value){
            $('#data').val(value);
            $('#date_time').text(value);
        }
    });

    var setData = function(val, ele){
        var $data = $('#data');
        $data.val(val);
        $(ele).parent().find('button').addClass('layui-btn-primary');
        $(ele).removeClass('layui-btn-primary');
        if(val == 'zd'){
            $('#date_time').show();
        }else{
            $('#date_time').hide();
        }
    }


</script>
{/block}
