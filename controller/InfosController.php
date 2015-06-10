<?php
class InfosController extends Controller
{
	public function listAction()
	{
		$labels = array();
		$uv = array();
		$pv = array();
		$timestamp = time();
		for ($i=0; $i<14; ++$i)
		{
			$date = date('Y-m-d', $timestamp - 3600*24*(14-1) + $i*3600*24);

			$sql = 'select date(time_str) as date, count(*) as total from'
				.' (select time_str from stats where time_str <= "'.$date.' 23:59:59"'
				.' and time_str >= "'.$date.' 00:00:00" group by remote_host) as A'
				.' group by date(time_str)';
			$remote_host_count = MySqlOpt::select_query($sql);
			if ($remote_host_count == false)
			{
				header("Location: /index/notfound");
				return;
			}
			$uv[] = $remote_host_count[0]['total'];
			$labels[] = $remote_host_count[0]['date'];
		}

		$sql = 'select date(time_str) as date, count(*) as total from'
			.' stats where time_str <= "'.date('Y-m-d', $timestamp).' 23:59:59"'
			.' and time_str >= "'.date('Y-m-d', $timestamp - 3600*24*(14-1)).' 00:00:00"'
			.' group by date(time_str) order by time_str';
		$pv_count = MySqlOpt::select_query($sql);
		if ($pv_count == false)
		{
			header("Location: /index/notfound");
			return;
		}

		foreach ($pv_count as $pv_info)
			$pv[] = $pv_info['total'];

		$sql = 'select count(*) as total from stats';
		$all_pv = MySqlOpt::select_query($sql);
		if ($all_pv == false)
		{
			header("Location: /index/notfound");
			return;
		}
		$all_pv = $all_pv[0]['total'];

		$sql = 'select sum(num) as total from'
			.' (select date(time_str) as day, count(distinct remote_host) as num'
			.' from stats group by day) as A';
		$all_uv = MySqlOpt::select_query($sql);
		if ($all_uv == false)
		{
			header("Location: /index/notfound");
			return;
		}
		$all_uv = $all_uv[0]['total'];

		$sql = 'select category.category_id, category.category, count(*) as total'
			.' from article, category'
			.' where article.category_id = category.category_id'
			.' group by article.category_id';
		$category_infos = MySqlOpt::select_query($sql);
		if ($category_infos == false)
		{
			header("Location: /index/notfound");
			return;
		}

		$colors = array(
			array('color'=>'rgba(0, 255, 255, 0.8)',
			'light'=>'rgba(220, 220, 220, 0.3)'),
			array('color'=>'rgba(255, 255, 128, 0.8)',
			'light'=>'rgba(220, 220, 220, 0.3)'),
			array('color'=>'rgba(128, 128, 255, 0.8)',
			'light'=>'rgba(220, 220, 220, 0.3)'),
			array('color'=>'rgba(17, 177, 255, 0.8)',
			'light'=>'rgba(220, 220, 220, 0.3)'),
			array('color'=>'rgba(255, 128, 192, 0.8)',
			'light'=>'rgba(220, 220, 220, 0.3)'),
			array('color'=>'rgba(128, 255, 128, 0.8)',
			'light'=>'rgba(220, 220, 220, 0.3)'),
			array('color'=>'rgba(104, 180, 255, 0.8)',
			'light'=>'rgba(220, 220, 220, 0.3)'),
		);

		$category_data = array();
		$temp_infos = array();
		for ($i = 0; $i < count($category_infos); ++$i)
		{
			$infos = $category_infos[$i];
			$temp_infos['label'] = $infos['category'];
			$temp_infos['value'] = intval($infos['total']);
			$temp_infos['color'] = $colors[$i]['color'];
			$temp_infos['highlight'] = $colors[$i]['light'];
			$category_data[] = $temp_infos;
		}

		$sql = 'select count(*) as total from mood';
		$mood_infos = MySqlOpt::select_query($sql);
		if ($mood_infos == false)
		{
			header("Location: /index/notfound");
			return;
		}
		$category_data[] = array(
			'label'	=> '心情小说',
			'value'	=> intval($mood_infos[0]['total']),
			'color'	=> $colors[count($colors)-1]['color'],
			'highlight'	=> $colors[count($colors)-1]['light'],
		);

		$sql = 'select a.category_id, a.article_id, b.category, a.title, a.inserttime'
			.' from article a, category b where not exists'
			.' ( select 1 from article where category_id=a.category_id'
			.' and inserttime>a.inserttime )'
			.' and a.category_id = b.category_id order by category_id';
		$category_infos = MySqlOpt::select_query($sql);
		if ($category_infos == false)
		{
			header("Location: /index/notfound");
			return;
		}

		$category_ids = array('龙潭书斋'=>1, '读书笔记'=>2, '龙渊阁记'=>3, '技术分享'=>4);
		if ($this->is_root)
		{
			$category_ids['龙泉日记'] = 5;
			$category_ids['龙泉财报'] = 6;
			$category_ids['心情小说'] = 'mood';
		}

		$params = array(
			'all_pv' => $all_pv,
			'all_uv' => $all_uv,
			'today_pv' => $pv[13],
			'today_uv' => $uv[13],
			'category_infos' => $category_infos,
			'category_ids' => json_encode($category_ids),
			'pv' => json_encode($pv),
			'uv' => json_encode($uv),
			'labels' => json_encode($labels),
			'category_data' => json_encode($category_data),
			'title' => '数据统计',
		);
		$this->display(__METHOD__, $params);
	}
}
?>
