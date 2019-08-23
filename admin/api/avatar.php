<?php 
/**
 * 根据用户邮箱获取用户头像
 * email => image
 */

// require_once '../../config.php';
require_once '../../functions.php';

if (empty($_GET['email'])) {
	exit('缺少参数');
}

$email = $_GET['email'];

// $connection = mysqli_connect(XIU_DB_HOST,XIU_DB_USER,XIU_DB_PASS,XIU_DB_NAME);
// if (!$connection) {
// 	exit('连接失败');
// }
// $query = mysqli_query($connection,"select avatar from users where email='{$email}' limit 1");
// if (!$query) {
// 		exit('查询失败');
// }
// $row = mysqli_fetch_assoc($query);

$row = xiu_fetch_one("select avatar from users where email='{$email}' limit 1;");

echo($row['avatar']);
