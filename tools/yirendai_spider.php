<?php
require_once (__DIR__.'/../app/register.php');
$options = getopt('c:');
if (!isset($options['c'])) {
	echo 'usage: php yirendai_spider.php -c cookie'.PHP_EOL;
	return;
}

HttpCurl::set_cookie($options['c']);
$i = 1;
$count = 0;
$max = 100;
while (1) {
	echo '~~~~~~~~~~~~~ BEGIN_TO_GET_WEB ~~~~~~~~~~~~~~~'.PHP_EOL;
	$response = HttpCurl::get('https://www.yirendai.com/lender/finance/my/list/'
		.$i.'?dateRange=&state=4');
	echo '~~~~~~~~~~~~~ WEB_GET_SUCCESS ~~~~~~~~~~~~~~~'.PHP_EOL;
	$body = $response['body'].PHP_EOL;
	$pageNum = StringOpt::spider_string(
		$body, '<div class="m-page m-page-min">', '<input');
	$max = getMax($pageNum);
	$tbody = StringOpt::spider_string(
		$body, 'mylicaiservice<![&&]>table_gray<![&&]><tbody', ' </tbody>');
	if (empty($tbody)) {
		echo $count.PHP_EOL;
		return;
	}
	while (1) {
		$capital = StringOpt::spider_string($tbody,
			'<tr<![&&]><td><![&&]><td><![&&]><td><![&&]><td>', '</td>', $tbody);
		$income = StringOpt::spider_string(
				$tbody, '<td><![&&]><td>', '</td>', $tbody);
		if (empty($capital)) {
			break;
		}
		$count += $capital + $income;
	}
	if ($i++ > $max) {
		echo $count.PHP_EOL;
		return;
	}
	sleep(1);
}

function getMax($pageNum) {
	$max = 0;
	while (1) {
		$num = StringOpt::spider_string(
			$pageNum, 'class="m-pageNum<![&&]>>', '<', $pageNum);
		if (empty($num)) {
			return $max;
		}
		if ($num > $max) {
			$max = $num;
		}
	}
}
?>
