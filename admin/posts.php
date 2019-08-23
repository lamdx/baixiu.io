<?php 
/**
 * 文章管理
 */
// 载入脚本
// ========================================
require_once '../functions.php';

// 访问控制
// ========================================

// 获取登录用户信息
//判断用户是否登录一定是最先去做
xiu_get_current_user();

// 处理筛选逻辑
// ========================================

// 数据库查询筛选条件（默认为 1 = 1，相当于没有条件）
$where = '1 = 1';
// 记录本次请求的查询参数
$search = '';

// 分类筛选
if (isset($_GET['category']) && $_GET['category'] !== 'all') {
  $where .= ' and posts.category_id=' . $_GET['category'];
  $search .= '&category=' . $_GET['category'];
}

// 状态筛选
if (isset($_GET['status']) && $_GET['status'] !== 'all') {
  $where .= " and posts.status='{$_GET['status']}'";
  $search .= '&status=' . $_GET['status'];
}

// 处理分页
// ========================================

// 定义每页显示数据量（一般把这一项定义到配置文件中）
$size = 20;

// 获取分页参数 没有或传过来的不是数字的话默认为 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
  // 页码小于 1 没有任何意义，则跳转到第一页
  // header('Location:/admin/posts2.php?page=1'.$search);
  header('Location:?page=1' . $search);
}

// 查询总条数
$total_count = xiu_fetch_one("select 
count(1) as num
from posts
INNER JOIN categories on posts.category_id = categories.id
INNER JOIN users on posts.user_id = users.id
where {$where};")['num'];

// 计算总页数
$total_pages = (int)ceil($total_count/$size);
// =>51
 
if ($page > $total_pages) {
  // 超出范围，则跳转到最后一页
  // header('Location:/admin/posts2.php?page='.$total_pages.$search);
  header('Location:?page=' . $total_pages . $search);
}

$offset = ($page - 1) * $size;
// 查询数据
// ========================================

// 查询文章数据
// 数据库联合查询
$posts = xiu_fetch_all("select 
posts.id,
posts.title,
posts.created,
posts.`status`,
categories.`name` as category_name,
users.nickname as user_name
from posts
INNER JOIN categories on posts.category_id = categories.id
INNER JOIN users on posts.user_id = users.id
where {$where}
order by posts.created desc
limit {$offset}, {$size};");

// 查询全部分类数据
$category = xiu_fetch_all("select * from categories;");

// 数据过滤函数
// ========================================
/**
 * [xiu_convert_status description]
 * @param  string $status 英文状态
 * @return string         中文状态
 */
function xiu_convert_status($status){
  $dict = array('published' => '已发布','drafted' => '草稿','trashed' => '回收站' );
  return isset($dict[$status]) ? $dict[$status] : '未知';
}

/**
 * [xiu_convert_date description]
 * @param  string $created 时间字符串
 * @return string          格式化后的时间字符串
 */
function xiu_convert_date($created){
  // 设置默认时区！！！ PRC 指的是中华人民共和国
  date_default_timezone_set('PRC');
  // 转换为时间戳
  $timestamp = strtotime($created);
  // 格式化并返回 由于 r 是特殊字符，所以需要 \r 转义一下
  return date('Y年m月d日<b\r>H:i:s', $timestamp);
}
 ?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Posts &laquo; Admin</title>
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
        <h1>所有文章</h1>
        <a href="/admin/post-add.php" class="btn btn-primary btn-xs">写文章</a>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <a class="btn btn-danger btn-sm btn-delete" href="/admin/api/posts-delete.php" style="display: none">批量删除
        </a>
        <form class="form-inline" action="<?php echo $_SERVER['PHP_SELF'] ?>">
          <select name="category" class="form-control input-sm">
            <option value="all">所有分类</option>
            <?php foreach ($category as $item): ?>
            <option value="<?php echo $item['id']; ?>"<?php echo isset($_GET['category']) && $_GET[
              'category'] === $item['id'] ? ' selected' : '' ?>><?php echo $item['name'] ?></option> 
            <?php endforeach ?>
          </select>
          <select name="status" class="form-control input-sm">
            <option value="all">所有状态</option>  
            <option value="drafted"<?php echo isset($_GET['status']) && $_GET['status'] === 'drafted' ? ' selected' : '' ?>>草稿</option>
            <option value="published"<?php echo isset($_GET['status']) && $_GET['status'] === 'published' ? ' selected' : '' ?>>已发布</option>
            <option value="trashed"<?php echo isset($_GET['status']) && $_GET['status'] === 'trashed' ? ' selected' : '' ?>>回收站</option>
          </select>
          <button class="btn btn-default btn-sm">筛选</button>
        </form>

        <ul class="pagination pagination-sm pull-right">
          <?php xiu_pagination($page, $total_pages, '?page=%d' . $search); ?>
        </ul>

      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th>标题</th>
            <th>作者</th>
            <th>分类</th>
            <th class="text-center">发表时间</th>
            <th class="text-center">状态</th>
            <th class="text-center" width="100">操作</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($posts as $item): ?>
          <tr>
            <td class="text-center"><input type="checkbox" data-id="<?php echo $item['id'] ?>"></td>
            <td><?php echo $item['title'] ?></td>
            <!-- <td><?php //echo xiu_get_user($item['user_id']) ?></td>
            <td><?php //echo xiu_get_category($item['category_id']) ?></td> -->
            <td><?php echo $item['user_name']; ?></td>
            <td><?php echo $item['category_name']; ?></td>
            <td class="text-center"><?php echo xiu_convert_date($item['created']) ?></td>
            <td class="text-center"><?php echo xiu_convert_status($item['status']) ?></td>
            <td class="text-center">
              <a href="javascript:;" class="btn btn-default btn-xs">编辑</a>
              <a href="/admin/api/posts-delete.php?id=<?php echo $item['id'] ?>" class="btn btn-danger btn-xs">删除</a>
            </td>
          </tr>
        <?php endforeach ?>
        </tbody>
      </table>
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
      // 定义一个数组记录被选中的
      var allCheckeds = [];
      $tbodyCheckboxs.on('change', function(event) {
        event.preventDefault();
        // js访问自定义属性
        // console.log(this.dataset['id']);
        // jquery访问自定义属性用方法data
        // console.log($(this).data('id'));
        // 根据有没有选中当前这个 checkbox 决定是添加还是移除
        var id = $(this).data('id');
        if ($(this).prop('checked')) {
          // allCheckeds.indexOf(id) !== -1 || allCheckeds.push(id);
          // includes() 方法用来判断一个数组是否包含一个指定的值，如果是返回 true，否则false。
          allCheckeds.includes(id) || allCheckeds.push(id);
        }else{
          allCheckeds.splice(allCheckeds.indexOf(id), 1);
        }
        // 根据剩下多少选中的 checkbox 决定是否显示删除
        allCheckeds.length ? $btnDelete.fadeIn() : $btnDelete.fadeOut();
        // $btnDelete.attr('href', '/admin/category-delete.php?id='+allCheckeds);
        // $btnDelete.prop('search','?id='+allCheckeds);这个方法更加有效
        $btnDelete.prop('search','?id=' + allCheckeds);
      });

      // 全选或全不选
      $('thead input').on('change', function(event) {
        event.preventDefault();
        var checked = $(this).prop('checked');
        // trigger() 方法触发被选元素的指定事件类型。
        $tbodyCheckboxs.prop('checked', checked).trigger('change');
      });
    })

      // attr 和 prop 区别：
      // - attr 访问的是 元素属性
      // - prop 访问的是 元素对应的DOM对象的属性
  </script>
  <script>NProgress.done()</script>
</body>
</html>
