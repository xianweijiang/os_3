{extend name="public/container"}
{block name="content"}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        #container {
            padding: 10px;
            width: 100%;
            height: 900px;
            border-radius: 8px;
            background: white;
            position: relative;
        }
        .header {
            width: 100%;
            height: 20px;
            border-bottom: solid 1px #F6F6F6;
            margin-bottom: 2s0px;
        }
        .headline {
            font-size: 16px;
            font-weight: bold;
        }
        .jump-radio {
            display: flex;
            font-size: 14px;
        }
        .text {
            width: 220px;
            position: relative;
            left: 168px;
        }
        .jump1 {
            margin: 0 auto;
            width: 1200px;
            margin-top: 20px;
        }
        .jump-radio>div>img {
            width: 320px;
            height: 295px;
        }
        .text2 {
            margin-left: 200px;
        }
        .box {
            display: flex;
            width: 460px;
            height: 30px;
            border: solid 1px #D7D7D7;
        }
        .btn-box {
            display: flex;
            justify-content: space-evenly;
            font-size: 12px;
        }
        .btn {
            padding-left: 12px;
            text-align: center;
            color: #676A6C;
            width: 62px;
            height: 22px;
            line-height: 22px;
            border: solid 1px #D7D7D7;
            border-radius: 4px;
            position: relative;
            cursor: pointer;
        }
        .icon {
            display: inline-block;
            position: absolute;
            width: 16px;
            height: 16px;
            left: 4px;
            top: -2px;
        }
        .icon img {
            width: 16px;
            height: 16px;
        }
        .btn span {
            position: relative;
            top: -6px;
            left: 6px;
        }
        #hide-box {
            position: absolute;
            right: 550px;
            bottom: 200px;
            width: 220px;
            height: 220px;
            background: url('/public/system/images/xfk.png');
            background-size: 220px 238px;
            display: none;
        }
        #hide-box img{
            margin-left: 35px;
            width: 150px;
            height: 150px;
            margin-top: -45px;
        }
        #hide-box p{
            position: absolute;
            font-size: 12px;
            margin: 15px 0 0 30px;
            top: 15px;
        }
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
        .jump1 .jump-input .headline-top {
            width: 120px
        }
    </style>
</head>
<body>
    <div id="container">
        <div class="header"></div>
        <div class="alert alert-info" role="alert">
            特别说明：不论选择跳转至框架页还是单独PC端，框架页都一样存在。也可被访问到。必须购买PC端模块后，方可开启单独PC端的跳转。(注意：框架页的跳转目前只支持https协议！)
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="jump1">
            <div class="jump-radio">
                <div><span class="headline">开启PC端跳转：</span></div>
                <div class="i-checks" style="margin-left: 52px;margin-right: 280px;"><label><input name="Jump" type="radio" value="" id="radio1" data-jump="{$info.is_jump}"/>开启PC端强制跳转 </label></div> 
                <div class="i-checks"><label><input name="Jump" type="radio" value="" id="radio2"/>关闭PC端强制跳转 </label></div>
            </div>
            <p>
                <div class="text">开启后，SEO小站与动态站，均会按照规则自动跳转至对应PC端</div>
            </p>
        </div>
        <div class="jump1" style="margin-top: 40px">
            <div class="jump-radio">
                <div style="margin-right: 33px;"><span class="headline">PC端强制跳转至：</span></div>
                <div style="margin-right: 100px;">
                    <img src="/public/system/images/pc1.png" alt="无法加载图片">
                </div>
                <div>
                    <img src="/public/system/images/pc2.png" alt="无法加载图片">
                </div>
            </div>
        </div>
        <div class="jump1" style="width: 867px">
            <div class="jump-radio">
                <div style="margin-right: 322px;" class="i-checks"><label><input name="Kind" type="radio" value="" id="radio3"/>框架网页形式 </label></div> 
                <div class="i-checks"><label><input name="Kind" type="radio" value="" id="radio4"/>单独PC端 </label></div>
            </div>
            <div style="display: flex">
                <div class="text" style="left: 0px">按照500px宽度，框架载入动态站，功能齐全，体验较差</div>
                <div class="text2">采用独立PC端，功能较少，但是体验更好</div>
            </div>
        </div>
        <div class="jump1" style="margin-top: 40px;position: relative">
            <div class="jump-radio jump-input">
                <div style="margin-right: 48px;" class='headline-top'><span class="headline">PC端访问网址：</span></div>
                <div class="box" style="position: absolute;top: -4px;left: 165px;margin-right: 66px;">
                    <div style="width: 55px;margin-top: 5px;margin-left: 5px;margin-right: 38px;margin-right: 28px">
                        框架页:
                    </div>
                    <div  style="margin-right: 0px;margin-top: 5px">
                        <input type="text" value="{$info.frame_url}" id="text1" style="outline: none;border: none;width: 330px;" onchange="change1()" onkeydown="keyBoard(event)">
                    </div>
                </div>
                <div class="btn-box" style="position: absolute;left: 692px;">
                    <div class="share btn"  onclick="makeCode(1)" style="margin-right: 50px;">
                        <i class="icon"><img src="/public/system/images/share-1.png" alt=""></i>
                        <span>分享</span>
                    </div>
                    <div class="copy btn" onclick="copyText('text1')">
                        <i class="icon"><img src="/public/system/images/copy-1.png" alt=""></i>
                        <span>复制</span>
                    </div>
                </div>
            </div>
            <div class="box" style="margin-left: 165px;margin-top: 15px;margin-right: 66px;position: absolute;top: 36px;">
                    <div  style="width: 80px;margin-top: 5px;padding-left: 5px;margin-right: 8px">独立PC端:</div>
                    <div  style="margin-right: 0px;margin-top: 5px">
                        <input type="text" value="{$info.pc_url}" id="text2" style="outline: none;border: none;width: 330px;" onchange="change()" onkeydown="keyBoard(event)">
                    </div>
            </div>
            <div class="btn-box" style="position: absolute;bottom: -55px;right: 335px;">
                    <div class="share btn" style="margin-right: 50px" onclick="makeCode(2)">
                        <i class="icon"><img src="/public/system/images/share-1.png" alt=""></i>
                        <span>分享</span>
                    </div>
                    <div class="copy btn" onclick="copyText('text2')">
                        <i class="icon"><img src="/public/system/images/copy-1.png" alt=""></i>
                        <span>复制</span>
                    </div>
            </div>
        </div>
        <div class="form-group" id="upload_img_content">
            <div class="col-md-12" style="height: 97px;margin-top: 80px;">
                <div class="input-group" style="display: flex;padding-left: 152px;position: relative;">
                    <span class="input-group-addon headline" style="width: auto;line-height: 24px;border: none">请上传公众号二维码：</span>
                    <input type="file" class="upload" name="image" style="display: none;" id="image"/>
                    <div id="img_content" style="display: flex;max-width: 600px;flex-wrap: wrap" data-img="{$info.image}">
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
        <p><button type="button" class="btn btn-w-m btn-info save_news" style="height: 35px;left: 350px;top: 26px;color: white" onclick="submit()">提交</button></p>
        <div id="hide-box">
            <p>扫描二维码，分享网页到微信</p>
            <img src="" alt="" id="img">
        </div>
    </div>
</body>
</html>
{/block}
{block name="script"}
<script src="{__FRAME_PATH}js/toast-js.js"></script>
<script src="{__PUBLIC_PATH}install/js/qrcode.min.js"></script>
<link href="{__FRAME_PATH}css/plugins/iCheck/custom.css" rel="stylesheet">
<script src="{__ADMIN_PATH}plug/validate/jquery.validate.js"></script>
<script src="{__ADMIN_PATH}frame/js/plugins/iCheck/icheck.min.js"></script>
<script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>
<script>
    // 获取元素和值
    let inputElement1 = document.getElementById('text1');
    let inputElement2 = document.getElementById('text2');
    let attr1 = inputElement1.getAttribute('value');
    let attr2 = inputElement2.getAttribute('value');
    let is_jump = 0;
    let jump_type = 1;
    let val = document.getElementById("text2").value;
    let imageElements = document.getElementsByClassName('upload-img-box-img');
    let image = '';
    let imgAttr = document.getElementById('img_content').getAttribute('data-img');

    $().ready(function() {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
    });
    
    if (imgAttr) {
        $("#img_content").append('<div class="upload-img-box"><div class="delete-btn"><img src="{__ADMIN_PATH}css/delete.png" alt=""></div><img class="upload-img-box-img" src=' + imgAttr + ' id="add-img"></div>');
    }

    if (imageElements.length) {
        image = imageElements[0].src;
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
    if(imgVal.length > 0){
      $eb.message('error', '最多上传1张');
    //   $("#upload_img_box").hide();
      return;
    }
    if (document.getElementById('add-img')) {
        document.getElementById('add-img').src = pic;
    } else {
        $("#img_content").append('<div class="upload-img-box"><div class="delete-btn"><img src="{__ADMIN_PATH}css/delete.png" alt=""></div><img class="upload-img-box-img" src=' + pic + ' id="add-img"></div>');
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
    
    $('input[name="Jump"]').on('ifChanged', function(){
        if ($('input[name="Jump"][id="radio1"]').prop("checked")) {
            $('input[name="Kind"][id="radio3"]').iCheck('check');
            $.ajax({
                type: 'POST',
                url: "{:Url('pc.pc_set/update')}",
                data: {is_jump: 1, jump_type, frame_url: attr1, pc_url: attr2, image: imgAttr},
                dataType: 'json',
                success(res) {
                }
            })
            } else if ($('input[name="Jump"][id="radio2"]').prop("checked")){
                $('input[name="Kind"][id="radio3"]').iCheck('uncheck');
                $('input[name="Kind"][id="radio4"]').iCheck('uncheck');
                $.ajax({
                type: 'POST',
                url: "{:Url('pc.pc_set/update')}",
                data: {is_jump: 0, frame_url: attr1, pc_url: attr2, image: imgAttr},
                dataType: 'json',
                success(res) {
                }
            })
        }
    })

    $('input[name="Kind"]').on('ifChanged', function(){
        if ($('input[name="Kind"][id="radio3"]').prop("checked")) {
            $('input[name="Jump"][id="radio1"]').iCheck('check');
            $.ajax({
                type: 'POST',
                url: "{:Url('pc.pc_set/update')}",
                data: {jump_type: 1, is_jump: 1,  frame_url: attr1, pc_url: attr2, image: imgAttr},
                dataType: 'json',
                success(res) {
                }
            })
            } else if ($('input[name="Kind"][id="radio4"]').prop("checked")){
                $('input[name="Jump"][id="radio1"]').iCheck('check');
                $.ajax({
                type: 'POST',
                url: "{:Url('pc.pc_set/update')}",
                data: {jump_type: 2, is_jump: 1, frame_url: attr1, pc_url: attr2, image: imgAttr},
                dataType: 'json',
                success(res) {
                }
            })
        }
    })

    // 复制的方法
    function copyText(id) {
        if (id == 'text1') {
            inputElement1.select();//选中input框的内容
            document.execCommand("Copy");// 执行浏览器复制命令
            $eb.message('success', '复制成功');
        } else if (id == 'text2') {
            inputElement2.select();
            document.execCommand("Copy");
            $eb.message('success', '复制成功');
        } else {
            $eb.message('error', '复制失败');
        }
    }

    // 定时器清楚提示文字
    setTimeout(function () {
        $('.alert-info').hide();
    }, 10000);

    // 自动获取单选框状态
   if ({$info.is_jump} === 0) {
        document.getElementById('radio2').checked = true
   } else if ({$info.is_jump} === 1){
        document.getElementById('radio1').checked = true
   }

   if ({$info.jump_type} === 1) {
        document.getElementById('radio3').checked = true
   } else if ({$info.jump_type} === 2){
        document.getElementById('radio4').checked = true
   }

    function change(){
        var x = document.getElementById("text2");
        x.value = x.value;
    }

    function change1(){
       var x = document.getElementById("text1");
       x.value = x.value;
    }

   function postMsg() {
       let val1 = inputElement1.value;
       let val2 = inputElement2.value;
       is_jump = document.getElementById('radio1').checked ? 1 : 0;
       jump_type = document.getElementById('radio3').checked || document.getElementById('radio4').checked ? document.getElementById('radio3').checked ? 1 : 2 : 0;
       if (!document.getElementsByClassName('upload-img-box-img').length) {
            $eb.message('error', '请选择一张二维码上传！');
            return
       }
       image = document.getElementsByClassName('upload-img-box-img')[0].src;
            $.ajax({
                    type: 'POST',
                    url: "{:Url('pc.pc_set/update')}",
                    data: {jump_type, is_jump,  frame_url: val1, pc_url: val2, image},
                    dataType: 'json',
                    success(res) {
                        $eb.message('success', '提交成功');
                    }
                })
   }

   function keyBoard(event) {
        if (event.keyCode == '13') {
            postMsg()
        }
   }

   var qrcode = new QRCode(document.getElementById("hide-box"), {
	    width : 180,
	    height : 180
    });
    function makeCode(num) {		
        let inputElement1 = document.getElementById('text1');
        let inputElement2 = document.getElementById('text2');
        let attr1 = inputElement1.getAttribute('value');
        let attr2 = inputElement2.getAttribute('value');
        let text = num == 1 ? attr1 : attr2;
	    if (text) {
            qrcode.makeCode(text)
        } else {
            $eb.message('error', '失败');
        }
        shareErWei()
    }

    function shareErWei() {
        document.getElementById('hide-box').style.display = 'block';
        setTimeout(() => {
            document.getElementById('hide-box').style.display = 'none';
        }, 5000);
    }

    function submit() {
        postMsg()
    } 
</script>
{/block}
