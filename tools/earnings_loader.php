<?php
require_once (__DIR__.'/../app/register.php');
LogOpt::init ('earnings_loader', true);

$options = getopt('m:a:i:e:');

if (!isset($options['m'])
	|| !isset($options['a'])
	|| !isset($options['i'])
	|| !isset($options['e'])
)
{
	echo "useage: php earnings_loader.php -m month -a image_id"
		." -i income -e expend (图片大小：400*345)".PHP_EOL;

	return; 
}

$month = $options['m'];
$income = (float)$options['i'];
$expend = (float)$options['e'];
$image_id = (int)$options['a'];

$sql = 'select path from images where image_id = '.$image_id;
$path = Repository::findPathFromImages(
	array('eq' => array('image_id' => $image_id)));
if ($path == false)
{
	LogOpt::set('exception', 'image_not_exists');
	return false;
}

$article = new ArticleModel(
	array(
		'title' => $month.'财报',
		'updatetime' => 'now()',
		'inserttime' => 'now()',
		'draft' => '<div>'.PHP_EOL.'<!-- 图片大小：700*467 -->'.PHP_EOL.'</div>',
		'category_id' => 6
	)
);
$article_id = Repository::persist($article);
if ($article_id == false)
{
	LogOpt::set('exception', 'new_note_insert_into_article_error');
	return false;
}
LogOpt::set('info', 'new_note_insert_into_article_success',
	'article_id', $article_id
);
$infos = array();
$infos['article_id'] = $article_id;
$infos['image_id'] = $image_id;
$infos['month'] = $month;
$infos['income'] = $income;
$infos['expend'] = $expend;
$earnings = new EarningsModel(
	array(
		'article_id' => $article_id,
		'image_id' => $image_id,
		'month' => $month,
		'income' => $income,
		'expend' => $expend,
		'inserttime' => 'now()'
	)
);
$earnings_id = Repository::persist($earnings);
if ($earnings_id == false)
{
	LogOpt::set('exception', 'new_earnings_insert_into_booknote_error');
	return false;
}
LogOpt::set('info', 'new_earnings_insert_into_booknote_success',
	'earnings_id', $earnings_id
);
?>
