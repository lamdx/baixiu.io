<?php
/**
 * 后台首页
 */

// 很多页面都要登录后才能访问，所以在functions.php封装一个函数 xiu_get_current_user();
// session_start();
// if (empty($_SESSION['current_login_user'])) {
//   header('Location:/admin/login.php');
// }
 
// 载入脚本
// ========================================
require_once 'functions.php';

// 访问控制
// ========================================
//判断用户是否登录一定是最先去做
xiu_get_current_user();

// 查询数据
// ========================================
//获取界面所需要的数据
$posts_count=xiu_fetch_one('select count(1) as num from posts;')['num'];
$categories_count=xiu_fetch_one('select count(1) as num from categories;')['num'];
$comments_count=xiu_fetch_one('select count(1) as num from comments;')['num'];
$posts_count_drafted=xiu_fetch_one("select count(1) as num from posts where status='drafted';")['num'];
$comments_count_held=xiu_fetch_one("select count(1) as num from comments where status='held';")['num'];

$current_user = xiu_get_current_user();
$_SERVER['PHP_SELF'] = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
 ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Dashboard &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
<nav class="navbar">
  <button class="btn btn-default navbar-btn fa fa-bars"></button>
  <ul class="nav navbar-nav navbar-right">
    <li><a href="/admin/profile.php"><i class="fa fa-user"></i>个人中心</a></li>
    <li><a href="/admin/login.php?action=login_out"><i class="fa fa-sign-out"></i>退出</a></li>
  </ul>
</nav>    

    <div class="container-fluid">
      <div class="jumbotron text-center">
        <h1>One Belt, One Road</h1>
        <p>Thoughts, stories and ideas.</p>
        <p><a class="btn btn-primary btn-lg" href="/admin/post-add.php" role="button">写文章</a></p>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">站点内容统计：</h3>
            </div>
            <ul class="list-group">
              <li class="list-group-item"><strong><?php echo $posts_count ?></strong>篇文章（<strong><?php echo $posts_count_drafted; ?></strong>篇草稿）</li>
              <li class="list-group-item"><strong><?php echo $categories_count ?></strong>个分类</li>
              <li class="list-group-item"><strong><?php echo $comments_count ?></strong>条评论（<strong><?php echo $comments_count_held; ?></strong>条待审核）</li>
            </ul>
          </div>
        </div>
        <div class="col-md-4"><canvas id="chart"></canvas></div> 
        <div class="col-md-4"></div>
      </div>
    </div>
  </div>
  
<div class="aside">
  <div class="profile">
    <img class="avatar" src="<?php echo $current_user['avatar'] ?>">
    <h3 class="name"><?php echo $current_user['nickname'] ?></h3>
  </div>
  <ul class="nav">
    <li class="<?php echo $_SERVER['PHP_SELF'] === '/admin/index.php' ? 'active' : ''; ?>">
      <a href="/admin/index.php"><i class="fa fa-dashboard"></i>仪表盘</a>
    </li>
    <!-- 二级导航菜单的高亮  要观察class样式变化的特点-->
     <?php $menuPosts = array('/admin/posts.php', '/admin/post-add.php', '/admin/categories.php')  ?>
    <li>
      <a href="#menu-posts"<?php echo in_array($_SERVER['PHP_SELF'], $menuPosts) ? '' : 
      'class="collapsed"' ?> data-toggle="collapse">
        <i class="fa fa-thumb-tack"></i>文章<i class="fa fa-angle-right"></i>
      </a>
      <ul id="menu-posts" class="collapse<?php echo in_array($_SERVER['PHP_SELF'], $menuPosts) ? ' in' : '' ?>">
        <li class="<?php echo $_SERVER['PHP_SELF'] === '/admin/posts.php' ? 'active' : ''; ?>">
          <a href="/admin/posts.php">所有文章</a></li>
        <li class="<?php echo $_SERVER['PHP_SELF'] === '/admin/post-add.php' ? 'active' : ''; ?>">
          <a href="/admin/post-add.php">写文章</a></li>
        <li class="<?php echo $_SERVER['PHP_SELF'] === '/admin/categories.php' ? 'active' : ''; ?>">
          <a href="/admin/categories.php">分类目录</a></li>
      </ul>
    </li>
    <li class="<?php echo $_SERVER['PHP_SELF'] === '/admin/comments.php' ? 'active' : ''; ?>">
      <a href="/admin/comments.php"><i class="fa fa-comments"></i>评论</a>
    </li>
    <li class="<?php echo $_SERVER['PHP_SELF'] === '/admin/users.php' ? 'active' : ''; ?>">
      <a href="/admin/users.php"><i class="fa fa-users"></i>用户</a>
    </li>
    <?php $menuSettings=array('/admin/nav-menus.php', '/admin/slides.php', '/admin/settings.php') ?>
    <li>
      <a href="#menu-settings"<?php echo in_array($_SERVER['PHP_SELF'], $menuSettings) ? '': 
      'class="collapsed"' ?> data-toggle="collapse">
        <i class="fa fa-cogs"></i>设置<i class="fa fa-angle-right"></i>
      </a>
      <ul id="menu-settings" class="collapse<?php echo in_array($_SERVER['PHP_SELF'],$menuSettings)?' in':'' ?>">
        <li class="<?php echo $_SERVER['PHP_SELF'] === '/admin/nav-menus.php' ? 'active' : ''; ?>">
          <a href="/admin/nav-menus.php">导航菜单</a></li>
        <li class="<?php echo $_SERVER['PHP_SELF'] === '/admin/slides.php' ? 'active' : ''; ?>">
          <a href="/admin/slides.php">图片轮播</a></li>
        <li class="<?php echo $_SERVER['PHP_SELF'] === '/admin/settings.php' ? 'active' : ''; ?>">
          <a href="/admin/settings.php">网站设置</a></li>
      </ul>
    </li>
  </ul>
</div>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/chart/Chart.js"></script>
  <script>
      //界面显示图表的应用案例 1. <canvas id="chart"></canvas>   2. 引用chart.js
      var config = {
      type: 'pie',
      data: {
        datasets: [{
          data: [
            <?php echo $posts_count ?>,
            <?php echo $categories_count ?>,
            <?php echo $comments_count ?>,
          ],
          backgroundColor: [
            'red',
            'orange',
            'yellow',
          ],
          label: 'Dataset 1'
        }],
        labels: [
          '文章',
          '分类',
          '评论',
        ]
      },
      options: {
        responsive: true
      }
    };

    window.onload = function() {
      var ctx = document.getElementById('chart').getContext('2d');
      window.myPie = new Chart(ctx, config);
    };
  </script>
  <script>NProgress.done()</script>
</body>
</html>
