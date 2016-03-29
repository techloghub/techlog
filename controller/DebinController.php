<?php
class DebinController extends Controller
{
	private $sphinx;
	protected $limit;

	public function __construct()
	{
		parent::__construct();
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

		$count = Repository::findCountFromArticle(
			array(
				'eq' => array('category_id' => $category_id)
			)
		);
		$articles = Repository::findFromArticle(
			array(
				'eq' => array('category_id' => $category_id),
				'order' => array('inserttime' => 'desc'),
				'range' => array(($page-1)*$this->limit, $this->limit),
			)
		);
		$category = Repository::findCategoryFromCategory(
			array(
				'eq' => array('category_id' => $category_id),
			)
		);
		if ($category == false)
		{
			header("Location: /index/notfound");
			return;
		}

		$params = array(
			'page'	=> $page,
			'base'	=> '/debin/category/'.$category_id,
			'limit'	=> $this->limit,
			'title'	=> $category,
			'method'	=> __METHOD__,
			'article_count'	=> $count,
			'article_infos'	=> $this->getArticleInfos($articles),
		);
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

		$count = SqlRepository::getArticleCountByTagId($tag_id);
		$articles = SqlRepository::getArticlesByTagId(
			$tag_id, array(($page-1)*$this->limit, $this->limit));
		$tag_name = Repository::findTagNameFromTags(
			array('eq' => array('tag_id' => $tag_id))
		);

		$params = array(
			'page'	=> $page,
			'base'	=> '/debin/tag/'.$tag_id,
			'limit'	=> $this->limit,
			'title'	=> $tag_name,
			'method'	=> __METHOD__,
			'article_count'	=> $count,
			'article_infos'	=> $this->getArticleInfos($articles),
		);

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
		if (!$this->is_root)
		{
			header("Location: /index/notfound");
			return;
		}
		$page = (isset($query_params[0]) and intval($query_params[0])) > 0 ?
			intval($query_params[0]) : 1;

		$count = Repository::findCountFromMood();
		$moods = Repository::findFromMood(
			array(
				'order' => array('inserttime' => 'desc'),
				'range' => array(($page-1)*$this->limit, $this->limit)
			)
		);

		$params = array(
			'page'	=> $page,
			'base'	=> '/debin/mood',
			'limit'	=> $this->limit,
			'title'	=> '心情小说',
			'ismood'	=> true,
			'method'	=> __METHOD__,
			'article_count'	=> $count,
			'article_infos'	=> $this->getMoodInfos($moods),
		);
		$this->predisplay($params);
	}

	public function searchArticle($request)
	{
		$request['limit'] = $this->limit;
		$request['ismood'] = false;
		$request['isroot'] = $this->is_root;
		$count = SqlRepository::getArticleMoodCountByRequest($request);
		$articles = SqlRepository::getArticleMoodByRequest($request);

		$params = array(
			'page'	=> $request['page'],
			'tags'	=> $request['tags'],
			'limit'	=> $this->limit,
			'title'	=> '检索结果',
			'method'	=> __METHOD__,
			'search'	=> $request['search'],
			'opt_type'	=> $request['opt_type'],
			'category'	=> $request['category'],
			'article_count'	=> $count,
			'article_infos'	=> $this->getArticleInfos($articles),
		);

		$this->predisplay($params);
	}

	public function searchMood($request)
	{
		$request['limit'] = $this->limit;
		$request['ismood'] = true;
		$request['isroot'] = $this->is_root;
		$count = SqlRepository::getArticleMoodCountByRequest($request);
		$moods = SqlRepository::getArticleMoodByRequest($request);

		$params = array(
			'page'	=> $request['page'],
			'tags'	=> $request['tags'],
			'limit'	=> $this->limit,
			'title'	=> '检索结果',
			'ismood'	=> true,
			'method'	=> __METHOD__,
			'search'	=> $request['search'],
			'opt_type'	=> $request['opt_type'],
			'category'	=> $request['category'],
			'article_count'	=> $count,
			'article_infos'	=> $this->getMoodInfos($moods),
		);
		$this->predisplay($params);
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

	private function predisplay($params)
	{
		$params['new_articles'] = $this->getNewArticles();
		$params['hot_articles'] = $this->getHotArticles();
		$params['rand_tags'] = SqlRepository::getRandTags();
		$this->display(__CLASS__.'::listAction', $params);
	}

	private function getNewArticles()
	{
		$new_articles = Repository::findFromArticle(
			array(
				'lt' => array('category_id' => 5),
				'order' => array('updatetime' => 'desc'),
				'range' => array(0, 10)
			)
		);
		return $new_articles;
	}

	private function getHotArticles()
	{
		$hot_articles = Repository::findFromArticle(
			array(
				'lt' => array('category_id' => 5),
				'order' => array('access_count' => 'desc'),
				'range' => array(0, 10)
			)
		);
		return $hot_articles;
	}

	public function getMoodInfos($moods)
	{
		$ret_infos = array();
		foreach ($moods as $mood)
		{
			$tmp_infos = array(
				'title'	=> $mood->get_contents(),
				'contents'	=> $mood->get_inserttime(),
			);
			preg_match('/^(?<month>\d{4}-\d{2})-(?<date>\d{2})/is',
				$mood->get_inserttime(), $arr);
			$tmp_infos['month'] = str_replace('-', '/', $arr['month']);
			$tmp_infos['date'] = $arr['date'];
			$ret_infos[] = $tmp_infos;
		}
		return $ret_infos;
	}

	private function getArticleInfos($articles, $is_moode = false)
	{
		if (empty($articles))
			return array();
		$ret = array();
		foreach($articles as $article)
		{
			$ret_infos = array();

			preg_match('/^(?<month>\d{4}-\d{2})-(?<date>\d{2})/is',
				$article->get_inserttime(), $arr);
			$ret_infos['month'] = str_replace('-', '/', $arr['month']);
			$ret_infos['date'] = $arr['date'];

			$tags = SqlRepository::getTags($article->get_article_id());
			if (is_array($tags))
				$ret_infos['tags'] = array_slice( $tags, 0, 4);

			$contents = TechlogTools::pre_treat_article($article->get_draft());
			$imgpath = StringOpt::spider_string($contents, 'img<![&&]>src="', '"');
			if ($imgpath == null)
			{
				$ret_infos['contents'] = strip_tags($contents);
				$ret_infos['contents'] = mb_substr($ret_infos['contents'], 0, 600, 'utf-8');
			}
			else
			{
				$ret_infos['contents'] =
					'<p><a href="/article/list/'.$article->get_article_id().'" target="_blank">'
					.'<img class="img-thumbnail" alt="200x200" style="height: 200px;"'
					.' src="'.$imgpath.'"></a></p><br /><p>'
					.mb_substr(strip_tags($contents), 0, 100, 'utf-8')
					.'</p>';
			}

			$ret_infos['title'] = $article->get_title();
			$ret_infos['article_id'] = $article->get_article_id();
			$ret[] = $ret_infos;
		}
		return $ret;
	}
}
?>
