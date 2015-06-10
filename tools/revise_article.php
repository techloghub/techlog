<?php
require_once (dirname(__FILE__).'/../'.'library/zeyublog.php');

LogOpt::init('revise_article', true);

$options = getopt('i:');
if (!isset ($options['i']) || !is_numeric($options['i']))
{
	echo 'usage: php revise_article.php -i article_id'.PHP_EOL;
	return;
}
$query = 'select draft,title from article where article_id='.$options['i'];
$draft = MySqlOpt::select_query($query);
if ($draft == null)
{
	LogOpt::set ('exception', 'cannot find the article',
		'article_id', $options['i'],
		MySqlOpt::errno(), MySqlOpt::error());

	return;
}
$title = $draft[0]['title'];
$draft = $draft[0]['draft'];

$filename = dirname(__FILE__).'/../'.'draft/draft'.$options['i'].'.tpl';

file_put_contents($filename, $draft);

LogOpt::set('info', '已将文件加载至'.$filename,
	'article_id', $options['i'],
	'title', $title
);
?>
