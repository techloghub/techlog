<?php
class ArticleController extends Controller
{
	public function listAction($query_params)
	{
		if (empty($query_params) || !is_array($query_params))
		{
			header("Location: /index/notfound");
			return;
		}

		$params = $this->getArticle(intval($query_params[0]));
		$this->display(__METHOD__, $params);
	}

	public function testAction($query_params)
	{
		if (!$this->is_root)
		{
			header("Location: /index/notfound");
			return;
		}

		$draft_id = (isset($query_params[0]) && intval($query_params[0]) > 0)
			? intval($query_params[0]) : '';
		$contents = file_exists(DRAFT_PATH.'/draft'.$draft_id.'.tpl')
			? TechlogTools::pre_treat_article(
				file_get_contents(DRAFT_PATH.'/draft'.$draft_id.'.tpl')
			) : '';

		if (StringOpt::spider_string(
			$contents, '"page-header"', '</div>') === null)
		{
			$contents = '<div class="page-header"><h1>草稿'
				.$draft_id.'</h1></div>'.$contents;
		}

		$index = TechlogTools::get_index($contents);

		$params = array(
			'tags'	=> array(),
			'title'	=> '测试页面',
			'contents'	=> $contents,
			'inserttime'	=> '',
			'title_desc'	=> '仅供测试',
			'article_category_id'	=> 3,
		);
		if (count($index) > 0)
			$params['index'] = $index;

		$this->display(__CLASS__.'::listAction', $params);
	}

	private function getArticle($article_id)
	{
		$params = array();

		$article_query = 'select * from article where article_id='.$article_id;
		if (!$this->is_root)
			$article_query .= ' and category_id < 5';
		$article_info = MySqlOpt::select_query($article_query);

		if ($article_info == false)
		{
			header("Location: /index/notfound");
			return;
		}
		$article_info = $article_info[0];

		$params['tags']		= TechlogTools::get_tags($article_id);
		$params['title']	= $article_info['title'];
		$params['indexs']	= json_decode($article_info['indexs']);
		$params['contents'] = \
			TechlogTools::pre_treat_article($article_info['draft']);
		$params['title_desc']	= $article_info['title_desc'];
		$params['article_category_id']	= $article_info['category_id'];

		if (
			StringOpt::spider_string(
				$params['contents'],
				'"page-header"',
				'</div>'
			) === null)
		{
			$params['contents'] = '<div class="page-header"><h1>'
				.$article_info['title']
				.'</h1></div>'.$params['contents'];
		}

		$sql = 'update article set access_count=access_count+1'
			.' where article_id = "'.$article_id.'"';
		MySqlOpt::update_query($sql);

		$params['inserttime'] = $article_info['inserttime']
			.'&nbsp;&nbsp;&nbsp;最后更新: '
			.$article_info['updatetime']
			.'&nbsp;&nbsp;&nbsp;访问数量：'
			.($article_info['access_count']+1);

		return $params;
	}
}
?>
