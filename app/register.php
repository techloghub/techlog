<?php
require_once(APP_PATH.'/file_register.php');

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
	'ArticleTagRelationModel',
	'BooknoteModel',
	'CategoryModel',
	'EarningsModel',
	'ImagesModel',
	'MoodModel',
	'TagsModel',
	'StatsModel',
);

require_once (LIB_PATH.'/'.'Controller.php');
foreach ($controller_list as $controller)
	require_once(CONTROLLER_PATH.'/'.$controller.'.php');

foreach ($model_list as $model)
	require_once(MODEL_PATH.'/'.$model.'.php');

?>
