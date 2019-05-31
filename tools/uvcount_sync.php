<?php
require_once (__DIR__.'/../app/register.php');

echo '是否同步 UV 数据？ [y/N]';

$sure = fgets(STDIN);
if (trim($sure[0]) != 'Y' && trim($sure[0]) != 'y')
	exit;

$offset = 0;
$limit = 500;

$count = 0;
$lastid = 0;
while (true) {
    echo '查询 【'.$lastid.', '.$offset.', '.$limit.'】'.PHP_EOL;
    $params = array(
        'gt' => array('stats_id' => $lastid),
        'order' => array('stats_id' => 'asc'),
        'range' => array($offset, $limit));
    $hosts = Repository::findFromStats($params);
    if (empty($hosts)) {
        break;
    }
    foreach ($hosts as $host) {
        RedisRepository::setIpCache(trim($host->get_remote_host()));
        $lastid = $host->get_stats_id();
        $count++;
    }
}
echo '完成同步 【'.$count.'】 条数据';
