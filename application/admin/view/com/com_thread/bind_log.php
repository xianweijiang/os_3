{volist name='user' id='vo'}
    <button type="button" data-role="choose_uid" class="user-name" data-uid="{$vo['uid']}" data-nickname="{$vo['nickname']}">{$vo.nickname}</button>
{/volist}

<script>
    var formSelects = layui.formSelects;
    $('[data-role="choose_uid"]').click(function () {
        var uid=$(this).attr('data-uid');
        $.ajax({
            url:"{:Url('get_user')}",
            data:{uid:uid},
            type:'get',
            dataType:'json',
            success:function(res){
                if(res.code == 200){
                    var selectHtml = '<option value='+res.data.uid+' selected>'+res.data.nickname+'</option>';
                    if(res.data){
                        $("#bind_select").append(selectHtml);
                        var form = layui.form;
                        form.render();
                        formSelects.config('user_select', {
                            type: 'get',                //请求方式: post, get, put, delete...
                            searchName: 'nickname',      //自定义搜索内容的key值
                            clearInput: true,          //当有搜索内容时, 点击选项是否清空搜索内容, 默认不清空
                        }, false);
                    }
                }else{
                    Toast.error(res.msg);
                }
            }
        });
    });

</script>