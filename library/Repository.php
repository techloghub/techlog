<?php
require_once (__DIR__.'/../app/register.php');
require_once(LIB_PATH.'/TechlogTools.php');

class Repository
{
	private $dbfd;
	private $debug;
	private $pdo_instance;
	private $table;

	public function __construct($db = 'db', $debug = false)
	{
		$this->dbfd = $db;
		$this->debug = $debug;
		$mode = $this->debug ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
		$this->dbConnect($mode);
	}

	function __destruct()
	{
		$this->pdo_instance = null;
	}

	private function dbConnect($mode = PDO::ERRMODE_EXCEPTION)
	{
		if (!empty($this->pdo_instance))
			return;

		$config = file_get_contents(APP_PATH.'/config.json');
		$config = json_decode($config, true);
		if (empty($config))
		{
			echo 'ERROR: CONFIG ERROR'.PHP_EOL;
			exit;
		}

		$mysql_config = 'mysql:'
			.'host='.$config[$this->dbfd]['host'].';'
			.'dbname='.$config[$this->dbfd]['dbname'];
		$this->pdo_instance = new PDO($mysql_config,
			$config[$this->dbfd]['username'], $config[$this->dbfd]['password'],
			array(PDO::ATTR_ERRMODE => $mode)
		);
		$this->pdo_instance->exec('set names utf8');
	}

	public function setTable($table)
	{
		$this->table = $table;
		return $this;
	}

	public function getTable()
	{
		return $this->table;
	}

	public function setdbfd($dbfd)
	{
		$this->dbfd = $dbfd;
		$this->pdo_instance = null;
		$mode = $this->debug ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
		$this->dbConnect($mode);
	}

	public function findBy($params)
	{
		if (empty($this->table))
			return '{"code":-1, "errmsg":"table empty"}';
		if (empty($this->pdo_instance))
			$this->dbConnect();
		$sql = 'select * from '.$this->table.' where 1';
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

		$stmt = $this->pdo_instance->prepare($sql);
		if (!empty($query_params))
		{
			foreach ($query_params as $key=>$value)
				$stmt->bindParam(':'.$key, $value);
		}
		$table_class = ucfirst(StringOpt::unlinetocamel($this->table).'Model');
		$stmt->execute();
		$ret = $stmt->fetchAll(PDO::FETCH_CLASS, $table_class);
		return $ret;
	}

	public function findOneBy($params)
	{
		if (!isset($params['range']))
			$params['range'] = array(0, 1);
		$params['range'][1] = 1;
		$objs = $this->findBy($params);
		return $objs[0];
	}

	public function getInstance()
	{
		return $this->pdo_instance;
	}

	public function persist($model)
	{
		return $model->is_set_pri() ? $this->update($model) : $this->insert($model);
	}

	private function insert($model)
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
			$this->pdo_instance->setAttribute(
				PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo_instance->beginTransaction();
			$sql = 'insert into '.$this->table
				.' ('.implode(', ', $keys).')'
				.' values ('.implode(', ', $params_keys).')';
			$stmt = $this->pdo_instance->prepare($sql);
			$stmt->execute($query_params);
			$this->pdo_instance->commit();
		}
		catch(PDOExecption $e)
		{
			$dbh->rollback();
			return 'ERROR: '.$e->getMessage();
		}
		return $this->pdo_instance->lastInsertId();
	}

	private function update ()
	{
		return 'update';
	}
}
?>
