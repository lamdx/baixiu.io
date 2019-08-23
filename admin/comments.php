<?php 
/**
 * 评论管理
 */

// 载入脚本
// ========================================
require_once '../functions.php';

// 访问控制
// ========================================
//判断用户是否登录一定是最先去做
xiu_get_current_user();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Comments &laquo; Admin</title>
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
      background: #51CACC;
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
        <h1>所有评论</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <div class="btn-batch" style="display: none">
          <button class="btn btn-info btn-sm">批量批准</button>
          <button class="btn btn-warning btn-sm">批量拒绝</button>
          <button class="btn btn-danger btn-sm">批量删除</button>
        </div>
        <ul class="pagination pagination-sm pull-right"></ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th>作者</th>
            <th width="500px">评论</th>
            <th>评论在</th>
            <th>提交于</th>
            <th>状态</th>
            <th class="text-center" width="150">操作</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <?php include 'includes/aside.php' ?>

<div id="loading">
<div class="lds-css ng-scope">
<div class="lds-spinner" style="width:100%;height:100%"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
</div>
</div>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/jsrender/jsrender.js"></script>
  <script src="/static/assets/vendors/twbs-pagination/jquery.twbsPagination.js"></script>
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
  <script>
    // NProgress
    $(document)
    .ajaxStart(function() {
      NProgress.start();
      $('#loading').fadeIn();
    })
    .ajaxStop(function() {
      NProgress.done();
      $('#loading').fadeOut();
    });


    var $tbody = $('tbody')
    var $tmpl = $('#comment_tmpl')
    var $pagination = $('.pagination')
    var $btnBatch = $('.btn-batch')
    var $thInput =$('th input')

    // 选中项集合
    var allCheckeds = []
    // 当前页码
    var currentPage = 1

    /**
      * 加载指定页数据
      */
    function loadPage(page){
      $tbody.fadeOut()
      $.getJSON('/admin/api/comments.php', {page: page}, function(data) {
        console.log(data);
        if (page > data.total_pages) {
          // 删除完最后一页数据时跳转面到临界页面，即新的最后一页
          loadPage(data.total_pages);
          return 
        }

        // 动态总页数 调用destroy方法，然后使用新选项初始化它
        $('.pagination').twbsPagination('destroy')
        // 分页组件
        $('.pagination').twbsPagination({
          // 渲染分页页面组件
          initiateStartPageClick: false, // 否则 onPageClick 第一次就会触发
          first: '首页',
          last:  '未页',
          prev: '上一页',
          next: '下一页',
          startPage: page,
          totalPages: data.total_pages,
          visiblePages: 5,
          onPageClick:function(e,page){
            // 点击分页页码才会执行这里的代码
            loadPage(page);
            currentPage = page;
          }
        })
        // 将数据渲染到页面上
        var html = $('#comment_tmpl').render({comments: data.comments});
        $tbody.html(html).fadeIn();   
      });      
    }
    loadPage(currentPage);
	 
    // 删除功能
    // 由于删除按钮是动态添加的，且执行动态添加按钮的代码是在渲染数据之后执行的，过早注册不上
    // $('.btn-delete').on('click', function(event) {
    //   event.preventDefault();
    //   console.log('ok');
    // });   
    $tbody.on('click', '.btn-delete', function() {
       // 删除单条数据的时机
       // 1. 拿到需要删除数据的ID
       var $tr = $(this).parent().parent()
       var id = $tr.data('id')
       // console.log(id);
       // 2. 发送AJAX请求告诉服务端要删除的哪一条具体数据
       $.get('/admin/api/comments-delete.php', {id: id}, function(data) {
       	 // console.log(data);
         if (!data) {return};
          // 3. 根据服务端返回的删除响应是否成功决定是否在界面上移除元素
          // $tr.remove();
          // 删除成功
         loadPage(currentPage);
       });
    });

    // 修改评论状态
    $tbody.on('click', '.btn-edit', function () {
      var id = parseInt($(this).parent().parent().data('id'))
      var status = $(this).data('status')
      $.post('/admin/api/comments-status.php?id=' + id, { status: status }, function (data) {
       	if (!data) {return};
        loadPage(currentPage);
      })
    })

    /**
      * 表格中的复选框选中发生改变时控制删除按钮的链接参数和显示状态
      */ 
    $tbody.on('change', 'td input', function(event) {
      event.preventDefault();
      var id = parseInt($(this).parent().parent().data('id'))
      if ($(this).prop('checked')) {
        // allCheckeds.indexOf(id) !== -1 || allCheckeds.push(id);
        // includes() 方法用来判断一个数组是否包含一个指定的值，如果是返回 true，否则false。
        allCheckeds.includes(id) || allCheckeds.push(id);
      }else{
        allCheckeds.splice(allCheckeds.indexOf(id), 1);
      }
      console.log(allCheckeds);
      // 根据剩下多少选中的 checkbox 决定是否显示删除
      allCheckeds.length ? $btnBatch.fadeIn() : $btnBatch.fadeOut();
      // $btnBatch.attr('href', '/admin/category-delete.php?id='+allCheckeds);
      // $btnBatch.prop('search','?id='+allCheckeds);这个方法更加有效
      // $btnBatch.prop('search','?id=' + allCheckeds);
    });

    // 全选或全不选
    $thInput.on('change', function(event) {
      event.preventDefault();
      var checked = $(this).prop('checked');
      // trigger() 方法触发被选元素的指定事件类型。
      $('td input').prop('checked', checked).trigger('change');
    });

	  // 批量操作
    $btnBatch
      // 批准
      .on('click', '.btn-info', function (event) {
        // 字符串拼接数组，浏览器默认会把数组转为以逗号分隔的字符串
        $.post('/admin/api/comments-status.php?id=' + allCheckeds, { status: 'approved' }, 
        	function (data) {
          	  if (!data) {return};
           	  loadPage(currentPage);
              allCheckeds=[];
              $btnBatch.fadeOut();
              $thInput.prop('checked', false);
            })
      	})
      // 拒绝
      .on('click', '.btn-warning', function (event) {
        $.post('/admin/api/comments-status.php?id=' + allCheckeds.join(','), { status: 'rejected' }, 
        	function (data) {
          	  if (!data) {return};
          	  loadPage(currentPage);
              allCheckeds=[];
              $btnBatch.fadeOut();
              $thInput.prop('checked', false);
        	})
      	})
      // 删除
      .on('click', '.btn-danger', function (event) {
        $.get('/admin/api/comments-delete.php', { id: allCheckeds.join(',') }, function (data) {
          if (!data) {return};
          loadPage(currentPage);
          allCheckeds=[];
          $btnBatch.fadeOut();
          $thInput.prop('checked', false);
        })
      })    

  </script>
  <script>NProgress.done()</script>
</body>
</html>
