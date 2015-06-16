<?php

$conf = file_get_contents('/etc/zeyu203/techlog.conf');
$conf = unserialize(base64_decode($conf));
$conf = $conf['database'];

$sql_host  = $conf['host'];
$sql_login = $conf['user'];
$sql_passe = $conf['pwd'];
$sql_dbase = $conf['db'];
$sql_table = 'stats';

?>
