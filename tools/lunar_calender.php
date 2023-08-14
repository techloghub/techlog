<?php
require_once (__DIR__.'/../app/register.php');


\date_default_timezone_set('PRC');

$options = getopt('i:');

if (!isset($options['i'])) {
	echo 'Useage: php '.__FILE__.' -i calendar id'.PHP_EOL;
	exit;
}

$id = $options['i'];

$ret = CalendarAlertService::update_next_alert_time($id);
echo $ret.PHP_EOL;
?>
