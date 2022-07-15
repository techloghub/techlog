<?php
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */

/**
 * @method static ArticleModel findOneFromArticle(array $params)
 * @method static CalendarAlertModel[] findFromCalendarAlert(array $params)
 * @method static CommentModel findOneFromComment(array $params)
 * @method static CommentModel[] findFromComment(array $comments_params)
 * @method static int findCountFromArticle(array $array)
 * @method static ArticleModel[] findFromArticle(array $params)
 * @method static string findCategoryFromCategory(array $array)
 * @method static string[] findTagNameFromTags(array $array)
 * @method static int findCountFromMood($array = array())
 * @method static MoodModel[] findFromMood(array $array)
 * @method static ImagesModel findOneFromImages(array $array)
 * @method static string findTitleFromArticle(array $array)
 * @method static int findCountFromImages(array $query_params)
 * @method static ImagesModel[] findFromImages(array $query_params)
 * @method static findPathFromImages(array $array)
 */
class Repository
{
	private static $dbfd;
	private static $debug;
    /**
     * @var PDO
     */
	private static $pdo_instance;
	private static $table;

	private static function dbConnect()
	{ // {{{
		if (!empty(self::$pdo_instance))
			return;

		if (empty(self::$dbfd))
			self::$dbfd = 'db';
		if (empty(self::$debug))
			self::$debug = false;
		$mode = self::$debug ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;

		$config = file_get_contents(CONF_PATH.'/config.json');
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
	} // }}}

	public static function setTable($table)
	{ // {{{
		self::dbConnect();
		self::$table = $table;
	} // }}}

	public static function getTable()
	{ // {{{
		self::dbConnect();
		return self::$table;
	} // }}}

	public static function setdbfd($dbfd)
	{ // {{{
		self::$dbfd = $dbfd;
		self::$pdo_instance = null;
		self::dbConnect();
	} // }}}

	public static function setDebug($debug)
	{ // {{{
		self::$debug = $debug;
		self::$pdo_instance = null;
		self::dbConnect();
	} // }}}

	public static function findByField($field, $params)
	{ // {{{
		self::dbConnect();
		if (empty(self::$table))
			return '{"code":-1, "errmsg":"table empty"}';
		$query_params = array();
		if (substr($field, 0, 3) == 'sum') {
			$field = 'sum('.substr($field, 4).')';
		}
		$sql = 'select ';
		if (isset($params['group'][0]) && $field != $params['group'][0]) {
			$sql .= $field.', '.$params['group'][0];
		} else {
			$sql .= $field;
		}
		$sql .= ' from '.self::$table.' where 1'
			.self::getParams($params, $query_params);

		$stmt = self::$pdo_instance->prepare($sql);
		$stmt->execute($query_params);
		$ret = $stmt->fetchAll();
		if (count($ret) == 1) {
			$ret = $ret[0];
			return isset($ret[$field]) ? $ret[$field] : false;
		} else {
			$fields = array();
			foreach ($ret as $data)
			{
				if (isset($params['group'][0]) && $field != $params['group'][0]) {
					$fields[$data[$params['group'][0]]] = $data[$field];
				} else {
					$fields[] = $data[$field];
				}
			}
			return $fields;
		}
	} // }}}

	public static function findBy($params)
	{ // {{{
		self::dbConnect();
		if (empty(self::$table))
			return '{"code":-1, "errmsg":"table empty"}';
		$query_params = array();
		$sql = 'select * from '.self::$table.' where 1'
			.self::getParams($params, $query_params);

		$stmt = self::$pdo_instance->prepare($sql);
		$table_class = ucfirst(StringOpt::unlinetocamel(self::$table).'Model');
		$stmt->execute($query_params);
		$ret = $stmt->fetchAll(PDO::FETCH_CLASS, $table_class);
		return $ret;
	} // }}}

	public static function findOneBy($params)
	{ // {{{
		self::dbConnect();
		if (!isset($params['range']))
			$params['range'] = array(0, 1);
		$params['range'][1] = 1;
		$objs = self::findBy($params);
		return isset($objs[0]) ? $objs[0] : false;
	} // }}}

	public static function findCountBy($params)
	{ // {{{
		self::dbConnect();
		if (empty(self::$table))
			return '{"code":-1, "errmsg":"table empty"}';
		$query_params = array();
		$sql = 'select count(*) as total from '.self::$table.' where 1'
			.self::getParams($params, $query_params);
		$stmt = self::$pdo_instance->prepare($sql);
		$stmt->execute($query_params);
		$ret = $stmt->fetch();
		return isset($ret['total']) ? $ret['total'] : false;
	} // }}}

    /**
     * @param string $dbfd
     * @param string $debug
     * @return PDO
     */
	public static function getInstance($dbfd = 'db', $debug = 'false')
	{ // {{{
		self::$dbfd = $dbfd;
		self::$debug = $debug;
		self::dbConnect();
		return self::$pdo_instance;
	} // }}}

	public static function persist(AbstractModel $model)
	{ // {{{
		self::dbConnect();
		$class = get_class($model);
		$pattern = '/^(?<table>.*)Model$/';
		$table_infos = array();
		if (preg_match($pattern, $class, $table_infos) == false)
		{
			echo 'ERROR: params error'.PHP_EOL;
			return false;
		}
		$table = StringOpt::cameltounline(lcfirst($table_infos['table']));
		self::setTable($table);
		return $model->is_set_pri() ? self::update($model) : self::insert($model);
	} // }}}

	private static function insert(AbstractModel $model)
	{ // {{{
		$fields = $model->get_model_fields();
		$keys = $params_keys = $query_params = array();
		foreach ($fields as $key)
		{
			$func = 'get_'.$key;
			$value = $model->$func();
			if ($key == $model->get_pri_key())
				continue;
			$keys[] = $key;
			if ($value === 'now()')
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
			$insert_id = self::$pdo_instance->lastInsertId();
			self::$pdo_instance->commit();
		}
		catch(PDOException $e)
		{
			self::$pdo_instance->rollback();
			return 'INSERT_ERROR: '.$e->getMessage();
		}
		return $insert_id;
	} // }}}

	private static function update (AbstractModel $model)
	{ // {{{
		$pri_key = $model->get_pri_key();
		$fields = $model->get_model_fields();
		$func = 'get_'.$pri_key;
		$old_model = self::findOneBy(array('eq'=>array($pri_key=>$model->$func())));
		$set_params = array();
		$query_params = array();
		foreach ($fields as $key)
		{
			$func = 'get_'.$key;
			if ($model->$func() !== $old_model->$func())
			{
				if ($model->$func() === 'now()')
					$set_params[] = $key.'=now()';
				else
				{
					$set_params[] = $key.'=:'.$key;
					$query_params[':'.$key] = $model->$func();
				}
			}
		}
		if (!empty($set_params))
		{
			try
			{
				self::$pdo_instance->setAttribute(
					PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$pdo_instance->beginTransaction();
				$func = 'get_'.$pri_key;
				$sql = 'update '.self::$table.' set '.implode(', ', $set_params)
					.' where '.$pri_key.'='.$model->$func();
				$stmt = self::$pdo_instance->prepare($sql);
				$stmt->execute($query_params);
				self::$pdo_instance->commit();
			}
			catch(PDOException $e)
			{
				self::$pdo_instance->rollback();
				return 'UPDATE_ERROR: '.$e->getMessage();
			}
		}
		$func = 'get_'.$pri_key;
		return $model->$func();
	} // }}}

	public static function __callStatic($method, $params)
	{ // {{{
		$pattern = '/^find(?<sth>(.*){0,1})From(?<table>.*)$/';
		$method_infos = array();
		if (preg_match($pattern, $method, $method_infos) == false)
		{
			echo 'ERROR: method error'.PHP_EOL;
			return false;
		}
		$table = StringOpt::cameltounline(lcfirst($method_infos['table']));
		self::setTable($table);
		switch ($method_infos['sth'])
		{
		case '':
		case 'One':
		case 'Count':
			$func = 'find'.$method_infos['sth'].'By';
			$params = empty($params) ? array('') : $params;
			return self::$func($params[0]);
		default:
			$field = StringOpt::cameltounline(lcfirst($method_infos['sth']));
			return self::findByField($field, $params[0]);
		}
	} // }}}

	private static function getParams($params, &$query_params)
	{ // {{{
		$sql = '';
		if (isset($params['eq']))
		{
			foreach ($params['eq'] as $key=>$value)
			{
                if ($value === 'now()') {
                    $sql .= ' and '.$key.' = now()';
                } else {
                    $sql .= ' and '.$key.' = :eq_'.$key;
                    $query_params['eq_'.$key] = $value;
                }
			}
		}
		if (isset($params['like']))
		{
			foreach ($params['eq'] as $key=>$value)
			{
				$sql .= ' and '.$key.' like	:like_'.$key;
				$query_params['like_'.$key] = $value;
			}
		}
		if (isset($params['ne']))
		{
			foreach ($params['ne'] as $key=>$value)
			{
                if ($value === 'now()') {
                    $sql .= ' and '.$key.' != now()';
                } else {
                    $sql .= ' and '.$key.' != :ne_'.$key;
                    $query_params['ne_'.$key] = $value;
                }
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
                if ($value === 'now()') {
                    $sql .= ' and '.$key.' < now()';
                } else {
                    $sql .= ' and '.$key.' < :lt_'.$key;
                    $query_params['lt_'.$key] = $value;
                }
			}
		}
		if (isset($params['gt']))
		{
			foreach ($params['gt'] as $key=>$value)
			{
                if ($value === 'now()') {
                    $sql .= ' and '.$key.' > now()';
                } else {
                    $sql .= ' and '.$key.' > :gt_'.$key;
                    $query_params['gt_'.$key] = $value;
                }
			}
		}
		if (isset($params['le']))
		{
			foreach ($params['le'] as $key=>$value)
			{
                if ($value === 'now()') {
                    $sql .= ' and '.$key.' <= now()';
                } else {
                    $sql .= ' and '.$key.' <= :le_'.$key;
                    $query_params['le_'.$key] = $value;
                }
			}
		}
		if (isset($params['ge']))
		{
			foreach ($params['ge'] as $key=>$value)
			{
                if ($value === 'now()') {
                    $sql .= ' and '.$key.' >= now()';
                } else {
                    $sql .= ' and '.$key.' >= :ge_'.$key;
                    $query_params['ge_'.$key] = $value;
                }
			}
		}
		if (isset($params['group']))
		{
			$sql .= ' group by '.implode(',', $params['group']);
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

		return $sql;
	} // }}}
}
