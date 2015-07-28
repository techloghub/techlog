<?php
require_once(__DIR__.'/../app/register.php');

if (strpos(__DIR__, 'example_techlog') !== false) {
	Dispatcher::getInstance('debug')->dispatch();
} else {
	Dispatcher::getInstance()->dispatch();
}
?>
