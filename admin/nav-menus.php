<?php 
/**
 * 导航菜单
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


function add_nav(){
  $navs = xiu_fetch_one("select `value` from options where `key` = 'nav_menus' limit 1;");
  var_dump($navs['value']);
  echo('<br/>');
  $data = array();
  // if (empty($_POST['text'])||empty($_POST['title'])||empty($_POST['href'])) {
  //   $GLOBALS['message'] = '请填写完整的表单';
  //   $GLOBALS['success'] = false;//用来显示不同样式的标记
  //   return;
  // }
  $data['icon'] = 'fa fa-glass';
  // $data['title'] = $_POST['title'];
  // $data['href'] = $_POST['href'];
  // $data['text'] = $_POST['text'];
  $data['title'] = urlencode('信息工程系');
  $data['href'] =  urlencode('机械工程系');
  $data['text'] = urlencode('信息');

  $old=json_decode($navs['value'],true);
  var_dump($old);
  echo('<br/>');
  array_push($old, $data);
  var_dump($old);
  echo('<br/>');
  $new_json = urldecode(json_encode($old));
  var_dump($new_json);
  // PHP数组转字符串？

  // $rows = xiu_execute("update options set `value` = '{$old}' where `key` = 'nav_menus';");

  // $GLOBALS['message'] = $rows <= 0 ? '添加失败' : '添加成功';
  // $GLOBALS['success'] = $rows > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    add_nav();
}

 ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Navigation menus &laquo; Admin</title>
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
        <h1>导航菜单</h1>
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
            <h2>添加新导航链接</h2>
            <div class="form-group">
              <label for="text">文本</label>
              <input id="text" class="form-control" name="text" type="text" placeholder="文本">
            </div>
            <div class="form-group">
              <label for="title">标题</label>
              <input id="title" class="form-control" name="title" type="text" placeholder="标题">
            </div>
            <div class="form-group">
              <label for="href">链接</label>
              <input id="href" class="form-control" name="href" type="text" placeholder="链接">
            </div>
            <div class="form-group">
              <button class="btn btn-primary" type="submit">添加</button>
            </div>
          </form>
        </div>
        <div class="col-md-8">
          <div class="page-action">
            <!-- show when multiple checked -->
            <a class="btn btn-danger btn-sm btn-delete" href="javascript:;" style="display: none">批量删除</a>
          </div>
          <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th class="text-center" width="40"><input type="checkbox"></th>
                <th>文本</th>
                <th>标题</th>
                <th>链接</th>
                <th class="text-center" width="100">操作</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/aside.php' ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/jsrender/jsrender.js"></script>
  <script src="/static/assets/vendors/twbs-pagination/jquery.twbsPagination.js"></script>
  <script id="nav_tmpl" type="text/x-jsrender">
  {{for navs}}
    <tr>
      <td class="text-center"><input type="checkbox" data-id="{{: #index }}"></td>
      <td><i class="{{: icon }}"></i>{{:text}}</td>
      <td>{{: title }}</td>
      <td>{{: link }}</td>
      <td class="text-center">
        <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>
      </td>
    </tr>
  {{/for}}
  </script>
  <script>
    $(function($){
      var $tbody = $('tbody')
      var $tmpl = $('#nav_tmpl')

      $.getJSON('/admin/api/nav-menus.php', {key: 'nav_menus'}, function(data) {
        console.log(data);
        // var data = JSON.parse(data)
        var html = $('#nav_tmpl').render({navs:data});
        $tbody.html(html).fadeIn();
      }); 

      // 不要重复使用无意义的选择操作，应该采用变量去本地化
      var $btnDelete = $('.btn-delete');
    
      // 用于记录界面上选中行的数据 ID
      var allCheckeds = [];

      /**
       * 表格中的复选框选中发生改变时控制删除按钮的链接参数和显示状态
       */      
      $('tbody').on('change', 'input', function(event) {
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
          // includes() 方法用来判断一个数组是否包含一个指定的值，如果是 返回 true，否false。
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
        var checked = $(this).prop('checked');
        console.log(checked);
        // trigger() 方法触发被选元素的指定事件类型。
        $('tbody input').prop('checked', checked).trigger('change');
      });
    })

  </script>

  <script>NProgress.done()</script>
</body>
</html>
