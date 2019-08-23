<?php 
/**
 * 分类管理
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

function add_category(){
  if (empty($_POST['name'])||empty($_POST['slug'])) {
    $GLOBALS['message'] = '请填写完整的表单';
    $GLOBALS['success'] = false;//用来显示不同样式的标记
    return;
  }
  $name = $_POST['name'];
  $slug = $_POST['slug'];
  $rows = xiu_execute("insert into categories values(null,'{$slug}','{$name}')");
  $GLOBALS['message'] = $rows <= 0 ? '添加失败' : '添加成功';
  $GLOBALS['success'] = $rows > 0;
}

function edit_category(){
  $id = isset($_POST['id']) ? $_POST['id'] : '';
  if (empty($_POST['name'])||empty($_POST['slug'])) {
    $GLOBALS['message'] = '请填写完整的表单';
    $GLOBALS['success'] = false;//用来显示不同样式的标记
    return;
  }
  $name = $_POST['name'];
  $slug = $_POST['slug'];
  $rows = xiu_execute("update categories set slug = '{$slug}', name = '{$name}' where id = '{$id}';");
  $GLOBALS['message'] = $rows < 0 ? '更新失败' : '更新成功';
  $GLOBALS['success'] = $rows >= 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($_POST['id'])) {
    // 没有提交 ID 代表新增，则新增数据
    add_category();
  }else{
    // 提交 ID 就代表是更新，则更新数据
    edit_category();
  }
}

// 查询数据
// ========================================

// 查询全部分类信息
$categories = xiu_fetch_all('select * from categories;');
var_dump ($categories);
 ?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Categories &laquo; Admin</title>
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
        <h1>分类目录</h1>
      </div>
      <!-- 重点就是在输出时知道到底是成功还是失败，找规律，或者定义标识变量都可以 -->
      <!-- 有错误信息时展示 -->
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
            <h2>添加新分类目录</h2>
            <input id="id" name="id" type="hidden">
            <div class="form-group">
              <label for="name">名称</label>
              <input id="name" class="form-control" name="name" type="text" placeholder="分类名称">
            </div>
            <div class="form-group">
              <label for="slug">别名</label>
              <input id="slug" class="form-control" name="slug" type="text" placeholder="slug">
              <p class="help-block">https://zce.me/category/<strong>slug</strong></p>
            </div>
            <div class="form-group">
              <button class="btn btn-primary btn-save" type="submit">添加</button>
              <button class="btn btn-default btn-cancel" type="button" style="display: none;">取消</button>
            </div>
          </form>
        </div>
        <div class="col-md-8">
          <div class="page-action">
            <!-- show when multiple checked -->
            <a  class="btn btn-danger btn-sm btn-delete" href="/admin/api/category-delete.php" style="display: none">批量删除</a>
          </div>
          <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th class="text-center" width="40"><input type="checkbox"></th>
                <th>名称</th>
                <th>Slug</th>
                <th class="text-center" width="100">操作</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $item): ?>
              <tr>
                <!-- html5中，自定义属性 统一用data当前缀 -->
                <td class="text-center"><input type="checkbox" data-id="<?php echo $item['id']; ?>"></td>
                <td><?php echo $item['name'] ?></td>
                <td><?php echo $item['slug'] ?></td>
                <td class="text-center">
                  <a href="javascript:;" class="btn btn-info btn-xs btn-edit">编辑</a>
                  <a href="/admin/api/category-delete.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-xs">删除</a>
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

      /**
       * 编辑分类
       */
      $('.btn-edit').on('click', function () {
        // 变量本地化（效率）
        var $tr = $(this).parent().parent()
        var $tds = $tr.children()

        // 拿到当前行数据
        var id = $tds.eq(0).children().data('id');// 先在表单加个隐藏域存储id，这是用来定位匹配的
        console.log(id);
        var name = $tds.eq(1).text()
        var slug = $tds.eq(2).text()

        // 将数据放到表单中
        $('#id').val(id)
        $('#name').val(name)
        $('#slug').val(slug).trigger('input')

        // 界面显示变化
        $('form > h2').text('编辑《'+name+'》')
        $('form > div > .btn-save').text('保存')
        $('form > div > .btn-cancel').show()
      })

      /**
       * 取消编辑
       */
      $('.btn-cancel').on('click', function () {
        // 清空表单元素上的数据
        $('#id').val('')
        $('#name').val('')
        $('#slug').val('').trigger('input')

        // 界面显示变化
        $('form > h2').text('添加新分类目录')
        $('form > div > .btn-save').text('添加')
        $('form > div > .btn-cancel').hide()
      })
    })    

      // attr 和 prop 区别：
      // - attr 访问的是 元素属性
      // - prop 访问的是 元素对应的DOM对象的属性
  </script>

  <script>NProgress.done()</script>
</body>
</html>
