<?php
require_once (__DIR__.'/../app/register.php');
require_once (__DIR__.'/../library/MarkdownTools.php');

$options = getopt('i:');
if (!isset ($options['i'])) {
	echo 'usage: php turn_to_markdown.php -i [article_id|draft]'.PHP_EOL;
	return;
}
$draft = null;
if ($options['i'] == 'draft') {
	$draft = file_get_contents(DRAFT_PATH.'/draft.tpl');
} else {
	$article = Repository::findOneFromArticle(
		array('eq' => array('article_id' => $options['i'])));
	if ($article == false) {
		echo 'exception: cannot find the article'.PHP_EOL;
		return;
	}
	$draft = $article->get_draft();
}
if ($draft === null) {
	echo 'error: not exist'.PHP_EOL;
	return;
}
$command = 'rm -rf '.DRAFT_PATH.'/markdown && mkdir '.DRAFT_PATH.'/markdown';
exec($command);
$filename = DRAFT_PATH.'/markdown/markdown'.$options['i'].'.md';
$markdown = MarkdownTools::treat_article($draft);
file_put_contents($filename, $markdown);
