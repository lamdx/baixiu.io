<?php 
/**
 * 用户管理
 */

// 载入脚本
// ======================================== 
require_once '../functions.php';

// 访问控制
// ========================================
// 判断用户是否登录一定是最先去做
// 获取登录用户信息
xiu_get_current_user();

// 功能逻辑
// 处理表单提交
// ========================================

function add_user(){
  if (empty($_POST['email'])||empty($_POST['slug'])||empty($_POST['nickname'])||empty($_POST['password'])) {
    $GLOBALS['message'] = '请填写完整的表单';
    $GLOBALS['success'] = false;//用来显示不同样式的标记
    return;
  }
  $slug = $_POST['slug'];
  $email = $_POST['email'];
  $password = md5($_POST['password']);
  $nickname = $_POST['nickname'];
  $avatar = '/static/uploads/avatar.jpg';
  $bio = '';
  $status = "activated";
  $rows = xiu_execute("insert into users values(null,'{$slug}','{$email}','{$password}','{$nickname}','{$avatar}','{$bio}','{$status}')");
  $GLOBALS['message'] = $rows <= 0 ? '添加失败' : '添加成功';
  $GLOBALS['success'] = $rows > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    add_user();
}

// 查询数据
// ========================================

// 查询全部用户信息
$users = xiu_fetch_all('select * from users;');
 ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Users &laquo; Admin</title>
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
      <div class="page-title">
        <h1>用户</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <?php if (isset($message)): ?>
        <?php if ($success): ?>
          <div class="alert alert-success">
          <strong>成功！</strong><?php echo $message ?>
          </div>
          <?php else: ?>
          <div class="alert alert-danger">
          <strong>失败！</strong><?php echo $message ?>
          </div>
        <?php endif ?>
      <?php endif ?>
      <div class="row">
        <div class="col-md-4">
          <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <h2>添加新用户</h2>
            <div class="form-group">
              <label for="email">邮箱</label>
              <input id="email" class="form-control" name="email" type="email" placeholder="邮箱">
            </div>
            <div class="form-group">
              <label for="slug">别名</label>
              <input id="slug" class="form-control" name="slug" type="text" placeholder="slug">
              <p class="help-block">https://zce.me/author/<strong>slug</strong></p>
            </div>
            <div class="form-group">
              <label for="nickname">昵称</label>
              <input id="nickname" class="form-control" name="nickname" type="text" placeholder="昵称">
            </div>
            <div class="form-group">
              <label for="password">密码</label>
              <input id="password" class="form-control" name="password" type="text" placeholder="密码">
            </div>
            <div class="form-group">
              <button class="btn btn-primary" type="submit">添加</button>
              <button class="btn btn-default btn-cancel" type="button" style="display: none;">取消</button>
            </div>
          </form>
        </div>
        <div class="col-md-8">
          <div class="page-action">
            <!-- show when multiple checked -->
            <a class="btn btn-danger btn-sm btn-delete" href="/admin/api/users-delete.php" style="display: none">批量删除</a>
          </div>
          <table class="table table-striped table-bordered table-hover">
            <thead>
               <tr>
                <th class="text-center" width="40"><input type="checkbox"></th>
                <th class="text-center" width="80">头像</th>
                <th>邮箱</th>
                <th>别名</th>
                <th>昵称</th>
                <th>状态</th>
                <th class="text-center" width="100">操作</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $item): ?>
              <tr>
                <td class="text-center"><input type="checkbox" data-id="<?php echo $item['id']; ?>"></td>
                <td class="text-center"><img class="avatar" src="<?php echo $item['avatar'] ?>"></td>
                <td><?php echo $item['email'] ?></td>
                <td><?php echo $item['slug'] ?></td>
                <td><?php echo $item['nickname'] ?></td>
                <td><?php echo $item['status'] ?></td>
                <td class="text-center">
                  <a href="/admin/users-edit.php?id=<?php echo $item['id']; ?>" class="btn btn-default btn-xs">编辑</a>
                  <a href="/admin/api/users-delete.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-xs">删除</a>
                </td>
              </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/aside.php' ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>
    $(function($){
      // 不要重复使用无意义的选择操作，应该采用变量去本地化
      var $tbodyCheckboxs = $('tbody input');
      var $btnDelete = $('.btn-delete');
      
      // 用于记录界面上选中行的数据 ID
      var allCheckeds = [];

      /**
       * 表格中的复选框选中发生改变时控制删除按钮的链接参数和显示状态
       */      
      $tbodyCheckboxs.on('change', function(event) {
        // console.log('ok');
        event.preventDefault();
        var $this = $(this)
        // 为了可以在这里获取到当前行对应的数据 ID
        // 在服务端渲染 HTML 时，给每一个 tr 添加 data-id 属性，记录数据 ID
        // 这里通过 data-id 属性获取到对应的数据 ID
        //         
        // js访问自定义属性
        // console.log(this.dataset['id']);
        // jquery访问自定义属性用方法data
        // console.log($(this).data('id'));
        // 根据有没有选中当前这个 checkbox 决定是添加还是移除
        var id = $(this).data('id');

        if ($this.prop('checked')) {
          // 选中就追加到数组中
          // allCheckeds.indexOf(id) !== -1 || allCheckeds.push(id);
          // includes() 方法用来判断一个数组是否包含一个指定的值，如果是 返回 true，否则false。
          allCheckeds.includes(id)||allCheckeds.push(id);
        }else{
          // 未选中就从数组中移除
          allCheckeds.splice(allCheckeds.indexOf(id), 1);
        }
        // 根据剩下多少选中的 checkbox 决定是否显示删除按钮
        allCheckeds.length ? $btnDelete.fadeIn() : $btnDelete.fadeOut();
        // $btnDelete.attr('href', '/admin/category-delete.php?id='+allCheckeds);
        // $btnDelete.prop('search', '?id='+allCheckeds);这个方法更加有效
        // 批量删除按钮链接参数
        // search 是 DOM 标准属性，用于设置或获取到的是 a 链接的查询字符串
        $btnDelete.prop('search', '?id='+allCheckeds);
        console.log(allCheckeds);
      });

      /**
       * 全选 / 全不选
       */
      $('thead input').on('change', function(event) {
        event.preventDefault();
        var checked = $(this).prop('checked');
        // trigger() 方法触发被选元素的指定事件类型。
        $tbodyCheckboxs.prop('checked', checked).trigger('change');
        });

      /**
        * slug 预览
        */
       $('#slug').on('input', function () {
         $(this).next().children().text($(this).val())
       })

      // attr 和 prop 区别：
      // - attr 访问的是 元素属性
      // - prop 访问的是 元素对应的DOM对象的属性
    })
  </script>
  <script>NProgress.done()</script>
</body>
</html>
