<?php
class NoteController extends Controller
{
	public function listAction()
	{
		$notes = SqlRepository::getBooknotes();
		$booknotes = array();
		foreach ($notes as $note)
		{
			$infos = array();
			$infos['idx_href'] = '/article/list/'.$note->get_index_article_id();
			$infos['image_path'] = Repository::findPathFromImages(
				array('eq' => array('image_id' => $note->get_image_id())));
			$title = Repository::findTitleFromArticle(
				array(
					'eq' => array('article_id' => $note->get_index_article_id())
				)
			);
			$infos['title'] = mb_substr($title, 0, 35);
			$infos['descs'] = mb_substr($note->get_descs(), 0, 35);
			$booknotes[] = $infos;
		}

		$params = array(
			'infos' => $booknotes,
			'title' => '读书笔记',
			'category_id' => 2
		);

		$this->display(__METHOD__, $params);
	}
}
?>
