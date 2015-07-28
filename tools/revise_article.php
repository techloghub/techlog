<?php
require_once (__DIR__.'/../app/register.php');

LogOpt::init('revise_article', true);

$options = getopt('i:');
if (!isset ($options['i']) || !is_numeric($options['i']))
{
	echo 'usage: php revise_article.php -i article_id'.PHP_EOL;
	return;
}
$draft = Repository::findDraftFromArticle(
	array('eq' => array('article_id' => $options['i'])));
$title = Repository::findTitleFromArticle(
	array('eq' => array('article_id' => $options['i'])));
if ($draft == false || $title == false)
{
	LogOpt::set ('exception', 'cannot find the article',
		'article_id', $options['i']);
	return;
}
$filename = DRAFT_PATH.'/draft'.$options['i'].'.tpl';
file_put_contents($filename, $draft);
LogOpt::set('info', '已将文件加载至'.$filename,
	'article_id', $options['i'], 'title', $title);
?>
