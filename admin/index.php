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
require_once '../functions.php';

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
  <?php include 'includes/navbar.php' ?>
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
  
  <?php include 'includes/aside.php' ?>

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
