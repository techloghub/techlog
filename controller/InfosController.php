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

			list($total, $date) = SqlRepository::getUVInfos($date);
			$uv[] = $total;
			$labels[] = $date;
		}

		$pv_count = SqlRepository::getPVInfos($timestamp);
		foreach ($pv_count as $pv_info)
			$pv[] = $pv_info['total'];

		$all_pv = Repository::findCountFromStats();
		$all_uv = SqlRepository::getAllUV();

		$category_infos = SqlRepository::getCategoryInfos();

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

		$category_data[] = array(
			'label'	=> '心情小说',
			'value'	=> intval(Repository::findCountFromMood()),
			'color'	=> $colors[count($colors)-1]['color'],
			'highlight'	=> $colors[count($colors)-1]['light'],
		);

		$category_infos = SqlRepository::getCategoryNewArticle();
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
