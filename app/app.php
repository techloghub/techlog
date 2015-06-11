<?php
ini_set('date.timezone','Asia/Shanghai');
require_once(__DIR__.'/Dispatcher.php');

Dispatcher::getInstance()->dispatch();
?>
