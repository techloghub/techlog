<?php
class DebinController extends Controller
{
	private $sphinx;
	protected $limit;

	public function __construct()
	{
		parent::__construct();
		$this->sphinx = $this->getSphinx();
		$this->limit = 10;
	}

	public function categoryAction($query_params)
	{
		if (empty($query_params) || !is_array($query_params)
			|| (!$this->is_root && intval($query_params[0]) >= 5))
		{
			header("Location: /index/notfound");
			return;
		}

		$category_id = intval($query_params[0]);
		$page = isset($query_params[1]) ? intval($query_params[1]) : 1;

		$count_sql = 'select count(*) as count from article'
			.' where category_id = '.$category_id;
		$sql = 'select * from article'
			.' where category_id = '.$category_id
			.' order by inserttime desc'
			.' limit '.(($page-1)*$this->limit).', '.$this->limit;
		$category_sql = 'select category from category'
			.' where category_id = '.$category_id;
		$count = MySqlOpt::select_query($count_sql);
		$article_infos = MySqlOpt::select_query($sql);
		$category = MySqlOpt::select_query($category_sql);
		if (!isset($category[0]['category']))
		{
			header("Location: /index/notfound");
			return;
		}

		$params = array(
			'article_count' => $count[0]['count'],
			'method' => __METHOD__,
			'title' => $category[0]['category'],
		);

		$params['article_infos'] = $this->getArticleInfos($article_infos);
		$params['page'] = $page;
		$params['limit'] = $this->limit;
		$params['base'] = '/debin/category/'.$category_id;
		echo 'hello';
		$this->predisplay($params);
	}

	public function tagAction($query_params)
	{
		if (empty($query_params) || !is_array($query_params))
		{
			header("Location: /index/notfound");
			return;
		}

		$tag_id = intval($query_params[0]);
		$page = isset($query_params[1]) ? intval($query_params[1]) : 1;

		$count_sql = 'select count(*) as count from'
			.' (select 1 from article_tag_relation'
			.' where tag_id = '.$tag_id.' group by article_id) as A';
		$sql = 'select article.*, tags.tag_name'
			.' from article, article_tag_relation, tags'
			.' where article.article_id = article_tag_relation.article_id'
			.' and tags.tag_id = article_tag_relation.tag_id'
			.' and tags.tag_id = '.$tag_id
			.' order by inserttime desc'
			.' limit '.(($page-1)*$this->limit).', '.$this->limit;
		$tag_sql = 'select tag_name from tags where tag_id = '.$tag_id;
		$count = MySqlOpt::select_query($count_sql);
		$article_infos = MySqlOpt::select_query($sql);
		$tag_name = MySqlOpt::select_query($tag_sql);

		$params = array(
			'article_count' => $count[0]['count'],
			'method' => __METHOD__,
			'title' => $tag_name[0]['tag_name'],
		);

		$params['article_infos'] = $this->getArticleInfos($article_infos);
		$params['page'] = $page;
		$params['limit'] = $this->limit;
		$params['base'] = '/debin/tag/'.$tag_id;
		$this->predisplay($params);
	}

	public function searchAction($query_params)
	{
		$request = $this->getQueryInfo($_REQUEST);
		$request['category'] == 'mood' ? $this->searchMood($request)
			: $this->searchArticle($request);
	}

	public function moodAction($query_params)
	{
	}

	public function searchArticle($request)
	{
		$where_str = $this->getWhere($request, false);
		$count_sql = 'select count(*) as count from article where 1'
			.$where_str;
		$article_sql = 'select * from article where 1'.$where_str
			.' limit '.$request['start'].', '.$this->limit;
		$count = MySqlOpt::select_query($count_sql);
		$article_infos = MySqlOpt::select_query($article_sql);

		$params = array(
			'article_count' => $count[0]['count'],
			'method' => __METHOD__,
			'title' => '检索结果',
		);

		$params['article_infos'] = $this->getArticleInfos($article_infos);
		$params['page'] = $request['page'];
		$params['limit'] = $this->limit;
		$params['opt_type'] = $request['opt_type'];
		$params['category'] = $request['category'];
		$params['tags'] = $request['tags'];
		$params['search'] = $request['search'];
		$this->predisplay($params);
	}

	public function searchMood($request)
	{
	}

	public function getQueryInfo($input)
	{
		$query_info['page'] = isset($input['page']) ? intval($input['page']) : 1;
		if ($query_info['page'] < 1)
			$query_info['page'] = 1;

		$query_info['limit'] =
			isset($input['limit']) ? intval($input['limit']) : $this->limit;

		if ($query_info['limit'] < 1)
			$query_info['limit'] = 1;

		$query_info['tags']		=
			isset($input['tags']) ? $input['tags'] : '';
		$query_info['start']	=
			($query_info['page'] - 1) * $query_info['limit'];
		$query_info['search']	=
			isset($input['search']) ? $input['search'] : '';
		$query_info['category'] =
			isset($input['category']) ? $input['category'] : '';
		$query_info['opt_type'] =
			isset($input['opt_type']) ? $input['opt_type'] : 'content';

		return $query_info;
	}

	public function getWhere($request, $ismood = false)
	{
		$dates = array();
		$tag_ids = array();
		$where_str = '';
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
					$tag_ids[] = mysql_escape_string($tag_infos[2]);
					break;
				case 'date':
					$tag_infos[2][4] = '-';
					$dates[] = $tag_infos[2];
					break;
				default:
					break;
				}
			}
			if (!$ismood && (!$this->root || $request['opt_type'] != 'all'))
				$where_str .= ' and category_id < 5';
			else if (!$this->is_root)
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
				$search_ret = $this->sphinx->query($key, $request['opt_type']);

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
			$where_str .= ' and article_id in ('.implode(',', $article_ids).')';
		}
		return $where_str;
	}

	private function predisplay($params)
	{
		$params['new_articles'] = $this->getNewArticles();
		$params['hot_articles'] = $this->getHotArticles();
		$params['rand_tags'] = $this->getRandTags();
		$this->display(__CLASS__.'::listAction', $params);
	}

	private function getNewArticles()
	{
		$sql = 'select title, article_id from article where category_id < 5'
			.' order by updatetime desc limit 10';
		$new_articles = MySqlOpt::select_query($sql);
		return $new_articles;
	}

	private function getHotArticles()
	{
		$sql = 'select title, article_id from article where category_id < 5'
			.' order by access_count desc limit 10';
		$hot_articles = MySqlOpt::select_query($sql);
		return $hot_articles;
	}

	private function getRandTags()
	{
		$sql = 'select * from `tags`'
			.' where tag_id >= (select floor( max(tag_id) * rand()) from `tags` )'
			.' order by tag_id limit 20';
		$rand_tags = MySqlOpt::select_query($sql);
		return $rand_tags;
	}

	private function getArticleInfos($article_infos, $is_moode = false)
	{
		$ret = array();
		foreach($article_infos as $infos)
		{
			$ret_infos = array();

			preg_match('/^(?<month>\d{4}-\d{2})-(?<date>\d{2})/is',
				$infos['inserttime'], $arr);
			$ret_infos['month'] = str_replace('-', '/', $arr['month']);
			$ret_infos['date'] = $arr['date'];

			$tags = TechlogTools::get_tags($infos['article_id']);
			if (is_array($tags) && count($tags) > 4)
				$ret_infos['tags'] = array_slice( $tags, 0, 4);

			$contents = TechlogTools::pre_treat_article($infos['draft']);
			$imgpath = StringOpt::spider_string($contents, 'img<![&&]>src="', '"');
			if ($imgpath == null)
			{
				$ret_infos['contents'] = strip_tags($contents);
				$ret_infos['contents'] = mb_substr($ret_infos['contents'], 0, 500, 'utf-8');
			}
			else
			{
				$ret_infos['contents'] =
					'<p><a href="article.php?id='.$infos['article_id'].'" target="_blank">'
					.'<img class="img-thumbnail" alt="200x200" style="height: 200px;"'
					.' src="'.$imgpath.'"></a></p><br /><p>'
					.mb_substr(strip_tags($contents), 0, 100, 'utf-8')
					.'</p>';
			}

			$ret_infos['title'] = $infos['title'];
			$ret_infos['article_id'] = $infos['article_id'];
			$ret[] = $ret_infos;
		}
		return $ret;
	}

	private function getSphinx()
	{
		$sphinx = new SphinxClient();
		$sphinx->setServer("localhost", 9312);
		$sphinx->setMatchMode(SphinxClient::SPH_MATCH_PHRASE);
		$sphinx->setLimits(0, 1000);
		$sphinx->setMaxQueryTime(30);

		return $sphinx;
	}
}
?>
