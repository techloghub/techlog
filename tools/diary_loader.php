<?php
require_once (__DIR__.'/../app/register.php');
LogOpt::init('diary_loader', true);

$options = getopt('t:');

$infos = array();
$draft_array = scandir(DRAFT_PATH);
$draft_result = array();
foreach ($draft_array as $draft_name) {
	$match_count = preg_match('/^draft(\..*|$)/', $draft_name, $result);
	if ($match_count > 0) {
		$draft_result[] = $draft_name;
	}
}

if (count($draft_result) > 1) {
	echo 'Error：不止一个可选的 draft 文件'.PHP_EOL;
	return;
}
if (empty($draft_result)) {
	echo '指定日志的草稿不存在'.PHP_EOL;
	return;
}

$draft_file = DRAFT_PATH.'/'.$draft_result[0];
$infos['draft'] = file_get_contents($draft_file);

$draft_name_infos = explode('.', $draft_result[0]);
if (sizeof($draft_name_infos) > 0 && $draft_name_infos[sizeof($draft_name_infos) - 1] == 'md') {
	$infos['draft'] = MarkdownTools::turn_markdown_to_techlog($infos['draft']);
}

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
$infos['comment_count'] = 0;
$infos['access_count'] = 0;
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
