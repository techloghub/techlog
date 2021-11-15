<?php
class Dispatcher
{
	public static $rewriteRule = array(
		array(
			'pattern'	=> '/^\/html\/?(index\.php)?$/i',
			'replace'	=> '/',
		),
		array(
			'pattern'	=> '/^.*article\.php\?id=(\d+)$/i',
			'replace'	=> '/article/list/$1',
		),
		array(
			'pattern'	=> '/^.*debin\.php\?category=0\&tags=icon_tag_(\d+)$/i',
			'replace'	=> '/debin/tag/$1/1',
		),
		array(
			'pattern'	=> '/^.*debin\.php\?category=(\d+)$/i',
			'replace'	=> '/debin/category/$1/1',
		),
		array(
			'pattern'	=> '/^.*debin\.php\?category=mood$/i',
			'replace'	=> '/debin/mood/1',
		),
		array(
			'pattern'	=> '/^.*note\.php$/i',
			'replace'	=> '/note',
		),
	);

	private function __construct()
	{
		define('URI', isset($_SERVER['REQUEST_URI']) ?
			$_SERVER['REQUEST_URI'] : '');
		define('REDIRECT', isset($_SERVER['REDIRECT_URL']) ?
			$_SERVER['REDIRECT_URL'] : '');
		define('HTTP_HOST', strtolower($_SERVER['HTTP_HOST']));
	}

	public static function getInstance($debug = null)
	{
		if (!empty($debug))
		{
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
		} else {
			ini_set('display_errors', 0);
		}

		if (!empty($loader))
			return $loader;
		$loader = new self;
		return $loader;
	}

	public function dispatch()
	{
		$this->rewrite();
		list($class, $func, $params) = $this->parseUrl();

		$obj_exsists = false;
		if (class_exists($class))
		{
			$obj = new $class();
			if (method_exists($obj, $func))
			{
				$ret = $obj->$func($params);
                if (!empty($ret)) {
                    $ret = (is_string($ret) ? $ret : json_encode($ret));
                    echo $ret;
                }
				$obj_exsists = true;
			}
			else if (method_exists($obj, $func.'Ajax'))
			{
				$func = $func.'Ajax';
				if (!$this->checkAjax())
				{
					echo '{"code":1, "msg":"ERROR: MUST BE AN AJAX REQUEST"}';
					exit;
				}
				$ret = $obj->$func($params);
				$ret = empty($ret) ?
					'' : (is_string($ret) ? $ret : json_encode($ret));
				echo $ret;
				$obj_exsists = true;
			}
		}
		if (!$obj_exsists)
		{
			header("Location: /index/notfound");
			exit;
		}
	}

	protected function checkAjax()
	{
		if(isset($_SERVER["HTTP_X_REQUESTED_WITH"])
			&& strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest"
		)
			return true;
		return false;
	}

	protected function rewrite()
	{
		foreach (self::$rewriteRule as $rule)
		{
			$count = 0;
			$result = preg_replace(
				$rule['pattern'], $rule['replace'],
				URI, -1, $count
			);
			if ($count)
			{
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: '.$result);
				exit;
			}
		}
	}

	protected function parseUrl()
	{
		$pattern = '/^\/'.'(?<class>[^\/?]+)?'.'\/?'
			.'(?<func>[^\/?]+)?'.'\/?'
			.'(?<params>[^\/?]+(\/[^\/?]+)*)?'.'/is';
		if (preg_match($pattern, URI, $uri_infos) == false)
		{
			header('Location: /index/notfound');
			exit;
		}

		$uri_infos['class'] = isset($uri_infos['class']) ?
			ucfirst(strtolower($uri_infos['class'])).'Controller' : 'IndexController';
		$uri_infos['func'] = isset($uri_infos['func']) ?
			StringOpt::unlinetocamel($uri_infos['func']).'Action' : 'listAction';
		$uri_infos['params'] = isset($uri_infos['params']) ?
			explode('/', $uri_infos['params']) : array();

		return array($uri_infos['class'], $uri_infos['func'], $uri_infos['params']);
	}
}
