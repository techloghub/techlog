<?php require(VIEW_PATH.'/base/base.php'); ?>
<?php if ($params['is_mobile']): ?>
<style>
.mod-bloglist {
	width:80%;
}
.mod-blog-pagerbar, .mod-cs-pagebar {
	background:#ddd;
	-webkit-box-shadow:2px 2px 10px #000;
	-moz-box-shadow:2px 2px 10px #000;
	box-shadow:2px 2px 10px #000;
}
.mod-realcontent, .mod-cs-contentblock, .cs-contentblock-bg {
	width:100%;
	background:#aaa;
	-webkit-box-shadow:2px 2px 10px #000;
	-moz-box-shadow:2px 2px 10px #000;
	box-shadow:2px 2px 10px #000;
}
.q-summary, .item-content, .cs-contentblock-detailcontent {
	width:100%;
}
.mod-blogitem {
	padding-left:120px;
}
</style>
<?php else:?>
<style>
.mod-realcontent, .mod-cs-contentblock, .cs-contentblock-bg {
	width:620px;
	-webkit-box-shadow:2px 2px 10px #e7e5e6;
	-moz-box-shadow:2px 2px 10px #e7e5e6;
	box-shadow:2px 2px 10px #e7e5e6;
}
.item-content, .cs-contentblock-detailcontent {
	width: 580px;
}
</style>
<?php endif ?>
<br />
<br />
<br />
<br />
<div id="customDoc">
<?php require(VIEW_PATH.'/base/debin.php'); ?>
	<section class=mod-page-body>
	<div class="mod-page-main wordwrap clearfix">
		<div class=x-page-container>
			<section class="mod-topspaceinfo mod-cs-header">
			<div class=head-topbar>
			</div>
			<div class=container>
				<h1>
					<div class="space-name cs-header-spacename">
						<?php echo $params['title'] ?> -- <?php echo $params['article_count'] ?>
					</div>
				</h1>
				<br />
				<br />
				<br />
			</div>
			<div class=head-footer>
			</div>
			</section>
			<section class="grid-98 mod-blogpage">
			<section class="mod-bloglist left">

			<?php require(VIEW_PATH.'/debin/page.php'); ?>

			<?php foreach($params['article_infos'] as $article_info) { ?>
			<article class="mod-blogitem mod-item-text">
			<div class="mod-real-text">
				<div class=box-postdate>
					<div class=q-day>
						<?php echo $article_info['date'] ?>
					</div>
					<div class=q-month-year>
						<?php echo $article_info['month'] ?>
					</div>
				</div>
			</div>
			<div class="mod-realcontent mod-cs-contentblock" <?php if (isset($params['ismood']) && $params['ismood']): ?>style="background-color:rgba(255,255,255,.6)"<?php endif ?>>
				<div class=cs-contentblock-bg>
				</div>
				<div class=item-head>
					<?php if (!isset($params['ismood']) or !$params['ismood']): ?>
					<a href="/article/list/<?php echo $article_info['article_id'] ?>" class="a-incontent a-title cs-contentblock-hoverlink" target=_blank>
						<?php else: ?>
						<div class="a-incontent a-title cs-contentblock-hoverlink" style='font-family:"PT Serif","Georgia","Helvetica Neue",Arial,sans-serif' >
						<?php endif ?>
						<?php echo $article_info['title'] ?>
						<?php if (!isset($params['ismood']) or !$params['ismood']): ?>
						</a>
						<?php else: ?>
					</div>
					<?php endif ?>
				</div>
				<div class="item-content cs-contentblock-detailcontent">
					<div class=q-summary>
						<?php echo $article_info['contents'] ?>
					</div>
				</div>
				<?php if ((!isset($params['ismood']) or !$params['ismood']) && !empty($article_info['tags'])): ?>
				<div class="item-foot clearfix">
					<span class=box-tag>
						<?php foreach ($article_info['tags'] as $tag) { ?>
						<span class=q-tag>#<?php echo $tag['tag_name'] ?>
						</span>&nbsp;&nbsp;
						<?php } ?>
					</span>
				</div>
				<?php endif ?>
				<div class=blog-cmt-wraper>
				</div>
			</div>
			</article>
			<?php } ?>

			<?php require(VIEW_PATH.'/debin/page.php'); ?>

			</section>
			<?php if (!$params['is_mobile']): ?>
			<section class="mod-rightsidebar clearfix mod-cs-sidebar">
			<div class=mod-siderbar>
				<section id=qFriendlyLinks style="font-size:14px;">
				<div  class=mod-friendlyLinks>
					<p style="text-indent:0em;"><h1 class=friendlyLinks-title><strong style="font-size:26px;">龍&nbsp;泉&nbsp;居&nbsp;士</strong></h1></p>
					<br />
					<p style="text-indent:0em;"><img src="images/psb.jpg" width="158px"></p>
					<br />
					<div class=friendlyLinks-linkList>
						<ul class=friendlyLinks-linkListContainer>
							<li>
							<center>
								<h1 style="font-size: 18px; color: rgb(63, 28, 143); margin: 20px;">
									<strong>最热文章</strong>
								</h1>
							</center>
							</li>
						</ul>
						<ul class=friendlyLinks-linkListContainer style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
							<?php foreach ($params['hot_articles'] as $article) { ?>
							<li>
							<h2 style="line-height:30px">
								<a href="/article/list/<?php echo $article->get_article_id() ?>" target="_blank"><?php echo $article->get_title() ?></a>
							</h2>
							</li>
							<?php } ?>
						</ul>
						<ul class=friendlyLinks-linkListContainer>
							<li>
							<center>
								<h1 style="font-size: 18px; color: rgb(63, 28, 143); margin: 20px;">
									<strong>最近更新</strong>
								</h1>
							</center>
							</li>
						</ul>
						<ul class=friendlyLinks-linkListContainer style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
							<?php foreach ($params['new_articles'] as $article) { ?>
							<li>
							<h2 style="line-height:30px">
								<a href="/article/list/<?php echo $article->get_article_id() ?>" target="_blank"><?php echo $article->get_title() ?></a>
							</h2>
							</li>
							<?php } ?>
						</ul>
						<ul class=friendlyLinks-linkListContainer>
							<li>
							<center>
								<h1 style="font-size: 18px; color: rgb(63, 28, 143); margin: 20px;">
									<strong>标签</strong>
								</h1>
							</center>
							</li>
						</ul>
						<ul class=friendlyLinks-linkListContainer style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
							<?php foreach ($params['rand_tags'] as $tag) { ?>
							<li>
							<h2 style="line-height:30px">
								<a href="/debin/tag/<?php echo $tag->get_tag_id() ?>" target="_blank"><?php echo $tag->get_tag_name() ?></a>
							</h2>
							</li>
							<?php } ?>
						</ul>
						<ul class=friendlyLinks-linkListContainer>
							<li>
							<center>
								<h1 style="font-size: 18px; color: rgb(63, 28, 143); margin: 20px;">
									<strong>关于博主</strong>
								</h1>
							</center>
							</li>
						</ul>
						<ul>
							<li>
							<h2 style="line-height:30px">
								<a href="/article/list/10182614" target="_blank">博客简介</a>
							</h2>
							</li>
							<li>
							<h2 style="line-height:30px">
								<a href="https://github.com/zeyu203" target="_blank">github</a>
							</h2>
							</li>
							<li>
							<h2 style="line-height:30px">
								<strong>QQ：1053038465</strong>
							</h2>
							</li>
							<li>
							<h2 style="line-height:30px">
								<strong>邮箱：zeyu203@qq.com</strong>
							</h2>
							</li>
						</ul>
					</div>
				</div>
				</section>
				<?php endif ?>
			</div>
			</section>
			<script src="http://hi.bdimg.com/static/qbase/js/mod/mod_foot.js?v=382c615f.js">
			</script>
			<script>qext.stat.ns('m_20120425_20001');</script>
			</script>
			<!--[if (lt IE 8.0)]>
			<link rel=stylesheet type=text/css href="http://hi.bdimg.com/static/qmessage/css/qmessage_mod_msg_bubble.css?v=a727011c.css">
			<![endif]-->
			<!--[if (!IE)|(gte IE 8.0)]>
			<!-->
			<link rel=stylesheet type=text/css href="http://hi.bdimg.com/static/qmessage/css/qmessage_mod_msg_bubble_datauri.css?v=6ddd3ba9.css">
			<!--<![endif]-->
			</script>
			<link href="http://hi.bdimg.com/static/qbase/css/yoyo/yoyo.css?v=4dd11df1.css" type=text/css rel=stylesheet>
			</script>
			<script>wpo.tti=new Date*1;</script>
			<script src="http://hi.baidu.com/cm/static/js/allsite.js?v=75674092.js?v=201401041944">
			</script>
			<script src="http://hi.bdimg.com/static/qbase/js/mod/mod_bubble.js?v=a9273ef3.js">
			</script>
			<script>document.write(unescape("%3Cscript src='http://hm.baidu.com/h.js%3F8c869b543955d43e496c2efee5b55823' type='text/javascript'%3E%3C/script%3E"));qext.stat.ns('m_20120713_qing_pv');</script>
		</div>
<form action="/debin/search" method="post" name="params_form" id="params_form">
	<input type="hidden" id='page' name='page' value="<?php echo_ifset($params, 'page') ?>"/>
	<input type="hidden" id='tags' name='tags' value="<?php echo_ifset($params, 'tags') ?>"/>
	<input type="hidden" id='limit' name='limit' value="<?php echo_ifset($params, 'limit') ?>"/>
	<input type="hidden" id="search" name="search" value="<?php echo_ifset($params, 'search') ?>"/>
	<input type="hidden" id='category' name='category' value="<?php echo_ifset($params, 'category') ?>"/>
	<input type="hidden" id='opt_type' name='opt_type' value="<?php echo_ifset($params, 'opt_type') ?>"/>
</form>
<br />
<br />
<br />
<?php require(VIEW_PATH.'/base/footer.php'); ?>
<script>
	function js_submit(pagenum)
	{
		$('#page').val(pagenum);
		$('#params_form')[0].submit();
	}
</script>
