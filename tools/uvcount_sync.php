<?php
require_once (__DIR__.'/../app/register.php');

echo '是否同步 UV 数据？ [y/N]';

$sure = fgets(STDIN);
if (trim($sure[0]) != 'Y' && trim($sure[0]) != 'y')
	exit;
