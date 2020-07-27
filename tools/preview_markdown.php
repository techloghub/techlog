<?php
require_once (__DIR__.'/../app/register.php');
$options = getopt('i:');

$draft_id = isset($options['i']) ? $options['i'] : '';
$draft_array = scandir(DRAFT_PATH);
$draft_result = array();
foreach ($draft_array as $draft_name) {
	if (preg_match('/^draft'.$draft_id.'(\..*$|$)/', $draft_name, $result) > 0) {
		$draft_result[] = $draft_name;
	}
}

if (count($draft_result) > 1) {
	echo 'Error：不止一个可选的 draft 文件'.PHP_EOL;
	return;
}

if (empty($draft_result)) {
	echo '指定日志的草稿不存在'.PHP_EOL;
	return;
}

$draft_file = DRAFT_PATH.'/'.$draft_result[0];
$contents = file_get_contents($draft_file);
echo MarkdownTools::turn_markdown_to_techlog($contents).PHP_EOL;
?>
