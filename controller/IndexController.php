<?php
class IndexController extends Controller
{
	public function listAction($params)
	{
		$this->display(__METHOD__, $params);
	}

	public function notfoundAction($params)
	{
		$this->display(__METHOD__, array('msg'=>'您访问的页面不存在'));
	}
}
?>
