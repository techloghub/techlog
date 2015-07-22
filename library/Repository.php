<?php
require_once (__DIR__.'/../app/register.php');
require_once(LIB_PATH.'/TechlogTools.php');

class Repository
{
	private static $dbfd;
	private static $debug;
	private static $pdo_instance;
	private static $table;

	private static function dbConnect()
	{
		if (!empty(self::$pdo_instance))
			return;

		if (empty(self::$dbfd))
			$dbfd = 'db';
		if (empty(self::$debug))
			self::$debug = false;
		$mode = self::$debug ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;

		$config = file_get_contents(APP_PATH.'/config.json');
		$config = json_decode($config, true);
		if (empty($config))
		{
			echo 'ERROR: CONFIG ERROR'.PHP_EOL;
			exit;
		}

		$mysql_config = 'mysql:'
			.'host='.$config[self::$dbfd]['host'].';'
			.'dbname='.$config[self::$dbfd]['dbname'];
		self::$pdo_instance = new PDO($mysql_config,
			$config[self::$dbfd]['username'], $config[self::$dbfd]['password'],
			array(PDO::ATTR_ERRMODE => $mode)
		);
		self::$pdo_instance->exec('set names utf8');
	}

	public static function setTable($table)
	{
		self::dbConnect();
		self::$table = $table;
	}

	public static function getTable()
	{
		self::dbConnect();
		return self::$table;
	}

	public static function setdbfd($dbfd)
	{
		self::$dbfd = $dbfd;
		self::$pdo_instance = null;
		self::dbConnect();
	}

	public static function setdebug($debug)
	{
		self::$debug = $debug;
		self::$pdo_instance = null;
		self::dbConnect();
	}

	public static function findBy($params)
	{
		self::dbConnect();
		if (empty(self::$table))
			return '{"code":-1, "errmsg":"table empty"}';
		$sql = 'select * from '.self::$table.' where 1';
		$query_params = array();
		if (isset($params['eq']))
		{
			foreach ($params['eq'] as $key=>$value)
			{
				$sql .= ' and '.$key.' = :eq_'.$key;
				$query_params['eq_'.$key] = $value;
			}
		}
		if (isset($params['ne']))
		{
			foreach ($params['ne'] as $key=>$value)
			{
				$sql .= ' and '.$key.' != :ne_'.$key;
				$query_params['ne_'.$key] = $value;
			}
		}
		if (isset($params['in']))
		{
			foreach ($params['in'] as $key=>$values)
			{
				$sql .= ' and '.$key.' in (';
				foreach ($values as $index=>$value)
				{
					if ($index != 0)
						$sql .= ', ';
					$sql .= ':in_'.$key.'_'.$index;
					$query_params['in_'.$key.'_'.$index] = $value;
				}
				$sql .= ')';
			}
		}
		if (isset($params['lt']))
		{
			foreach ($params['lt'] as $key=>$value)
			{
				$sql .= ' and '.$key.' < :lt_'.$key;
				$query_params['lt_'.$key] = $value;
			}
		}
		if (isset($params['gt']))
		{
			foreach ($params['gt'] as $key=>$value)
			{
				$sql .= ' and '.$key.' > :gt_'.$key;
				$query_params['gt_'.$key] = $value;
			}
		}
		if (isset($params['le']))
		{
			foreach ($params['le'] as $key=>$value)
			{
				$sql .= ' and '.$key.' <= :le_'.$key;
				$query_params['le_'.$key] = $value;
			}
		}
		if (isset($params['ge']))
		{
			foreach ($params['ge'] as $key=>$value)
			{
				$sql .= ' and '.$key.' >= :ge_'.$key;
				$query_params['ge_'.$key] = $value;
			}
		}
		if (isset($params['order']))
		{
			foreach ($params['order'] as $key=>$value)
				$sql .= ' order by '.$key.' '.$value;
		}
		if (isset($params['range']))
		{
			$sql .= ' limit '.$params['range'][0].','.$params['range'][1];
		}

		$stmt = self::$pdo_instance->prepare($sql);
		if (!empty($query_params))
		{
			foreach ($query_params as $key=>$value)
				$stmt->bindParam(':'.$key, $value);
		}
		$table_class = ucfirst(StringOpt::unlinetocamel(self::$table).'Model');
		$stmt->execute();
		$ret = $stmt->fetchAll(PDO::FETCH_CLASS, $table_class);
		return $ret;
	}

	public static function findOneBy($params)
	{
		self::dbConnect();
		if (!isset($params['range']))
			$params['range'] = array(0, 1);
		$params['range'][1] = 1;
		$objs = self::findBy($params);
		return $objs[0];
	}

	public static function getInstance()
	{
		self::dbConnect();
		return self::$pdo_instance;
	}

	public static function persist($model)
	{
		self::dbConnect();
		return $model->is_set_pri() ? self::update($model) : self::insert($model);
	}

	private static function insert($model)
	{
		$obj_vars = $model->get_model_fields();
		$keys = $params_keys = $query_params = array();
		foreach ($obj_vars as $key)
		{
			$func = 'get_'.$key;
			$value = $model->$func();
			if ($key == $model->get_pri_key())
				continue;
			$keys[] = $key;
			if ($value == 'now()')
			{
				$params_keys[] = 'now()';
			}
			else
			{
				$params_keys[] = ':'.$key;
				$query_params[':'.$key] = $value;
			}
		}
		try
		{
			self::$pdo_instance->setAttribute(
				PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$pdo_instance->beginTransaction();
			$sql = 'insert into '.self::$table
				.' ('.implode(', ', $keys).')'
				.' values ('.implode(', ', $params_keys).')';
			$stmt = self::$pdo_instance->prepare($sql);
			$stmt->execute($query_params);
			self::$pdo_instance->commit();
		}
		catch(PDOExecption $e)
		{
			$dbh->rollback();
			return 'ERROR: '.$e->getMessage();
		}
		return self::$pdo_instance->lastInsertId();
	}

	private static function update ()
	{
		return 'update';
	}
}
?>
