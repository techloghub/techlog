<?php
function echo_ifset($params, $key)
{
	echo (isset($params[$key]) ? $params[$key] : '');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta name="renderer" content="webkit"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<script type="text/javascript" src="/resource/jquery/js/jquery-1.7.min.js"></script>
		<script type="text/javascript" src="/resource/jquery/js/jquery.alerts.js"></script>
		<script type="text/javascript" src="/resource/bootstrap/js/bootstrap.min.js"></script>
		<script src="/resource/ace-master/build/src-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
		<link href="/resource/bootstrap/css/docs.css" rel="stylesheet" type="text/css">
		<link href="/resource/bootstrap/css/bootstrap-responsive.css" rel="stylesheet" type="text/css">
		<link href="/resource/bootstrap/css/bootstrap-theme.css" rel="stylesheet" type="text/css">
		<link href="/resource/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link href="/resource/bootstrap/css/prettify.css" rel="stylesheet" type="text/css">
		<link href="/resource/bootstrap/css/github.min.css" rel="stylesheet" type="text/css">
		<link href="/resource/jquery/css/jquery.alerts.css" rel="stylesheet" type="text/css">
		<link href="/resource/jquery/css/jquery.ui.css" rel="stylesheet" type="text/css">
		<link href="/resource/zeyu_blog/css/zeyu_blog.css" rel="stylesheet" type="text/css">
		<link rel="shortcut icon" href="/images/icon.png">
		<?php if (!isset($params['is_root']))
			$params['is_root'] = false;
		?>
		<?php if (isset($params['category_id']) &&
			($params['category_id'] != 2 && $params['category_id'] != 1)): ?>
			<link href="/resource/bootstrap/css/site.css" rel="stylesheet"></link>
		<?php endif ?>
		<?php if (isset($params['title']) && $params['title'] == '龙潭相册'): ?>
			<script type="text/javascript" src="/resource/datetimepicker-master/js/bootstrap-datetimepicker.js"></script>
			<link href="/resource/datetimepicker-master/build/build_standalone.less" rel="stylesheet" type="text/css">
			<link href="/resource/datetimepicker-master/css/bootstrap-datetimepicker.css" rel="stylesheet" type="text/css">
		<?php endif ?>
		<div id="navbar">
			<header class="navbar navbar-inverse navbar-fixed-top bs-docs-nav" role="banner">
			<?php if (empty($params['title'])): ?>
				<title>龍潭齋</title>
			<?php else: ?>
				<title><?php echo $params['title']; ?></title>
			<?php endif ?>
				<div class="container">
					<nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
						<ul class="nav navbar-nav">
							<li class="active">
							<a href="/">龙潭斋</a>
							</li>
							<li>
							<a href="/note">技术专题</a>
							</li>
							<li>
							<a href="/debin/category/4">技术乱炖</a>
							</li>
							<li>
							<a href="/debin/category/3">随笔轧志</a>
							</li>
							<?php if ($params['is_root']): ?>
								<li>
								<a href="/debin/category/1">专题解读</a>
								</li>
								<li>
								<a href="/debin/category/5">龙泉日记</a>
								</li>
								<li>
								<a href="/debin/mood">心情小说</a>
								</li>
								<li>
								<a href="/earnings">龙泉财报</a>
								</li>
							<?php endif ?>
							<li>
							<a href="/infos">数据统计</a>
							</li>
						</ul>
						<ul class="nav navbar-nav navbar-right">
							<?php if ($params['is_root']): ?>
								<li>
								<a href="/article/test">草稿</a>
								</li>
								<li>
								<a href="/pictures">相册</a>
								</li>
								<li>
								<a href="/search">检索</a>
								</li>
							<?php else: ?>
								<li>
								<a href="/search">检索</a>
								</li>
								<li>
								<a href="javascript:void(0)" data-toggle="modal" data-target="#login_modal">登录</a>
								</li>
							<?php endif ?>
						</ul>
					</nav>
				</div>
			</header>
		</div>
	</head>
	<body style="background: <?php echo $params['background'] ?> fixed no-repeat; background-size: cover;">
	<link href='/resource/bootstrap/css/nav.css'  rel="stylesheet">
	<?php require(VIEW_PATH.'/base/modal.php'); ?>
