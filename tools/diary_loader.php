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
while (1)
{
	$image_path = StringOpt::spider_string(
		$contents,
		'img<![&&]>src="',
		'"',
		$contents
	);

	if ($image_path === null
		|| $image_path === false
		|| trim($image_path) == ''
	)
		break;

	$image_path = trim($image_path);
	if (!file_exists(WEB_PATH.'/resource/'.$image_path))
	{
		LogOpt::set('exception', 'the image does not exist',
			'image_path', $image_path
		);

		return;
	}
	$image_id = Repository::findImageIdFromImages(
		array('eq' => array('path' => $image_path)));
	if ($image_id == false)
	{
		$full_path = WEB_PATH.'/resource/'.$image_path;
		$image_id = TechlogTools::load_image($full_path, 'article');
		if ($image_id == false)
		{
			LogOpt::set('exception', '添加图片到数据库失败',
				'image_path', $image_path);
		}
		LogOpt::set('info', '添加图片到数据库成功',
			'image_id', $image_id, 'image_path', $image_path);

		$image_ids[] = $image_id;
	}
}

$infos['title'] = date('Y-m-d H:i:s');
if (isset($options['t']))
	$infos['title'] = $options['t'].' -- '.date('Y-m-d');
$infos['category_id'] = '5';

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
