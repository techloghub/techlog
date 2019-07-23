<?php
define('APP_PATH', __DIR__);
define('CONF_PATH', __DIR__.'/conf');
define('WEB_PATH', __DIR__.'/../web');
define('LIB_PATH', __DIR__.'/../library');
define('VIEW_PATH', __DIR__.'/../views');
define('DRAFT_PATH', __DIR__.'/../draft');
define('MODEL_PATH', __DIR__.'/../model');
define('RESOURCE_PATH', __DIR__.'/../resource');
define('CONTROLLER_PATH', __DIR__.'/../controller');
define('VENDOR_PATH', __DIR__.'/../vendor');

ini_set('date.timezone','Asia/Shanghai');

foreach (array(LIB_PATH, CONTROLLER_PATH, MODEL_PATH, VENDOR_PATH) as $dir) {
    if (!file_exists($dir)) {
        continue;
    }
    loadFiles($dir);
}

function loadFiles($dir)
{
    $fileArr = scandir($dir);
    if (empty($fileArr)) {
        return false;
    }
    foreach ($fileArr as $fileName) {
        if (preg_match('/^\..*/i', $fileName)) {
            // 过滤隐藏文件
            continue;
        }
        if (is_dir($dir . '/' . $fileName)) {
            loadFiles($dir . '/' . $fileName);
        } else if (preg_match('/.*\.php$/i', $fileName)) {
            require_once($dir . '/' . $fileName);
        }
    }
    return true;
}
