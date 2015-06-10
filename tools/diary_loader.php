<?php
require_once (dirname(__FILE__).'/../'.'library/zeyublog.php');

ini_set('date.timezone','Asia/Shanghai');

LogOpt::init('diary_loader', true);

$options = getopt('t:');

$infos = array();
$draft_file = dirname(__FILE__).'/../'.'draft/draft.tpl';
if (!file_exists($draft_file))
{
	echo '指定日志的草稿不存在'.PHP_EOL;
	return;
}
$infos['draft'] = file_get_contents($draft_file);
$contents = ZeyuBlogOpt::pre_treat_article ($infos['draft']);
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
	if (!file_exists(dirname(__FILE__).'/../'.'html/'.$image_path))
	{
		LogOpt::set('exception', 'the image does not exist',
			'image_path', $image_path
		);

		return;
	}
	$query = 'select image_id from images where path="'.$image_path.'"';
	$image_id = MySqlOpt::select_query($query);
	if ($image_id == null)
	{
		$full_path = dirname(__FILE__).'/../'.'html/'.$image_path;
		$image_id = ZeyuBlogOpt::load_image($full_path, 'article');
		if ($image_id == false)
		{
			LogOpt::set('exception', '添加图片到数据库失败',
				'image_path', $image_path,
				MySqlOpt::errno(), MySqlOpt::error()
			);
		}
		LogOpt::set('info', '添加图片到数据库成功',
			'image_id', $image_id,
			'image_path', $image_path
		);

		$image_ids[] = $image_id;
	}
}

$infos['title'] = date('Y-m-d H:i:s');
if (isset($options['t']))
	$infos['title'] = $options['t'].' -- '.date('Y-m-d');
$infos['category_id'] = '5';

// 获取 index
$indexs = json_encode(ZeyuBlogOpt::get_index($contents));
if ($indexs != null)
	$infos['indexs'] = $indexs;

$infos['updatetime'] = 'now()';
$article_id = MySqlOpt::insert('article', $infos, true);

if ($article_id == null)
{
	LogOpt::set('exception', 'article insert error',
		MySqlOpt::errno(), MySqlOpt::error()
	);
	return;
}

LogOpt::set('info', '添加日志成功',
	'article_id', $article_id,
	'title', $infos['title']
);

unlink($draft_file);
?>
