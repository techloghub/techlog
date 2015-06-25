<?php
ini_set('date.timezone','Asia/Shanghai');
require_once(__DIR__.'/../app/Dispatcher.php');

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
#Dispatcher::getInstance('debug')->dispatch();
>>>>>>> ed1c3c5... fix bug - 路径错误
=======
Dispatcher::getInstance()->dispatch();
>>>>>>> af7121e... fix bug - 路径错误
=======
if (strpos(__DIR__, 'example_techlog') !== false) {
	#Dispatcher::getInstance('debug')->dispatch();
} else {
	Dispatcher::getInstance()->dispatch();
}
>>>>>>> bdc8f4f... 兼容测试
?>
