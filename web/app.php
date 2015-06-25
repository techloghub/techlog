<?php
ini_set('date.timezone','Asia/Shanghai');
require_once(__DIR__.'/../app/Dispatcher.php');

<<<<<<< HEAD
if (strpos(__DIR__, 'example_techlog') !== false) {
	Dispatcher::getInstance('debug')->dispatch();
} else {
	Dispatcher::getInstance()->dispatch();
}
=======
Dispatcher::getInstance()->dispatch();
>>>>>>> d627a3a... 目录切换
?>
