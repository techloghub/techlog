<?php
require_once (__DIR__.'/../app/register.php');

$options = getopt('i:s:d:');
if (!isset ($options['i']) || !is_numeric($options['i']))
{
	echo 'usage: php get_images.php -i image_id [-s source] [-d distinct]'.PHP_EOL;
	return;
}

if (!isset($options['s'])) {
	$config = file_get_contents(CONF_PATH.'/config.json');
	$config = json_decode($config, true);
	$options['s'] = $config['techlog']['image_dir'];
}

if (!isset($options['d'])) {
	$options['d'] = '.';
}

$image = Repository::findOneFromImages(
	array('eq' => array('image_id' => $options['i'])));
if ($image == false) {
	LogOpt::set ('exception', 'cannot find the image',
		'image_id', $options['i']);
	return;
}

$path = $image->get_path();
$source_path = $options['s'] . '/' . $path;
$distinct_path = $options['d'] . '/' . $path;
copy($source_path, $distinct_path);
