<?php
class SearchController extends Controller
{
	function listAction()
	{
		$date_num = array(31, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30);

		$sql = 'select tag_id,count(*) as article_count from article_tag_relation'
			.' group by tag_id'
			.' order by article_count desc, inserttime desc';
		$infos = MySqlOpt::select_query($sql);
		$tag_infos = array();
		foreach ($infos as $info)
		{
			$sql = 'select tag_name from tags where tag_id='.$info['tag_id'];
			$ret = MySqlOpt::select_query($sql);
			$info['tag_name'] = $ret[0]['tag_name'];
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

			$query = 'select count(*) from article'
				.' where inserttime>="'.$date.'-01 00:00:00"'
				.' and inserttime<="'.$date.'-31 23:59:59"';
			$article_count = MySqlOpt::select_query($query);
			if ($article_count == null)
			{
				header("Location: /index/notfound");
				return;
			}

			$article_count = intval($article_count[0]['count(*)']);
			$info['article'] = $article_count;

			$query = 'select count(*) from mood'
				.' where inserttime>="'.$date.'-01 00:00:00"'
				.' and inserttime<="'.$date.'-31 23:59:59"';
			$mood_count = MySqlOpt::select_query($query);
			if ($mood_count == null)
			{
				header("Location: /index/notfound");
				return;
			}

			$info['mood'] = intval($mood_count[0]['count(*)']);

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
