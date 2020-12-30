{extend name="public/container"}
{block name="head_top"}
<link href="{__ADMIN_PATH}module/wechat/news/css/style.css" type="text/css" rel="stylesheet">
<link href="{__FRAME_PATH}css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="{__FRAME_PATH}css/columnadd.css" rel="stylesheet">
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.all.js"></script>
<script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>
<script src="{__ADMIN_PATH}plug/validate/jquery.validate.js"></script>
<script src="{__FRAME_PATH}js/plugins/chosen/chosen.jquery.js"></script>

<link href="{__PLUG_PATH}layui2.5.5/css/layui.css" rel="stylesheet">
<script src="{__PLUG_PATH}layui2.5.5/layui.js"></script>

{/block}
{block name="content"}
<div class="row" style="width: 100%">
   <div class="col-sm-12">
       <div class="col-sm-2 panel panel-default news-left">
               <div class="panel-heading">文章列表</div>
       </div><!-- col-sm-10 panel panel-default news-right -->
       <div class="col-sm-12 panel panel-default" >
               <div class="panel-heading">视频内容编辑</div>
               <div class="panel-body">
                   <form class="form-horizontal" id="signupForm">
                       <input type="hidden" name="id" value="{$data['id']}">
                       <div class="form-group">
                           <div class="col-md-12">
                               <div class="input-group">
                                   <span class="input-group-addon">期刊名称</span>
                                   <input maxlength="64" placeholder="请在这里输入标题" name="name" class="layui-input" id="name" value="{$data['name']}">
                                   <input type="hidden" id="id" value="{$data['id']}">
                               </div>
                           </div>
                       </div>
                       <div class="form-group">
                           <div class="col-md-12">
                               <div class="form-control" style="height:auto">
                                   <label style="color:#ccc">封面大图片设置</label>
                                   <div class="row nowrap">
                                       <div class="col-xs-3" style="width:160px">
                                           <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url({$data['img']})">
                                               <input value="" type="hidden" name="local_url">
                                           </div>
                                       </div>
                                       <div class="col-xs-6">
                                           <input type="file" class="upload" name="image" style="display: none;" id="image" />
                                           <br>
                                           <a class="btn btn-sm add_image upload_span">选择图片</a>
                                           <br>
                                           <br>
                                       </div>
                                   </div>
                                   <input type="hidden" name="image" id="img" value="{$data['img']}"/>
                                   <p class="help-block" style="margin-top:10px;color:#ccc">封面大图片建议尺寸：900像素 * 500像素</p>
                               </div>
                           </div>
                       </div>
                       <div class="form-group">
                           <div class="col-md-12">
                               <label style="color:#aaa">详情编辑</label>
                               <textarea type="text/plain" id="myEditor" style="width:100%;">{$data['content']}</textarea>
                           </div>
                       </div>
	                   {if($switch==1)}
	                   <div class="added-line">
		                   <i class="nfa nfa-tengxunvicon"></i>
		                   系统已开启云点播服务，可直接上传腾讯云
	                   </div>
	                   <div class="cloud-video-upload">
		                   <div class="upload-tips">请上传以*.mp4格式为主的视频文件，上传过程中请耐心等待提示</div>
		                   <div class="layui-upload-list" id="video-expand"></div>
		                   <div class="button-box">
			                   <button type="button" class="layui-btn layui-btn-normal" id="qcloud-video-select">
				                   选择视频文件
			                   </button>
			                   <button type="button" class="layui-btn" id="qcloud-video-upload">
				                   开始上传
			                   </button>
			                   <!--<input type="file" name="hiddenPath" id="hiddenPath" style="display: none">-->
			                   <input type="hidden" name="content" value="{$data['info']}" id="urli">
		                   </div>
	                   </div>
	                   <div class="layui-progress layui-progress-big" lay-filter="demo">
		                   <div class="layui-progress-bar layui-bg-blue" lay-percent="0%"></div>
	                   </div>
	                   {else}
	                   <div class="added-line">
		                   <i class="nfa nfa-yzhuyi"></i>
		                   系统尚未设置云点播上传服务，可以使用本地模式上传
	                   </div>
                       <div class="form-group">
                           <div class="col-md-12">
                               <div class="form-control" style="height:auto">
                                   <label style="color:#ccc">视频上传：请上传 *.rmvb , *.avi , *.mp4 格式的视频文件</label>
                                   <button type="button" class="layui-btn" id="video-upload">
                                      <i class="layui-icon">&#xe67c;</i>上传视频
                                    </button>
                                    <input type="hidden" name="content" value="{$data['info']}" id="urli">
                               </div>
                           </div>
                       </div>
                       <div class="form-group" style="padding: 0 15px;">
                          <div class="layui-progress layui-progress-big" lay-filter="demo">
                          <div class="layui-progress-bar layui-bg-blue" lay-percent="0%"></div>
                        </div>
                       </div>
	                   {/if}
                       <div class="form-group">
                           <div class="col-md-12">
                               <div class="col-md-6">
                                   <label style="color:#aaa">是否显示</label>
                                   <br/>
                                   {if condition="0==$data['is_show']"}
                                   <input type="radio" name="is_show" class="layui-radio" value="0" checked >否
                                   <input type="radio" name="is_show" class="layui-radio" value="1" >是
                                   {else}
                                   <input type="radio" name="is_show" class="layui-radio" value="0" >否
                                   <input type="radio" name="is_show" class="layui-radio" value="1" checked>是
                                   {/if}
                               </div>
                               <div class="col-md-6">
                                   <label style="color:#aaa">是否为试读</label>
                                   <br/>
                                   {if condition="0==$data['is_read']"}
                                   <input type="radio" name="is_read" class="layui-radio" value="0" checked>否
                                   <input type="radio" name="is_read" class="layui-radio" value="1" >是
                                   {else}
                                   <input type="radio" name="is_read" class="layui-radio" value="0" >否
                                   <input type="radio" name="is_read" class="layui-radio" value="1" checked>是
                                   {/if}
                               </div>
                           </div>
                       </div>
                       <div class="form-actions">
                           <div class="row">
                               <div class="col-md-offset-4 col-md-9">
                                   <button type="button" class="btn btn-w-m btn-info save_news">保存</button>
                               </div>
                           </div>
                       </div>
                   </form>
               </div>
       </div>
   </div>
</div>
{/block}
{block name="script"}
<!-- <script src="{__MODULE_PATH}widget/file.js"></script> -->
<script>
  layui.use('upload', function(){
  var upload = layui.upload;
  var qCloudUpload = layui.upload;
   
  //执行实例
  var uploadInst = upload.render({
    elem: '#video-upload',
    url: '{:Url('widget.uplodes/upload')}',//'http://www.shop.me/index.php/admin/widget.uplodes/upload',
    data: {
      path: 'video'
    },
    field: 'file',
    accept: 'video',
    done: function (res) {
      var urli = res.src
      $('#urli').val(urli);
      $eb.message('success','上传成功');
      return false;
    },
    progress: function(n){
      console.log(n)
      var percent = n + '%' //获取进度百分比
      layui.use('element', function(){
        var element = layui.element;
        element.progress('demo', percent); //可配合 layui 进度条元素使用
        $('#percent').attr('lay-percent', percent)
      });
    }
  });
  //使用腾讯云上传
      qCloudUpload.render({
          elem: '#qcloud-video-select',
          url: "{:Url('mediaUpload')}",
          data: { path:'video'},
          field: 'file',
          accept: 'video',
          auto: false,
          bindAction: '#qcloud-video-upload',

          choose: function(obj){
              obj.preview(function(index, file, result){
                  var tag = $(['<span class="uploaded-info" id="upload-'+index+'">',
                      '<span class="tit">文件名：</span><span class="info">['+file.name+']</span>',
                      '<span class="tit">大小：</span><span class="info">['+(file.size/1024).toFixed(1)+'KB]</span>',
                      '<span><button class="layui-btn layui-btn-xs layui-btn-danger in-delete">删除</button></span>',
                      '</span>'].join(''));
                  $('#video-expand').append(tag);

                  //删除
                  tag.find('.in-delete').on('click', function(){
                      delete files[index];
                      tr.remove();
                      uploadListIns.config.elem.next()[0].value = '';
                  });

              });
          },
          done: function (res) {
              if( res.status == 1){
                  var urli = res.src;
                  $('#urli').val(urli);
                  $eb.message('success',res.msg);
                  console.log(urli);
              }else{
                  $eb.message('error',res.msg.url);
              }
              return false;
          },
          progress: function(n){
              //console.log(n)
              var percent = n + '%'
              layui.use('element', function(){
                  var element = layui.element;
                  element.progress('demo', percent);
                  $('#percent').attr('lay-percent', percent)
              });
          }
      });
});
  var uploadurl = "{:Url('widget.uplodes/upload')}"; //上传图片地址
  var ue = UE.getEditor('myEditor',{
    autoHeightEnabled: false,
    initialFrameHeight: 200,
    wordCount: false,
    maximumWords: 100000
  });
            /**
            * 获取编辑器内的内容
            * */
            function getContentInfo() {
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
                $('#img').val(pic);
            };
            /**
             * 上传图片
             * */
            $('.upload_span').on('click',function (e) {
//                $('.upload').trigger('click');
                createFrame('选择图片','{:Url('widget.images/index')}?fodder=image');
            })

            /**
             * 编辑器上传图片
             * */
            $('.edui-icon-image').on('click',function (e) {
//                $('.upload').trigger('click');
                createFrame('选择图片','{:Url('widget.images/index')}?fodder=image');
            })

            /**
             * 上传音频
             * */
            $('.upload_music').on('click',function (e) {
              console.log(9876543);
//                $('.upload').trigger('click');
                createFrame('选择图片','{:Url('widget.uplodes/upload')}?path=video');
            })

            /**
             * 编辑器上传图片
             * */
            $('.edui-icon-music').on('click',function (e) {
//                $('.upload').trigger('click');
                createFrame('选择图片','{:Url('widget.images/index')}?fodder=image');
            })

            /**
            * 提交图文
            * */
            $('.save_news').on('click',function(){
              console.log(urli)
                var list = {};
                list.name = $('#name').val();/* 标题 */
                list.img = $('#img').val();/* 图片 */
                list.id = $('#id').val();/* id */
                list.type = 3;/* 类型 */
                list.info = $('#urli').val();/* 内容 */
                list.content = getContentInfo();/* 简介 */
                list.is_show = $("input[name='is_show']:checked").val();
                list.is_read = $("input[name='is_read']:checked").val();
                // var Expression = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
                // var objExp=new RegExp(Expression);
                if(list.name == ''){
                    $eb.message('error','请输入标题');
                    return false;
                }
                if(list.img == ''){
                    $eb.message('error','请选择图片');
                    return false;
                }
                if(list.content == ''){
                    $eb.message('error','请输入简介');
                    return false;
                }
                if(list.info == ''){
                    $eb.message('error','请上传视频');
                    return false;
                }
                // if(list.content == ''){
                //     $eb.message('error','请输入内容');
                //     return false;
                // }
                // if(list.info == ''){
                //     $eb.message('error','请输入简介');
                //     return false;
                // }
                var data = {};
                $.ajax({
                    url:"{:Url('updates')}",
                    data:list,
                    type:'post',
                    dataType:'json',
                    success:function(re){
                        if(re.code == 200){
                          console.log(re)
                            data[re.data] = list;
                            $('.type-all>.active>.new-id').val(re.data);
                            $eb.message('success',re.msg);
                            setTimeout(function (e) {
                              // parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                              parent.layer.close(parent.layer.getFrameIndex(window.name));
                            },600)
                        }else{
                            $eb.message('error',re.msg);
                        }
                    }
                })
            });
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
{/block}