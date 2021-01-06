<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:117:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/com/com_message_news/create_message_news.php";i:1597214754;s:95:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/modal-frame.php";i:1597214754;s:94:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/frame_head.php";i:1597214754;s:89:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/style.php";i:1597214754;s:96:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/frame_footer.php";i:1597214754;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if(empty($is_layui) || (($is_layui instanceof \think\Collection || $is_layui instanceof \think\Paginator ) && $is_layui->isEmpty())): ?>
    <link href="/public/system/frame/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
    <?php endif; ?>
    <link href="/public/static/plug/layui/css/layui.css" rel="stylesheet">
    <link href="/public/system/css/layui-admin.css" rel="stylesheet"></link>
    <link href="/public/system/frame/css/font-awesome.min.css?v=4.3.0" rel="stylesheet">
    <link href="/public/system/frame/css/animate.min.css" rel="stylesheet">
    <link href="/public/system/frame/css/style.min.css?v=3.0.0" rel="stylesheet">
    <!--专栏Column新增图标-->
    <link href="https://at.alicdn.com/t/font_1685608_u3j40gwewag.css" rel="stylesheet">
    <script src="/public/system/frame/js/jquery.min.js"></script>
    <script src="/public/system/frame/js/bootstrap.min.js"></script>
    <script src="/public/static/plug/layui/layui.all.js"></script>
    <script>
        $eb = parent._mpApi;
        window.controlle="<?php echo strtolower(trim(preg_replace("/[A-Z]/", "_\\0", think\Request::instance()->controller()), "_"));?>";
        window.module="<?php echo think\Request::instance()->module();?>";
    </script>



    <title></title>
    
<link href="/public/system/module/wechat/news/css/style.css" type="text/css" rel="stylesheet">
<link href="/public/system/frame/css/plugins/chosen/chosen.css" rel="stylesheet">
<script type="text/javascript" charset="utf-8" src="/public/system/plug/ueditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="/public/system/plug/ueditor/ueditor.all.js"></script>
<script src="/public/system/frame/js/ajaxfileupload.js"></script>
<script src="/public/system/plug/validate/jquery.validate.js"></script>
<script src="/public/system/frame/js/plugins/chosen/chosen.jquery.js"></script>

    <!--<script type="text/javascript" src="/static/plug/basket.js"></script>-->
<script type="text/javascript" src="/public/static/plug/requirejs/require.js"></script>
<?php /*  <script type="text/javascript" src="/static/plug/requirejs/require-basket-load.js"></script>  */ ?>
<script>
    var hostname = location.hostname;
    if(location.port) hostname += ':' + location.port;
    requirejs.config({
        map: {
            '*': {
                'css': '/public/static/plug/requirejs/require-css.js'
            }
        },
        shim:{
            'iview':{
                deps:['css!iviewcss']
            },
            'layer':{
                deps:['css!layercss']
            }
        },
        baseUrl:'//'+hostname+'/public/',
        paths: {
            'static':'static',
            'system':'system',
            'vue':'static/plug/vue/dist/vue.min',
            'axios':'static/plug/axios.min',
            'iview':'static/plug/iview/dist/iview.min',
            'iviewcss':'static/plug/iview/dist/styles/iview',
            'lodash':'static/plug/lodash',
            'layer':'static/plug/layer/layer',
            'layercss':'static/plug/layer/theme/default/layer',
            'jquery':'static/plug/jquery/jquery.min',
            'moment':'static/plug/moment',
            'sweetalert':'static/plug/sweetalert2/sweetalert2.all.min'

        },
        basket: {
            excludes:['system/js/index','system/util/mpVueComponent','system/util/mpVuePackage']
//            excludes:['system/util/mpFormBuilder','system/js/index','system/util/mpVueComponent','system/util/mpVuePackage']
        }
    });
</script>
<script type="text/javascript" src="/public/system/util/mpFrame.js"></script>
    
</head>
<body class="white-bg">
<div class="wrapper wrapper-content">
    
<div class="panel">
   <div class="panel-body">
       <form class="form-horizontal" id="signupForm">
           <div class="form-group">
               <div class="col-md-12">
                   <div class="input-group">
                       <span class="input-group-addon">标题</span>
                       <?php if($style == 'create'): ?>
                       <input maxlength="64" placeholder="请在这里输入标题" name="title" class="layui-input" id="title" value="">
                       <?php elseif($style == 'edit'): ?>
                       <input maxlength="64" placeholder="请在这里输入标题" name="title" class="layui-input" id="title" value="<?php echo $messageNews['title']; ?>">
                       <?php elseif($style == 'view'): ?>
                       <input maxlength="64" placeholder="请在这里输入标题" name="title" class="layui-input" id="title" value="<?php echo $messageNews['title']; ?>" readonly>
                       <?php endif; ?>
                       <input type="hidden" name="id" value="">
                   </div>
               </div>
           </div>
           <div class="form-group">
               <div class="col-md-12">
                   <div class="input-group">
                       <span class="input-group-addon">虚拟浏览量</span>
                       <?php if($style == 'create'): ?>
                       <input maxlength="64" placeholder="请输入虚拟浏览量，不输入默认为0" name="false_view" class="layui-input" id="false_view" value="" type="number">
                       <?php elseif($style == 'edit'): ?>
                       <input maxlength="64" placeholder="请输入虚拟浏览量，不输入默认为0" name="false_view" class="layui-input" id="false_view" value="<?php echo $messageNews['false_view']; ?>" type="number">
                       <?php elseif($style == 'view'): ?>
                       <input maxlength="64" placeholder="请输入虚拟浏览量，不输入默认为0" name="false_view" class="layui-input" id="false_view" value="<?php echo $messageNews['false_view']; ?>" readonly type="number">
                       <?php endif; ?>
                       <input type="hidden" name="id" value="">
                   </div>
               </div>
           </div>
           <div class="form-group">
               <div class="col-md-12">
                   <div class="input-group" style="display: flex">
                       <span class="input-group-addon" style="width: auto;line-height: 24px">作者</span>
                       <?php if($style == 'create'): ?>
                       <input class="layui-input now_bind_user" readonly id="now_bind_user" value="" style="display: inline-block">
                       <button type="button" class="btn btn-w-m btn-info bind-user" style="height: 38px;">绑定用户</button>
                       <?php elseif($style == 'edit'): ?>
                       <input class="layui-input now_bind_user" readonly id="" value="<?php echo $messageNews['user']; ?>" style="display: inline-block">
                       <button type="button" class="btn btn-w-m btn-info bind-user" style="height: 38px;">绑定用户</button>
                       <?php elseif($style == 'view'): ?>
                       <input class="layui-input now_bind_user" readonly id="" value="<?php echo $messageNews['user']; ?>" style="display: inline-block">
                       <?php endif; ?>
                   </div>
               </div>
           </div>
           <div class="form-group">
               <div class="col-md-12">
                   <div class="input-group" data-select="$select">
                       <span class="input-group-addon">版块分类</span>
                       <?php if($style == 'create'): ?>
                       <select class="layui-select layui-input" name="fid" id="fid" value="">
                           <option value="">请选择版块分类</option>
                           <?php if(is_array($select) || $select instanceof \think\Collection || $select instanceof \think\Paginator): $i = 0; $__LIST__ = $select;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                           <option value="<?php echo $vo['id']; ?>"><?php echo $vo['name']; ?></option>
                           <?php endforeach; endif; else: echo "" ;endif; ?>
                       </select>
                       <?php elseif($style == 'edit'): ?>
                       <select class="layui-select layui-input" name="fid" id="fid" value="<?php echo $messageNews['fid']; ?>">
                           <option value="">请选择版块分类</option>
                           <?php if(is_array($select) || $select instanceof \think\Collection || $select instanceof \think\Paginator): $i = 0; $__LIST__ = $select;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;if($vo['id'] == $messageNews['fid']): ?>
                           <option value="<?php echo $vo['id']; ?>" selected><?php echo $vo['name']; ?></option>
                           <?php else: ?>
                           <option value="<?php echo $vo['id']; ?>"><?php echo $vo['name']; ?></option>
                           <?php endif; endforeach; endif; else: echo "" ;endif; ?>
                       </select>
                       <?php elseif($style == 'view'): ?>
                       <select class="layui-select layui-input" name="fid" id="fid" disabled value="<?php echo $messageNews['fid']; ?>">
                           <option value="">请选择版块分类</option>
                           <?php if(is_array($select) || $select instanceof \think\Collection || $select instanceof \think\Paginator): $i = 0; $__LIST__ = $select;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;if($vo['id'] == $messageNews['fid']): ?>
                           <option value="<?php echo $vo['id']; ?>" selected><?php echo $vo['name']; ?></option>
                           <?php else: ?>
                           <option value="<?php echo $vo['id']; ?>"><?php echo $vo['name']; ?></option>
                           <?php endif; endforeach; endif; else: echo "" ;endif; ?>
                       </select>
                       <?php endif; ?>
                   </div>
               </div>
           </div>
           <div class="form-group">
               <div class="col-md-12">
                   <div class="input-group">
                       <span class="input-group-addon">二级分类</span>
                       <?php if($style == 'create'): ?>
                       <select class="layui-select layui-input" name="class_id" id="class_id" value="">
                           <option value="">不选择</option>
                       </select>
                       <?php elseif($style == 'edit'): ?>
                       <select class="layui-select layui-input" name="class_id" id="class_id" value="<?php echo $messageNews['class_id']; ?>">
                           <option value="">不选择</option>
                           <?php if(is_array($class) || $class instanceof \think\Collection || $class instanceof \think\Paginator): $i = 0; $__LIST__ = $class;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;if($v['id'] == $messageNews['class_id']): ?>
                           <option value="<?php echo $v['id']; ?>" selected><?php echo $v['name']; ?></option>
                           <?php else: ?>
                           <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                           <?php endif; endforeach; endif; else: echo "" ;endif; ?>
                       </select>
                       <?php elseif($style == 'view'): ?>
                       <select class="layui-select layui-input" name="class_id" id="class_id" disabled value="<?php echo $messageNews['class_id']; ?>">
                           <option value="">未关联分类</option>
                           <?php if(is_array($class) || $class instanceof \think\Collection || $class instanceof \think\Paginator): $i = 0; $__LIST__ = $class;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;if($v['id'] == $messageNews['class_id']): ?>
                           <option value="<?php echo $v['id']; ?>" selected><?php echo $v['name']; ?></option>
                           <?php else: ?>
                           <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                           <?php endif; endforeach; endif; else: echo "" ;endif; ?>
                       </select>
                       <?php endif; ?>
                   </div>
               </div>
           </div>
           <div class="form-group">
               <div class="col-md-12">
                   <div class="input-group">
                       <span class="input-group-addon">推送时间</span>
                       <input type="text" class="layui-input" id="send_time" readonly placeholder="请选择推送时间">
                   </div>
               </div>
           </div>
           <div class="form-group">
               <div class="col-md-12">
                   <div class="input-group">
                       <span class="input-group-addon">推送图片(660*260)</span>
                       <?php if($style == 'create'): ?>
                       <input type="file" class="upload" name="image" style="display: none;" id="image" />
                       <a style="display: block;width: 102px;border: 1px solid #E5E6E7;" class="btn-sm add_image upload_span" >
                           <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url('/public/system/module/wechat/news/images/image.png')">
                               <input value="" type="hidden" id="image_input" name="local_url">
                           </div>
                       </a>
                       <?php elseif($style == 'edit'): ?>
                       <input type="file" class="upload" name="image" style="display: none;" id="image" />
                       <a style="display: block;width: 102px;border: 1px solid #E5E6E7;" class="btn-sm add_image upload_span" >
                           <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url('<?php echo $messageNews['logo']; ?>')">
                               <input value="<?php echo $messageNews['logo']; ?>" type="hidden" id="image_input" name="local_url">
                           </div>
                       </a>
                       <?php elseif($style == 'view'): ?>
                       <input type="file" class="upload" name="image" style="display: none;" id="image" />
                       <a style="display: block;width: 102px;border: 1px solid #E5E6E7;cursor: pointer" class="btn-sm add_image" >
                           <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url('<?php echo $messageNews['logo']; ?>')">
                               <input value="<?php echo $messageNews['logo']; ?>" type="hidden" id="image_input" name="local_url">
                           </div>
                       </a>
                       <?php endif; ?>
                   </div>
               </div>
           </div>
           <div class="form-group">
               <div class="col-md-12">
                   <div class="input-group">
                       <span class="input-group-addon">摘要</span>
                       <?php if($style == 'create'): ?>
                       <input maxlength="64" placeholder="请在这里输入摘要(建议20个字以内)" name="summary" class="layui-input" id="summary" value="">
                       <?php elseif($style == 'edit'): ?>
                       <input maxlength="64" placeholder="请在这里输入摘要(建议20个字以内)" name="summary" class="layui-input" id="summary" value="<?php echo $messageNews['summary']; ?>">
                       <?php elseif($style == 'view'): ?>
                       <input maxlength="64" placeholder="请在这里输入摘要(建议20个字以内)" name="summary" class="layui-input" id="summary" value="<?php echo $messageNews['summary']; ?>" readonly>
                       <?php endif; ?>
                       <input type="hidden" name="id" value="">
                   </div>
               </div>
           </div>
           <div class="form-group">
               <div class="col-md-12">
                   <label style="color:#aaa">公告内容</label>
                   <?php if($style == 'create'): ?>
                   <textarea type="text/plain" id="myEditor" style="width:100%;"></textarea>
                   <?php elseif($style == 'edit'): ?>
                   <textarea type="text/plain" id="myEditor" style="width:100%;"><?php echo $messageNews['content']; ?></textarea>
                   <?php elseif($style == 'view'): ?>
                   <textarea type="text/plain" id="myEditor" style="width:100%;"><?php echo $messageNews['content']; ?></textarea>
                   <?php endif; ?>
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

    
    
    <script>
      $("#fid").change(function () {
        $.ajax({
          url:"<?php echo Url('com.com_thread/select_class'); ?>",
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
        $eb.createModalFrame("绑定用户",'<?php echo Url('bind_user_vim'); ?>',{w:document.body.clientWidth,h:document.body.clientHeight})
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
      $.ajax({
        url:"<?php echo Url('get_user'); ?>",
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
      function add0(m){return m<10?'0'+m:m }
      function format(shijianchuo)
      {
//shijianchuo是整数，否则要parseInt转换
        var time = new Date(shijianchuo);
        var y = time.getFullYear();
        var m = time.getMonth()+1;
        var d = time.getDate();
        var h = time.getHours();
        var mm = time.getMinutes();
        var s = time.getSeconds();
        return y+'-'+add0(m)+'-'+add0(d)+' '+add0(h)+':'+add0(mm)+':'+add0(s);
      }
    </script>
    <?php if($style == 'create'): ?>
    <script>
      layui.use('laydate', function(){
        var laydate = layui.laydate;
        //执行一个laydate实例
        laydate.render({
          elem: '#send_time', //指定元素
          type:'datetime',
        });
      });
      /**
       * 提交图文
       * */
      $('.save_news').on('click',function(){
        var sendTime = $('#send_time').val();
        var list = {};
        list.title = $('#title').val();/* 标题 */
        list.false_view = $('#false_view').val();/* 虚拟浏览量 */
        list.author_uid = $('#author').val();/* 作者 */
          list.summary = $('#summary').val();/* 摘要 */
        list.content = getContent();/* 内容 */
        list.send_time = dataToStamp(sendTime);/* 推送时间 */
        list.logo = $("#image_input").val();/* 推送图片 */
        list.fid= $("#fid option:selected").val();
        list.class_id= $("#class_id option:selected").val();
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
        if(list.send_time == ''){
          $eb.message('error','请选择推送时间');
          return false;
        }
        if(list.logo == ''){
          $eb.message('error','请选择推送图片');
          return false;
        }
        if(list.content == ''){
          $eb.message('error','请输入内容');
          return false;
        }
        var data = {};
        $.ajax({
          url:"<?php echo Url('add_message_news'); ?>",
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
    <?php elseif($style == 'edit'): ?>
    <script>
        var sendTime = "<?php echo $messageNews['send_time']; ?>";
        var sendTimeDate = format(sendTime*1000);
      layui.use('laydate', function(){
        var laydate = layui.laydate;
        //执行一个laydate实例
        laydate.render({
          elem: '#send_time', //指定元素
          type:'datetime',
          value: sendTimeDate
        });
      });
        /**
         * 提交图文
         * */
        $('.save_news').on('click',function(){
          var sendTime = $('#send_time').val();
          var list = {};
          list.id = "<?php echo $messageNews['id']; ?>";
          list.title = $('#title').val();/* 标题 */
          list.false_view = $('#false_view').val();/* 虚拟浏览量 */
          list.author_uid = $('#author').val();/* 作者 */
            list.summary = $('#summary').val();/* 摘要 */
          list.content = getContent();/* 内容 */
          list.send_time = dataToStamp(sendTime);/* 推送时间 */
          list.logo = $("#image_input").val();/* 推送图片 */
          list.fid= $("#fid option:selected").val();
          list.class_id= $("#class_id option:selected").val();
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
          if(list.send_time == ''){
            $eb.message('error','请选择推送时间');
            return false;
          }
          if(list.logo == ''){
            $eb.message('error','请选择推送图片');
            return false;
          }
          if(list.content == ''){
            $eb.message('error','请输入内容');
            return false;
          }
          var data = {};
          $.ajax({
            url:"<?php echo Url('edit_message_news'); ?>",
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
    <?php elseif($style == 'view'): ?>
    <script>
      var sendTime = "<?php echo $messageNews['send_time']; ?>";
      var sendTimeDate = format(sendTime*1000);
      $("#send_time").val(sendTimeDate);
    </script>
    <?php endif; ?>
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
            $('.upload_span').on('click',function (e) {
//                $('.upload').trigger('click');
                createFrame('选择图片','<?php echo Url('widget.images/index'); ?>?fodder=image');
            })

            /**
             * 编辑器上传图片
             * */
            $('.edui-icon-image').on('click',function (e) {
//                $('.upload').trigger('click');
                createFrame('选择图片','<?php echo Url('widget.images/index'); ?>?fodder=image');
            })


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
  function dataToStamp(data) {
    if(data === ""){
      return "";
    }else {
      var str = data.replace(/-/g,'/');
      var dataTime = new Date(str);
      dataTime = Date.parse(dataTime);
      dataTime = dataTime / 1000;
      return dataTime;
    }
  }

        </script>
    <?php if($style == 'view'): ?>
    <script>
      ue.ready(function () {
        ue.setDisabled()
      });
    </script>
    <?php endif; ?>

    
</div>
</body>
</html>
