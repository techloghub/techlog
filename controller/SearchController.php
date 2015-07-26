<?php
class SearchController extends Controller
{
	function listAction()
	{
		$date_num = array(31, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30);

		$infos = SqlRepository::getTagIdCount();
		$tag_infos = array();
		foreach ($infos as $info)
		{
			$info['tag_name'] = Repository::findTagNameFromTags(
				array('eq' => array('tag_id' => $info['tag_id'])));
			$tag_infos[] = $info;
		}

		//$first_date = '2013-12-15';
		$dates = array();
		$timestamp = time()-3600;
		$month_num = (date('Y', $timestamp)-2013)*12 + (date('m', $timestamp)-12) + 1;
		for ($i=0; $i<=$month_num; $i++)
		{
			$info = array();
			$y = date('Y', $timestamp);
			$m = date('m', $timestamp);

			$timestamp -= 3600*24*$date_num[$m-1];
			if ($m == 3
				&& (
					( $y % 100 == 0 && $y % 400 == 0 )
					|| ($y % 100 != 0 && $y % 4 == 0 )
				)
			)
			$timestamp -= 3600;

			$date = $y.'-'.$m;
			$info['id'] = $y.'0'.$m;
			$info['month'] = $date;

			$info['article'] = Repository::findCountFromArticle(
				array(
					'ge' => array('inserttime' => $date.'-01 00:00:00"'),
					'le' => array('inserttime' => $date.'-31 00:00:00"'),
				)
			);
			$info['mood'] = Repository::findCountFromMood(
				array(
					'ge' => array('inserttime' => $date.'-01 00:00:00"'),
					'le' => array('inserttime' => $date.'-31 00:00:00"'),
				)
			);

			$dates[] = $info;
		}

		$params = array(
			'dates' => $dates,
			'tags' => $tag_infos,
			'tag_count' => count($tag_infos),
			'title' => '检索一下',
			'category_id' => 1,
		);

		$this->display(__METHOD__, $params);
	}
}
?>
