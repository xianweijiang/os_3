{extend name="public/container"}
{block name="head_top"}
<link href="{__ADMIN_PATH}module/wechat/news/css/style.css" type="text/css" rel="stylesheet">
<link href="{__FRAME_PATH}css/plugins/chosen/chosen.css" rel="stylesheet">
<script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/jquery.min.js"></script>
<script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/template.min.js"></script>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.all.js"></script>

<script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>
<script src="{__ADMIN_PATH}plug/validate/jquery.validate.js"></script>
<script src="{__FRAME_PATH}js/plugins/chosen/chosen.jquery.js"></script>
{/block}
{block name="content"}
<div class="row" style="width: 100%;margin-left: 0;">
   <div class="col-sm-12" style="background-color: #fff">
       <div class="panel-body">
           <form class="form-horizontal" id="signupForm">
               <div class="form-group">
                   <div class="col-md-12">
                       <label style="color:#aaa">奖励说明</label>
                       <textarea type="text/plain" id="myEditor" style="width:100%;"></textarea>
                   </div>
               </div>
           </form>
           <button type="button" class="btn btn-w-m btn-info save_news">保存</button>
       </div>
   </div>
</div>
{/block}
{block name="script"}
<script>
    var ue = UE.getEditor('myEditor',{
      autoHeightEnabled: false,
      initialFrameHeight: 400,
      wordCount: false,
      maximumWords: 100000
    });
    /**
    * 获取编辑器内的内容
    * */
    function getContent() {
        return (ue.getContent());
    }
    function hasContent() {
        return (UM.getEditor('myEditor').hasContents());
    }
    ue.ready(function() {
        ue.setContent('<?= $remarks?>');
    });

    $('.save_news').on('click',function(){
        var list = {};
        list.is_post = 1;
        list.remarks = getContent();/* 内容 */
        $.ajax({
            url:"{:Url('reward_remarks')}",
            data:list,
            type:'post',
            dataType:'json',
            success:function(re){
                if(re.code == 200){
                    $eb.message('success',re.msg);
                    setTimeout(function (e) {
                        parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                        parent.layer.close(parent.layer.getFrameIndex(window.name));
                    },600)
                }else{
                    $eb.message('error',re.msg);
                }
            }
        })
    });
</script>
{/block}