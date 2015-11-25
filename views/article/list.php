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
							<?php if(!empty($params['indexs'])): ?>
							<?php foreach($params['indexs'] as $idx_key=>$idx_val) { ?>
							<li>
							<a href="#<?php echo $idx_key ?>"><?php echo $idx_val ?></a>
							</li>
							<?php } ?>
							<?php endif ?>
							<?php if(isset($params['article_category_id']) && !in_array($params['article_category_id'], array(2, 5, 6))): ?>
							<li>
							<a href="#tags">标签</a>
							</li>
							<?php if($params['comment_count'] > 0): ?>
							<li>
							<a href="#comment">评论 (<?php echo $params['comment_count'] ?>)</a>
							</li>
							<?php endif ?>
							<li>
							<a href="#addcomment">添加评论</a>
							</li>
							<?php endif ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div id="md3" style="display:block" class="col-md-3"></div>
		<div id="md9" class="col-md-9" role="main">
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
	<?php if(isset($params['article_category_id']) && !in_array($params['article_category_id'], array(2, 5, 6))): ?>
	<?php if($params['comment_count'] > 0): ?>
	<div class="container bs-docs-container" style="margin-bottom:80px;">
		<div class="row">
			<div id="comment" class="col-md-12" role="main">
				<div class="bs-docs-section">
					<div class="page-header">
						<h1 id="commentlist">评论</h1>
					</div>
					<?php foreach ($params['comments'] as $comment) { ?>
					<p><h4 style="margin-right:5px;color:#0047dd"><i style="margin-right:5px; color:#000"><?php echo $comment->get_floor() ?>#</i><?php echo $comment->get_nickname() ?>: </h4></p>
					<p><?php echo htmlspecialchars($comment->get_content()) ?></p>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<?php endif ?>
	<div class="container bs-docs-container" style="margin-bottom:80px;">
		<div class="row">
			<div id="addcomment" class="col-md-12" role="main">
				<div class="bs-docs-section">
					<div class="page-header">
						<h1 id="add">添加评论</h1>
						<form class="navbar-form navbar-left" role="addcomment" id="commentform" method="post" action="javascript:void(0)">
							<div class="input-group" style="margin-top:10px;margin-bottom:10px;">
								<span class="input-group-addon" style="width:120px;">昵称</span>
								<input type="text" class="form-control" name="qq" id="nickname" placeholder="nickname" value="<?php echo_ifset($params, 'nickname') ?>" style="width:300px;margin-right:20px"/>
								<i style="color:#f00;diplay:none" id="errmsg"></i>
							</div>
							<div class="input-group" style="margin-top:10px;margin-bottom:10px;">
								<span class="input-group-addon" style="width:120px;">QQ 号码</span>
								<input type="text" class="form-control" name="qq" id="qq" placeholder="QQ" value="<?php echo_ifset($params, 'qq') ?>" style="width:300px;margin-right:20px"/>
							</div>
							<div class="input-group" style="margin-top:10px;margin-bottom:10px;">
								<span class="input-group-addon" style="width:120px;">电子邮箱</span>
								<input type="email" class="form-control" name="email" id="email" placeholder="Email" style="width:300px;" value="<?php echo_ifset($params, 'email') ?>"/>
							</div>
							<div class="input-group" style="margin-top:10px;margin-bottom:10px;">
								<textarea class="form-control" rows="10" class="col-md-12" id="comment_content"></textarea>
							</div>
							<div class="input-group" style="float:right; margin-top:10px;margin-bottom:10px;">
								<input type="hidden" style="display:none" name="article_id" id="article_id" value="<?php echo $params['article_id'] ?>"/>
								<button class="btn btn-primary btn-lg" type="submit" role="button" onclick="submitcomment()">提交评论</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endif ?>
</div>
<script src="/resource/zeyu_blog/js/article.js"></script>
<?php require(VIEW_PATH.'/base/footer.php'); ?>
