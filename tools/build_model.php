<?php
require_once (__DIR__.'/../app/register.php');
require_once (LIB_PATH.'/TechlogTools.php');

$options = getopt('t:');
if (!isset($options['t']))
{
	echo 'usage: php create_model.php'
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

$rp = new Repository('db', true);
$pdo = $rp->getInstance();
$sql = 'describe '.$table;
$rs = $pdo->query($sql);
$model = '<?php'.PHP_EOL.'class '.$table_class.PHP_EOL.'{'.PHP_EOL;
$rows = $rs->fetchAll();
foreach ($rows as $row)
{
	$model .= "\t".'private $'.$row['Field'].';'.PHP_EOL;
}
$model .= "\t".'public function __construct($params = array())'.PHP_EOL
	."\t{".PHP_EOL
	."\t\t".'foreach (get_object_vars($this) as $key=>$value)'.PHP_EOL
	."\t\t".'{'.PHP_EOL."\t\t\t".'if (isset($params[$key]))'.PHP_EOL
	."\t\t\t\t".'$this->$key = $value;'.PHP_EOL
	."\t\t\t".'else'.PHP_EOL."\t\t\t\t".'$this->$key = "";'.PHP_EOL
	."\t\t}".PHP_EOL."\t}".PHP_EOL;
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
$model .= '}';
echo $model.PHP_EOL;
?>
