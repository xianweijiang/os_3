{extend name="public/container"}
{block name="content"}
<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
    <div class="layui-row layui-col-space15" id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                    </form>
                </div>
            </div>
        </div>
        <!--版块列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        榜单列表
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="avatar">
                        {{#  if(d.avatar==''){ }}
                        <p style="height: 80px;margin-top: 15px;margin-left: 15px"><img class="avatar" style=""  data-image="{{d.avatar}}" src="/public/system/images/avatar.png" "></p>
                        {{#  } else { }}
                        <p style="height: 80px;margin-top: 15px;margin-left: 15px"><img class="avatar" style=""  data-image="{{d.avatar}}" src="{{d.avatar}}" "></p>
                        {{#  } }}
                    </script>
                    <script type="text/html" id="act">
                        <a  class="layui-btn layui-btn-xs" href="{:Url('user.user/index')}?uid={{d.uid}}" >
                            查看用户
                        </a>
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
    layList.tableList('List',"{:Url('rank_user_list')}",function (){
        join=[
            {field: 'uid', title: 'UID'},
            {field: 'avatar', title: '头像', event:'open_image', width: '6%', templet: '#avatar'},
            {field: 'nickname', title: '昵称'},
            {field: 'new_fans', title: '周粉丝数',sort: true,event:'new_fans'},
            {field: 'week_rank', title: '周排名'},
            {field: 'fans', title: '总粉丝数',sort: true,event:'fans'},
            {field: 'rank', title: '总排名'},
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
        }
    };
    //排序
    layList.sort(function (obj) {
        var type = obj.type;
        switch (obj.field){
            case 'new_fans':
                layList.reload({order: layList.order(type,'new_fans')},true,null,obj);
                break;
            case 'fans':
                layList.reload({order: layList.order(type,'fans')},true,null,obj);
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
