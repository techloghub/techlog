<?php
define('APP_PATH', __DIR__);
define('WEB_PATH', __DIR__.'/../web');
define('LIB_PATH', __DIR__.'/../library');
define('VIEW_PATH', __DIR__.'/../views');
define('DRAFT_PATH', __DIR__.'/../draft');
define('MODEL_PATH', __DIR__.'/../model');
define('RESOURCE_PATH', __DIR__.'/../resource');
define('CONTROLLER_PATH', __DIR__.'/../controller');

$controller_list = array(
	'ArticleController',
	'IndexController',
	'DebinController',
	'MsgchkController',
	'NoteController',
	'InfosController',
	'SearchController',
	'EarningsController',
	'PicturesController',
);

$model_list = array(
	'ArticleModel',
);

require_once (LIB_PATH.'/'.'Controller.php');
foreach ($controller_list as $controller)
	require_once(CONTROLLER_PATH.'/'.$controller.'.php');

foreach ($model_list as $model)
	require_once(MODEL_PATH.'/'.$model.'.php');

?>
