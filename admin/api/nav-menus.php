<?php 
require_once '../../functions.php';

// 设置响应类型为 JSON
// header('Content-Type: application/json');

if (empty($_GET['key'])) {
	exit();
}

$sql = sprintf("select `value` from options where `key` = '%s' limit 1;",$_GET['key'] );

// 查询所有的评论数据
$navs = xiu_fetch_one($sql);


// echo $navs['value'];// => [{},{}]  客户端收到的是[{},{}] 输出OK echo输出的只能是字符串

// $json=json_encode($navs);
// echo $json;// => "{"value":"[]"}"  这样输出不行，value的值是字符串，不是数组，
// 模板引擎渲染数据的时候需要的是数组

// $json = json_encode($navs['value']);
$json = $navs['value'];
echo $json;// => "[{},{}]"  客户端收到的是"[{},{}]"

// 可以和admin/api/comments.php  做比较
