<?php

$files = $_FILES['file'];
// $array = $_POST['file'];
print_r($files);die;
echo json_encode($_POST['key']);die;
$filename ="/templates/".time().$files["name"];

$res=move_uploaded_file($files["tmp_name"],$filename);//将临时地址移动到指定地址

if ($res) {
	echo json_encode('12345678');
}

echo json_encode('987654321');