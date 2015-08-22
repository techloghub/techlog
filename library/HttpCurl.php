<?php
class HttpCurl
{
	private static $handle = null;
	private static $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/3.0.195.32 Safari/532.0';
    private static $max_redirects = 8;
	private static $connect_timeout = 20;
	private static $http_header = null;

    private function __clone()
    {
        //ban on cloning!!!
    }

    private function __construct()
    {
        //ban on construct!!!
    }

	public static function curlInit()
	{
		if (!empty(self::$handle))
			curl_close(self::$handle);
		self::$user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US)'
			.' AppleWebKit/532.0 (KHTML, like Gecko)'
			.' Chrome/3.0.195.32 Safari/532.0';
		self::$max_redirects = 8;
		self::$connect_timeout = 20;
		self::$http_header = null;

		self::$handle = curl_init();
        curl_setopt(self::$handle, CURLOPT_USERAGENT, self::$user_agent);
        curl_setopt(self::$handle, CURLOPT_CONNECTTIMEOUT, self::$connect_timeout);
        curl_setopt(self::$handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt(self::$handle, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt(self::$handle, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt(self::$handle, CURLOPT_FILETIME, TRUE);
        curl_setopt(self::$handle, CURLOPT_MAXREDIRS, self::$max_redirects);
        curl_setopt(self::$handle, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt(self::$handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt(self::$handle, CURLOPT_HEADERFUNCTION, 'self::header_handler');
        curl_setopt(self::$handle, CURLOPT_HEADER, FALSE);
        #curl_setopt(self::$handle, CURLOPT_ENCODING, 'gzip,deflate');
	}

	public static function __callStatic($name, $params)
	{
		$method = strtoupper($name);
		$url = $params[0];
		$params = (isset($params[1]) ? $params[1] : null);

		if (empty(self::$handle))
			self::curlInit();

		curl_setopt(self::$handle, CURLOPT_PUT, FALSE);
		curl_setopt(self::$handle, CURLOPT_POST, FALSE);

		switch ($method)
		{
		case 'POST':
			curl_setopt(self::$handle, CURLOPT_POST, TRUE);
			curl_setopt(self::$handle, CURLOPT_POSTFIELDS, $params);
			break;
		case 'PUT':
			curl_setopt(self::$curl_handle, CURLOPT_PUT, TRUE);
			if($params != null)
			{
				$temp_file = tmpFile();
				fwrite($temp_file, $params);
				fseek($temp_file, 0);
				curl_setopt(self::$curl_handle, CURLOPT_INFILE, $temp_file);
				curl_setopt(self::$curl_handle, CURLOPT_INFILESIZE, strlen($params));
			}
			break;
		case 'GET':
			if (!empty($params))
			{
				if (is_array($params))
				{
					$params_arr = array();
					foreach ($params as $key=>$value)
					{
						$params_arr[] = $key.'='.$value;
					}
					$params = implode('&', $params_arr);
				}
				$url .= '?'.$params;
			}
			break;
		default:
			break;
		}
		curl_setopt(self::$handle, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt(self::$handle, CURLOPT_URL, $url);
		$ret = array();
		$ret['body'] = curl_exec(self::$handle);
        $ret['http_code'] = curl_getinfo(self::$handle, CURLINFO_HTTP_CODE);
		$ret['http_info'] = curl_getinfo(self::$handle);
		if ($ret['body'] == false)
			$ret['error'] = curl_error(self::$handle);
		$ret['header'] = self::$http_header;

		return $ret;
	}

    private static function header_handler($curl_handle, $header)
    {
		self::$http_header = array();
        $i = strpos($header, ':');
        if(!empty($i))
        {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            if(isset(self::$http_header[$key]))
            {
				self::$http_header[$key] .= ';' . $value;
            }
            else
            {
				self::$http_header[$key] = $value;
            }
        }

        return strlen($header);
    }

	public static function set_authorize($username = null, $password = null)
	{
		if (empty(self::$handle) || empty($username) || empty($password))
		{
			self::curlInit();
		}
		else
        {
            curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($handle, CURLOPT_USERPWD, "{$username}:{$password}");
        }
	}

	public static function set_header($header = null)
	{
		if (empty(self::$handle))
			self::curlInit();
		curl_setopt(self::$handle, CURLOPT_HTTPHEADER, $header);
	}

	public static function set_cookie($cookie = null)
	{
		if (empty(self::$handle))
			self::curlInit();
		if (empty($cookie) || is_string($cookie))
			$set_cookie = $cookie;
		else if (is_array($cookie))
		{
			$cookie_arr = array();
			foreach ($cookie as $key => $value)
				$cookie_arr[] = $key.'='.$value;
			$set_cookie = implode(';', $cookie_arr);
		}
		curl_setopt(self::$handle, CURLOPT_COOKIE, $set_cookie);
	}
}

$ret = HttpCurl::get('http://techlog.cn');
var_dump($ret);
?>
