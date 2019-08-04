<?php
require_once(__DIR__.'/../app/register.php');
$baidupanGateway = new BaiduPanGateway();
$baidupanGateway->precreate('/home/zeyu/a.txt', '/techlog/test/a.txt');