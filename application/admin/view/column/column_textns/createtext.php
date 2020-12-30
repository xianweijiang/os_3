{extend name="public/container"}
{block name="head_top"}
<link href="{__ADMIN_PATH}module/wechat/news/css/style.css" type="text/css" rel="stylesheet">
<link href="{__FRAME_PATH}css/plugins/chosen/chosen.css" rel="stylesheet">
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.all.js"></script>
<script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>
<script src="{__ADMIN_PATH}plug/validate/jquery.validate.js"></script>
<script src="{__FRAME_PATH}js/plugins/chosen/chosen.jquery.js"></script>
{/block}
{block name="content"}
<div class="row" style="width: 100%">
   <div class="col-sm-12">
       <div class="col-sm-2 panel panel-default news-left">
               <div class="panel-heading">文章列表</div>
       </div><!-- col-sm-10 panel panel-default news-right -->
       <div class="col-sm-12 panel panel-default" >
               <div class="panel-heading">文章内容编辑</div>
               <div class="panel-body">
                   <form class="form-horizontal" id="signupForm">
                      <input type="hidden" name="pid" value="{$pid}">
                       <div class="form-group">
                           <div class="col-md-12">
                               <div class="input-group">
                                   <span class="input-group-addon">期刊名称</span>
                                   <input maxlength="64" placeholder="请在这里输入标题" name="name" class="layui-input" id="name" value="">
                                   <input type="hidden" id="pid" value="{$pid}">
                               </div>
                           </div>
                       </div>
                       <div class="form-group">
                           <div class="col-md-12">
                               <label style="color:#aaa">期刊内容</label>
                               <textarea type="text/plain" id="myEditorn" style="width:100%;"></textarea>
                           </div>
                       </div>
                       <div class="form-group">
                           <div class="col-md-12">
                               <div class="col-md-6">
                                   <label style="color:#aaa">是否显示</label>
                                   <br/>
                                   <input type="radio" name="is_show" class="layui-radio" value="0" checked>否
                                   <input type="radio" name="is_show" class="layui-radio" value="1">是
                               </div>
                               <div class="col-md-6">
                                   <label style="color:#aaa">是否为试读</label>
                                   <br/>
                                   <input type="radio" name="is_read" class="layui-radio" value="0" >否
                                   <input type="radio" name="is_read" class="layui-radio" value="1" checked>是
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
<script>
  var ue = UE.getEditor('myEditor',{
    autoHeightEnabled: false,
    initialFrameHeight: 200,
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

  var uen = UE.getEditor('myEditorn',{
    autoHeightEnabled: false,
    initialFrameHeight: 200,
    wordCount: false,
    maximumWords: 100000
  });
            /**
            * 获取编辑器内的内容
            * */
            function getContentInfo() {
              return (uen.getContent());
            }
            function hasContent() {
                return (UM.getEditor('myEditorn').hasContents());
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
            * 提交图文
            * */
            $('.save_news').on('click',function(){
                var list = {};
                list.name = $('#name').val();/* 标题 */
                list.img = 'null';/* 图片 */
                list.pid = $('#pid').val();/* 父级id */
                list.type = 1;/* 类型 */
                list.content = 'null';/* 内容 */
                list.info = getContentInfo();/* 新期刊内容 */
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
                    $eb.message('error','请输入内容');
                    return false;
                }
                if(list.info == ''){
                    $eb.message('error','请输入期刊内容');
                    return false;
                }
                var data = {};
                $.ajax({
                    url:"{:Url('create')}",
                    data:list,
                    type:'post',
                    dataType:'json',
                    success:function(re){
                        if(re.code == 200){
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