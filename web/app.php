<?php
ini_set('date.timezone','Asia/Shanghai');
require_once(__DIR__.'/../app/Dispatcher.php');

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
if (strpos(__DIR__, 'example_techlog') !== false) {
	Dispatcher::getInstance('debug')->dispatch();
} else {
	Dispatcher::getInstance()->dispatch();
}
=======
Dispatcher::getInstance()->dispatch();
>>>>>>> d627a3a... 目录切换
=======
#Dispatcher::getInstance('debug')->dispatch();
>>>>>>> 1976c14... 目录调整，提高安全性
=======
Dispatcher::getInstance()->dispatch();
>>>>>>> 105d5b0... 增加访问信息页面
?>
