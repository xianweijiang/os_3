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
<link href="{__PLUG_PATH}layui2.5.5/css/layui.css" rel="stylesheet">
<script src="{__PLUG_PATH}layui2.5.5/layui.js"></script>
{/block}
{block name="content"}
<div class="row" style="width: 100%;margin-left: 0;">
   <div class="col-sm-12" style="background-color: #fff">
       <div class="panel-body">
           <form class="form-horizontal" id="signupForm">
               <div class="form-group">
                   <div class="col-md-12">
                       <div class="input-group">
                           <span class="input-group-addon">标题</span>
                           {if condition="$style eq 'create_video'"}
                           <input maxlength="64" placeholder="请在这里输入标题" name="title" class="layui-input" id="title" value="">
                           {elseif condition="$style eq 'edit_video'"/}
                           <input maxlength="64" placeholder="请在这里输入标题" name="title" class="layui-input" id="title" value="{$info.title}">
                           {elseif condition="$style eq 'view_video'"/}
                           <input maxlength="64" placeholder="请在这里输入标题" name="title" class="layui-input" id="title" readonly value="{$info.title}">
                           {/if}
                           <input type="hidden" name="id" value="">
                       </div>
                   </div>
               </div>
               {if condition="$style neq 'create_video'"}
               <div class="form-group">
                   <div class="col-md-12">
                       <div class="input-group">
                           {if condition="$style neq 'create_video'"}
                           <span class="input-group-addon">虚拟浏览量</span>
                           {/if}
                           {if condition="$style eq 'create_video'"}
                           <!-- <input maxlength="64" placeholder="请输入虚拟浏览量，不输入默认为0" name="false_view" class="layui-input" id="false_view" value="" type="number"> -->
                           {elseif condition="$style eq 'edit_video'"/}
                           <input maxlength="64" placeholder="请输入虚拟浏览量，不输入默认为0" name="false_view" class="layui-input" id="false_view" value="{$info.false_view}" type="number">
                           {elseif condition="$style eq 'view_video'"/}
                           <input maxlength="64" placeholder="请输入虚拟浏览量，不输入默认为0" name="false_view" class="layui-input" id="false_view" value="{$info.false_view}" readonly type="number">
                           {/if}
                           <input type="hidden" name="id" value="">
                       </div>
                   </div>
               </div>
               {/if}
               <div class="form-group">
                   <div class="col-md-12">
                       <div class="input-group" style="display: flex">
                           <span class="input-group-addon" style="width: auto;line-height: 24px">作者</span>
                           {if condition="$style eq 'create_video'"}
                           <input class="layui-input now_bind_user" readonly id="now_bind_user" value="" style="display: inline-block">
                           <button type="button" class="btn btn-w-m btn-info bind-user" style="height: 38px;">绑定用户</button>
                           {elseif condition="$style eq 'edit_video'"/}
                           <input class="layui-input now_bind_user" readonly id="" value="{$info.user}" data-id="{$info.author_uid}" style="display: inline-block">
                           <button type="button" class="btn btn-w-m btn-info bind-user" style="height: 38px;">绑定用户</button>
                           {elseif condition="$style eq 'view_video'"/}
                           <input class="layui-input now_bind_user" readonly id="" value="{$info.user}" data-id="{$info.author_uid}" style="display: inline-block">
                           {/if}
                       </div>
                   </div>
               </div>

               <div class="form-group">
                   <div class="col-md-12">
                       <div class="input-group">
                           <!-- <span class="input-group-addon">封面(225*150)</span> -->
                           <span class="input-group-addon">上传封面</span>
                           {if condition="$style eq 'create_video'"}
                           <input type="file" class="upload" name="image" style="display: none;" id="image" />
                           <a style="display: block;width: 102px;border: 1px solid #E5E6E7;" class="btn-sm add_image upload_span" id="upload_img">
                               <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:80px 80px;background-image:url('/public/system/module/wechat/news/images/image.png');background-size: 100px 85px;
                                    background-position: -9px -3px;">
                                   <input value="" type="hidden" id="image_input" name="local_url">
                               </div>
                           </a>
                           {elseif condition="$style eq 'edit_video'"/}
                           <input type="file" class="upload" name="image" style="display: none;" id="image" />
                           <a style="display: block;width: 102px;border: 1px solid #E5E6E7;" class="btn-sm add_image upload_span" >
                               <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url('{$info.cover}')">
                                   <input value="{$info.cover}" type="hidden" id="image_input" name="local_url">
                               </div>
                           </a>
                           {elseif condition="$style eq 'view_video'"/}
                           <input type="file" class="upload" name="image" style="display: none;" id="image" />
                           <a style="display: block;width: 102px;border: 1px solid #E5E6E7;cursor: default" class="btn-sm add_image" >
                               <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url('{$info.cover}')">
                                   <input value="{$info.cover}" type="hidden" id="image_input" name="local_url">
                               </div>
                           </a>
                           {/if}
                       </div>
                   </div>
               </div>
               <div class="form-group">
                   <div class="col-md-12">
                       <div class="input-group" data-select="$select">
                           <span class="input-group-addon">版块分类</span>
                           {if condition="$style eq 'create_video'"}
                           <select class="layui-select layui-input" name="fid" id="fid">
                               <option value="">请选择版块分类</option>
                               {volist name="select" id="vo"}
                               <option value="{$vo.id}">{$vo.name}</option>
                               {/volist}
                           </select>
                           {elseif condition="$style eq 'edit_video'"/}
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
                           {elseif condition="$style eq 'view_video'"/}
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
                           {if condition="$style eq 'create_video'"}
                           <select class="layui-select layui-input" name="class_id" id="class_id">
                               <option value="">不选择</option>
                           </select>
                           {elseif condition="$style eq 'edit_video'"/}
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
                           {elseif condition="$style eq 'view_video'"/}
                           <select class="layui-select layui-input" name="class_id" id="class_id" disabled value="{$info.class_id}">
                               <option value="">不选择</option>
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
               <div class="form-group">
                   <div class="col-md-12">
                       <label style="color:#aaa">内容</label>
                       {if condition="$style eq 'create_video'"}
                       <textarea type="text/plain" id="myEditor" style="width:100%;"></textarea>
                       {elseif condition="$style eq 'edit_video'"/}
                       <textarea type="text/plain" id="myEditor" style="width:100%;"></textarea>
                       {elseif condition="$style eq 'view_video'"/}
                       <textarea type="text/plain" id="myEditor" style="width:100%;"></textarea>
                       {/if}
                   </div>
               </div>
               <div class="form-actions">
                   <div class="row">
                       <div class="col-md-offset-4 col-md-9">
                           {if condition="$style eq 'create_video'"}
                           <button type="button" class="btn btn-w-m btn-info save_news">保存</button>
                           {elseif condition="$style eq 'edit_video'"/}
                           <button type="button" class="btn btn-w-m btn-info save_news">保存</button>
                           {elseif condition="$style eq 'view_video'"/}

                           {/if}
                       </div>
                   </div>
               </div>
           </form>
       </div>
   </div>
</div>
{/block}
{block name="script"}
<script src="{__MODULE_PATH}widget/file.js"></script>
<script>
  $.ajax({
    url:"{:Url('get_user')}",
    data:{},
    type:'get',
    dataType:'json',
    success:function(res){
      if(res.code == 200){
        if(res.data){
          $("#now_bind_user").val(res.data.nickname);
          $("#now_bind_user").attr('data-id',res.data.uid);
        }
      }else{
        Toast.error(res.msg);
      }
    }
  });
  window.addEventListener("storage", function (e) {
    if(e.key === "bind_username"){
      $(".now_bind_user").val(e.newValue);
      window.localStorage.removeItem("bind_username")
    }else if(e.key === "bind_userId"){
      $(".now_bind_user").attr('data-id',e.newValue);
      window.localStorage.removeItem("bind_username")
    }
  });
  $("#fid").change(function () {
    $.ajax({
      url:"{:Url('select_class')}",
      data:{id:$("#fid").val()},
      type:'post',
      dataType:'json',
      success:function(res){
        if(res.code == 200){
          var optionHtml = '<option value="">不选择</option>';
          for(var i in res.data){
            optionHtml += '<option value='+res.data[i].id+'>'+res.data[i].name+'</option>'
          }
          $("#class_id").html(optionHtml)
        }else{

        }
      }
    })
  })
  $(".bind-user").on("click",function () {
    $eb.createModalFrame("绑定用户",'{:Url('bind_user_vim')}',{w:document.body.clientWidth,h:document.body.clientHeight})
  });
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
            function changeIMG(index,pic){
                $(".image_img").css('background-image',"url("+pic+")");
                $(".active").css('background-image',"url("+pic+")");
                $('#image_input').val(pic);
            };
            /**
             * 上传图片
             * */
            $('#upload_img').on('click',function (e) {
            // $('.upload').trigger('click');
                createFrame('选择图片','{:Url('widget.images/index')}?fodder=image');
            })
            /**
             *上传视频
             **/
            // $('#upload_video').on('click',function (e) {
            // // $('.upload').trigger('click');
            //     createFrame('选择视频','{:Url('widget.videos/index')}?fodder=image');
			// })



            $('.article-add ').on('click',function (e) {
                var num_div = $('.type-all').children('div').length;
                if(num_div > 7){
                  $eb.message('error','一组图文消息最多可以添加8个');
                  return false;
                }
                var url = "/public/system/module/wechat/news/images/image.png";
                html = '';
                html += '<div class="news-item transition active news-image" style=" margin-bottom: 20px;background-image:url('+url+')">'
                    html += '<input type="hidden" name="new_id" value="" class="new-id">';
                    html += '<span class="news-title del-news">x</span>';
                html += '</div>';
                $(this).siblings().removeClass("active");
                $(this).before(html);
            })
            $(document).on("click",".del-news",function(){
                $(this).parent().remove();
            })
            $(document).ready(function() {
                var config = {
                    ".chosen-select": {},
                    ".chosen-select-deselect": {allow_single_deselect: true},
                    ".chosen-select-no-single": {disable_search_threshold: 10},
                    ".chosen-select-no-results": {no_results_text: "沒有找到你要搜索的分类"},
                    ".chosen-select-width": {width: "95%"}
                };
                for (var selector in config) {
                    $(selector).chosen(config[selector])
                }
            })
        </script>
    {if condition="$style eq 'create_video'"}
    <script>
      /**
       * 提交图文
       * */
      $('.save_news').on('click',function(){
        var list = {};
        list.title = $('#title').val();/* 标题 */
        list.false_view = $('#false_view').val();/* 虚拟浏览量 */
        list.author_uid = $('.now_bind_user').data("id");/* 作者 */
        list.content = getContent();/* 内容 */
        list.fid= $("#fid option:selected").val();
        list.class_id= $("#class_id option:selected").val();
        list.video_cover = $("#image_input").val();/* 图片*/
        var Expression = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
        var objExp=new RegExp(Expression);
        if(list.title == ''){
          $eb.message('error','请输入标题');
          return false;
        }
        if(list.author_uid == ''){
          $eb.message('error','请输入作者');
          return false;
        }
        if(list.fid == ''){
          $eb.message('error','请选择版块分类');
          return false;
        }
        if(list.content == ''){
          $eb.message('error','请输入内容');
          return false;
        }
        var data = {};
        $.ajax({
          url:"{:Url('add_video')}",
          data:list,
          type:'post',
          dataType:'json',
          success:function(re){
            if(re.code == 200){
              data[re.data] = list;
              $('.type-all>.active>.new-id').val(re.data);
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
    {elseif condition="$style eq 'edit_video'"/}
    <script>
        var contentHtml = $('<div>').html({$info.content}).html();
        contentHtml.replace(/&quot;/g,"\"");
        ue.addListener("ready", function () {
            ue.setContent(contentHtml);
        });
      /**
       * 提交图文
       * */
      $('.save_news').on('click',function(){
        var list = {};
        list.id = '{$info.id}';
        list.type = 6;
        list.title = $('#title').val();/* 标题 */
          list.false_view = $('#false_view').val();/* 虚拟浏览量 */
        list.author_uid = $('.now_bind_user').data("id");/* 作者 */
        list.content = getContent();/* 内容 */
        list.fid= $("#fid option:selected").val();
        list.class_id= $("#class_id option:selected").val();
        list.video_cover = $("#image_input").val();/* 图片*/
        var Expression = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
        var objExp=new RegExp(Expression);
        if(list.title == ''){
          $eb.message('error','请输入标题');
          return false;
        }
        if(list.author_uid == ''){
          $eb.message('error','请输入作者');
          return false;
        }
        if(list.fid == ''){
          $eb.message('error','请选择版块分类');
          return false;
        }
        if(list.content == ''){
          $eb.message('error','请输入内容');
          return false;
        }
        var data = {};
        $.ajax({
          url:"{:Url('edit_thread')}",
          data:list,
          type:'post',
          dataType:'json',
          success:function(re){
            if(re.code == 200){
              data[re.data] = list;
              $('.type-all>.active>.new-id').val(re.data);
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
    {elseif condition="$style eq 'view_video'"/}
    <script>
        var contentHtml = $('<div>').html({$info.content}).html();
        contentHtml.replace(/&quot;/g,"\"");
        ue.addListener("ready", function () {
            ue.setContent(contentHtml);
        });
      //不可编辑
      ue.ready(function () {
        ue.setDisabled()
      });
    </script>
    {/if}
{/block}