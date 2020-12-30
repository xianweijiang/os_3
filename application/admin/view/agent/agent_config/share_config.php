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
<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }
    .createFollowCheck input[type="number"] {
        -moz-appearance: textfield;
    }
    .common-input{
        margin-left: 10px;
        width: 300px;
        margin-right: 5px;
    }
</style>
{/block}
{block name="content"}
<div class="row" style="width: 100%;margin-left: 0;">
    <div class="col-sm-12" style="background-color: #fff">
        <div class="layui-card-header">分享海报配置</div>
        <div class="form" style="margin: 20px">
            <div class="input-box" style="display: flex;align-items: center;">
                <label for="" style="margin-bottom: 0;width: 78px;text-align: right">企业名称</label>
                <input class="common-input form-control valid" id="name" value="{$agent_share_config.agent_share_config_title}" type="text">
            </div>
            <div class="input-box" style="display: flex;align-items: flex-start;margin-top: 20px;">
                <label for="" style="margin-bottom: 0;width: 78px;text-align: right">企业LOGO<br>(40*40)</label>
                <input type="file" class="upload" name="image" style="display: none;" id="image" />
                <a style="display: block;width: 102px;border: 1px solid #E5E6E7;margin-left: 10px;" class="btn-sm add_image upload_span" >
                    {if condition="$agent_share_config.agent_share_config_logo"}
                    <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url('{$agent_share_config.agent_share_config_logo}')">
                        <input value="" type="hidden" id="image_input" name="local_url">
                    </div>
                    {else/}
                    <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url('/public/system/module/wechat/news/images/image.png')">
                        <input value="" type="hidden" id="image_input" name="local_url">
                    </div>
                    {/if}
                </a>
            </div>
            <div class="btn" id="save_btn" style="background-color: #0092DC;color: #fff;margin: 40px 0 20px 116px;">
                保存
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
  /**
   * 上传图片
   * */
  $('.upload_span').on('click',function (e) {
//                $('.upload').trigger('click');
    createFrame('选择图片','{:Url('widget.images/index')}?fodder=image');
  })
  function changeIMG(index,pic){
    $(".image_img").css('background-image',"url("+pic+")");
    $(".active").css('background-image',"url("+pic+")");
    $('#image_input').val(pic);
  };
  function createFrame(title,src,opt){
    opt === undefined && (opt = {});
    return layer.open({
      type: 2,
      title:title,
      area: [(opt.w || 700)+'px', (opt.h || 650)+'px'],
      fixed: false, //不固定
      maxmin: true,
      moveOut:false,//true  可以拖出窗外  false 只能在窗内拖
      anim:5,//出场动画 isOutAnim bool 关闭动画
      offset:'auto',//['100px','100px'],//'auto',//初始位置  ['100px','100px'] t[ 上 左]
      shade:0,//遮罩
      resize:true,//是否允许拉伸
      content: src,//内容
      move:'.layui-layer-title'
    });
  }
  $('#save_btn').on('click',function(){
    var list = {};
    list.agent_share_config_title = $("#name").val();
    list.agent_share_config_logo = $("#image_input").val();/* 图片*/
    $.ajax({
      url:"{:Url('saveShareConfig')}",
      data:list,
      type:'post',
      dataType:'json',
      success:function(re){
        if(re.code == 200){
          $eb.message('success',re.msg);
        }else{
          $eb.message('error',re.msg);
        }
      }
    })
  });
</script>
{/block}
<!--分销申请协议设置页面

/admin/agent.agent_config/saveXieyi

agent_xieyi_config-->