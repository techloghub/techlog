<?php
class PicturesController extends Controller
{
	protected $limit = 10;
	public function listAction($query_params)
	{
		if (!$this->is_root)
		{
			header("Location: /index/notfound");
			return;
		}

		$params_key = array(
			'md5',
			'page',
			'path',
			'category',
			'end_time',
			'image_id',
			'start_time',
		);
		$request = $this->getParams($_REQUEST, $params_key);
		$page = intval($request['page']) > 0 ? intval($request['page']) : 1;
		unset($request['page']);

		$category = $request['category'];
		if ($request['category'] == 'all')
			$request['category'] = '';

		$where_str = $this->getWhere($request);
		$sql = 'select count(*) as count from images where 1'
			.$where_str;
		$count = MySqlOpt::select_query($sql);
		$count = $count[0]['count'];

		$start = ($page-1)*$this->limit;
		$sql = 'select * from images where 1'.$where_str
			.' limit '.$start.', '.$this->limit;
		$infos = MySqlOpt::select_query($sql);

		$sql = 'select category from images group by category';
		$category_infos = MySqlOpt::select_query($sql);
		$category_list = array('all');
		foreach ($category_infos as $cat)
			$category_list[] = $cat['category'];

		$params = array(
			'page'	=> $page,
			'count'	=> $count,
			'infos'	=> $infos,
			'title'	=> '龙潭相册',
			'limit'	=> $this->limit,
			'category'	=> $category,
			'category_list'	=> $category_list,
		);

		foreach ($request as $key=>$value)
			$params[$key] = $value;

		$this->display(__METHOD__, $params);
	}

	public function insertAction()
	{
		if ($_FILES["file"]["error"] > 0)
		{
		}
	}

	private function getParams ($input, $keys)
	{
		$params = array();
		foreach ($keys as $key)
			$params[$key] = isset($input[$key]) ? $input[$key] : '';
		return $params;
	}

	private function getWhere($request)
	{
		$sql = '';
		foreach ($request as $key => $value)
		{
			if (empty($value))
				continue;
			if ($key == 'start_time')
				$sql .= ' and inserttime >= "'.mysql_escape_string($value).'"';
			else if ($key == 'end_time')
				$sql .= ' and inserttime <= "'.mysql_escape_string($value).'"';
			else
				$sql .= ' and '.$key.'="'.mysql_escape_string($value).'"';
		}
		return $sql;
	}
}
?>
