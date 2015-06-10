<?php
require_once(dirname(__FILE__) . '/../' . 'resource/require/dbdriver.php');

class MySqlOpt
{
	private static $db_client = null;

	public static function get_db_client()
	{
		if (self::$db_client === null)
		{
			$conf = file_get_contents('/etc/zeyu203/techlog.conf');
			$conf = unserialize(base64_decode($conf));
			$conf = $conf['database'];

			self::$db_client =
				new DbDriver(
					$conf['host'],
					$conf['db'],
					$conf['user'],
					$conf['pwd']
				);

			self::$db_client->query('set names utf8');
		}

		if (self::$db_client === null)
			return false;

		return self::$db_client;
	}

	public static function errno()
	{
		if (self::get_db_client() === false)
			return false;
		return self::$db_client->errno();
	}

	public static function error()
	{
		if (self::get_db_client() === false)
			return false;
		return self::$db_client->error();
	}

	public static function select_query($sql)
	{
		if (self::get_db_client() === false)
			return false;
		return self::$db_client->executeRead($sql);
	}

	public static function update_query($sql)
	{
		if (self::get_db_client() === false)
			return false;
		return self::$db_client->query($sql);
	}

	public static function insert ($table, $infos, $id_flag=false)
	{
		if (self::get_db_client() === false)
			return false;

		$cmd_list = array('now()');

		$keys = array_keys($infos);
		foreach($keys as $i=>$key)
		{
			$key = self::$db_client->escape_string($key);
			$keys[$i] = $key;
		}
		$key_str = implode(', ', $keys);
		$values = array();
		foreach($infos as $value)
		{
			if(in_array($value, $cmd_list))
			{
				$values[] = $value;
				continue;
			}
			$values[] = '"' . self::$db_client->escape_string($value) . '"';
		}
		$value_str = implode(', ', $values);
		$sql = "insert into $table ($key_str) values ($value_str)";
		$ret = self::$db_client->query($sql);

		if($ret === true && $id_flag === true)
		{
			return self::$db_client->insert_id();
		}

		return $ret;
	}

	public static function update ($table, $infos, $where_infos=array())
	{
		if (self::get_db_client() === false)
			return false;

		$cmd_list = array('now()');

		$values = array();
		foreach ($infos as $key=>$value)
		{
			$key = self::$db_client->escape_string($key);
			$value = self::$db_client->escape_string($value);
			if (in_array($value, $cmd_list))
				$values[] = $key.'='.$value;
			else
				$values[] = $key.'="'.$value.'"';
		}
		$value_str = implode(', ', $values);
		$where_str = self::get_where_string ($where_infos);
		$sql = sprintf('update %s set %s', $table, $value_str);
		if ($where_str != false)
			$sql .= $where_str;

		$ret = self::$db_client->query($sql);

		return $ret;
	}

	private static function get_where_string ($where_infos)
	{
		if (!is_array($where_infos) || count($where_infos) <= 0)
			return false;
		foreach ($where_infos as $key=>$value)
		{
			$key = self::$db_client->escape_string($key);
			$value = self::$db_client->escape_string($value);
			$where_arr[] = "$key = '$value'";
		}
		$where_str = implode(' and ', $where_arr);
		return ' where '.$where_str;
	}
}
?>
