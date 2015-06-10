<?php
class Controller
{
	public $is_root = false;

	public function __construct()
	{
		$conf = file_get_contents('/etc/zeyu203/techlog.conf');
		$conf = unserialize(base64_decode($conf));

		if (isset($_COOKIE["LoginInfo"])
			&& $_COOKIE["LoginInfo"] == $conf['admin']['logininfo']
		)
		{
			setcookie('LoginInfo', $conf['admin']['logininfo'], time()+1800);
			$this->is_root = true;
		}
	}

	public function display($func, $params)
	{
		$params['background'] = $this->is_root ?
			'images/183755241795a6aac850b8.jpg'
			: 'images/17183518883b16614c2fe8.jpg';

		$pattern = '/^(?<class>.+)Controller::(?<func>.+)Action/is';
		if (preg_match($pattern, $func, $arr) == false)
		{
			header('Location: /index/notfound');
			exit;
		}
		$class = strtolower($arr['class']);
		$func = strtolower($arr['func']);
		$file = $class.'/'.$func.'.php';

		if ($func == 'notfound')
		{
			header('HTTP/1.1 404 Not Found');
			header("status: 404 Not Found");
		}

		$params['is_root'] = $this->is_root;

		if (empty($class) || empty($func) || !file_exists(VIEW_PATH.'/'.$file))
			header("Location: /index/notfound");
		else
			require(VIEW_PATH.'/'.$file);
	}
}
?>
