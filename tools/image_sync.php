<?php
require_once (__DIR__.'/../app/register.php');
$limit = 20;
$count = Repository::findCountFromImages();
$basepath = '/var/www/techlog/resource/';
for ($page = 1; $page - 1 < intval($count/$limit); $page++) {
	$images = Repository::findFromImages(
		array(
			'order' => array('inserttime' => 'desc'),
			'range' => array(($page - 1)*$limit, $limit)
		)
	);
	foreach ($images as $image) {
		if (!file_exists($basepath.$image->get_path())
			|| md5_file($basepath.$image->get_path()) != $image->get_md5()) {

			$file = HttpCurl::get('http://techlog.cn/resource/'.$image->get_path());
			file_put_contents($basepath.$image->get_path(), $file);
			echo 'DOWNLOAD_IMAGE'."\t".$basepath.$image->get_path().PHP_EOL;
		} else exit;
	}
}
?>
