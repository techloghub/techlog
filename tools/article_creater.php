<?php
require_once (__DIR__.'/../app/register.php');
LogOpt::init('article_creater', true);
$options = getopt('i:t:g:c:d:a:');
if (!isset($options['t']) || !isset($options['g']) || !isset($options['c']))
{
	echo 'usage: php article_creater.php'
		.' [-a inserttime] [-i article_id] -t title'
		.' [-d title_desc] -g tags -c category'.PHP_EOL;
	return;
}
if (isset($options['i']))
	$draft_file = DRAFT_PATH.'/draft'.$options['i'].'.tpl';
else
	$draft_file = DRAFT_PATH.'/draft.tpl';
if (!file_exists($draft_file))
{
	echo '指定日志的草稿不存在'.PHP_EOL;
	return;
}
$infos = array();
// 获取 contents
$infos['draft'] = file_get_contents($draft_file);
$temp_contents = TechlogTools::pre_treat_article($infos['draft']);
// 获取 index
$indexs = json_encode(TechlogTools::get_index($temp_contents));
if ($indexs != null)
	$infos['indexs'] = $indexs;
// 获取 images
$image_ids = array();
while (1)
{
	$image_path = StringOpt::spider_string(
		$temp_contents,
		'img<![&&]>src="',
		'"',
		$temp_contents
	);
	if ($image_path === null
		|| $image_path === false
		|| trim($image_path) == ''
	)
		break;
	$image_path = trim($image_path);
	if (!file_exists(WEB_PATH.'/resource/'.$image_path))
	{
		echo '文中目标图片不存在'."\t".$image_path.PHP_EOL;
		return;
	}
	$image_id = Repository::findImageIdFromImages(
		array(
			'eq' => array('path' => $image_path)
		)
	);
	if ($image_id == false)
	{
		$full_path = WEB_PATH.'/resource/'.$image_path;
		$image_id = TechlogTools::load_image ($full_path, 'article');
		if ($image_id == null)
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
// 获取 category_id
$query = 'select category_id from category where category="'.$options['c'].'"';
$infos['category_id'] = Repository::findCategoryIdFromCategory(
	array('eq' => array('category' => $options['c']))
);
if ($infos['category_id'] == false)
{
	echo '指定category不存在'."\t".$options['c']."\t".PHP_EOL;
	return;
}
// 获取 title、title_desc、updatetime
$infos['title'] = $options['t'];
if (isset($options['d']))
	$infos['title_desc'] = $options['d'];
$infos['updatetime'] = 'now()';
// 设置inserttime
$infos['inserttime'] = isset($options['a']) ? $options['a'] : 'now()';
// 插入日志
if (isset($options['i']))
{
	$article = Repository::findOneFromArticle(
		array('eq' => array('article_id' => $options['i'])));
	if ($article == false)
	{
		LogOpt::set('exception', '日志不存在',
			'article_id', $options['i']
		);
		return;
	}
	foreach ($infos as $key => $value)
	{
		$func = 'set_'.$key;
		$article->$func($value);
	}
}
else
{
	$article = new ArticleModel($infos);
}
$article_id = Repository::persist($article);
if ($article_id == false)
{
	LogOpt::set ('exception', '日志插入失败');
	return;
}
LogOpt::set ('info', '日志插入成功',
	'article_id', $article_id,
	'title', $options['t']
);
unlink($draft_file);
// 添加 article 并获取新加 article_id 后需要更新为 tags 表对应项
$tags = explode(',', $options['g']);
if ($tags == null)
{
	echo 'tags 参数有误'."\t".$tags.PHP_EOL;
	return;
}
foreach ($tags as $tag)
{
	$tag = trim($tag);
	if ($tag == '')
		continue;
	$tag_id =
		Repository::findTagIdFromTags(array('eq' => array('tag_name' => $tag)));
	if ($tag_id == false)
	{
		$tag = new TagsModel(array('tag_name' => $tag, 'inserttime' => 'now()'));
		$tag_id = Repository::persist($tag);
		if ($tag_id == false)
		{
			LogOpt::set ('exception', 'tag 添加失败');
			continue;
		}
	}
	$article_tag_relation = new ArticleTagRelationModel(
		array(
			'article_id' => $article_id,
			'tag_id' => $tag_id,
			'inserttime' => 'now()'
		)
	);
	try
	{
		$relation_id = Repository::persist($article_tag_relation);
	} catch (PDOException $e) {
		LogOpt::set('exception', 'article_tag_relation 已存在',
			'article_id', $article_id, 'tag_id', $tag_id
		);
		$pdo = Repository::getInstance();
		$pdo->rollback();
		continue;
	}
	if ($relation_id == false)
	{
		LogOpt::set('exception', 'article_tag_relation 更新失败',
			'article_id', $article_id, 'tag_id', $tag_id
		);
		continue;
	}
	LogOpt::set ('info', 'article_tag_relation 更新成功',
		'relation_id', $relation_id
	);
}
?>
