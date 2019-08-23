<?php 
/**
 * 根据客户端传递过来的ID删除对应数据
 */

require_once '../../functions.php';

if (empty($_GET['id'])) {
	exit('缺少必要参数');
}

$id = $_GET['id'];
// => '1 or 1 = 1'
// sql 注入
// $id = (int)$_GET['id'];
// 

$rows = xiu_execute('delete from users where id in(' . $id . ');');

// 获取删除后跳转到的目标链接，优先跳转到来源页面，否则默认跳转到列表页
$target = empty($_SERVER['HTTP_REFERER']) ? '/admin/users.php' : $_SERVER['HTTP_REFERER'];
header('Location: ' . $target);

