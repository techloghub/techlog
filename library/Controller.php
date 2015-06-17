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
			setcookie('LoginInfo', $conf['admin']['logininfo'], time()+1800, '/');
			$this->is_root = true;
		}
		else
		{
			$this->record_access();
		}
	}

	public function record_access()
	{
		if (isset($_SERVER['REMOTE_ADDR']))
			$remote_host	= $_SERVER['REMOTE_ADDR'];
		else
			$remote_host	= "-";
		if (isset($_SERVER['HTTP_REFERER']))
			$referer	= $_SERVER['HTTP_REFERER'];
		else
			$referer	= "-";
		if (isset($_SERVER['HTTP_USER_AGENT']))
			$user_agent	= $_SERVER['HTTP_USER_AGENT'];
		else
			$user_agent	= "-";

		$infos = array(
			'time_str' => 'now()',
			'remote_host' => $remote_host,
			'request' => "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
			'referer' => $referer,
			'user_agent' => $user_agent,
		);

		MySqlOpt::insert('stats', $infos);
	}

	public function mysql_protect($s)
	{
		return "\"" . mysql_escape_string ($s) . "\"";
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

		$params['is_root'] = $this->is_root;

		if (empty($class) || empty($func) || !file_exists(VIEW_PATH.'/'.$file))
			header("Location: /index/notfound");
		else
			require(VIEW_PATH.'/'.$file);
	}
}
?>
