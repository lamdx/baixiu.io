<?php
/**
 * 封装大家公用的函数
 */

// 载入配置选项，为了防止 functions.php 重复被载入时载入配置报错，所以使用 require_once 
require_once 'config.php';

// 定义函数时一定要注意：函数名与内置函数冲突问题
// JS 判断方式：typeof fn === 'function'
// PHP 判断函数是否定义的方式： function_exists('get_current_user')

// 启动会话
// 给用户找一个箱子（如果你之前有就用之前的，没有给个新的）
session_start();

/**
 * 获取当前登录用户信息，如果没有获取到则自动跳转到登录页面
 */
function xiu_get_current_user(){
	if (empty($_SESSION['current_login_user'])) {
		// 没有当前登录用户信息，意味着没有登录
	  	header('Location:/admin/login.php');
	  	exit();// 没有必要再执行之后的代码
	}
	return $_SESSION['current_login_user'];
}
	
/**
 * 通过一个数据库查询获取多条数据
 * => 索引数组套关联数组
 */
function xiu_fetch_all($sql){
	// 获取数据库连接
	$connection = mysqli_connect(XIU_DB_HOST, XIU_DB_USER, XIU_DB_PASS, XIU_DB_NAME);
	if (!$connection) {
		// 链接数据库失败，打印错误信息，注意：生产环境不能输出具体的错误信息（不安全）
		exit('连接失败');
	}
	$query = mysqli_query($connection,$sql);
	if (!$query) {// 用来证明mysql查询语句有没有问题？！
		// 查询失败
		return false;
	}
	// 代码执行到这里代表mysql查询语句没有问题，即查询结果为空数据记录，或者是带有数据的记录
	// 这里一定要定义$result,因为假设没有进入while循环，后面会出现错误Undefined variable，
  // 例如在分页组件 comments-delete.php 
	// 定义结果数据容器，用于装载查询到的数据
	$result = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$result[] = $row;
	}
	// 释放结果集
	mysqli_free_result($query);
	// 关闭数据库连接
	mysqli_close($connection);
	return $result;
}

/**
 * 获取单条数据
 * => 关联数组
 */
function xiu_fetch_one($sql){
	$res = xiu_fetch_all($sql);
	return isset($res[0]) ? $res[0] : null;
}

/**
 * 执行一个非查询(增删改) 查询语句，返回执行语句后受影响的行数
 * @param  [string] $sql 非查询 查询语句
 * @return [integer]      受影响的行数
 */
function xiu_execute($sql){
	$connection = mysqli_connect(XIU_DB_HOST, XIU_DB_USER, XIU_DB_PASS, XIU_DB_NAME);
	if (!$connection) {
		exit('连接失败');
	}
	$query = mysqli_query($connection,$sql);
	if (!$query) {
		// 查询失败
		return false;
	}
	// 对于增删修改类的操作都是获取受影响行数
	$affected_rows = mysqli_affected_rows($connection);
	// mysqli_free_result($query);// 不需要
	// 关闭数据库连接
	mysqli_close($connection);
	// 返回受影响的行数
	return isset($affected_rows) ? $affected_rows : 0;
}

/**
 * 输出分页链接
 * @param  integer $page    当前页码
 * @param  integer $total   总页数
 * @param  string  $format    链接模板，%d 会被替换为具体页数
 * @param  integer $visible 可见页码数量（可选参数，默认为 5）
 * @example
 *   <?php xiu_pagination(2, 10, '/list.php?page=%d', 5); ?>
 */
function xiu_pagination ($page, $total_page, $format, $visible = 5) {
  // 计算起始页码
  // 当前页左侧应有几个页码数，如果一共是 5 个，则左边是 2 个，右边是两个
  $left = floor($visible / 2);
  // 开始页码
  $begin = $page - $left;
  // 确保开始不能小于 1
  $begin = $begin < 1 ? 1 : $begin;
  // 结束页码
  $end = $begin + $visible;
  // 确保结束不能大于最大值 $total_page
  $end = $end > $total_page ? $total_page+1 : $end;
  // 如果 $end 变了，$begin 也要跟着一起变
  $begin = $end - $visible;
  // 确保开始不能小于 1
  $begin = $begin < 1 ? 1 : $begin;

  // 上一页
  if ($page - 1 > 0) {
    printf('<li><a href="%s">&laquo;</a></li>', sprintf($format, $page - 1));
  }

  // 省略号
  if ($begin > 1) {
    print('<li class="disabled"><span>···</span></li>');
  }

  // 数字页码
  for ($i = $begin; $i < $end; $i++) {
    // 经过以上的计算 $i 的类型可能是 float 类型，所以此处用 == 比较合适
    $activeClass = $i == $page ? ' class="active"' : '';
    printf('<li%s><a href="%s">%d</a></li>', $activeClass, sprintf($format, $i), $i);
  }

  // 省略号
  if ($end < $total_page) {
    print('<li class="disabled"><span>···</span></li>');
  }

  // 下一页
  if ($page + 1 <= $total_page) {
    printf('<li><a href="%s">&raquo;</a></li>', sprintf($format, $page + 1));
  }

  
  // $region = floor($visible / 2);// =>2
  // $begin = $page - $region;
  // $end = $begin + $visible;
  // if ($begin < 1) {
  //   $begin = 1;
  //   $end = $begin + $visible;
  //   if ($end > $total_page) {
  //     $end = $total_page + 1;
  //   }
  // }
  // if ($end > $total_page) {
  //   $end = $total_page + 1;
  //   $begin = $end - $visible;
  //   if ($begin < 1) {
  //     $begin = 1;
  //   }
  // }
}
