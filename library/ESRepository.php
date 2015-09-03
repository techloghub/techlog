<?php
class ESRepository
{
	private static $patterns = array(
		array(
			'pattern' => '/^get(?<index>(.*))List$/',
			'method' => 'getList'
		),
		array(
			'pattern' => '/^get(?<field>(.*))$/',
			'method' => 'getField'
		)
	);
	public static function __callStatic($name, $params)
	{
		$infos = array();
		foreach (self::$patterns as $pattern_infos)
		{
			if (preg_match($pattern_infos['pattern'], $name, $infos) === 1)
			{
				return self::$pattern_infos['method']($infos, $params);
			}
		}
	}

	private static function getList($infos, $params)
	{
		$infos = StringOpt::cameltounline($infos['index']);
		$infos = explode('_', $infos);
		if (empty($infos[0]) || empty($infos[1]))
			return false;
		$index = $infos[0];
		$type = $infos[1];

		$url = 'http://localhost:9200/'.$index.'/'.$type.'/_search';
		$ret = HttpCurl::get($url, json_encode($params[0]));
		$body = json_decode($ret['body'], true);
		if ($body == false || empty($body['hits']['total']))
			return false;
		return $body;
	}
}
?>
