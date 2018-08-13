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
	<p class="indexfont" style='font-family:"PT Serif","Georgia","Helvetica Neue",Arial,sans-serif' class="lead">這個世上只有兩樣東西永遠都不會衰老，一個是青春，一個是對知識的渴求</p>
	<p style='font-family:"PT Serif","Georgia","Helvetica Neue",Arial,sans-serif;' class="indexfont"> ---- 熱愛生活，熱愛編碼，為了夢想奔跑的程序員</p>
	<br />
	<p>
		<a class="btn btn-lg btn-primary" href="/article/list/10182614" role="button" style='font-family:"Hiragino Sans GB", "Microsoft YaHei","WenQuanYi Micro Hei"'>博客簡介</a>
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
