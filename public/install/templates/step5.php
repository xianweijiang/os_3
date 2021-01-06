<!doctype html>
<html>
<head>
<meta charset="UTF-8" />
<title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
<link rel="stylesheet" href="./css/install.css?v=9.0" />
<script src="js/jquery.js"></script>
<?php 
$uri = $_SERVER['REQUEST_URI'];
$root = substr($uri, 0,strpos($uri, "install"));
$admin = $root."../index.php/admin/index/";

?>

</head>
<body>
<div class="wrap">
  <?php require './templates/header.php';?>
  <section class="section">
    <div class="">
      <div class="success_tip cc"> <a href="<?php echo $admin;?>" class="f16 b">安装完成，进入后台管理</a>
		<p>为了您站点的安全，安装完成后即可将网站根目录下的“install”文件夹删除，或者/install/目录下创建install.lock文件防止重复安装。<p>
      </div>
	        <div class="bottom tac"> 
	        <a href="<?php echo 'http://'.$host;?>/index.php/admin/login/index" class="btn btn_submit J_install_btn">进入后台</a>
      </div>
      <div class=""> </div>
    </div>
  </section>
</div>
<?php   $my = fopen("code.txt", "r") or die("Unable to open file!");
$str = fread($my,filesize('code.txt'));//指定读取大小，这里把整个文件内容读取出来
fclose($my);?>
<script>
    $.ajax({
        url:"<?php echo 'http://'.$host;?>/index.php/osapi/base/giveAuth/auth_get_code/<?php echo $str;?>/no_out/0",
        data:{},
        type:"post",
        dataType:"json",
    })
</script>
<?php require './templates/footer.php';?>
</body>
</html>