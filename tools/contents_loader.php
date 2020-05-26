<?php
require_once (__DIR__.'/../app/register.php');
LogOpt::init('contents_loader', true);

$draft_files = scandir(DRAFT_PATH);
foreach ($draft_files as $draft)
{
	if ($draft[0] == '.')
		continue;
	$article_id = StringOpt::spider_string($draft, 'draft', '.<![||]><![+INF]>');
	if (empty($article_id))
		continue;
	$title = Repository::findTitleFromArticle(
		array('eq' => array('article_id' => $article_id)));
	if ($title == false)
	{
		LogOpt::set('exception', '草稿原文不存在', 'article_id', $article_id);
		continue;
	}
	echo '是否加载该草稿到日志原文？《'.$title.'》'
		.'(arctile_id:'.$article_id.') [y/N]';

	$sure = fgets(STDIN);
	if (trim($sure[0]) != 'Y' && trim($sure[0]) != 'y')
		continue;

	$draft_file = DRAFT_PATH.'/'.$draft;

	$infos = array();
	$infos['draft'] = file_get_contents ($draft_file);
	$draft_name_infos = explode('.', $draft);
	if (sizeof($draft_name_infos) > 0 && $draft_name_infos[sizeof($draft_name_infos) - 1] == 'md') {
		$infos['draft'] = MarkdownTools::turn_markdown_to_techlog($infos['draft']);
	}

	if (strpos($infos['draft'], '微信公众号') === false) {
	$infos['draft'] .= PHP_EOL.'<h1>微信公众号'.PHP_EOL
		.'欢迎关注微信公众号，以技术为主，涉及历史、人文等多领域的学习与感悟，'
		.'每周三到七篇推文，只有全部原创，只有干货没有鸡汤'.PHP_EOL.'<img id="rqcode"/>';
}
	$contents = TechlogTools::pre_treat_article ($infos['draft']);
	$indexs = json_encode(TechlogTools::get_index($contents));
	if ($indexs != null)
		$infos['indexs'] = $indexs;

	$infos['updatetime'] = 'now()';
	$image_ids = array();
	while (1)
	{
		$image_path = StringOpt::spider_string(
			$contents,
			'img<![&&]>src="',
			'?<![||]>"',
			$contents
		);

		if ($image_path === null
			|| $image_path === false
			|| trim($image_path) == ''
		)
		{
			break;
		}

		$image_path = trim($image_path);
		if (!file_exists(WEB_PATH.'/resource/'.$image_path))
		{
			LogOpt::set('exception', '文中目标图片不存在',
				'image_path', $image_path);
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
					'image_path', $image_path
				);

				return;
			}
			LogOpt::set('info', '添加图片到数据库成功',
				'image_id', $image_id,
				'image_path', $image_path
			);

			$image_ids[] = $image_id;
		}
	}

	$article = Repository::findOneFromArticle(
		array(
			'eq' => array('article_id' => $article_id)
		)
	);
	foreach ($infos as $key=>$value)
	{
		$func = 'set_'.$key;
		$article->$func($value);
	}
	$ret = Repository::persist($article);
	if ($ret == false)
	{
		LogOpt::set ('exception', 'article 更新失败', 'article_id', $article_id);
		return;
	}
	LogOpt::set ('info', 'article 更新成功', 'article_id', $ret);
	unlink($draft_file);
}
?>
