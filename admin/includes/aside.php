<?php 
$_SERVER['PHP_SELF'] = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';

// 因为这个 sidebar.php 是被 index.php 载入执行，所以 这里的相对路径 是相对于 index.php
// 如果希望根治这个问题，可以采用物理路径解决
require_once '../functions.php';

$current_user = xiu_get_current_user();
 ?>

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