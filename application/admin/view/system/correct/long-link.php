{extend name="public/container"}
{block name="content"}
<div class="row" style="width: 100%;margin-left: 0;">
    <div class="col-sm-12" style="background-color: #fff;min-height: 600px;">
        <div style="margin-top: 10px; border-bottom: 1px solid #f3f0f0; width: 100%; font-size: 22px; line-height: 50px;">{$title}</div>
        <div style="margin-top: 20px;"><a href="{:Url('system.correct/index')}" class="btn btn-w-m btn-primary">返回</a></div>
        <div style="width: 100%;margin-top: 20px; text-align: center;">
            <div id="status-block" style="width: 500px;height: 400px;border: 1px solid #ececec;overflow-y: scroll;text-align: left; line-height: 30px; padding: 10px;"></div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    var url="{$link_url}";

    var row=1000;
    var show_message=function (str) {
        var $tag=$('#status-block');
        $tag.append('<p>'+str+'</p>');
        var scrollHeight = $tag.prop("scrollHeight");
        $tag.scrollTop(scrollHeight,200);
    }
    var ajax_post=function (page) {
        $.ajax({
            url:url,//地址
            dataType:'json',//数据类型
            type:'POST',//类型
            timeout:10000,//超时
            data:{
                'page':page,
                'row':row,
            },
            //请求成功
            success:function(res){
                console.log(res);
                if(res.code == 200) {
                    page=parseInt(res.data.now_page);
                    console.log(page);
                    var start_num=(page-1)*row+1;
                    if(res.data.has_more==1){
                        var num_string=''+start_num+'~'+(start_num+row-1)
                        show_message('-----------执行第'+num_string+'条数据------------');
                        ajax_post(page+1);
                    }else{
                        if(res.data.do_num>0){
                            var num_string=''+start_num+'~'+(start_num+res.data.do_num-1)
                            show_message('-----------执行第'+num_string+'条数据------------');
                        }
                        show_message('-----------执行完成------------');
                    }
                }else{
                    show_message('-----------执行失败------------');
                    return false;
                }
            },
            //失败/超时
            error:function(XMLHttpRequest,textStatus,errorThrown){
                if(textStatus==='timeout'){
                    alert('请求超时');
                }
            }
        });
    };
    $(function () {
        show_message('-----------开始执行------------');
        ajax_post(1);
    });
</script>
{/block}