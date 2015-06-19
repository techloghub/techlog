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
		if (!$this->is_root)
		{
			header("Location: /index/notfound");
			return;
		}

		$url = '/pictures';
		if ($_FILES["file"]["error"] > 0)
		{
			$this->display('IndexController::notfoundAction', array(
					'msg'	=> 'Error: '.$_FILES["file"]["error"],
					'url'	=> $url,
				)
			);
		}

		$params_key = array('insert_id', 'insert_category');
		$request = $this->getParams($_REQUEST, $params_key);
		$request['insert_id'] = intval($request['insert_id']) >= 0
			? intval($request['insert_id']) : null;
		$request['name'] = trim($_FILES["file"]["name"]);
		$file = '/home/zeyu/Documents/images/'.$request['name'];
		$ret = copy($_FILES["file"]["tmp_name"], $file);
		if ($ret == false)
		{
			$this->display('IndexController::notfoundAction', array(
					'msg'	=> '临时文件不存在',
					'url'	=> $url,
				)
			);
		}

		$ret = TechlogTools::picture_insert(
			$request['name'],
			$request['insert_category'],
			$request['insert_id']
		);

		switch ($ret)
		{
		case -1:
			$message = '源文件不存在';
			break;
		case -2:
			$message = '文件替换失败，请查看权限';
			break;
		case -3:
			$message = '目录创建失败，请查看权限';
			break;
		case -4:
			$message = '指定被替换文件 ID 不存在';
			break;
		case -5:
			$message = '文件添加失败，请查看权限';
			break;
		default:
			$message = '文件添加成功';
			$url .= '?image_id='.$ret;
		}
		$this->display('IndexController::notfoundAction', array(
				'msg'	=> $message,
				'url'	=> $url,
			)
		);
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