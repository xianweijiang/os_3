{extend name="public/modal-frame"}
{block name="head_top"}
<link href="{__ADMIN_PATH}module/wechat/news/css/style.css" type="text/css" rel="stylesheet">
<link href="{__FRAME_PATH}css/plugins/chosen/chosen.css" rel="stylesheet">
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.all.js"></script>
<script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>
<script src="{__ADMIN_PATH}plug/validate/jquery.validate.js"></script>
<script src="{__FRAME_PATH}js/plugins/chosen/chosen.jquery.js"></script>
<style>
    .upload-img-box {
        border: 1px solid #E5E6E7;
        padding: 5px 10px;
        margin-right: 5px;
        margin-top: 5px;
        border-radius: 3px;
        position: relative;
        width: 102px;
        height: 92px;
    }

    .upload-img-box-img {
        width: 80px;
        height: 80px;
    }

    .delete-btn {
        display: none;
        position: absolute;
        top: 5px;
        right: 10px;
        width: 80px;
        height: 80px;
        cursor: pointer;
        font-size: 20px;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .delete-btn img {
        width: 30px;
        height: 30px;
    }
</style>
{/block}
{block name="content"}
<div class="panel">
    <div class="panel-body">
        <form class="form-horizontal" id="signupForm">
            <div class="form-group">
                <div class="col-md-12">
                    <div class="input-group" style="display: flex">
                        <span class="input-group-addon" style="width: auto;line-height: 24px">作者</span>
                        {if condition="$style eq 'create'"}
                        <input class="layui-input now_bind_user" readonly id="now_bind_user" value=""
                               style="display: inline-block">
                        <button type="button" class="btn btn-w-m btn-info bind-user" style="height: 38px;">绑定用户</button>
                        {elseif condition="$style eq 'edit_weibo'"/}
                        <input class="layui-input now_bind_user" readonly id="" value="{$info.user}"
                               data-id="{$info.author_uid}" style="display: inline-block">
                        <button type="button" class="btn btn-w-m btn-info bind-user" style="height: 38px;">绑定用户</button>
                        {elseif condition="$style eq 'view_weibo'"/}
                        <input class="layui-input now_bind_user" readonly id="" value="{$info.user}"
                               data-id="{$info.author_uid}" style="display: inline-block">
                        {/if}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-addon">虚拟浏览量</span>
                        {if condition="$style eq 'create'"}
                        <input maxlength="64" placeholder="请输入虚拟浏览量，不输入默认为0" name="false_view" class="layui-input"
                               id="false_view" value="" type="number">
                        {elseif condition="$style eq 'edit_weibo'"/}
                        <input maxlength="64" placeholder="请输入虚拟浏览量，不输入默认为0" name="false_view" class="layui-input"
                               id="false_view" value="{$info.false_view}" type="number">
                        {elseif condition="$style eq 'view_weibo'"/}
                        <input maxlength="64" placeholder="请输入虚拟浏览量，不输入默认为0" name="false_view" class="layui-input"
                               id="false_view" value="{$info.false_view}" readonly type="number">
                        {/if}
                        <input type="hidden" name="id" value="">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <div class="input-group" data-select="$select">
                        <span class="input-group-addon">版块分类</span>
                        {if condition="$style eq 'create'"}
                        <select class="layui-select layui-input" name="fid" id="fid" value="">
                            <option value="">请选择版块分类</option>
                            {volist name="select" id="vo"}
                            <option value="{$vo.id}">{$vo.name}</option>
                            {/volist}
                        </select>
                        {elseif condition="$style eq 'edit_weibo'"/}
                        <select class="layui-select layui-input" name="fid" id="fid" value="{$info.fid}">
                            <option value="">请选择版块分类</option>
                            {volist name="select" id="vo"}
                            {if condition="$vo.id eq $info.fid"}
                            <option value="{$vo.id}" selected>{$vo.name}</option>
                            {else/}
                            <option value="{$vo.id}">{$vo.name}</option>
                            {/if}
                            {/volist}
                        </select>
                        {elseif condition="$style eq 'view_weibo'"/}
                        <select class="layui-select layui-input" name="fid" id="fid" disabled value="{$info.fid}">
                            <option value="">请选择版块分类</option>
                            {volist name="select" id="vo"}
                            {if condition="$vo.id eq $info.fid"}
                            <option value="{$vo.id}" selected>{$vo.name}</option>
                            {else/}
                            <option value="{$vo.id}">{$vo.name}</option>
                            {/if}
                            {/volist}
                        </select>
                        {/if}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-addon">二级分类</span>
                        {if condition="$style eq 'create'"}
                        <select class="layui-select layui-input" name="class_id" id="class_id" value="">
                            <option value="">不选择</option>
                        </select>
                        {elseif condition="$style eq 'edit_weibo'"/}
                        <select class="layui-select layui-input" name="class_id" id="class_id" value="{$info.class_id}">
                            <option value="">不选择</option>
                            {volist name="class" id="v"}
                            {if condition="$v.id eq $info.class_id"}
                            <option value="{$v.id}" selected>{$v.name}</option>
                            {else/}
                            <option value="{$v.id}">{$v.name}</option>
                            {/if}
                            {/volist}
                        </select>
                        {elseif condition="$style eq 'view_weibo'"/}
                        <select class="layui-select layui-input" name="class_id" id="class_id" disabled
                                value="{$info.class_id}">
                            <option value="">未关联分类</option>
                            {volist name="class" id="v"}
                            {if condition="$v.id eq $info.class_id"}
                            <option value="{$v.id}" selected>{$v.name}</option>
                            {else/}
                            <option value="{$v.id}">{$v.name}</option>
                            {/if}
                            {/volist}
                        </select>
                        {/if}
                    </div>
                </div>
            </div>

            {if condition="$style eq 'create'"}
            <div class="form-group" id="upload_img_content">
                <div class="col-md-12">
                    <div class="input-group" style="display: flex">
                        <span class="input-group-addon" style="width: auto;line-height: 24px;border: none">封面</span>
                        <input type="file" class="upload" name="image" style="display: none;" id="image"/>
                        <div id="img_content" style="display: flex;max-width: 600px;flex-wrap: wrap">
                            <a style="display: block;width: 102px;height: 92px;border: 1px solid #E5E6E7;margin-top: 5px;margin-right: 5px;"
                               class="btn-sm add_image upload_span" id="upload_img_box">
                                <div class="upload-image-box transition image_img"
                                     style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url('/public/system/module/wechat/news/images/image.png')">
                                    <input value="" type="hidden" id="image_input" name="local_url">
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            {/if}

            {if condition="$style eq 'edit_weibo'"}
            <div class="form-group" id="upload_img_content">
                <div class="col-md-12">
                    <div class="input-group" style="display: flex">
                        <span class="input-group-addon" style="width: auto;line-height: 24px;border: none">封面</span>
                        <input type="file" class="upload" name="image" style="display: none;" id="image"/>
                        <div id="img_content" style="display: flex;max-width: 600px;flex-wrap: wrap">
                            <a style="display: block;width: 102px;height: 92px;border: 1px solid #E5E6E7;margin-top: 5px;margin-right: 5px;"
                               class="btn-sm add_image upload_span" id="upload_img_box">
                                <div class="upload-image-box transition image_img"
                                     style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url('/public/system/module/wechat/news/images/image.png')">
                                    <input value="" type="hidden" id="image_input" name="local_url">
                                </div>
                            </a>
                            {volist name="info.image" id="v"}
                            <div class="upload-img-box">
                                <div class="delete-btn"><img src="{__ADMIN_PATH}css/delete.png" alt=""></div>
                                <img class="upload-img-box-img" src="{$v}" alt="">
                            </div>
                            {/volist}
                        </div>
                    </div>
                </div>
            </div>

            {/if}

            {if condition="$style eq 'view_weibo'"}
            <div class="form-group" id="upload_img_content">
                <div class="col-md-12">
                    <div class="input-group" style="display: flex">
                        <span class="input-group-addon" style="width: auto;line-height: 24px;border: none">封面</span>
                        <input type="file" class="upload" name="image" style="display: none;" id="image"/>
                        <div id="img_content" style="display: flex;max-width: 600px;flex-wrap: wrap">
                            {volist name="info.image" id="v"}
                            <div class="upload-img-box">
                                <!--<div class="delete-btn"><img src="{__ADMIN_PATH}css/delete.png" alt=""></div>-->
                                <img class="upload-img-box-img" src="{$v}" alt="">
                            </div>
                            {/volist}
                        </div>
                    </div>
                </div>
            </div>
            {/if}
            <div class="form-group">
                <div class="col-md-12">
                    <label style="color:#aaa">动态内容</label>
                    {if condition="$style eq 'create'"}
                    <textarea id="text_content" style="width:100%;height: 300px;resize:none;padding: 5px;"></textarea>
                    {elseif condition="$style eq 'edit_weibo'"/}
                    <textarea id="text_content" style="width:100%;height: 300px;resize:none;padding: 5px;">{$info.content}</textarea>
                    {elseif condition="$style eq 'view_weibo'"/}
                    <textarea readonly style="width:100%;height: 300px;resize:none;padding: 5px;">{$info.content}</textarea>
                    {/if}
                </div>
            </div>
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-4 col-md-9">
                        {if condition="$style eq 'create'"}
                        <button type="button" class="btn btn-w-m btn-info save_news">保存</button>
                        {elseif condition="$style eq 'edit_weibo'"/}
                        <button type="button" class="btn btn-w-m btn-info save_news">保存</button>
                        {elseif condition="$style eq 'view_weibo'"/}
                        {/if}
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
{/block}
{block name="script"}
<script>
  $.ajax({
    url: "{:Url('get_user')}",
    data: {},
    type: 'get',
    dataType: 'json',
    success: function (res) {
      if (res.code == 200) {
        if (res.data) {
          $("#now_bind_user").val(res.data.nickname);
          $("#now_bind_user").attr('data-id', res.data.uid);
        }
      } else {
        Toast.error(res.msg);
      }
    }
  });
  window.addEventListener("storage", function (e) {
    if (e.key === "bind_username") {
      $(".now_bind_user").val(e.newValue);
      window.localStorage.removeItem("bind_username")
    } else if (e.key === "add_goods_val") {
      var goodsVal = e.newValue;
      goodsVal = JSON.parse(goodsVal);
      ue.focus();
      ue.execCommand('inserthtml', '<div class="product-box" data-id=' + goodsVal.id + ' style="display: flex;border: 1px solid #000;padding: 10px;margin-bottom: 5px"><img src=' + goodsVal.img + ' width="155" height="110"/><div class="product-info" style="margin-left: 20px;"><div class="product-name" style="height: 86px;">' + goodsVal.name + '</div><div class="product-price">￥' + goodsVal.price + '</div></div></div>');
      window.localStorage.removeItem("add_goods_val")
    } else if (e.key === "bind_userId") {
      $(".now_bind_user").attr('data-id', e.newValue);
      window.localStorage.removeItem("bind_username")
    }
  });
  $("#fid").change(function () {
    $.ajax({
      url: "{:Url('select_class')}",
      data: {id: $("#fid").val()},
      type: 'post',
      dataType: 'json',
      success: function (res) {
        if (res.code == 200) {
          var optionHtml = '<option value="">不选择</option>';
          for (var i in res.data) {
            optionHtml += '<option value=' + res.data[i].id + '>' + res.data[i].name + '</option>'
          }
          $("#class_id").html(optionHtml)
        } else {

        }
      }
    })
  })

  function hasContent() {
    return (UM.getEditor('myEditor').hasContents());
  }

  function createFrame(title, src, opt) {
    opt === undefined && (opt = {});
    return layer.open({
      type: 2,
      title: title,
      area: [(opt.w || 700) + 'px', (opt.h || 650) + 'px'],
      fixed: false, //不固定
      maxmin: true,
      moveOut: false,//true  可以拖出窗外  false 只能在窗内拖
      anim: 5,//出场动画 isOutAnim bool 关闭动画
      offset: 'auto',//['100px','100px'],//'auto',//初始位置  ['100px','100px'] t[ 上 左]
      shade: 0,//遮罩
      resize: true,//是否允许拉伸
      content: src,//内容
      move: '.layui-layer-title'
    });
  }


  $(".bind-user").on("click", function () {
    $eb.createModalFrame("绑定用户", '{:Url('bind_user_vim')}',{w: document.body.clientWidth, h: document.body.clientHeight})
  });
  var autoImg = 1;
  $("input[name='show']").on("change", function () {
    var change = $("input[type='checkbox']").is(':checked'); //checkbox选中判断
    if (change) {
      $("#upload_img_content").hide();
      autoImg = 1;
    } else {
      $("#upload_img_content").show();
      autoImg = 0
    }
  });

  $("body").on("mouseover mouseout", ".upload-img-box", function (event) {
    if (event.type === "mouseover") {
      $(this).find("div").css("display", "flex");
    } else if (event.type === "mouseout") {
      $(this).find("div").css("display", "none");
    }
  });

  var imgVal = [];
  $("body").on("click", ".delete-btn", function () {
    var index = $(this).parent().index();
    imgVal.splice(index-1, 1);
    var imgInputVal = imgVal.join(",");
    $('#image_input').val(imgInputVal);
    $(this).parent().remove();
    $("#upload_img_box").show();
  });


  function changeIMG(index, pic) {
    $("#img_content").append('<div class="upload-img-box"><div class="delete-btn"><img src="{__ADMIN_PATH}css/delete.png" alt=""></div><img class="upload-img-box-img" src=' + pic + '></div>');
    if(imgVal.length === 9){
      $eb.message('error', '最多上传9张');
      $("#upload_img_box").hide();
      return;
    }
    imgVal.push(pic);
    var imgInputVal = imgVal.join(",");
    $('#image_input').val(imgInputVal);
    if(imgVal.length === 9){
      $("#upload_img_box").hide();
    }
  }

  /**
   * 上传图片
   * */
  $('.upload_span').on('click', function (e) {
    createFrame('选择图片', '{:Url('widget.images/index')}?fodder=image');
  });
</script>
{if condition="$style eq 'create'"}
<script>
  $('.save_news').on('click', function () {
    var list = {};
    list.type = 1;
    list.from = "HouTai";
    list.is_weibo = 1;
    list.false_view = $('#false_view').val();/* 虚拟浏览量 */
    list.image = $('#image_input').val();/* 封面 */
    list.author_uid = $('.now_bind_user').data("id");/* 作者 */
    list.content = $("#text_content").val();/* 内容 */
    list.fid = $("#fid option:selected").val();
    list.class_id = $("#class_id option:selected").val();
    //list.is_auto_image = autoImg;
    var Expression = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
    var objExp = new RegExp(Expression);
    if (list.author_uid == '') {
      $eb.message('error', '请输入作者');
      return false;
    }
    if (list.fid == '') {
      $eb.message('error', '请选择版块分类');
      return false;
    }
    if (list.content == '') {
      $eb.message('error', '请输入内容');
      return false;
    }
    var data = {};
    $.ajax({
      url: "{:Url('add_weibo')}",
      data: list,
      type: 'post',
      dataType: 'json',
      success: function (re) {
        if (re.code == 200) {
          data[re.data] = list;
          $('.type-all>.active>.new-id').val(re.data);
          $eb.message('success', re.msg);
          setTimeout(function (e) {
            parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
            parent.layer.close(parent.layer.getFrameIndex(window.name));
          }, 600)
        } else {
          $eb.message('error', re.msg);
        }
      }
    })
  });
</script>
{elseif condition="$style eq 'edit_weibo'"/}
{volist name="info.image" id="v"}
<script>
  imgVal.push("{$v}")
</script>
{/volist}
<script>
  var imgInputVal = imgVal.join(",");
  $('#image_input').val(imgInputVal);

  $('.save_news').on('click', function () {
    var list = {};
    list.id = '{$info.id}';
    list.type = 1;
    list.is_weibo = 1;
    list.from = newFrom;
    list.image = $('#image_input').val();/* 封面 */
    list.false_view = $('#false_view').val();/* 虚拟浏览量 */
    list.author_uid = $('.now_bind_user').data("id");/* 作者 */
    list.content = $("#text_content").val();/* 内容 */
    list.fid = $("#fid option:selected").val();
    list.class_id = $("#class_id option:selected").val();
    //list.is_auto_image = autoImg;
    var Expression = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
    var objExp = new RegExp(Expression);
    if (list.author_uid == '') {
      $eb.message('error', '请输入作者');
      return false;
    }
    if (list.fid == '') {
      $eb.message('error', '请选择版块分类');
      return false;
    }
    if (list.content == '') {
      $eb.message('error', '请输入内容');
      return false;
    }
    var data = {};
    $.ajax({
      url: "{:Url('edit_thread')}",
      data: list,
      type: 'post',
      dataType: 'json',
      success: function (re) {
        if (re.code == 200) {
          data[re.data] = list;
          $('.type-all>.active>.new-id').val(re.data);
          $eb.message('success', re.msg);
          setTimeout(function (e) {
            parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
            parent.layer.close(parent.layer.getFrameIndex(window.name));
          }, 600)
        } else {
          $eb.message('error', re.msg);
        }
      }
    })
  });
</script>
{elseif condition="$style eq 'view_weibo'"/}
<script>

</script>
{/if}

{if condition="$style eq 'edit_weibo'"}
{if condition="$info.from eq 'HouTai'"}
<script>
  var newFrom = "HouTai"
</script>
{else/}
<script>
  var newFrom = "{$info.from}"
</script>
{/if}
{else/}
<script
  var newFrom = "HouTai"
</script>
{/if}
{/block}