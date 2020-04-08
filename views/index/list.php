<?php require(VIEW_PATH.'/base/base.php'); ?>
<br /><br /><br />
<br /><br /><br />
<!-- Jumbotron -->
<style>
.indexfont {
	<?php if ($params['is_mobile']): ?>
	color:#000;
	<?php else: ?>
	color:#fff;
	<?php endif ?>
}
</style>
<div class="jumbotron">
	<br />
	<h1><p class="indexfont">龍潭齋</p></h1>
	<br />
	<p class="indexfont" style='font-family:"PT Serif","Georgia","Helvetica Neue",Arial,sans-serif' class="lead">这个世上只有两样东西永远都不会衰老，一个是青春，一个是对知识的渴求</p>
	<p style='font-family:"PT Serif","Georgia","Helvetica Neue",Arial,sans-serif;' class="indexfont"> ---- 热爱生活，热爱编码，为了梦想奔跑的程序员</p>
	<br />
	<p>
		<a class="btn btn-lg btn-primary" href="/article/list/10182614" role="button" style='font-family:"Hiragino Sans GB", "Microsoft YaHei","WenQuanYi Micro Hei"'>博客简介</a>
		<?php if ($params['is_root']): ?>
		<a class="btn btn-lg btn-primary" href="http://admin.techlog.cn/techlog_manager/tasklist/list" role="button" style='font-family:"Hiragino Sans GB", "Microsoft YaHei","WenQuanYi Micro Hei"' target="_blank">任务管理</a>
		<?php endif ?>
	</p>
</div>
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<?php require(VIEW_PATH.'/base/footer.php'); ?>
