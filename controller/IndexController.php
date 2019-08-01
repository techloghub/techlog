<?php
class IndexController extends Controller
{
	public function listAction($params)
	{
		$this->display(__METHOD__, $params);
	}

	public function tokenAction($params)
	{
        var_dump($params);
        var_dump($_REQUEST);
        exit;
	}

	public function notfoundAction($params)
	{
		header('HTTP/1.1 404 Not Found');
		header("status: 404 Not Found");
		$this->display(__METHOD__, array('msg'=>'您访问的页面不存在'));
	}
}
?>
