<?php
require_once (__DIR__.'/../app/register.php');
LogOpt::init('diary_loader', true);

$options = getopt('t:');

$infos = array();
$draft_file = DRAFT_PATH.'/draft.tpl';
if (!file_exists($draft_file))
{
	echo '指定日志的草稿不存在'.PHP_EOL;
	return;
}
$infos['draft'] = file_get_contents($draft_file);
$contents = TechlogTools::pre_treat_article ($infos['draft']);
$image_ids = array();

$infos['title'] = date('Y-m-d H:i:s');
if (isset($options['t']))
	$infos['title'] = $options['t'].' -- '.date('Y-m-d');
$infos['category_id'] = '5';
$infos['online'] = '1';

// 获取 index
$indexs = json_encode(TechlogTools::get_index($contents));
if ($indexs != null)
	$infos['indexs'] = $indexs;

$infos['updatetime'] = 'now()';
$infos['inserttime'] = 'now()';
$article = new ArticleModel($infos);
$article_id = Repository::persist($article);
if ($article_id == false)
{
	LogOpt::set('exception', 'article insert error');
	return;
}

LogOpt::set('info', '添加日志成功',
	'article_id', $article_id,
	'title', $infos['title']
);

unlink($draft_file);
?>
