<?php
require_once(__DIR__.'/Dispatcher.php');

Dispatcher::getInstance('debug')->dispatch();
exit;
Dispatcher::getInstance()->dispatch();
?>
