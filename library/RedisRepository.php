<?php
class RedisRepository {
    /**
     * @var Redis
     */
	private static $redis;

	private static function connect() {
		if (!empty(self::$redis)) {
			return;
		}

		$config = file_get_contents(CONF_PATH.'/config.json');
		$config = json_decode($config, true);
		if (empty($config)) {
			echo 'ERROR: CONFIG ERROR'.PHP_EOL;
			exit;
		}
		$config = $config['redis'];

		try {
			self::$redis = new Redis();
			self::$redis->connect($config['host'], $config['port']);
			self::$redis->auth($config['password']);
		} catch (Exception $e) {
			echo '缓存连接失败 '.$e->getMessage();
			exit;
		}
	}

	public static function setIpCache($ip) {
		self::connect();
		return self::$redis->sadd('ipset', $ip);
	}

	public static function getAllUV() {
		self::connect();
		return self::$redis->scard('ipset');
	}

	public static function getAuthcode() {
		self::connect();
		return self::$redis->get('authcode');
	}

	public static function setAuthcode($code) {
		self::connect();
		return self::$redis->set('authcode', $code);
	}
}
