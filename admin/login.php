<?php 
/**
 * 登录页面
 */
// 载入配置文件
require_once '../functions.php'; 

// include '../config.php';
// 给用户找一个箱子（如果你之前有就用之前的，没有给个新的）
// session_start();

function login(){
  if (empty($_POST['email'])) {
    $GLOBALS['message'] = '请输入邮箱';
    return;
  }
  if (empty($_POST['password'])) {
    $GLOBALS['message'] = '请输入密码';
    return;
  }
  $email = $_POST['email'];
  $password = $_POST['password'];

  // $connection = mysqli_connect(XIU_DB_HOST,XIU_DB_USER,XIU_DB_PASS,XIU_DB_NAME);
  // if (!$connection) {
  //   // 链接数据库失败，打印错误信息，注意：生产环境不能输出具体的错误信息（不安全）
  //   exit('<h1>连接数据库失败</h1>');
  // }

  // // limit 是为了提高查询效率
  // $query = mysqli_query($connection,"select * from users where email='{$email}' limit 1;");
  // if (!$query) {// 用来证明mysql查询语句有没有问题？！
  //   // 查询失败
  //   return false;
  // }
  // $user = mysqli_fetch_assoc($query);
   
  // limit 是为了提高查询效率
  // $user = xiu_fetch_all("select * from users where email = '{$email}' limit 1;")[0]; 
  
  $user = xiu_fetch_one("select * from users where email = '{$email}' limit 1;");
  // var_dump($user);
  // $user为null 用户不存在
  if (!$user) {
    $GLOBALS['message'] = '用户不存在';
    return;
  }
  // 一般密码是加密存储的
  if ($user['password'] !== md5($password)) {
    $GLOBALS['message'] = '邮箱与密码不匹配';
    return;
  }

  // 存一个登录标识
  $_SESSION['current_login_user'] = $user;

  header('Location:/admin');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  login();
}


/**
 * 退出登录
 */
// 退出登录界面
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'login_out') {
  // 销毁会话
  unset($_SESSION['current_login_user']);
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Sign in &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/animate-css/animate.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
</head>
<body>
  <div class="login">
    <!-- novalidate阻止本地校验 -->
    <form class="login-wrap<?php echo isset($message)? ' shake animated' : '' ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" novalidate autocomplete="off">
      <img class="avatar" src="/static/assets/img/default.png">
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong> 用户名或密码错误！
      </div> -->
      <?php if (isset($message)): ?>
       <div class="alert alert-danger">
        <strong>错误！</strong> <?php echo $message; ?>
      </div> 
      <?php endif ?>
      <div class="form-group">
        <label for="email" class="sr-only">邮箱</label>
        <input id="email" name="email" type="email" class="form-control" placeholder="邮箱" autofocus
        value="<?php echo isset($_POST['email'])? $_POST['email'] : '' ?>">
      </div>
      <div class="form-group">
        <label for="password" class="sr-only">密码</label>
        <input id="password" name="password" type="password" class="form-control" placeholder="密码">
      </div>
      <button class="btn btn-primary btn-block">登 录</button>
    </form>
  </div>
  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script>
    $(function($){
      // 1. 单独作用域
      // 2. 确保页面加载过后执行

      // 目标：在用户输入自己的邮箱过后，页面上展示这个邮箱对应的头像
      // 实现：
      // - 时机：邮箱文本框失去焦点，并且能够拿到文本框中填写的邮箱时
      // - 事情：获取这个文本框中填写的邮箱对应的头像地址，展示到上面的 img 元素上
      
      var emailFormat = /^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/;

      $('#email').on('blur', function(event) {
        event.preventDefault();
        var value=$(this).val();
        // 忽略掉文本框为空或者不是一个邮箱
        if (!value || !emailFormat) {return};

        // 用户输入了一个合理的邮箱地址
        // 获取这个邮箱对应的头像地址
        // 因为客户端的 JS 无法直接操作数据库，应该通过 JS 发送 AJAX 请求 告诉服务端的某个接口，
        // 让这个接口帮助客户端获取头像地址
        $.get('/admin/api/avatar.php', {email:value}, function(data) {
          // 希望 data => 这个邮箱对应的头像地址
          if (!data) {return}
          // 展示到上面的 img 元素上
          // $('.avatar').fadeOut().attr('src', data).fadeIn()
          $('.avatar').fadeOut( function() {
            // 等到 淡出完成
            $(this).on('load', function(event) {
              // 图片完全加载成功过后
              event.preventDefault();
              $(this).fadeIn();
            }).attr('src', data);
            // 先注册事件on load来触发fadeIn事件 再设置attr
            // 因为如果先设置attr，图片可能已经加载完成了，触发不了on load 事件load有变化才会执行
            // 那问题来为什么不直接fadeIn事件，图片加载是一条一条加载的，图片不知道什么时候才能加载完
          });
        });
      });
    })


  </script>
</body>
</html>
