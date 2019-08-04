<?php
require_once(__DIR__.'/../app/register.php');
trigger_error(E_USER_NOTICE);
$baidupanGateway = new BaiduPanGateway();
$baidupanGateway->precreate('/home/zeyu/a.txt', '/techlog/test/a.txt');