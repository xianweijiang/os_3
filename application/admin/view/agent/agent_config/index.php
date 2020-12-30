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
        width: 200px;
        height: 30px;
        padding-left: 5px;
    }
</style>
{/block}
{block name="content"}
<div class="row" style="width: 100%;margin-left: 0;">
    <div class="col-sm-12" style="background-color: #fff">
        <div class="layui-card-header">分销设置</div>
        <div class="form">
            <div class="layui-form-item">
                <label class="layui-form-label">成为分销商的条件</label>
                <div style="float:left;padding-top: 9px;">
                    <div style="display: flex;align-items: center">
                        <input id="none" type="radio" name="condition" value="1" style="margin-top: 0;" checked>
                        <label for="none" style="margin-bottom: 0;margin-left: 10px;margin-right: 5px;">无条件（需要审核）</label>
                    </div>
                    <div style="margin-top: 20px;">
                        <div style="display: flex;align-items: center">
                            <input id="set" type="radio" name="condition" value="2" style="margin-top: 0;">
                            <label for="set" style="margin-bottom: 0;margin-left: 10px;margin-right: 5px;">设置条件（需要审核）</label>
                        </div>
                        <div id="second_condition" style="margin-left: 36px;display: none;">
                            <div style="display: flex;align-items: center;margin-top: 20px;">
                                <input name="task" style="margin-top: 0;" type="checkbox" value="store_pay" id="store_pay"/>
                                <div style="display: flex;align-items: center">
                                    <div style="width: 85px;margin-left: 10px;">商城消费满</div>
                                    <input class="form-control valid" id="pay_num" style="margin-right: 10px;width: 200px;" type="number">
                                    元
                                </div>
                            </div>
                            <div style="display: flex;align-items: center;margin-top: 20px;">
                                <input name="task" style="margin-top: 0;" type="checkbox" value="com_post" id="com_post"/>
                                <div style="display: flex;align-items: center">
                                    <div style="width: 85px;margin-left: 10px;">社区发帖数满</div>
                                    <input class="form-control valid" id="post_num" style="margin-right: 10px;width: 200px;" type="number">
                                    条
                                </div>
                            </div>
                            <div style="display: flex;align-items: center;margin-top: 20px;">
                                <input name="task" style="margin-top: 0;" type="checkbox" value="total_score" id="total_score"/>
                                <div style="display: flex;align-items: center">
                                    <div style="width: 85px;margin-left: 10px;">累积经验值达</div>
                                    <input class="form-control valid" id="exp_num" style="width: 200px" type="number">
                                </div>
                            </div>
	                        <div style="display: flex;align-items: center;margin-top: 20px;">
		                        <input name="task" style="margin-top: 0;" type="checkbox" value="column_pay" id="column_pay"/>
		                        <div style="display: flex;align-items: center">
			                        <div style="width: 100px;margin-left: 10px;">知识商城消费满</div>
			                        <input class="form-control valid" id="col_num" style="width: 185px;margin-right: 10px;" type="number">
			                        元
		                        </div>
	                        </div>
                        </div>
                    </div>
                    <div style="margin-top: 20px;">
                        <div style="display: flex;align-items: center">
                            <input id="buy" type="radio" name="condition" value="3" style="margin-top: 0;">
                            <label for="buy" style="margin-bottom: 0;margin-left: 10px;margin-right: 5px;">购买商品自动生效</label>
                        </div>
                        <div id="third_condition" style="display: none;">
                            <div style="margin-top: 20px;margin-left: 36px;display: flex;">
                                <div style="display: flex;align-items: center">
                                    <input id="anything" type="radio" name="goods" value="all_goods" style="margin-top: 0;" checked>
                                    <label for="anything" style="margin-bottom: 0;margin-left: 10px;margin-right: 5px;font-weight: 500">购买任意商品</label>
                                </div>
                                <div style="display: flex;align-items: center;margin-left: 10px">
                                    <input id="this_id" type="radio" name="goods" value="one_goods" style="margin-top: 0;">
                                    <label for="this_id" style="margin-bottom: 0;margin-left: 10px;margin-right: 5px;font-weight: 500;">购买指定商品</label>
                                </div>
	                              <div style="display: flex;align-items: center;margin-left: 10px">
		                              <input id="col_id" type="radio" name="goods" value="column_goods" style="margin-top: 0;">
		                              <label for="col_id" style="margin-bottom: 0;margin-left: 10px;margin-right: 5px;font-weight: 500;">购买指定专栏商品</label>
	                              </div>
                            </div>
                            <div id="goods_choose_box" style="margin: 10px 0 0 36px;align-items: center;display: none;">
                                <input id="goods_name_input" class="form-control valid" readonly type="text" value="">
                                <input type="hidden" id="goods_id_input">
                                <div id="open_choose_goods" style="border-radius: 4px;cursor: pointer;min-width: 100px !important;height: 30px;line-height: 30px;background-color: #0092DC;color: #fff;padding: 0 20px;margin-left:10px;text-align: center">选择商品</div>
	                              <div id="open_choose_column" style="border-radius: 4px;cursor: pointer;min-width: 100px !important;height: 30px;line-height: 30px;background-color: #0092DC;color: #fff;padding: 0 20px;margin-left:10px;text-align: center">选择专栏</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn" id="save_btn" style="background-color: #0092DC;color: #fff;margin: 20px 0 20px 116px;">
                保存
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
  $('#save_btn').on('click',function(){
    var taskList=[];
    $('input[name="task"]:checked').each(function(){
      taskList.push($(this).val());//向数组中添加元素
    });
    var list = {};
    list.agent_way = $("input[name='condition']:checked").val();

    list.agent_rules = taskList;
    list.store_pay_value = $("#pay_num").val();
    list.com_post_value = $("#post_num").val();
    list.total_score_value = $("#exp_num").val();
    list.column_pay_value = $("#col_num").val();

    list.goods_type = $("input[name='goods']:checked").val();
    list.goods_id = $("#goods_id_input").val();

    var choosePay = $.inArray("store_pay",list.agent_rules);
    var choosePost = $.inArray("com_post",list.agent_rules);
    var chooseExp = $.inArray("total_score",list.agent_rules);
    var chooseCol = $.inArray("column_pay",list.agent_rules);

    if(list.agent_way === "2"){
      console.log(taskList)
      if(taskList.length === 0){
        $eb.message('error',"请选择条件");
        return;
      }
      if(choosePay !== -1 && list.store_pay_value === ""){
        $eb.message('error',"请输入商城消费条件");
        return;
      }
      if(choosePost !== -1 && list.com_post_value === ""){
        $eb.message('error',"请输入社区发帖条件");
        return;
      }
      if(chooseExp !== -1 && list.total_score_value === ""){
        $eb.message('error',"请输入累积经验条件");
        return;
      }
      if(chooseCol !== -1 && list.column_pay_value === ""){
            $eb.message('error',"请输入知识商城消费条件");
            return;
        }
    }
    if(list.agent_way === "3" && list.goods_type === "one_goods"){
          if(list.goods_id === ""){
              $eb.message('error',"请选择商品");
              return;
          }
    }
    if(list.agent_way === "3" && list.goods_type === "column_goods"){
          if(list.goods_id === ""){
              $eb.message('error',"请选择专栏商品");
              return;
          }
    }
    $.ajax({
      url:"{:Url('saveConfig')}",
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
  $('input[type=radio][name=condition]').change(function() {
    if (this.value === '1'){
      $("#second_condition").hide();
      $("#third_condition").hide();
    }else if(this.value === '2'){
      $("#second_condition").show();
      $("#third_condition").hide();
    }else if(this.value === "3"){
      $("#second_condition").hide();
      $("#third_condition").show();
    }
  });
  $('input[type=radio][name=goods]').change(function() {
    if (this.value === 'all_goods'){
      $("#goods_choose_box").hide();
    }else if(this.value === 'one_goods'){
      $("#goods_choose_box").css("display","flex");
      $("#open_choose_column").hide();
      $("#open_choose_goods").show();
    }else if(this.value === 'column_goods'){
        $("#goods_choose_box").css("display","flex");
        $("#open_choose_goods").hide();
        $("#open_choose_column").show();
    }
  });
  $('#open_choose_goods').on("click",function () {
      //var goods_type_product = $('#this_id').attr('value') ;
    $eb.createModalFrame("选择商品",
		    '{:Url('add_goods')}',
				  {w:600,h:500}
    )
  });
  $('#open_choose_column').on("click",function () {
      //var goods_type_column = $('#col_id').attr('value') ;
      $eb.createModalFrame("选择专栏商品",
          '{:Url('add_columns')}',
          {w:600,h:500}
  )
  });
  window.addEventListener("storage", function (e) {
    if(e.key === "add_goods_val"){
      var goodsVal = e.newValue;
      console.log(goodsVal)
      goodsVal = JSON.parse(goodsVal);
      console.log(goodsVal)
      $("#goods_id_input").val(goodsVal.id);
      $("#goods_name_input").val(goodsVal.name);
      window.localStorage.removeItem("add_goods_val")
    }else if(e.key === "add_columns_val"){
        var goodsVal = e.newValue;
        goodsVal = JSON.parse(goodsVal);
        $("#goods_id_input").val(goodsVal.id);
        $("#goods_name_input").val(goodsVal.name);
        window.localStorage.removeItem("add_columns_val")
    }
  });
</script>
{if condition="$agent_config.agent_way eq '2'"}
<script>
  $(function () {
    $('input[type=radio][name=condition][value="2"]').prop("checked",true);
    $("#second_condition").show();
    $("#third_condition").hide();
    var taskList = "{$agent_config.agent_rules}";
    console.log(taskList)
    var arr = taskList.split(',');

    for(var i in arr){
      if(arr[i] === "store_pay"){
        $('#store_pay').prop("checked",true);
        $('#pay_num').val("{$agent_config.store_pay_value}")
      }else if(arr[i] === "com_post"){
        $('#com_post').prop("checked",true);
        $('#post_num').val("{$agent_config.com_post_value}")
      }else if(arr[i] === "total_score"){
        $('#total_score').prop("checked",true);
        $('#exp_num').val("{$agent_config.total_score_value}")
      }else if(arr[i] === "column_pay"){
          $('#column_pay').prop("checked",true);
          $('#col_num').val("{$agent_config.column_pay_value}")
      }
    }
  });
</script>
{elseif condition="$agent_config.agent_way eq '3'"/}
<script>
  $('input[type=radio][name=condition][value="3"]').prop("checked",true);
  $("#second_condition").hide();
  $("#third_condition").show();
  var goodsType = "{$agent_config.goods_type}";
  if(goodsType === "one_goods"){
    $('input[type=radio][name=goods][value="one_goods"]').prop("checked",true);
    $("#goods_choose_box").css("display","flex");
    $("#goods_id_input").val("{$agent_config.goods_id}");
    $("#goods_name_input").val("{$goods_info.store_name}");
    $("#open_choose_column").hide();
  }
  if(goodsType === "column_goods"){
      $('input[type=radio][name=goods][value="column_goods"]').prop("checked",true);
      $("#goods_choose_box").css("display","flex");
      $("#goods_id_input").val("{$agent_config.goods_id}");
      $("#goods_name_input").val("{$goods_info.store_name}");
      $("#open_choose_goods").hide();
      //console.log('ID='+{$agent_config.goods_id});
  }
</script>
{/if}
{/block}

