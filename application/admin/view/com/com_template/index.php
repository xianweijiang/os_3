{extend name="public/container"}
{block name="head_top"}
<script src="{__FRAME_PATH}js/toast-js.js"></script>
<style>
    .content {
        background-color: #fff;
        padding: 20px;
    }
    .platform-content{
        border-bottom: 1px solid #eee;
    }
    .module-content{
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }
    .unit-content{
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }
    .unit-box-content{
        margin-top: 10px;
    }
    .unit-box-content .checkbox-content{
        margin-left: 10px;
        height: 30px;
    }
    .title{
        font-size: 20px;
        font-weight: 600;
        margin: 15px 0;
    }
    .platform-content .checkbox-content{
        margin-top: 10px;
        display: flex;
        border-bottom: 1px solid #ccc;
        font-size: 18px;
    }
    .platform-content .checkbox-content label{
        display: flex;
        align-items: center;
        font-weight: 500;
        min-width: 60px;
        margin-left: 20px;
        height: 30px;
        font-size: 18px;
    }
    .choose-box{
        display: flex;
        align-items: center;
        margin-top: 15px;
    }
    .choose-box .layui-input{
        width: 200px;
        border: none;
        outline: none;
        height: 16px;
    }
    .choose-box label{
        display: flex;
        align-items: center;
        font-weight: 500;
        margin-bottom: 0;
    }
    .time-label{
        margin-left: 20px;
        color: #0ca6f2;
        text-decoration: underline;
    }
    .btn-content{
        margin-top: 20px;
    }
    .submit-btn{
        margin: 0 auto;
        cursor: pointer;
        color: #fff;
        background-color: #169bd5;
        width: 150px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        border-radius: 10px;
    }
</style>
{/block}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">评论模板</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container">
                        {if condition="$is_free_ban AND $is_end_ban"}
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit')}',{h:document.body.clientHeight,w:document.body.clientWidth})" style="margin-top: 10px">添加模板</button>
                        {else/}
                        <button class="layui-btn layui-btn-sm" data-type="unable" style="margin-top: 10px">添加模板</button>
                        {/if}

                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="avatar">
                        {{#  if(d.avatar==''){ }}
                        <p style="height: 80px;margin-top: 15px;margin-left: 15px"><img class="avatar" style=""  data-image="{{d.avatar}}" src="/public/system/images/avatar.png" "></p>
                        {{#  } else { }}
                        <p style="height: 80px;margin-top: 15px;margin-left: 15px"><img class="avatar" style=""  data-image="{{d.avatar}}" src="{{d.avatar}}" "></p>
                        {{#  } }}
                    </script>
                    <script type="text/html" id="act_common">
                        <button class="btn btn-success btn-xs" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit')}?id={{d.id}}&status=0')" type="button"><i class="fa fa-warning"></i> 编辑
                        </button>
                        <button class="btn btn-warning btn-xs" lay-event='pass'  type="button"><i class="fa fa-success"></i>删除
                        </button>
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
    layList.tableList('List',"{:Url('get_template_list')}",function (){
        var join = [
            {type:'checkbox'},
            {field: 'id', title: 'ID', event:'id',width:'5%'},
            {field: 'content', title: '内容',width:'80%'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'10%'},
        ];
        return join;
    });
    //自定义方法
    var action={
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

    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });

    layList.switch('status',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'com.com_post',a:'quick_edit',p:{value:1,field:'status',id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'com.com_post',a:'quick_edit',p:{value:0,field:'status', id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });

    //监听并执行排序
    // layList.sort(['id','sort'],true);
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'pass':
                var url=layList.U({c:'com.com_template',a:'del_version',q:{id:data.id,status:-1}});
                var code = {title:"操作提示",text:"确定删除吗？",type:'info',confirm:'是的'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            layList.reload();
                            $eb.$swal('success','删除成功');
                        }else
                            return Promise.reject( '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
        }
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
                'top': - ($(that).parents('td').height() / 2 + $(that).height() + $(that).next('ul').height()/2),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }else{
            $(that).next('ul').css({
                'padding': 10,
                'top':$(that).parents('td').height() / 2 + $(that).height(),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }
    }
</script>
{/block}
