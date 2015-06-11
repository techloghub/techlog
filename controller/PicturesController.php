<?php
class PicturesController extends Controller
{
	public function listAction()
	{
		if (!$this->is_root)
		{
			header("Location: /index/notfound");
			return;
		}
	}

	public function insertActionAjax()
	{
	}
}
?>
