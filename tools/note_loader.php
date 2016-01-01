<?php
require_once (__DIR__.'/../app/register.php');
LogOpt::init ('note_loader', true);

$options = getopt('b:i:d:');

if (!isset($options['b']) || !isset($options['i']))
{
	echo 'usage: php note_loader.php -b bookname -i bookimageid [-d desc]'
		.PHP_EOL;
	return; 
}

$bookname = $options['b'];
$image_id = intval(trim($options['i']));
$descs = isset($options['d']) ? $options['d'] : null;

$image = Repository::findOneFromImages(
	array('eq' => array('image_id' => $image_id)));
if ($image == false)
{
	LogOpt::set('exception', 'image not exists', 'image_id', $image_id);
	return false;
}

$draft = '<div>'.PHP_EOL.'<!--'.PHP_EOL.'</div>'.PHP_EOL
	.'<img id="'.$image_id.'"/>'.PHP_EOL
	.'<div>'.PHP_EOL.'-->'.PHP_EOL.'</div>'.PHP_EOL;
$article = new ArticleModel(
	array(
		'title' => $bookname,
		'updatetime' => 'now()',
		'inserttime' => 'now()',
		'category_id' => 2,
		'draft' => $draft
	)
);
$article_id = Repository::persist($article);
if ($article_id == false)
{
	LogOpt::set('exception', 'new_note_insert_into_article_error');
	return false;
}
LogOpt::set('info', 'new_note_insert_into_article_success',
	'article_id', $article_id);

$booknote = new BooknoteModel(
	array(
		'index_article_id' => $article_id,
		'image_id' => $image_id,
		'updatetime' => 'now()',
		'inserttime' => 'now()',
		'descs' => $descs
	)
);
$note_id = Repository::persist($booknote);
if ($note_id == false)
{
	LogOpt::set('exception', 'new_note_insert_into_booknote_error');
	return false;
}
LogOpt::set('info', 'new_note_insert_into_booknote_success',
	'note_id', $note_id);
?>
