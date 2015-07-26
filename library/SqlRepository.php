<?php
class SqlRepository
{
	public static function getArticleCountByTagId($tag_id)
	{ // {{{
		$pdo = Repository::getInstance();
		$tag_id = intval($tag_id);
		$sql = 'select count(*) as count from'
			.' (select 1 from article_tag_relation'
			.' where tag_id = '.$tag_id.' group by article_id) as A';
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetch();
		return isset($ret['count']) ? $ret['count'] : false;
	} // }}}

	public static function getArticlesByTagId($tag_id, $range)
	{ // {{{
		$pdo = Repository::getInstance();
		$sql = 'select article.*'
			.' from article, article_tag_relation, tags'
			.' where article.article_id = article_tag_relation.article_id'
			.' and tags.tag_id = article_tag_relation.tag_id'
			.' and tags.tag_id = '.intval($tag_id)
			.' order by inserttime desc'
			.' limit '.intval($range[0]).', '.intval($range[1]);

		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$ret = $stmt->fetchAll(PDO::FETCH_CLASS, 'ArticleModel');
		return $ret;
	} // }}}

	public static function getRandTags()
	{ // {{{
		$pdo = Repository::getInstance();
		$sql = 'select * from `tags`'
			.' where tag_id >= (select floor(max(tag_id) * rand()) from tags)'
			.' order by tag_id limit 20';

		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$ret = $stmt->fetchAll(PDO::FETCH_CLASS, 'TagsModel');
		return $ret;
	} // }}}

	public static function getTagsByArticleId($article_id)
	{ // {{{
		$sql = 'select C.* from'
			.' article as A,'
			.' article_tag_relation as B,'
			.' tags as C'
			.' where A.article_id = B.article_id'
			.' and B.tag_id = C.tag_id'
			.' and A.article_id = '.intval($article_id);

		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$ret = $stmt->fetchAll(PDO::FETCH_CLASS, 'TagsModel');
		return $ret;
	} // }}}

	public static function getArticleMoodCountByRequest($request)
	{ // {{{
		$start = $request['start'];
		$limit = $request['limit'];
		$is_mood = $request['ismood'];
		$is_root = $request['isroot'];
		$where_str = self::getWhere($request, $is_mood, $is_root);
		$table = $is_mood ? 'mood' : 'article';
		$sql = 'select count(*) as count from '.$table.' where 1'
			.$where_str;

		$pdo = Repository::getInstance();
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetch();
		return isset($ret['count']) ? $ret['count'] : false;
	} // }}}

	public static function getArticleMoodByRequest($request)
	{ // {{{
		$start = $request['start'];
		$limit = $request['limit'];
		$is_mood = $request['ismood'];
		$is_root = $request['isroot'];
		$where_str = self::getWhere($request, $is_mood, $is_root);
		$table = $is_mood ? 'mood' : 'article';
		$model = ucfirst($table).'Model';
		$sql = 'select * from '.$table.' where 1'.$where_str
			.' order by inserttime desc'
			.' limit '.$start.', '.$limit;

		$pdo = Repository::getInstance();
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetchAll(PDO::FETCH_CLASS, $model);
		return $ret;
	} // }}}

	public static function getBooknotes()
	{ // {{{
		$sql = 'select booknote.* from booknote, article'
			.' where booknote.index_article_id = article.article_id'
			.' and booknote.note_id not in (2, 3, 5, 9, 4)'
			.' order by article.access_count desc';

		$pdo = Repository::getInstance();
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetchAll(PDO::FETCH_CLASS, 'BooknoteModel');
		return $ret;
	} // }}}

	private static function getWhere($request, $ismood = false, $is_root = false)
	{ // {{{
		$sphinx = self::getSphinx();
		$where_str = '';
		$dates = array();
		$tag_ids = array();
		$ret_params = array();
		$tags = explode(',', $request['tags']);

		if (!empty($tags))
		{
			foreach ($tags as $tag)
			{
				$tag_infos = explode('_', $tag);
				if (count($tag_infos) != 3)
					continue;
				switch ($tag_infos[1])
				{
				case 'tag':
					$tag_ids[] = $tag_infos[2];
					break;
				case 'date':
					$tag_infos[2][4] = '-';
					$dates[] = $tag_infos[2];
					break;
				default:
					break;
				}
			}
			if (!$ismood && (!$is_root || $request['opt_type'] != 'all'))
				$where_str .= ' and category_id < 5';
			else if (!$is_root)
				$where_str .= ' and 0';

			if (!empty($tag_ids) && !$ismood)
			{
				$where_str .=
					' and article_id in ('
					.' select article_id from article_tag_relation'
					.' where tag_id in ('.implode(',', $tag_ids).')'
					.')';
			}

			if (!empty($dates))
			{
				$where_arr = array();
				foreach ($dates as $date)
				{
					$where_arr[] .=
						'inserttime >= "'.$date.'-01 00:00:00"'
						.' and inserttime <= "'.$date.'-31 23:59:59"';
				}
				$where_str .= ' and ('.implode(' or ', $where_arr).')';
			}
		}

		if (!empty($request['search']))
		{
			$article_ids = array();
			$searchs = explode(' ', $request['search']);
			foreach ($searchs as $key)
			{
				$key = trim($key);
				if (empty($key))
					continue;
				$search_ret = $sphinx->query($key, $request['opt_type']);

				if (empty($article_ids))
					$article_ids = array_keys($search_ret['matches']);
				else
				{
					$article_ids =
						array_intersect(
							$article_ids,
							array_keys($search_ret['matches'])
						);
				}
			}
			if (!$ismood)
				$where_str .= ' and article_id in ('.implode(',', $article_ids).')';
			else if ($is_root)
				$where_str .= ' and mood_id in ('.implode(',', $article_ids).')';
			else
				$where_str = ' and 0';
		}
		return $where_str;
	} // }}}

	private static function getSphinx()
	{ // {{{
		$sphinx = new SphinxClient();
		$sphinx->setServer("localhost", 9312);
		$sphinx->setMatchMode(SphinxClient::SPH_MATCH_PHRASE);
		$sphinx->setLimits(0, 1000);
		$sphinx->setMaxQueryTime(30);

		return $sphinx;
	} // }}}
}
?>
