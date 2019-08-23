<?php 
require_once '../functions.php';

//判断用户是否登录一定是最先去做
xiu_get_current_user();
var_dump( $_SESSION['current_login_user']);
echo $_SESSION['current_login_user']['id'];

function add_content(){
  if (empty($_POST['slug'])
    || empty($_POST['title'])
    || empty($_POST['created'])
    || empty($_POST['content'])
    || empty($_POST['status'])
    || empty($_POST['category'])) {
    $GLOBALS['error_message'] = '请填写完整的表单';
    $GLOBALS['success'] = false;//用来显示不同样式的标记
    return;
  }

if (empty($_FILES['feature'])) {
  $GLOBALS['error_message'] = '请上传文件';
  return;
}
$feature=$_FILES['feature'];
if ($feature['error']!==UPLOAD_ERR_OK) {
  $GLOBALS['error_message'] = '上传失败';
  return;
}
$allowed_type=array('image/png','image/gif','image/jpeg');
if (!in_array($feature['type'], $allowed_type)) {
  $GLOBALS['error_message'] = '文件格式不对';
  return;
}
if ($feature['size']>10*1024*1024) {
  $GLOBALS['error_message'] = '文件过大';
  return;
}
if ($feature['size']<1*1024*1024) {
  $GLOBALS['error_message'] = '文件过小';
  return;
}
$current=$feature['tmp_name'];
$target='/static/uploads/'.uniqid().'.'.pathinfo($feature['name'],PATHINFO_EXTENSION);
if (!(move_uploaded_file($current, $target))) {
  $GLOBALS['error_message'] = '上传失败';
  return;
}


  $slug = $_POST['slug'];
  $title = $_POST['title'];
  $feature = isset($image_file) ? $image_file : '';
  $created = $_POST['created'];
  $content = $_POST['content'];
  $status = $_POST['status'];

  $user_id = $current_user['id'];
  $category_id = $_POST['category'];

  $name = $_POST['name'];
  $slug = $_POST['slug'];
  $rows = xiu_execute("insert into categories values(null,'{$slug}','{$name}')");
  $GLOBALS['message'] = $rows <= 0 ? '添加失败' : '添加成功';
  $GLOBALS['success'] = $rows > 0;
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  add_content();
}

$categories = xiu_fetch_all("select * from categories");
 ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Add new post &laquo; Admin</title>
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
        <h1>写文章</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <form class="row" action="<?php echo $SERVER['PHP_SERVER'] ?>" method="post" enctype="multipart/form-data">
        <div class="col-md-9">
          <div class="form-group">
            <label for="title">标题</label>
            <input id="title" class="form-control input-lg" name="title" type="text" value="<?php echo isset($_POST['title']) ? $_POST['title'] : ''; ?>" placeholder="文章标题">
          </div>
          <div class="form-group">
            <label for="content">标题</label>
            <textarea id="content" class="form-control input-lg" name="content" cols="30" rows="10" placeholder="内容"><?php echo isset($_POST['content']) ? $_POST['content'] : ''; ?></textarea>
            <!-- <script id="content" type="text/plain">这是初始值</script> -->
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="slug">别名</label>
            <input id="slug" class="form-control" name="slug" type="text" value="<?php echo isset($_POST['slug']) ? $_POST['slug'] : ''; ?>" placeholder="slug">
            <p class="help-block">https://zce.me/post/<strong><?php echo isset($_POST['slug']) ? $_POST['slug'] : 'slug'; ?></strong></p>
          </div>
          <div class="form-group">
            <label for="feature">特色图像</label>
            <!-- show when image chose -->
            <img class="help-block thumbnail" style="display: none">
            <input id="feature" class="form-control" name="feature" type="file" accept="image/*">
          </div>
          <div class="form-group">
            <label for="category">所属分类</label>
            <select id="category" class="form-control" name="category">
              <?php foreach ($categories as $item): ?>
                <option value="<?php echo $item['id'] ?>"<?php echo isset($_POST['category']) && $_POST['category'] == $item['id'] ? ' selected' : '' ?>><?php echo $item['name'] ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="form-group">
            <label for="created">发布时间</label>
            <input id="created" class="form-control" name="created" type="datetime-local" value="<?php echo isset($_POST['created']) ? $_POST['created'] : ''; ?>">
          </div>
          <div class="form-group">
            <label for="status">状态</label>
            <select id="status" class="form-control" name="status">
              <option value="drafted"<?php echo isset($_POST['status']) && $_POST['status'] == 'draft' ? ' selected' : ''; ?>>草稿</option>
              <option value="published"<?php echo isset($_POST['status']) && $_POST['status'] == 'published' ? ' selected' : ''; ?>>已发布</option>
            </select>
          </div>
          <div class="form-group">
            <button class="btn btn-primary" type="submit">保存</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php include 'includes/aside.php' ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/ueditor/ueditor.config.js"></script>
  <script src="/static/assets/vendors/ueditor/ueditor.all.js"></script>
  <script>
    // 文本编辑器的应用
    // UE.getEditor('content',{
    //   initialFrameHeight:400,
    //   autoHeight: false
    // });
  </script>
  <script>NProgress.done()</script>
</body>
</html>
