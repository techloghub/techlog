<?php
define('APP_PATH', __DIR__.'/../../app');

$config = file_get_contents(APP_PATH.'/config.json');
$config = json_decode($config, true);
$conf = $config['db'];

$sql_host  = $conf['host'];
$sql_login = $conf['username'];
$sql_passe = $conf['password'];
$sql_dbase = $conf['dbname'];
$sql_table = 'stats';
?>
