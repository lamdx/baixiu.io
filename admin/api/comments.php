<?php 
/**
 * 分页返回评论数据接口（JSON）
 */

// 接收客户端的AJAX请求 返回评论数据
 
// 载入封装的所有函数
require_once '../../functions.php';

$page = empty($_GET['page']) ? 1 : intval($_GET['page']);// 转换成数值

$length = 8;
$skip = ($page - 1) * $length;

$sql = sprintf('select 
 comments.*,
 posts.title as post_title
 from comments
 inner join posts on comments.post_id = posts.id
 order by comments.created desc
 limit %d, %d;', $skip, $length);

// 查询所有的评论数据
$comments = xiu_fetch_all($sql);

// 先查询到所有数据的数量
$total_count = xiu_fetch_one('select count(1) as count 
 from comments
 inner join posts on comments.post_id = posts.id')['count'];
// 计算总页数
$total_pages = ceil($total_count / $length);

// 因为网络之间传输的只能是字符串
// 所以我们先将数据转换为字符串（序列化）
$json = json_encode(array(
	'total_pages' => $total_pages,
	'comments' => $comments
));


// 设置响应的响应体类型为JSON
// header('Content-Type: application/json');

// 响应给客户端
echo $json;// => {"total_pages":10,"comments":[]}


// 补充，关于父ID联合查询问题
// $sql = sprintf('select 
// comments.*,
// parent.content,
// posts.title as post_title
// from comments
// INNER JOIN posts ON comments.post_id = posts.id
// INNER JOIN comments as parent on comments.parent_id = parent.id')// as取别名
