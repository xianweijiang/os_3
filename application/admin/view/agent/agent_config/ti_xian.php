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
        <div class="layui-card-header">提现设置</div>
        <div class="form" style="margin: 20px">
            <div class="input-box" style="display: flex;align-items: center;color: #333">
                <label for="" style="margin-bottom: 0;">提现额度下限</label>
                <input id="lower_num" class="common-input form-control valid" value="{$agent_tixian_config.agent_tixian_config_min}" type="text">元
            </div>
            <div class="input-box" style="display: flex;align-items: center;margin-top: 20px;color: #333">
                <label for="" style="margin-bottom: 0;">提现额度上限</label>
                <input id="height_num" class="common-input form-control valid" value="{$agent_tixian_config.agent_tixian_config_max}" type="text">元
            </div>
            <div style="margin-top: 5px;color: #999;font-size: 12px;margin-left: 88px;">0元表示不限制每日提现金额</div>
            <div class="input-box" style="display: flex;align-items: center;margin-top: 20px;color: #333">
                <label for="" style="margin-bottom: 0;width: 78px;text-align: right">每月</label>
                <input id="data_day" class="common-input form-control valid" style="width: 200px;" value="{$agent_tixian_config.agent_tixian_config_day}" type="text">号可提现上个自然月结算收益
            </div>
            <div style="font-size: 12px;color: #999;margin-left: 88px;margin-top: 4px;">该日期即每月结算日，建议设置日期在每月25~28日之间，以尽量确保上月订单能正常结算。</div>
            <div class="input-box" style="display: flex;align-items: flex-start;margin-top: 20px;">
                <label for="" style="margin-bottom: 0;width: 78px;text-align: right">提现规则</label>
                <textarea type="text/plain" id="myEditor" style="width:700px;margin-left: 10px;">{$agent_tixian_config.agent_tixian_config_rules}</textarea>
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
  $('#save_btn').on('click',function(){
    var list = {};
    list.agent_tixian_config_min = $("#lower_num").val();
    list.agent_tixian_config_max = $("#height_num").val();
    list.agent_tixian_config_day = $("#data_day").val();
    list.agent_tixian_config_rules = getContent();
    if(Number(list.agent_tixian_config_min) >= Number(list.agent_tixian_config_max) && Number(list.agent_tixian_config_max) !== 0){
      $eb.message('error',"提现额度下限不能大于提现额度上限！");
      return;
    }
    $.ajax({
      url:"{:Url('saveTiXian')}",
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
<!--提现设置页面

/admin/agent.agent_config/saveTiXian

'agent_tixian_config_max',
'agent_tixian_config_min',
'agent_tixian_config_day',
'agent_tixian_config_rules'-->