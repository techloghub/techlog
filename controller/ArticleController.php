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
