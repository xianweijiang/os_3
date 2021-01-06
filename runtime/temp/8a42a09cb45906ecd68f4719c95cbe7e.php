<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:96:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/edit_content.php";i:1597214754;}*/ ?>
<!doctype html>
<!--suppress JSAnnotator -->
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>编辑内容</title>
    <link href="/public/system/frame/css/font-awesome.min.css" rel="stylesheet">
    <script type="text/javascript" src="/public/system/plug/umeditor/third-party/jquery.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="/public/system/plug/ueditor/ueditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="/public/system/plug/ueditor/ueditor.all.js"></script>
    <style>
        .edui-btn-toolbar .edui-btn.edui-active .edui-icon-fullscreen.edui-icon{  display: none;}
        .edui-container{overflow: initial !important;}
        /* button.btn-success.dim {  box-shadow: inset 0 0 0 #1872ab,0 5px 0 0 #1872ab,0 10px 5px #999; } */
        .float-e-margins .btn { margin-bottom: 5px;  }
        button.dim { display: inline-block; color: #fff; text-decoration: none; text-transform: uppercase; text-align: center;  margin-right: 10px; position: relative; cursor: pointer; border-radius: 20px; font-weight: 600; margin-bottom: 20px!important;  }
        .btn-success { background-color: #5bc9fa; border: none; color: #FFF;  }
        .btn { border-radius: 20px;  }
        .btn-success { color: #fff; background-color: #5bc9fa; border: none;  }
        .btn { display: inline-block; padding: 10px 15px; border-radius: 20px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none;}
        button, input, select, textarea { font-family: inherit; font-size: inherit; line-height: inherit;  }
        /* button.btn-success.dim:active { box-shadow: inset 0 0 0 #1872ab,0 2px 0 0 #1872ab,0 5px 3px #999; } */
        button.dim:active { bottom: 4px; }
        .btn-success.active, .btn-success:active, .open .dropdown-toggle.btn-success { background-image: none;  }
        .btn-success.active, .btn-success:active, .btn-success:focus, .btn-success:hover, .open .dropdown-toggle.btn-success { background-color: #1388bb; border: none; color: #FFF;box-shadow: none;  }
        .dim{bottom: 20px; right: 20px; z-index: 10030; position: fixed !important;}
        
    </style>
</head>
<body>
<button class="btn btn-success  dim submit-btn" data-url="<?php echo $action; ?>" type="button"><i class="fa fa-upload"></i>提交
</button>
<script type="text/plain" id="myEditor" style="width:100%;">
<?php echo !empty($content)?$content : ''; ?>
</script>
<script type="text/javascript">
    $eb = parent._mpApi;
    $('.dim').on('click',function(){
        $eb.axios.post($(this).data('url'),{'<?php echo $field; ?>':getContent()}).then(function(res){
            if(res.status == 200 && res.data.code == 200){
                $eb.message('success','保存成功!');
            } else
                return Promise.reject(res.data.msg || '保存失败!');
        }).catch(function(err){
            $eb.message('error',err);
        })
    });
var ue = UE.getEditor('myEditor',{
  autoHeightEnabled: false,
  fullscreen: true,
  wordCount: false,
  maximumWords: 100000
});
    function getContent() {
      return (ue.getContent());
    }
    function hasContent() {
        return (UM.getEditor('myEditor').hasContents());
    }
</script>
</body>
</html>