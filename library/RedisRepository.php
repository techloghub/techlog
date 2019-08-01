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

	public static function setBaiduAccessToken($access_token, $fresh_token, $timeout) {
	    self::connect();
	    self::$redis->set('baidu_access_token', $access_token, array('EX' => $timeout));
        return self::$redis->set('baidu_refresh_token', $fresh_token);
    }

    public static function getBaiduAccessToken() {
	    $access_token = self::$redis->get('baidu_access_token');
	    if (empty($access_token)) {
            $config = file_get_contents(CONF_PATH.'/config.json');
            $config = json_decode($config, true);

	        $refresh_token = self::$redis->get('baidu_refresh_token');
	        $url = 'https://openapi.baidu.com/oauth/2.0/token?grant_type=refresh_token&refresh_token='.$refresh_token
                .'&client_id='.$config['baidu']['client_id'].'&client_secret='.$config['baidu']['client_secret'];
	        $ret = HttpCurl::get($url);
            $body = json_decode($ret['body'], true);
            self::setBaiduAccessToken($body['access_token'], $body['refresh_token'], $body['expires_in']);
            return $body['access_token'];
        }
	    return $access_token;
    }
}
