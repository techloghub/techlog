<?php require(VIEW_PATH.'/base/base.php'); ?>
<style>
	p
	{
		text-indent:2em;
		word-wrap:break-word;
	}
</style>
<div class="bs-header" id="content" style="FILTER: progid:DXImageTransform.Microsoft.Gradient (GradientType=1, StartColorStr=#d9e45d EndColorStr=darkolivegreen .opacity{ opacity:0.3; filter: alpha(opacity=30); background-color:#000; }" >
	<div class="header container">
		<br />
		<h1 style='font-family:"PT Serif","Georgia","Helvetica Neue",Arial,sans-serif'><?php echo $params['title'] ?></h1>
		<p style="text-indent:0em;"><?php echo $params['title_desc'] ?></p>
		<p style="text-indent:5em;" class="bs-header-small"><?php echo $params['inserttime'] ?></p>
	</div>
</div>
<br/>
<br/>
<br/>
<div class="container_wrapper">
	<div class="container bs-docs-container" style="margin-bottom:80px;">
		<?php if(!empty($params['contents'])): ?>
		<div class="row">
			<?php if(!empty($params['indexs'])): ?>
			<div id="index">
			<script src="/resource/stickUp-master/stickUp.min.js"></script>
			<style>
				.isStuck
				{
					width:250px;
				}
			</style>
			<div class="navbar-wrapper">
				<div class="col-md-3" id="stuck_div">
					<div class="bs-sidebar hidden-print" role="complementary">
						<ul class="nav bs-sidenav">
							<li>
							<a href="#">回到顶端</a>
							</li>
							<?php foreach($params['indexs'] as $idx_key=>$idx_val) { ?>
							<li>
							<a href="#<?php echo $idx_key ?>"><?php echo $idx_val ?></a>
							</li>
							<?php } ?>
							<?php if(isset($params['article_category_id']) && !in_array($params['article_category_id'], array(2, 5, 6))): ?>
							<li>
							<a href="#tags">标签</a>
							</li>
							<?php endif ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div id="md3" style="display:block" class="col-md-3"></div>
		<div id="md9" class="col-md-9" role="main">
		<?php else: ?>
		<div id="md3" style="display:none" class="col-md-3"></div>
		<div id="md9" class="col-md-12" role="main">
			<?php endif ?>
					<div class="bs-docs-section">
						<?php echo $params['contents'] ?>
						<?php if(!in_array($params['article_category_id'], array(2, 5, 6))): ?>
						<br /><br /><br /><br /><br />
						<div class="page-header">
							<div id="tags">标签</div>
						</div>
						<?php foreach($params['tags'] as $tags) { ?>
						<a href="/debin/tag/<?php echo $tags['tag_id'] ?>" target="_blank"><?php echo $tags['tag_name'] ?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<?php } ?>
						<?php endif ?>
					</div>
					<br /><br />
				</div>
			</div>
			<?php endif ?>
		</div>
	</div>
</div>
<script src="/resource/zeyu_blog/js/article.js"></script>
<script type="text/javascript">
window.onresize = function() {
	resize();
}
resize();

function resize()
{
	if (document.body.clientWidth < 1190)
	{
		if ($('#index').is(":visible"))
		{
			$('#index').hide();
			$('#md3').hide();
			$('#md9').removeClass('col-md-9');
			$('#md9').addClass('col-md-12');
		}
	}
	else
	{
		if ($('#index').length > 0 && $('#index').is(":hidden"))
		{
			$('#index').show();
			$('#md3').show();
			$('#md9').removeClass('col-md-12');
			$('#md9').addClass('col-md-9');
		}
	}
}
</script>
<?php require(VIEW_PATH.'/base/footer.php'); ?>
