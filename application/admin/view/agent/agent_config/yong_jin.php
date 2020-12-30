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
        width: 400px;
        height: 30px;
        padding-left: 5px;
        margin-right: 5px;
    }
</style>
{/block}
{block name="content"}
<div class="row" style="width: 100%;margin-left: 0;">
    <div class="col-sm-12" style="background-color: #fff">
        <div class="layui-card-header">分销佣金设置</div>
        <div class="form" style="margin: 20px">
            <div class="input-box" style="display: flex;align-items: center">
                <label for="" style="margin-bottom: 0;">一级返佣比例</label>
                <input id="first_level" class="form-control valid" style="width: 300px;margin-left: 10px;margin-right: 5px;" value="{$agent_yongjin_config.agent_yongjin_config}" type="text">%
            </div>
            <div style="margin-top: 5px;color: #999;font-size: 12px;margin-left: 88px;">指订单交易成功后给一级推荐人返佣的比例0 ~100，例如，70 = 返该商品剥比的70%</div>
            <div class="input-box" style="display: flex;align-items: center;margin-top: 20px;">
                <label for="" style="margin-bottom: 0;">二级返佣比例</label>
                <input id="second_level" class="form-control valid" style="width: 300px;margin-left: 10px;margin-right: 5px;" value="{$agent_yongjin_config.agent_yongjin_config_2}" readonly type="text">%
            </div>
            <div style="margin-top: 5px;color: #999;font-size: 12px;margin-left: 88px;">指订单交易成功后给二级推荐人返佣的比例0 ~100，例如，30 = 返该商品剥比的30%</div>
            <div class="btn" id="save_btn" style="background-color: #0092DC;color: #fff;margin: 40px 0 20px 116px;">
                保存
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
  $("#first_level").bind("input propertychange",function(event){
    var firstVal = $("#first_level").val();
    firstVal = Number(firstVal);
    $("#second_level").val(100 - firstVal)
  });
  $('#save_btn').on('click',function(){
    var list = {};
    list.agent_yongjin_config = $("#first_level").val();
    $.ajax({
      url:"{:Url('saveYongJin')}",
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
<!--佣金设置页面

/admin/agent.agent_config/saveYongJin

agent_yongjin_config-->