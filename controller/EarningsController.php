<?php
class EarningsController extends Controller
{
	public function listAction()
	{
		if (!$this->is_root)
		{
			header("Location: /index/notfound");
			return;
		}

		$det_beg = empty($_GET['beg']) ? date('Y-m', time()) : $_GET['beg'];
		$det_end = empty($_GET['end']) ? '2013-09' : $_GET['end'];
		if ($det_beg > $det_end)
			list($det_beg, $det_end) = array($det_end, $det_beg);
		$drw_beg = empty($_GET['drw_beg']) ?
			date('Y-m', time()) : $_GET['drw_beg'];
		$drw_end = empty($_GET['drw_end']) ? '2013-09' : $_GET['drw_end'];
		if ($drw_beg > $drw_end)
			list($drw_beg, $drw_end) = array($drw_end, $drw_beg);

		$query = 'select * from earnings order by month desc limit 24';
		$earnings = Repository::findFromEarnings(
			array(
				'order' => array('month' => 'desc'),
				'range' => array(0, 24)
			)
		);
		$earn_infos = array();
		$month = array();
		$income = array();
		$expend = array();

		$index = 0;

		foreach ($earnings as $earning)
		{
			$infos = array();

			if ($earning->get_month() >= $det_beg
				and $earning->get_month() <= $det_end
				and $index < 12
			)
			{
				$infos['idx_href'] = '/article/list/'.$earning->get_article_id();

				$path = Repository::findPathFromImages(
					array('eq' => array('image_id' => $earning->get_image_id()))
				);

				$infos['image_path'] = $path;
				$infos['title'] = $earning->get_month();
				$infos['descs'] = '结余:&nbsp;&nbsp;'
					.($earning->get_income() - $earning->get_expend());
				$earn_infos[] = $infos;
				$index++;
			}

			if ($earning->get_month() >= $drw_beg and $earning->get_month() <= $drw_end)
			{
				$month[] = $earning->get_month();
				$income[] = $earning->get_income();
				$expend[] = $earning->get_expend();
			}
		}
		$average = round((array_sum($income)-array_sum($expend))/count($month), 2);

		$params = array(
			'det_beg_month'	=> $det_beg,
			'det_end_month'	=> $det_end,
			'drw_beg_month'	=> $drw_beg,
			'drw_end_month'	=> $drw_end,
			'labels'	=> json_encode(array_reverse($month)),
			'income'	=> json_encode(array_reverse($income)),
			'expend'	=> json_encode(array_reverse($expend)),
			'average'	=> $average,
			'infos'	=> $earn_infos,
			'category_id'	=> 1,
			'title'	=> '龙潭财报',
		);
		$this->display('NoteController::listAction', $params);
	}
}
?>
