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

$rows=xiu_execute('delete from posts where id in(' . $id . ');');

header('Location:' . $_SERVER['HTTP_REFERER']);