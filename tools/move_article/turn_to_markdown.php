<?php
require_once (__DIR__.'/../app/register.php');
require_once (__DIR__.'/../library/MarkdownTools.php');

$options = getopt('i:m:');
if (!isset ($options['i'])) {
	echo 'usage: php turn_to_markdown.php -i [article_id|draft] [-m moved]'.PHP_EOL;
	return;
}
$draft = null;
$article = new ArticleModel();
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
	echo $article->get_title().PHP_EOL;
}
if ($draft === null) {
	echo 'error: not exist'.PHP_EOL;
	return;
}
$command = 'rm -rf '.DRAFT_PATH.'/markdown && mkdir '.DRAFT_PATH.'/markdown';
exec($command);
$tags = SqlRepository::getTags($article->get_article_id());
$tag_arr = array();
foreach ($tags as $tag) {
	$tag_arr[] = $tag['tag_name'];
}
$filename = DRAFT_PATH.'/markdown/markdown'.$options['i'].'.md';
$markdown = '---'.PHP_EOL.'article_id: '.$article->get_article_id().PHP_EOL.'category: '.$article->get_category_id().PHP_EOL.'date: '.$article->get_inserttime().PHP_EOL.'tags: ["'.implode('","', $tag_arr).'"]'.PHP_EOL.'---'.PHP_EOL.PHP_EOL;
$markdown .= MarkdownTools::treat_article($draft);
file_put_contents($filename, $markdown);

if (isset($options['m'])) {
	$article->set_moved(1);
	$ret = Repository::persist($article);
}
