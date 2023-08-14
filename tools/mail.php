<?php
require_once (__DIR__.'/../app/register.php');

use PHPMailer\PHPMailer\PHPMailer;

$options = getopt('s:t:c:h:a:');
if (!isset($options['t']) || !isset($options['s']) || !isset($options['c']))
{
	echo 'usage: php mail.php'
		.' -s subject -t to -c content'
		.' [-h is html([0]/1)] [-a alt content]'.PHP_EOL;
	return;
}

if (!isset($options['a'])) {
	$options['a'] = '';
}
$subject = $options['s'];
$to = $options['t'];
$content = $options['c'];
$ishtml = isset($options['h'])?$options['h']: 0;

CalendarAlertService::send_mail($subject, $to, $content, $ishtml, $options['a']);
echo '发送成功';
?>
