<?php
require_once (dirname(__FILE__).'/../'.'library/zeyublog.php');

LogOpt::init('picture_inserter', true);
$options = getopt('c:n:i:');
if (!isset($options['n']))
{
	echo 'usage: php '.basename(__FILE__).' -n name -c category [-i id]'
		.' (c: earnings article background booknote icon mood)'.PHP_EOL;

	exit;
}

if (!isset($options['i']))
	$options['i'] = null;

if (!isset($options['c']))
	$options['c'] = 'article';

echo '确认插入图片 '.$options['n']
	.' ( CATEGORY : '.$options['c'].' )'.'吗？ y/N: ';

$sure = fgets(STDIN);
if (trim($sure[0]) != 'Y' && trim($sure[0]) != 'y')
	exit;

$ret = ZeyuBlogOpt::picture_insert($options['n'], $options['c'], $options['i']);

switch ($ret)
{
case -1:
	$message = '源文件不存在';
	break;
case -2:
	$message = '文件替换失败，请查看权限';
	break;
case -3:
	$message = '目录创建失败，请查看权限';
	break;
case -4:
	$message = '指定被替换文件 ID 不存在';
	break;
case -5:
	$message = '文件添加失败，请查看权限';
	break;
default:
	$message = '文件添加成功';
}

if ($ret < 0)
{
	LogOpt::set('exception', $message,
		'name', $options['n'],
		'id', $options['i']
	);
}
else
{
	$sql = 'select * from images where image_id='.$ret;
	$infos = MySqlOpt::select_query($sql);
	$infos = $infos[0];

	LogOpt::set('info', $message,
		'image_id', $ret,
		'md5', $infos['md5'],
		'path', $infos['path']
	);
}
?>
