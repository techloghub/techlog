<?php $max_page = intval(($params['article_count']-1)/$params['limit']+1) ?>
<?php if ($max_page > 1): ?>
<section class="mod-blog-pagerbar mod-cs-pagebar" style="display: block;font-size:62.5%">
<div class="mod-pagerbar" id="top_bar">
	<?php if ($max_page > 10 && $params['page'] > 6): ?>
		<?php if (isset($params['base'])): ?>
		<a href="<?php echo $params['base'].'/1' ?>" class="first">&lt;&lt;首页</a>
		<a href="<?php echo $params['base'].'/'.($params['page']-1) ?>" class="first">&lt;上页</a>
		<?php else: ?>
		<a href="javascript:void(0)" onclick="js_submit('1')" class="first">&lt;&lt;首页</a>
		<a href="javascript:void(0)" onclick="js_submit('<?php echo intval($params['page']-1) ?>')" class="first">&lt;上页</a>
		<?php endif ?>
	<?php endif ?>
	<?php if ($params['page'] > 1): ?>
		<?php $firstno = (($params['page'] < 6 or $max_page <= 10) ? 1 : $params['page']-5) ?>
		<?php $firstno = (($firstno > $max_page - 10 and $max_page > 10) ? $max_page - 10 : $firstno) ?>
		<?php foreach (range($firstno, $params['page']-1) as $pagenum) { ?>
			<?php if (isset($params['base'])): ?>
			<a href="<?php echo $params['base'].'/'.$pagenum ?>"><?php echo $pagenum ?></a>
			<?php else: ?>
			<a href="javascript:void(0)" onclick="js_submit('<?php echo $pagenum ?>')"><?php echo $pagenum ?></a>
			<?php endif ?>
		<?php } ?>
	<?php endif ?>
	<span><?php echo $params['page'] ?></span>
	<?php if ($params['page'] < $max_page): ?>
		<?php $endno = (($params['page'] < $max_page - 5 and $max_page > 10) ? $params['page'] + 5 : $max_page) ?>
		<?php $endno = (($endno < 11 and $max_page > 10) ? 11 : $endno) ?>
		<?php foreach (range($params['page']+1, $endno) as $pagenum) { ?>
			<?php if (isset($params['base'])): ?>
			<a href="<?php echo $params['base'].'/'.$pagenum ?>"><?php echo $pagenum ?></a>
			<?php else: ?>
			<a href="javascript:void(0)" onclick="js_submit('<?php echo $pagenum ?>')"><?php echo $pagenum ?></a>
			<?php endif ?>
		<?php } ?>
	<?php endif ?>
	<?php if ($max_page > 10 && $params['page'] < $max_page - 5): ?>
		<?php if (isset($params['base'])): ?>
		<a href="<?php echo $params['base'].'/'.($params['page']+1) ?>" class="first">下页&gt;</a>
		<a href="<?php echo $params['base'].'/'.$max_page ?>" class="first">尾页&gt;&gt;</a>
		<?php else: ?>
		<a href="javascript:void(0)" onclick="js_submit('<?php echo intval($params['page']+1) ?>')" class="first">下页&gt;</a>
		<a href="javascript:void(0)" onclick="js_submit('<?php echo $max_page ?>')" class="first">尾页&gt;&gt;</a>
		<?php endif ?>
	<?php endif ?>
</div>
</section>
<?php endif ?>
