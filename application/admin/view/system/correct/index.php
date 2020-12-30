{extend name="public/container"}
{block name="content"}
<div class="row" style="width: 100%;margin-left: 0;">
    <div class="col-sm-12" style="background-color: #fff;min-height: 500px;">
        <div style="width: 100%;margin-top: 50px; text-align: center;">
            <button type="button" class="btn btn-w-m btn-primary" data-url="{:Url('system.correct/correct_forum')}">版块数据修正</button>
            <button type="button" class="btn btn-w-m btn-primary" data-alink="1" data-url="{:Url('system.correct/correct_thread')}">帖子数据修正</button>
            <button type="button" class="btn btn-w-m btn-primary" data-alink="1" data-url="{:Url('system.correct/correct_user')}">用户数据修正</button>
        </div>
        <div style="width: 100%;margin-top: 50px; text-align: center; color: #848484；">修正完成后会存在10分钟的缓存，如需立刻刷新数据，请到“刷新缓存”页面删除缓存 </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $('button').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        var open_link=_this.data('alink');
        $eb.$swal('delete',function(){
            if(open_link==1){
                location.href=url;
            }else{
                $eb.axios.get(url).then(function(res){
                    if(res.status == 200 && res.data.code == 200) {
                        $eb.$swal('success',res.data.msg);
                    }else
                        return Promise.reject(res.data.msg || '操作失败')
                }).catch(function(err){
                    $eb.$swal('error',err);
                });
            }
        },{'title':'您确定要进行此操作吗？','text':'操作需要一定时间,请耐心等待！','confirm':'是的，我要操作'})
    })
</script>
{/block}
