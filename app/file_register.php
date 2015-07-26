<?php
define('APP_PATH', __DIR__);
define('WEB_PATH', __DIR__.'/../web');
define('LIB_PATH', __DIR__.'/../library');
define('VIEW_PATH', __DIR__.'/../views');
define('DRAFT_PATH', __DIR__.'/../draft');
define('MODEL_PATH', __DIR__.'/../model');
define('RESOURCE_PATH', __DIR__.'/../resource');
define('CONTROLLER_PATH', __DIR__.'/../controller');

require_once(LIB_PATH.'/StringOpt.php');
require_once(LIB_PATH.'/Controller.php');
require_once(LIB_PATH.'/Repository.php');
require_once(APP_PATH.'/Dispatcher.php');
require_once(LIB_PATH.'/TechlogTools.php');
require_once(LIB_PATH.'/SphinxClient.php');
require_once(LIB_PATH.'/SqlRepository.php');
?>
