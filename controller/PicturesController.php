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
		$query_params = $this->getQueryParams($request);
		$count = Repository::findCountFromImages($query_params);

		$start = ($page-1)*$this->limit;
		$query_params['order'] = array('inserttime' => 'desc');
		$query_params['range'] = array($start, $this->limit);
		$images = Repository::findFromImages($query_params);

		$category_list = array(
			'all',
			'icon',
			'mood',
			'article',
			'earnings',
			'booknote',
			'background',
		);

		$params = array(
			'page'	=> $page,
			'count'	=> $count,
			'images'	=> $images,
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
		$file = '/home/zeyu/Documents/techlog_images/images/'.$request['name'];
		$ret = copy($_FILES["file"]["tmp_name"], $file);
		if ($ret === false)
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
	private function getQueryParams($request)
	{
		$params = array();
		foreach ($request as $key => $value)
		{
			if (empty($value))
				continue;
			if ($key == 'start_time')
				$params['ge']['inserttime'] = $value;
			else if ($key == 'end_time')
				$params['le']['inserttime'] = $value;
			else
				$params['eq'][$key] = $value;
		}
		return $params;
	}
}
?>
