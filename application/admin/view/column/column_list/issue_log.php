{include file="public/frame_head"}
<style type="text/css" media="screen">
    td img{width: 50px; height: 50px;}
    table{
        max-width: none!important;
    }
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">类型</th>
                            <th class="text-center">期刊名称</th>
                            <th class="text-center">是否试读</th>
                            <th class="text-center">状态</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">{$vo.id}</td>
                            <td class="text-center">
	                            {if $vo.type == 2}
	                            音频
	                            {elseif $vo.type == 3}
	                            视频
	                            {else}
	                            图文
	                            {/if}
                            </td>
                            <td class="text-center" style="white-space: normal">{$vo.name}</td>
                            <td class="text-center">
                                {if $vo.is_read == 1}
                                试读
                                {else}
                                不试读
                                {/if}
                            </td>
                            <td class="text-center">
                                {if $vo.is_show == 1}
                                显示
                                {else}
                                隐藏
                                {/if}
                            </td>
                            <td class="text-center">
                                <a href="javascript:void(0);" onclick="$eb.createModalFrame(this.innerText,'{:Url('column.column_textns/ups')}?id={$vo.id}&type={$vo.type}')"">编辑</a>
                                <a href="javascript:void(0);" opt='{$vo.id}' class="dels">删除</a>
                            </td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="{__ADMIN_PATH}js/layuiList.js"></script>
    <script>
        $('.dels').click(function () {
        listid=$(this).attr('opt');
        $.ajax({
                url:"{:Url('column.column_textns/des')}?id="+listid,
                type:'get',
                dataType:'json',
                success:function(re){
                    console.log(re)
                    if(re.code == 200){
                        $eb.message('success',re.msg);
                        setTimeout(function (e) {
                            // parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                           parent.layer.close(parent.layer.getFrameIndex(window.name));
                        },600)
                    }else{
                        $eb.message('error',re.msg);
                    }
                }
            })
        });
    </script>
</div>
{include file="public/inner_footer"}
