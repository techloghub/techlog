<?php
class Controller
{
	public $is_root = false;

	public function __construct()
	{
		$config = file_get_contents(APP_PATH.'/config.json');
		$config = json_decode($config, true);

		if (isset($_COOKIE["LoginInfo"])
			&& $_COOKIE["LoginInfo"] == $config['admin']['logininfo']
		)
		{
			setcookie('LoginInfo', $config['admin']['logininfo'], time()+1800, '/');
			setcookie('unick', base64_encode('博主'), time()+1800, '/');
			setcookie('uemad', base64_encode('zeyu203@qq.com'), time()+1800, '/');
			$this->is_root = true;
		}
		else
		{
			$this->record_access();
		}
	}

	public function record_access()
	{
		$remote_host = isset($_SERVER['REMOTE_ADDR']) ?
			$_SERVER['REMOTE_ADDR'] : '-';
		$referer = isset($_SERVER['HTTP_REFERER']) ?
			$_SERVER['HTTP_REFERER'] : '-';
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ?
			$_SERVER['HTTP_USER_AGENT'] : '-';
		
		$stats = new StatsModel(
			array(
				'time_str' => 'now()',
				'remote_host' => $remote_host,
				'request' => "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
				'referer' => $referer,
				'user_agent' => $user_agent,
			)
		);

		Repository::persist($stats);
		RedisRepository::setIpCache($remote_host);
	}

	public function display($func, $params)
	{
		$params['background'] = $this->is_root ?
			'url(images/17183518883b16614c2fe8.jpg)'
			: 'url(images/37fa8df7da45fd6c8a44a1664021a5df.jpg)';
		if (TechlogTools::isMobile())
		{
			$params['background'] = '#F2F0F1';
		}

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
		$params['is_mobile'] = TechlogTools::isMobile();

		if (empty($class) || empty($func) || !file_exists(VIEW_PATH.'/'.$file))
			header("Location: /index/notfound");
		else
			require(VIEW_PATH.'/'.$file);
	}
}
?>
