<?php
require_once (__DIR__.'/../app/register.php');
require_once (__DIR__.'/../library/MarkdownTools.php');

$options = getopt('i:');
if (!isset ($options['i']) || !is_numeric($options['i'])) {
	echo 'usage: php turn_to_markdown.php -i article_id'.PHP_EOL;
	return;
}
$article = Repository::findOneFromArticle(
	array('eq' => array('article_id' => $options['i'])));
if ($article == false) {
	echo 'exception: cannot find the article'.PHP_EOL;
	return;
}
$filename = DRAFT_PATH.'/markdown'.$options['i'].'.txt';
$markdown = MarkdownTools::treat_articla($article->get_draft());
file_put_contents($filename, $markdown);
