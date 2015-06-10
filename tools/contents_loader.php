<?php
require_once (dirname(__FILE__).'/../'.'library/zeyublog.php');

LogOpt::init('contents_loader', true);

$draft_dir = dirname(__FILE__).'/../'.'draft/';
$draft_files = scandir($draft_dir);
foreach ($draft_files as $draft)
{
	if ($draft[0] == '.')
		continue;
	$article_id = StringOpt::spider_string($draft, 'draft', '.tpl');
	if (empty($article_id))
		continue;
	$sql = 'select title from article where article_id='.$article_id;
	$article_info = MySqlOpt::select_query($sql);
	if ($article_info == null)
	{
		LogOpt::set('exception', '草稿原文不存在',
			'article_id', $article_id,
			MySqlOpt::errno(), MySqlOpt::error()
		);

		continue;
	}
	echo '是否加载该草稿到日志原文？'
		.'《'.$article_info[0]['title'].'》'
		.'(arctile_id:'.$article_id.') [y/N]';

	$sure = fgets(STDIN);
	if (trim($sure[0]) != 'Y' && trim($sure[0]) != 'y')
		continue;

	$draft_file = $draft_dir.'draft'.$article_id.'.tpl';

	$infos = array();
	$infos['draft'] = file_get_contents ($draft_file);
	$contents = ZeyuBlogOpt::pre_treat_article ($infos['draft']);
	$indexs = json_encode(ZeyuBlogOpt::get_index($contents));
	if ($indexs != null)
		$infos['indexs'] = $indexs;

	$infos['updatetime'] = 'now()';
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
		{
			break;
		}

		$image_path = trim($image_path);
		if (!file_exists(dirname(__FILE__).'/'.'../html/'.$image_path))
		{
			LogOpt::set('exception', '文中目标图片不存在',
				'image_path', $image_path);
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

				return;
			}
			LogOpt::set('info', '添加图片到数据库成功',
				'image_id', $image_id,
				'image_path', $image_path
			);

			$image_ids[] = $image_id;
		}
	}

	$ret = MySqlOpt::update('article', $infos, array('article_id'=>$article_id));
	if ($ret == null)
	{
		LogOpt::set ('exception', 'article 更新失败',
			'article_id', $article_id,
			MySqlOpt::errno(), MySqlOpt::error()
		);

		return;
	}
	LogOpt::set ('info', 'article 更新成功', 'article_id', $article_id);
	unlink($draft_file);
}
?>
