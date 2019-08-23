<?php 
// var_dump($_FILES['avatar']);

if (empty($_FILES['avatar'])) {
	exit();
}
$avatar = $_FILES['avatar'];

if ($avatar['error'] !== UPLOAD_ERR_OK) {
	exit('上传失败');
}
// 校验文件大小

// 移动文件到网站范围内
$ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
$target = '../../static/uploads/img-' . uniqid() . '.' . $ext;
if (!move_uploaded_file($avatar['tmp_name'], $target)) {
	exit('上传失败');
}

echo substr($target, 5);