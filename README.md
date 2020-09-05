# 阿里百秀

> 一个用 PHP 写的动态网站项目

## 项目结构

```shell
└─baixiu.io               项目文件夹（网站根目录）
    ├─admin               后台文件夹
    │  ├─api              接口文件夹
    │  ├─includes         模块文件夹
    │  └─index.php        后台脚本文件
    ├─static              静态文件夹
    │    ├─assets         资源文件夹
    │    │  ├─css         样式文件夹
    │    │  ├─img         图片文件夹
    │    │  └─vendors     第三方资源
    │    └─uploads        上传文件夹
    │        └─2019       2019年上传文件目录
    ├─index.php           前台脚本文件
    ├─functions.php       封装函数文件
    └─config.php          项目配置文件
```

## 项目预览演示

### 用户登录

- 登录界面可以根据是否填写表单内容拒绝登录操作
- 用户名改变时动态获取头像
- 管理员可以通过用户名和密码登录到后台

### 内容管理 分类管理 评论管理 用户管理

- 管理员可以通过管理后台查看全部内容
- 管理员可以通过管理后台增加内容
- 管理员可以通过管理后台删除内容
- 管理员可以通过管理后台修改内容

## 抽离公共部分

由于每一个页面中都有一部分代码（侧边栏）是相同的，分散到各个文件中，不便于维护，所以应该抽象到一个公共的文件中。

## 服务端渲染 展示全部文章数据列表

### 展示全部文章数据列表

1.1 查询文章数据
1.2 基本的文章数据绑定
名词解释： 数据绑定是指将一个有结构的数据输出到特定结构的 HTML 上。

```php
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
```

### 数据过滤输出

2.1 文章状态友好展示
一般情况下，我们在数据库存储标识都用英文或数字方式存储，但是在界面上应该展示成中文方式，所以我们需要在输出的时候做一次转换，转换方式就是定义一个转换函数：

```php
/**
 * [xiu_convert_status description]
 * @param  string $status 英文状态
 * @return string         中文状态
 */
function xiu_convert_status($status){
  $dict = array('published' => '已发布','drafted' => '草稿','trashed' => '回收站' );
  return isset($dict[$status]) ? $dict[$status] : '未知';
}
```

2.2 日期格式化展示
如果需要自定义发布时间的展示格式，可以通过 date() 函数完成，而 date() 函数所需的参数除了控制输出格式的 format 以外，还需要一个整数类型的 timestamp 。

```php
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
```

2.3 关联数据查询展示
2.4 联合查询，一步到位
按照以上的方式，可以正常输出分类和作者信息，但是过程中需要有大量的数据库连接和查询操作。
在实际开发过程中，一般不这么做，通常我们会使用联合查询的方式，同时把我们需要的信息查询出来：

```sql
select *
from posts
inner join users on posts.user_id = users.id
inner join categories on posts.category_id = categories.id;
```

以上这条语句可以把 posts 、 users 、 categories 三张表同时查询出来（查询到一个结果集中）。

### 分页加载文章数据

3.1 查询一部分数据
当数据过多过后，如果还是按照以上操作每次查询全部数据，页面就显得十分臃肿，加载起来也非常慢，所以必须要通过分页加载的方式改善（每次只显示一部分数据）。
操作方式也非常简单，就是在原有 SQL 语句的基础之上加上 limit 和 order by 子句：

```sql
select
posts.id,
posts.title,
posts.created,
posts.status,
categories.name as category_name,
users.nickname as author_name
from posts
inner join users on posts.user_id = users.id
inner join categories on posts.category_id = categories.id
order by posts.created desc
limit 0, 10
```

> limit 用法：limit [offset, ]rows
> limit 10 -- 只取前 10 条数据
> limit 5, 10 -- 从第 5 条之后，第 6 条开始，向后取 10 条数据

3.2 分页参数计算
limit 子句中的 0 和 10 不是一成不变的，应该跟着页码的变化而变化，具体的规则就是：
第 1 页 limit 0, 10
第 2 页 limit 10, 10
第 3 页 limit 20, 10
第 4 页 limit 30, 10
...
根据以上规则得出公式： offset = (page - 1) \* size

```php
// 处理分页
// ========================================
// 定义每页显示数据量（一般把这一项定义到配置文件中）
$size = 20;
// 获取分页参数 没有或传过来的不是数字的话默认为 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// 查询数据
// ========================================
// 查询全部文章数据
// 数据库联合查询
$offset = ($page - 1) * $size;
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
```

3.3 获取当前页码
一般分页都是通过 URL 传递一个页码参数（通常使用 querystring ）
也就是说，我们应该在页面开始执行的时候获取这个 URL 参数：

```php
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
```

3.4 展示分页跳转链接
用户在使用分页功能时不可能通过地址栏改变要访问的页码，必须通过可视化的链接点击访问，所以我们需要根据数据的情况在界面上显示分页链接。
3.4.1 获取总页数

```php
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
```

知道了总页数，就可以对 URL 中传递过来的分页参数做范围校验了（$page <= $totel_pages）

```php
if ($page > $total_pages) {
  // 超出范围，则跳转到最后一页
  // header('Location:/admin/posts2.php?page='.$total_pages.$search);
  header('Location:?page=' . $total_pages . $search);
}
```

3.4.2 循环输出分页链接

```php
<ul class="pagination pagination‐sm pull‐right">
<?php if ($page ‐ 1 > 0) : ?>
<li><a href="?p=<?php echo $page ‐ 1; ?>">上一页</a></li>
<?php endif; ?>
<?php for ($i = 1; $i <= $total_pages; $i++) : ?>
<li<?php echo $i === $page ? ' class="active"' : '' ?>><a href="?p=<?php echo $i; ?>"><?php
echo $i; ?></a></li>
<?php endfor; ?>
<?php if ($page + 1 <= $total_pages) : ?>
<li><a href="?p=<?php echo $page + 1; ?>">下一页</a></li>
<?php endif; ?>
</ul>
```

3.4.3 控制显示页码个数
按照目前的实现情况，已经可以正常使用分页链接了，但是当总页数过多，显示起来也会有问题，所以需要控制显示页码的个数，一般情况下，我们是根据当前页码在中间，左边和右边各留几位。
实现以上需求的思路：主要就是想办法根据当前页码知道应该从第几页开始显示，到第几页结束，另外需要注意不能超出范围。
以下是具体实现：

```php
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
    printf('<li%s><a href="%s">%d</a></li>', $activeClass, sprintf($format, $i),$i);
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
```

### 数据筛选

4.1 获取提交参数
在查询数据之前，接受参数，组织查询参数：

```php
// 处理筛选逻辑
// ========================================
// 数据库查询筛选条件（默认为 1 = 1，相当于没有条件）
$where = '1 = 1';
// 记录本次请求的查询参数
$search = '';

// 分类筛选
if (isset($_GET['category']) && $_GET['category'] !== 'all') {
  $where .= ' and posts.category_id=' . $_GET['category'];
}

// 状态筛选
if (isset($_GET['status']) && $_GET['status'] !== 'all') {
  $where .= " and posts.status='{$_GET['status']}'";
}
```

4.2 添加查询参数
然后在进行查询时添加 where 子句：

```php
// 查询总条数
$total_count = xiu_fetch_one("select
count(1) as num
from posts
INNER JOIN categories on posts.category_id = categories.id
INNER JOIN users on posts.user_id = users.id
where {$where};")['num'];
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
```

4.3 结合分页
目前来说，单独看筛选功能和分页功能都是没有问题，但是同时使用会有问题：

1. 筛选过后，页数不对（没有遇到，但是常见）。
   原因：查询总条数时没有添加筛选条件
2. 筛选过后，点分页链接访问其他页，筛选状态丢失。
   原因：分类链接的 URL 中只有页码信息，不包括筛选状态
3. 分页链接加入筛选参数
   只要在涉及到分页链接的地方加上当前的筛选参数即可解决问题，所以我们在接收状态筛选参数时将其记录下来：

```php
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
```

### 单条删除，批量删除

5.1 跳转到来源
用户在点击删除链接删除指定内容后，需要手动返回到之前的页面，体验不佳，最好是能在 post-delete.php 执行完成过后，自动跳转到之前访问的页面，也就是来源页面。
那么如何获取来源页面的地址，就是我们接下来的重点：
5.1.1 HTTP Referer
在 HTTP 协议中约定：在请求头中包含一个 Referer 字段，值就是上一次访问的 URL
5.1.2 获取 Referer 并跳转
在 PHP 中可以通过 `$_SERVER['HTTP_REFERER']` 获取到 Referer

```php
// 获取删除后跳转到的目标链接，优先跳转到来源页面，否则跳转到文章列表
$target = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'posts.php';
// 跳转
header('Location: ' . $target);
```

5.2 多选批量删除
post-delete.php 只能删除指定的单个数据，如果需要支持批量删除，可以稍加改造：

1. 约定接收的 id 参数是一个以英文半角逗号分隔的 ID
2. 将删除 SQL 语句的 where 子句改为 where id in (%s)

```php
if (empty($_GET['id'])) {
  exit('缺少必要参数');
}
$id = $_GET['id'];
// => '1 or 1 = 1'
// sql 注入
// $id = (int)$_GET['id'];
$rows = xiu_execute(sprintf('delete from posts where id in (%s)', $_GET['id']));
// $sql => delete from posts where id in (22)
// $sql => delete from posts where id in (22,23,24)
```

## 本地图片预览

在了解了 HTML5 Web API 过后，我们知道 HTML5 提供了两种办法

1. Object URL
2. FileReader

## 表单处理

### 页面调整

我们需要调整页面上细节：表单属性 表单元素属性 等等

1. 为 `<form>` 添加必要属性

   - action="/admin/post-add.php" ：提交给页面自身。也可以在单独创建一个 php 文件，让 action 提交到这个单独的文件。
     **action="<?php echo $_SERVER['PHP_SELF'] ?>"**，提高代码的鲁棒性。
   - method="post" ：1. 提交的数据篇幅比较大；2. 主观意义上也是给服务端发数据。
   - enctype="" ：暂时不提，待会用到，再说。

2. 为 `<input>` 添加必要属性

   - name 属性，表单元素提交必要属性
   - label 关联，每一个表单元素都必须有一个关联的 label ，不管界面上需要还是不需要。

3. 提交按钮 type

   - 表单想要被提交必须要有提交按钮的点击，所以页面上必须有提交按钮

### 业务核心

1. 数据校验
2. 接收数据
3. 保存数据
4. 错误消息提示
5. 跳转

   ```php
   header('Location:/admin/users.php');
   ```

6. 表单元素的状态保持
   目前，如果提交表单时发生任何错误，当浏览器再次显示表单时，之前的数据全部会被清空，为了提高使用过程的 体验，所以需要保持之前的填写状态，在服务端渲染 HTML 时，给每一个表单元素加上 value 属性，值就从表单提交过来的数据中取。

## 图片上传功能

如果希望表单可以上传文件，必须将表单的 enctype 属性设置为 multipart/form-data ，
我们不可能在数据库中保存文件，一般情况下，我们都是将文件保存到一个目录中，在数据库中保存访问路径
URL

1. 修改表单的编码类型
   默认情况下表单的编码类型为： application/x-www-form-urlencoded ，表示当前表单中的数据是以 urlencoded 方式提交的。
   但是对于表单中有文件域的情况下，文件是无法用文本表述的，所以必须通过另外一种编码类型：`multipart/form-data`

2. 修改文件域文件限制
   默认文件域允许选择任何文件，可以通过 accept 属性限制：image/\* 指的是任意类型图片

   ```html
   <input id="avatar" name="avatar" type="file" accept="image/*" />
   ```

3. 接收上传文件内容

   在表单提交到服务端时，会自动把文件上传到服务器上，PHP（内部）执行时会自动将文件存到一个临时目录，然后将相关信息定义到 \$\_FILES 数组中。
   知道了这些，我们接下来要做的就是：

   - 判断是否上传了文件
   - 将上传的文件从临时目录移动到我们希望的目录
   - 将路径保存到数据库中

   ```php
   // 如何接受单个文件域的多文件上传
   // 一个文件域处理多个文件上传逻辑
   // 如果一个文件域是多文件上传的话，文件域的 name 应该是由 [] 结尾
   // var_dump($_FILES);
   if (empty($_FILES['images'])) {
   // 1. 客户端提交的表单中有没有images文件域
   $GLOBALS['error_message'] = '请正确上传文件';
   return;
   }

   // $images是临时变量，不用每次$_FILES['images']['']
   $images=$_FILES['images'];
   // var_dump($images);
   // array(5) {
   //   ["name"]=>
   //   array(2) {
   //     [0]=>
   //     string(11) "icon-04.png"
   //     [1]=>
   //     string(11) "icon-08.png"
   //   }
   //   ["type"]=>
   //   array(2) {
   //     [0]=>
   //     string(9) "image/png"
   //     [1]=>
   //     string(9) "image/png"
   //   }
   //   ["tmp_name"]=>
   //   array(2) {
   //     [0]=>
   //     string(22) "C:\Windows\php662A.tmp"
   //     [1]=>
   //     string(22) "C:\Windows\php662B.tmp"
   //   }
   //   ["error"]=>
   //   array(2) {
   //     [0]=>
   //     int(0)
   //     [1]=>
   //     int(0)
   //   }
   //   ["size"]=>
   //   array(2) {
   //     [0]=>
   //     int(6442)
   //     [1]=>
   //     int(8158)
   //   }
   // }

   $data['images']=array();
   // 2. 遍历某个文件域中的每一个文件（判断是否存在，是否选中，判断大小、类型，移动到网站目   中）
   for ($i=0; $i < count($images['name']) ; $i++) {
       // $images['error']=>[0,0,0]
       // 2.1 判断用户是否选择了文件
       if ($images['error'][$i]!==UPLOAD_ERR_OK) {
           $GLOBALS['error_type'] = 'images';
           $GLOBALS['error_message'] = '上传海报文件失败';
           return;
       }
       // 2.2 类型的校验  方法一：strpos()
       // $images['type']=>['image/png','image/jpeg','image/gif']
       if (strpos($images['type'][$i], 'image/')!==0) {
           $GLOBALS['error_type'] = 'images';
           $GLOBALS['error_message'] = '上传海报文件格式错误';
           return;
       }
       // 2.3 文件大小的判断
       if ($images['size'][$i]>1*1024*1024) {
           $GLOBALS['error_type'] = 'images';
           $GLOBALS['error_message'] = '上传海报文件过大';
           return;
       }
   ```

4. 保存文件路径到数据库

   ```php
   // 2.4移动文件
   $target='./uploads/'.uniqid().'-'.iconv('UTF-8', 'GBK', $images['name'][$   );// 存放路径
   $temp=$images['tmp_name'][$i];// 临时路径
   // move_uploaded_file 在 Windows 中文系统上要求传入的参数如果有中文必须是 GBK 编码
   // 切记在接收文件时注意文件名中文的问题，通过iconv函数转换中文编码为 GBK 编码
   $moved=move_uploaded_file($temp, $target);// 返回移动是否成功
   if (!$moved) {
       $GLOBALS['error_message'] = '上传海报失败2';
       return;
   }
   // json_encode()该函数只对UTF8编码的数据有效
   // 保存数据的路径一定要使用绝对路径
   $data['images'][]=iconv('GBK', 'UTF-8', '/crud'.substr($target, 1));
   }
   // var_dump($data);
   ```

## 客户端渲染 通过 AJAX 方式实现评论管理

### 数据接口

1. 数据接口
   既然是通过 AJAX 获取数据，然后在通过 DOM 操作渲染数据，前提就是要有一个可以获取评论数据 的接口，那么接下来我们就需要开发一个可以返回评论数据的接口。

2. 设计结构的特性
   从使用者的角度考虑每一个所需功能，反推出来对接口的要求，然后具体实现每个要求，这就是所谓的逆向工程。
   对于评论管理页面，我们的需求是：

   1. 可以分页查看评论数据列表（作者、评论、文章标题、评论时间、评论状态）
   2. 可以通过分页导航访问特定分页的数据，
   3. 可以通过操作按钮批准、拒绝、删除每一条评论

   根据需求得知，这个功能开发过程中需要三个的接口（endpoint），我们创建三个 php 文件：

   1. comment.php: 分页加载评论数据
   2. comment-delete.php: 删除评论
   3. comment-status.php: 改变评论状态

comment.php: 分页加载评论数据

```php
<?php
/**
 * 分页返回评论数据接口（JSON）
 */
// 接收客户端的AJAX请求 返回评论数据
// 载入封装的所有函数
require_once '../../functions.php';

$page = empty($_GET['page']) ? 1 : intval($_GET['page']);// 转换成数值

$length = 8;
$skip = ($page - 1) * $length;

$sql = sprintf('select
 comments.*,
 posts.title as post_title
 from comments
 inner join posts on comments.post_id = posts.id
 order by comments.created desc
 limit %d, %d;', $skip, $length);

// 查询所有的评论数据
$comments = xiu_fetch_all($sql);

// 先查询到所有数据的数量
$total_count = xiu_fetch_one('select count(1) as count
 from comments
 inner join posts on comments.post_id = posts.id')['count'];
// 计算总页数
$total_pages = ceil($total_count / $length);

// 因为网络之间传输的只能是字符串
// 所以我们先将数据转换为字符串（序列化）
$json = json_encode(array(
    'total_pages' => $total_pages,
    'comments' => $comments
));

// 设置响应的响应体类型为JSON
// header('Content-Type: application/json');
// 响应给客户端
echo $json;// => {"total_pages":10,"comments":[]}

// 补充，关于父ID联合查询问题
// $sql = sprintf('select
// comments.*,
// parent.content,
// posts.title as post_title
// from comments
// INNER JOIN posts ON comments.post_id = posts.id
// INNER JOIN comments as parent on comments.parent_id = parent.id')// as取别名
```

comment-delete.php: 删除评论

```php
<?php
/**
 * 根据客户端传递过来的ID删除对应数据
 */
require_once '../../functions.php';

if (empty($_GET['id'])) {
    exit('缺少必要参数');
}

$id = $_GET['id'];
// => '1 or 1 = 1'
// sql 注入
// $id = (int)$_GET['id'];
// var_dump($id);

$rows = xiu_execute('delete from comments where id in(' . $id . ');');
header('Content-Type: application/json');
echo json_encode($rows > 0);
```

comment-status.php: 改变评论状态

```php
<?php
/**
 * 修改评论状态
 * POST 方式请求
 * - id 参数在 URL 中
 * - status 参数在 form-data 中
 * 两种参数混着用
 */

require '../../functions.php';

// 设置响应类型为 JSON
header('Content-Type: application/json');

if (empty($_GET['id']) || empty($_POST['status'])) {
  // 缺少必要参数
  exit('缺少必要参数');
}

// 拼接 SQL 并执行
$affected_rows = xiu_execute(sprintf("update comments set status = '%s' where id in (%s)", $_POST['status'], $_GET['id']));

// 输出结果
echo json_encode($affected_rows >= 0);
```

### 模板引擎 jsrender

之前在服务端渲染数据的时候，没有太多这种感觉，而现在到了客户端渲染就十分恶心，根本原因是因为我们的方法过于原始，对于简单的数据渲染还是可以接受的，但是一旦数据复杂了，结构复杂了，过程就十分恶心，而后端 使用的实际上是一种“套模板”过程。

前端也有模板引擎，而且从使用上来说，更多更好更方便。

> 换而言之，模板引擎的本质其实就是各种恶心的字符串操作。

这里我们借助一个非常轻量的模板引擎 jsrender 解决以上问题，模板引擎的使用套路也都类似：

1. 引入模板引擎库文件
2. 准备一个模板
3. 准备一个需要渲染的数据
4. 调用一个模板引擎库提供的方法，把数据通过模板渲染成 HTML

载入模板引擎库：

```js
<script src="/static/assets/vendors/jsrender/jsrender.js" />
```

准备模板：

```php
  <script id="comment_tmpl" type="text/x-jsrender">
  {{for comments}}
    <tr class="{{: status === 'held' ? 'warning' : status === 'rejected' ? 'danger' : '' }}" data-id="{{: id }}">
      <td class="text-center"><input type="checkbox"></td>
      <td>{{: author }}</td>
      <td>{{: content }}</td>
      <td>{{: post_title }}</td>
      <td>{{: created }}</td>
      <td>{{: status === 'held' ? '待审' : status === 'rejected' ? '拒绝' : '准许' }}</td>
      <td class="text-center">
        {{if status === 'held'}}
        <a href="javascript:;" class="btn btn-info btn-xs btn-edit" data-status="approved">批准</a>
        <a href="javascript:;" class="btn btn-warning btn-xs btn-edit" data-status="rejected">拒绝</a>
        {{/if}}
        <a href="javascript:;" class="btn btn-danger btn-xs btn-delete">删除</a>
      </td>
    </tr>
  {{/for}}
  </script>
```

调用模板方法：

```js
// 将数据渲染到页面上
var html = $('#comment_tmpl').render({ comments: data.comments });
$tbody.html(html).fadeIn();
```

### 分页组件 twbs-pagination

分页组件展示
我们之前写的生成分页 HTML 是在服务端渲染分页组件，只能作用在服务端渲染数据的情况下。但是当下的情况我们采用的是客户端渲染的方案，自然就用不了之前的代码了，但是思想是相通的，我们仍然可以按照之前的套路来实现，只不过是在客户端，借助于 JavaScript 实现。

```js
/**
 * 加载指定页数据
 */
function loadPage(page) {
  $tbody.fadeOut();
  $.getJSON('/admin/api/comments.php', { page: page }, function (data) {
    console.log(data);
    if (page > data.total_pages) {
      // 删除完最后一页数据时跳转面到临界页面，即新的最后一页
      loadPage(data.total_pages);
      return;
    }
    // 动态总页数 调用destroy方法，然后使用新选项初始化它
    $('.pagination').twbsPagination('destroy');
    // 分页组件
    $('.pagination').twbsPagination({
      // 渲染分页页面组件
      initiateStartPageClick: false, // 否则 onPageClick 第一次就会触发
      first: '首页',
      last: '未页',
      prev: '上一页',
      next: '下一页',
      startPage: page,
      totalPages: data.total_pages,
      visiblePages: 5,
      onPageClick: function (e, page) {
        // 点击分页页码才会执行这里的代码
        loadPage(page);
        currentPage = page;
      },
    });
    // 将数据渲染到页面上
    var html = $('#comment_tmpl').render({ comments: data.comments });
    $tbody.html(html).fadeIn();
  });
}
loadPage(currentPage);
```

### 删除评论

如果是采用同步的方式，则与文章或分类管理的删除相同，但是此处我们的方案是采用 AJAX 方式。
万变不离其宗，想要删除掉评论，客户端肯定是做不到的，因为数据在服务端。可以通过客户端发送一个请求（信号）到服务端，服务端执行删除操作，服务端业务已经实现，现在的问题就是客户端发请求的问题。

1. 给删除按钮绑定点击事件
   常规思路：

   - 为删除按钮添加一个 btn-delete 的 class
   - 为所有 btn-delete 注册点击事件

   ```js
   $('.btn‐delete').on('click', function () {
     console.log('btn delete clicked');
   });
   ```

   但是经过测试发现，在点击删除按钮后控制台不会输出任何内容，也就是说按钮的点击事件没有执行。

   > 提问：为什么按钮的点击事件不会执行

   问题的答案也非常简单：当执行注册事件代码时，表格中的数据还没有初始化完成，那么通过 `$('.btn-delete')` 就不会选择到后来界面上的删除按钮元素，自然也就没办法注册点击事件了。

   解决办法：

   - 控制注册代码的执行时机；
   - 另外一种事件方式：委托事件；

   ```js
   // 删除评论
   $tbody.on('click', '.btn‐delete', function () {
     console.log('btn delete clicked');
   });
   ```

2. 发送删除评论的异步请求

点击事件执行 -> 发送异步请求 -> 移除当前点击按钮所属行

```js
$tbody.on('click', '.btn‐delete', function () {
  var $tr = $(this).parent().parent();
  var id = parseInt($tr.data('id'));
  $.get('/admin/comment‐delete.php', { id: id }, function (res) {
    res.success && $tr.remove();
  });
});
```

个人认为删除成功过后，不应该单单从界面上的表格中移除当前行，而是重新加载当前页数据。

### nprogress 进度条使用

```html
<style type="text/css">
  #loading {
    align-items: center;
    justify-content: center;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    display: flex;
  }
  @keyframes lds-spinner {
    0% {
      opacity: 1;
    }
    100% {
      opacity: 0;
    }
  }
  @-webkit-keyframes lds-spinner {
    0% {
      opacity: 1;
    }
    100% {
      opacity: 0;
    }
  }
  .lds-spinner {
    position: relative;
  }
  .lds-spinner div {
    left: 94px;
    top: 48px;
    position: absolute;
    -webkit-animation: lds-spinner linear 1s infinite;
    animation: lds-spinner linear 1s infinite;
    background: #51cacc;
    width: 12px;
    height: 24px;
    border-radius: 40%;
    -webkit-transform-origin: 6px 52px;
    transform-origin: 6px 52px;
  }
  .lds-spinner div:nth-child(1) {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
    -webkit-animation-delay: -0.916666666666667s;
    animation-delay: -0.916666666666667s;
  }
  .lds-spinner div:nth-child(2) {
    -webkit-transform: rotate(30deg);
    transform: rotate(30deg);
    -webkit-animation-delay: -0.833333333333333s;
    animation-delay: -0.833333333333333s;
  }
  .lds-spinner div:nth-child(3) {
    -webkit-transform: rotate(60deg);
    transform: rotate(60deg);
    -webkit-animation-delay: -0.75s;
    animation-delay: -0.75s;
  }
  .lds-spinner div:nth-child(4) {
    -webkit-transform: rotate(90deg);
    transform: rotate(90deg);
    -webkit-animation-delay: -0.666666666666667s;
    animation-delay: -0.666666666666667s;
  }
  .lds-spinner div:nth-child(5) {
    -webkit-transform: rotate(120deg);
    transform: rotate(120deg);
    -webkit-animation-delay: -0.583333333333333s;
    animation-delay: -0.583333333333333s;
  }
  .lds-spinner div:nth-child(6) {
    -webkit-transform: rotate(150deg);
    transform: rotate(150deg);
    -webkit-animation-delay: -0.5s;
    animation-delay: -0.5s;
  }
  .lds-spinner div:nth-child(7) {
    -webkit-transform: rotate(180deg);
    transform: rotate(180deg);
    -webkit-animation-delay: -0.416666666666667s;
    animation-delay: -0.416666666666667s;
  }
  .lds-spinner div:nth-child(8) {
    -webkit-transform: rotate(210deg);
    transform: rotate(210deg);
    -webkit-animation-delay: -0.333333333333333s;
    animation-delay: -0.333333333333333s;
  }
  .lds-spinner div:nth-child(9) {
    -webkit-transform: rotate(240deg);
    transform: rotate(240deg);
    -webkit-animation-delay: -0.25s;
    animation-delay: -0.25s;
  }
  .lds-spinner div:nth-child(10) {
    -webkit-transform: rotate(270deg);
    transform: rotate(270deg);
    -webkit-animation-delay: -0.166666666666667s;
    animation-delay: -0.166666666666667s;
  }
  .lds-spinner div:nth-child(11) {
    -webkit-transform: rotate(300deg);
    transform: rotate(300deg);
    -webkit-animation-delay: -0.083333333333333s;
    animation-delay: -0.083333333333333s;
  }
  .lds-spinner div:nth-child(12) {
    -webkit-transform: rotate(330deg);
    transform: rotate(330deg);
    -webkit-animation-delay: 0s;
    animation-delay: 0s;
  }
  .lds-spinner {
    width: 200px !important;
    height: 200px !important;
    -webkit-transform: translate(-100px, -100px) scale(1) translate(100px, 100px);
    transform: translate(-100px, -100px) scale(1) translate(100px, 100px);
  }
</style>
<link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css" />
<script src="/static/assets/vendors/nprogress/nprogress.js"></script>

<body>
  <script>
    NProgress.start();
  </script>

  <div id="loading">
    <div class="lds-css ng-scope">
      <div class="lds-spinner" style="width:100%;height:100%">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>
  </div>
  <script>
    // NProgress
    $(document)
      .ajaxStart(function () {
        NProgress.start();
        $('#loading').fadeIn();
      })
      .ajaxStop(function () {
        NProgress.done();
        $('#loading').fadeOut();
      });
  </script>
  <script>
    NProgress.done();
  </script>
</body>
```

## 补充

1.数据库导入 docs 文件夹中的 sql 文件

2.修改 config.php 文件中数据库用户名，密码，数据库名称

```php
/**
 * 项目配置文件
 */
/*
数据库主机
 */
define('XIU_DB_HOST', 'localhost');
/*
数据库用户名
 */
define('XIU_DB_USER', 'root');
/*
数据库密码
 */
define('XIU_DB_PASS', 'root');
/*
数据库名称
 */
define('XIU_DB_NAME', 'baixiu.io');
```

3.index.php 页面 登录账号密码
123456@qq.com
123456
