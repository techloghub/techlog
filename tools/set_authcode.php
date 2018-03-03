<?php
require_once (__DIR__.'/../app/register.php');
LogOpt::init('article_creater', true);
$options = getopt('c:');
if (!isset($options['c'])) {
	echo 'usage: php set_authcode.php -c authcode'.PHP_EOL;
	return;
}

$result = RedisRepository::setAuthcode($options['c']);
echo $result.PHP_EOL;
?>
