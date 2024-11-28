<?php
require_once (__DIR__.'/../app/register.php');
LogOpt::init('article_creater', true);
$options = getopt('i:t:g:c:d:a:b:');
if (!isset($options['t']) || !isset($options['g']) || !isset($options['c']))
{
	echo 'usage: php article_creater.php'
		.' [-a inserttime] [-i article_id] -t title'
		.' [-d title_desc] [-b booknote_id] -g tags -c category'.PHP_EOL;
	return;
}

$draft_id = isset($options['i']) ? $options['i'] : '';
$draft_array = scandir(DRAFT_PATH);
$draft_result = array();
foreach ($draft_array as $draft_name) {
	if (preg_match('/^draft'.$draft_id.'(\..*$|$)/', $draft_name, $result) > 0) {
		$draft_result[] = $draft_name;
	}
}

if (count($draft_result) > 1) {
	echo 'Error：不止一个可选的 draft 文件'.PHP_EOL;
	return;
}

if (empty($draft_result))
{
	echo '指定日志的草稿不存在'.PHP_EOL;
	return;
}

$draft_file = DRAFT_PATH.'/'.$draft_result[0];
$infos = array();
// 获取 contents
$infos['draft'] = file_get_contents($draft_file);

$draft_name_infos = explode('.', $draft_result[0]);
if (sizeof($draft_name_infos) > 0 && $draft_name_infos[sizeof($draft_name_infos) - 1] == 'md') {
	$infos['draft'] = MarkdownTools::turn_markdown_to_techlog($infos['draft']);
}

if (strpos($infos['draft'], '微信公众号') === false) {
	$infos['draft'] .= PHP_EOL.'<h1>微信公众号'.PHP_EOL
		.'欢迎关注微信公众号，以技术为主，涉及历史、人文等多领域的学习与感悟，'
		.'每周三到七篇推文，只有全部原创，只有干货没有鸡汤'.PHP_EOL.'<img id="rqcode"/>';
}

if (!empty($options['b'])) {
	$infos['draft'] .= PHP_EOL.'<h1>附录 -- 系列目录'.PHP_EOL
		.'<a id="'.$options['b'].'"/>';
}

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
		'?<![||]>"',
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
$infos['inserttime'] = isset($options['a']) ? $options['a'] : 'now()';
$infos['online'] = 1;
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
    $infos['comment_count'] = 0;
    $infos['access_count'] = 0;
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

if (!empty($options['b']) && intval($options['b']) > 0) {
	$booknote = Repository::findOneFromArticle(
		array('eq' => array('article_id' => $options['b'])));
	$draft = $booknote->get_draft();
	$draft_arr = explode('<h1>微信公众号', $draft);
	$draft = trim($draft_arr[0]).PHP_EOL.'<a id="'.$article_id.'" title="'.$options['t'].'"/>';
	if (sizeof($draft_arr) == 2) {
		$draft .= PHP_EOL.PHP_EOL.'<h1>微信公众号'.PHP_EOL.$draft_arr[1];
	}
	$booknote->set_draft($draft);
	$booknote->set_updatetime('now()');
	Repository::persist($booknote);
	LogOpt::set ('info', '专题更新成功', 'article_id', $options['b'], 'title', $booknote->get_title());
}

unlink($draft_file);
if ($infos['category_id'] != 2) {
	// 添加 article 并获取新加 article_id 后需要更新为 tags 表对应项
	$tags = explode('|', $options['g']);
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
}
?>
