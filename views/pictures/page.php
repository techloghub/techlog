<?php $max_page = intval(($params['count']-1)/$params['limit']+1) ?>
<?php if ($max_page > 1): ?>
	<?php if ($max_page > 10 && $params['page'] > 6): ?>
		<li><a href="javascript:void(0)" onclick="js_submit('1')" class="first">&lt;&lt;首页</a></li>
		<li><a href="javascript:void(0)" onclick="js_submit('<?php echo intval($params['page']-1) ?>')" class="first">&lt;上页</a></li>
	<?php endif ?>
	<?php if ($params['page'] > 1): ?>
		<?php $firstno = (($params['page'] < 6 or $max_page <= 10) ? 1 : $params['page']-5) ?>
		<?php $firstno = (($firstno > $max_page - 10 and $max_page > 10) ? $max_page - 10 : $firstno) ?>
		<?php foreach (range($firstno, $params['page']-1) as $pagenum) { ?>
			<li><a href="javascript:void(0)" onclick="js_submit('<?php echo $pagenum ?>')"><?php echo $pagenum ?></a></li>
		<?php } ?>
	<?php endif ?>
	<li class="active"><a href="javascript:void(0)"><?php echo $params['page'] ?></a></li>
	<?php if ($params['page'] < $max_page): ?>
		<?php $endno = (($params['page'] < $max_page - 5 and $max_page > 10) ? $params['page'] + 5 : $max_page) ?>
		<?php $endno = (($endno < 11 and $max_page > 10) ? 11 : $endno) ?>
		<?php foreach (range($params['page']+1, $endno) as $pagenum) { ?>
			<li><a href="javascript:void(0)" onclick="js_submit('<?php echo $pagenum ?>')"><?php echo $pagenum ?></a></li>
		<?php } ?>
	<?php endif ?>
	<?php if ($max_page > 10 && $params['page'] < $max_page - 5): ?>
		<li><a href="javascript:void(0)" onclick="js_submit('<?php echo intval($params['page']+1) ?>')" class="first">下页&gt;</a></li>
		<li><a href="javascript:void(0)" onclick="js_submit('<?php echo $max_page ?>')" class="first">尾页&gt;&gt;</a></li>
	<?php endif ?>
<?php endif ?>
