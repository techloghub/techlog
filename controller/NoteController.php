<?php
class NoteController extends Controller
{
	public function listAction()
	{
		$sql = 'select booknote.* from booknote, article'
			.' where booknote.index_article_id = article.article_id'
			.' and booknote.note_id not in (2, 3, 5, 9, 4)'
			.' order by article.access_count desc';
		$notes = MySqlOpt::select_query($sql);
		$booknotes = array();
		foreach ($notes as $note)
		{
			$infos = array();
			$infos['idx_href'] = '/article/list/'.$note['index_article_id'];
			$image_select_query = 'select path from images'
				.' where image_id='.$note['image_id'];
			$image_ret = MySqlOpt::select_query($image_select_query);
			$path = $image_ret[0]['path'];
			$infos['image_path'] = $path;
			$title_select_query = 'select title from article'
				.' where article_id='.$note['index_article_id'];
			$title = MySqlOpt::select_query($title_select_query);
			$infos['title'] = mb_substr($title[0]['title'], 0, 35);
			$infos['descs'] = mb_substr($note['descs'], 0, 35);
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
