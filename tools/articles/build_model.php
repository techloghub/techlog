<?php
require_once (__DIR__.'/../app/register.php');

$options = getopt('t:');
if (!isset($options['t']))
{
	echo 'usage: php build_model.php'
		.' -t table'.PHP_EOL;
	exit;
}
$table = $options['t'];
$table_class = ucfirst(StringOpt::unlinetocamel($table).'Model');
$file = MODEL_PATH.'/'.$table_class.'.php';
if (file_exists($file))
{
	echo '文件已存在，是否替换 [y/N]';
	$sure = fgets(STDIN);
	if (trim($sure[0]) != 'Y' && trim($sure[0]) != 'y')
		exit;
}

$pdo = Repository::getInstance('db', true);
$sql = 'describe '.$table;
$rs = $pdo->query($sql);
$model = '<?php'.PHP_EOL.'class '.$table_class.PHP_EOL.'{'.PHP_EOL;
$rows = $rs->fetchAll();
foreach ($rows as $row)
{
	$model .= "\t".'private $'.$row['Field'].';'.PHP_EOL;
}
$model .= PHP_EOL;
$model .= "\t".'public function __construct($params = array())'.PHP_EOL
	."\t{".PHP_EOL
	."\t\t".'foreach (get_object_vars($this) as $key=>$value)'.PHP_EOL
	."\t\t".'{'.PHP_EOL
	."\t\t\t".'if ($key != $this->get_pri_key() && isset($params[$key]))'.PHP_EOL
	."\t\t\t\t".'$this->$key = $params[$key];'.PHP_EOL
	."\t\t\t".'else if (empty($this->$key))'.PHP_EOL
	."\t\t\t\t".'$this->$key = "";'.PHP_EOL
	."\t\t}".PHP_EOL."\t}".PHP_EOL.PHP_EOL;
$model .= "\t".'public function get_model_fields()'.PHP_EOL
	."\t".'{'.PHP_EOL."\t\t".'return array_keys(get_object_vars($this));'.PHP_EOL
	."\t".'}'.PHP_EOL;
foreach ($rows as $row)
{
	$model .= "\t".'public function get_'.$row['Field'].'()'.PHP_EOL
		."\t".'{'.PHP_EOL."\t\t".'return $this->'.$row['Field'].';'.PHP_EOL
		."\t".'}'.PHP_EOL;
	if ($row['Key'] != 'PRI')
	{
		$model .= "\t".'public function set_'.$row['Field'].'($value)'.PHP_EOL
			."\t".'{'.PHP_EOL."\t\t".'$this->'.$row['Field'].' = $value;'.PHP_EOL
			."\t\t".'return $this;'.PHP_EOL."\t".'}'.PHP_EOL;
	}
	else
		$pri_row = $row;
}
$model .= "\t".'public function set($params)'.PHP_EOL
	."\t".'{'.PHP_EOL
	."\t\t".'foreach (get_object_vars($this) as $key=>$value)'.PHP_EOL
	."\t\t".'{'.PHP_EOL."\t\t\t".'if (isset($params[$key]))'.PHP_EOL
	."\t\t\t\t".'$this->$key = $params[$key];'.PHP_EOL
	."\t\t".'}'.PHP_EOL."\t".'}'.PHP_EOL;
$model .= "\t".'public function is_set_pri()'.PHP_EOL
	."\t".'{'.PHP_EOL
	."\t\t".'return !empty($this->'.$pri_row['Field'].');'.PHP_EOL
	."\t".'}'.PHP_EOL;
$model .= "\t".'public function get_pri_key()'.PHP_EOL
	."\t".'{'.PHP_EOL."\t\t".'return "'.$pri_row['Field'].'";'.PHP_EOL
	."\t".'}'.PHP_EOL;
$model .= '}';
file_put_contents($file, $model);
?>
