<?php
require_once (__DIR__.'/../app/register.php');

LogOpt::init('revise_article', true);

$options = getopt('i:');
if (!isset ($options['i']) || !is_numeric($options['i']))
{
	echo 'usage: php revise_article.php -i article_id'.PHP_EOL;
	return;
}
$article = Repository::findOneFromArticle(
	array('eq' => array('article_id' => $options['i'])));
if ($article == false)
{
	LogOpt::set ('exception', 'cannot find the article',
		'article_id', $options['i']);
	return;
}
$filename = DRAFT_PATH.'/draft'.$options['i'].'.tpl';
file_put_contents($filename, $article->get_draft());
LogOpt::set('info', '已将文件加载至'.$filename,
	'article_id', $options['i'], 'title', $article->get_title());
?>
