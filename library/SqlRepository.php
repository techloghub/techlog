<?php
/** @noinspection SqlDialectInspection */
/** @noinspection SqlResolve */
/** @noinspection SqlNoDataSourceInspection */

class SqlRepository
{
	public static function getTags($article_id)
	{ // {{{
		$sql = 'select C.* from'
			.' article as A,'
			.' article_tag_relation as B,'
			.' tags as C'
			.' where A.article_id = B.article_id'
			.' and B.tag_id = C.tag_id'
			.' and A.article_id = :article_id';

		$pdo = Repository::getInstance();
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(':article_id' => $article_id));
		$ret = $stmt->fetchAll();
		return $ret;
	} // }}}

	public static function getArticleCountByTagId($tag_id)
	{ // {{{
		$tag_id = intval($tag_id);
		$sql = 'select count(*) as count from'
			.' (select 1 from article_tag_relation'
			.' where tag_id = '.$tag_id.' group by article_id) as A';

		$pdo = Repository::getInstance();
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
        $pdo = Repository::getInstance();
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

    /**
     * @return BooknoteModel[]
     */
	public static function getBooknotes()
	{ // {{{
		$sql = 'select booknote.* from booknote, article'
			.' where booknote.index_article_id = article.article_id'
			.' and booknote.online = 1'
			.' order by article.access_count desc';

		$pdo = Repository::getInstance();
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetchAll(PDO::FETCH_CLASS, 'BooknoteModel');
		return $ret;
	} // }}}

	public static function getLedgersCategories()
	{ // {{{
		$sql = 'select category from ledgers group by category';

		$pdo = Repository::getInstance();
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetchAll();
		$categories = array();
		foreach ($ret as $data) {
			$categories[] = $data['category'];
		}
		return $categories;
	} // }}}

	public static function getTagIdCount()
	{ // {{{
		$sql = 'select tag_id,count(*) as article_count from article_tag_relation'
			.' group by tag_id'
			.' order by article_count desc, inserttime desc limit 90';

		$pdo = Repository::getInstance();
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetchAll();
		return $ret;
	} // }}}

	public static function getUVInfos($date)
	{ // {{{
		$sql = 'select date(time_str) as date, count(*) as total from'
			.' (select time_str from stats where time_str <= "'.$date.' 23:59:59"'
			.' and time_str >= "'.$date.' 00:00:00" group by remote_host) as A'
			.' group by date(time_str)';

		$pdo = Repository::getInstance();
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetch();
		return array($ret['total'], $ret['date']);
	} // }}}

	public static function getPVInfos($timestamp)
	{ // {{{
		$sql = 'select date(time_str) as date, count(*) as total from'
			.' stats where time_str <= "'.date('Y-m-d', $timestamp).' 23:59:59"'
			.' and time_str >= "'.date('Y-m-d', $timestamp - 3600*24*(14-1)).' 00:00:00"'
			.' group by date(time_str) order by time_str';

		$pdo = Repository::getInstance();
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetchAll();
		return $ret;
	} // }}}

	public static function getCategoryNewArticle()
	{ // {{{
		$sql = 'select a.category_id, a.article_id, b.category, a.title, a.inserttime'
			.' from article a, category b where not exists'
			.' ( select 1 from article where category_id=a.category_id'
			.' and inserttime>a.inserttime )'
			.' and a.category_id = b.category_id order by category_id';

		$pdo = Repository::getInstance();
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetchAll();
		return $ret;
	} // }}}

	public static function getCategoryInfos()
	{ // {{{
		$sql = 'select category.category_id, category.category, count(*) as total'
			.' from article, category'
			.' where article.category_id = category.category_id'
			.' group by article.category_id';

		$pdo = Repository::getInstance();
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetchAll();
		return $ret;
	} // }}}

	private static function getWhere($request, $ismood = false, $is_root = false)
	{ // {{{
		$sphinx = self::getSphinx();
		$where_str = '';
		$dates = array();
		$tag_ids = array();
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
			// 查询 article 并且非 root 或者查询内容（非全部内容）限制分类
			if (!$ismood
				&& (!$is_root
					|| ($request['opt_type'] != 'all'
						&& $request['opt_type'] != 'title'))) {
                $where_str .= ' and category_id < 5';
            } else if (!$is_root) {
                // 禁止非 root 查询 mood
                $where_str .= ' and 0';
            }

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
                try {
                    $search_ret = $sphinx->query($key, $request['opt_type']);
                } catch (ErrorException $e) {
                    $search_ret = null;
                }

                if (isset($search_ret['matches']))
				{
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
			}
			if (!empty($article_ids) && (!$ismood || $is_root))
			{
				if (!$ismood)
					$where_str .= ' and article_id in ('.implode(',', $article_ids).')';
				else if ($is_root)
					$where_str .= ' and mood_id in ('.implode(',', $article_ids).')';
			}
			else
			{
				$where_str = ' and 0';
			}
		}
		return $where_str;
	} // }}}

    /**
     * @return SphinxClient
     */
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
