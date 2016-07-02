<?php
class EarningsController extends Controller
{
	private $colors = array(
		array('rgba(0, 255, 255, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(17, 177, 255, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(128, 128, 255, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(255, 128, 192, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(104, 180, 255, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(255, 255, 128, 0.7)', 'rgba(220, 220, 220, 0.3)'),
		array('rgba(128, 255, 128, 0.7)', 'rgba(220, 220, 220, 0.3)'),
	);
	private $income;
	private $expend;

	public function listAction()
	{
		if (!$this->is_root)
		{
			header("Location: /index/notfound");
			return;
		}

		list($begMonth, $endMonth) = $this->getBegEndMonth($_REQUEST);
		$categories = Repository::findCategoryFromLedgers(array('group' => array('category')));
		$avg = $this->getAvg($begMonth, $endMonth, false);
		if ($avg === false)
		{
			header("Location: /index/notfound");
			return;
		}
		list($labels, $expends, $incomes) =
			$this->getInOutDatas($begMonth, $endMonth, false);
		if ($labels === false)
		{
			header("Location: /index/notfound");
			return;
		}
		list($inCategories, $outCategories) =
			$this->getCategoryDatas($begMonth, $endMonth, false);
		if ($inCategories === false)
		{
			header("Location: /index/notfound");
			return;
		}

		$params = array(
			'title' => '龙泉财报',
			'beg_month' => $begMonth,
			'end_month' => $endMonth,
			'incomes' => json_encode($incomes),
			'expends' => json_encode($expends),
			'labels' => json_encode($labels),
			'inCategories' => json_encode($inCategories),
			'outCategories' => json_encode($outCategories),
			'avg' => $avg,
			'income' => $this->income,
			'expend' => $this->expend,
			'categories' => $categories
		);

		$this->display(__METHOD__, $params);
	}

	public function redrawActionAjax()
	{
		$begMonth = $endMonth = $_REQUEST['month'];
		if (strtotime($begMonth) == false)
		{
			return array('code' => -1, 'msg' => 'PARAMS ERROR');
		}
		$avg = $this->getAvg($begMonth, $endMonth, false);
		list($inCategories, $outCategories) =
			$this->getCategoryDatas($begMonth, $endMonth, false);
		return array(
			'code' => 0,
			'beg_month' => $begMonth,
			'end_month' => $begMonth,
			'inCategories' => $inCategories,
			'outCategories' => $outCategories,
			'avg' => $avg,
			'income' => $this->income,
			'expend' => $this->expend,
		);
	}

	public function reloadActionAjax()
	{
		list($begMonth, $endMonth) = $this->getBegEndMonth($_REQUEST);
		list($labels, $expends, $incomes) =
			$this->getInOutDatas(
				$begMonth, $endMonth, false, $_REQUEST['category']);
		return array(
			'code' => 0,
			'labels' => $labels,
			'expends' => $expends,
			'incomes' => $incomes
		);
	}

	private function getBegEndMonth($request)
	{
		#$begMonth = date('Y-m', time()-24*3600*30*24);
		$begMonth = '2013-09';
		$endMonth = date('Y-m',
			time()-24*3600*intval(date('t', strtotime('-1 month'))));
		if (isset($request['beg_month']))
		{
			$begMonth = $request['beg_month'];
		}
		if (isset($request['end_month']))
		{
			$endMonth = $request['end_month'];
		}
		#if (strtotime($begMonth) == false || strtotime($endMonth) == false
		#	|| strtotime($begMonth) > strtotime($endMonth) - 3600*24*30
		#	|| strtotime($endMonth) < strtotime('2013-10')
		#)
		#{
		#	$begMonth = date('Y-m', time()-24*3600*30*24);
		#	$endMonth = date('Y-m',
		#		time()-24*3600*intval(date('t', strtotime('-1 month'))));
		#}
		if (strtotime($begMonth) < strtotime('2013-09'))
		{
			$begMonth = '2013-09';
		}
		return array($begMonth, $endMonth);
	}

	private function getCategoryDatas($begMonth, $endMonth, $useHouseFund)
	{
		$query_params = array();
		if (!$useHouseFund)
		{
			$query_params['ne']['fromAcc'] = '0b45af0bd5e741dbbe8e796f13943736';
		}
		$query_params['eq']['recType'] = 2;
		if (!empty($begMonth))
		{
			$query_params['ge']['date'] = $begMonth.'-01 00:00:00';
		}
		if (!empty($endMonth))
		{
			$enddate = date('Y-m-t 23:59:59', strtotime($endMonth));
			$query_params['le']['date'] = $enddate;
		}
		$query_params['group'] = array('category');
		$query_params['order'] = array('sum(money)' => 'desc');
		$ret = Repository::findSumMoneyFromLedgers($query_params);
		$inCategories = array();
		$outCategories = array();
		$i = 0;
		foreach ($ret as $key => $value)
		{
			$inCategories[] = array(
				'label' => $key.' ('
					.round($value*100/$this->income, 2).'%)',
				'value' => round($value, 2),
				'color' => $this->colors[$i%count($this->colors)][0],
				'highlight' => $this->colors[$i%count($this->colors)][1],
			);
			$i++;
		}

		$query_params['eq']['recType'] = 1;
		$ret = Repository::findSumMoneyFromLedgers($query_params);
		$i = 0;
		foreach ($ret as $key => $value)
		{
			$outCategories[] = array(
				'label' => $key.' ('
					.round($value*100/$this->expend, 2).'%)',
				'value' => round($value, 2),
				'color' => $this->colors[$i%count($this->colors)][0],
				'highlight' => $this->colors[$i%count($this->colors)][1],
			);
			$i++;
		}

		return array($inCategories, $outCategories);
	}

	private function getAvg($begMonth, $endMonth, $useHouseFund)
	{
		$query_params = array(
			'ne' => array('fromAcc' => '0b45af0bd5e741dbbe8e796f13943736'),
			'eq' => array('recType' => 2),
		);
		if (!empty($begMonth)) {
			$query_params['ge']['date'] = $begMonth.'-01 00:00:00';
		}
		if (!empty($endMonth)) {
			$query_params['le']['date']
				= date('Y-m-t 23:59:59', strtotime($endMonth));
		}
		$this->income = round(Repository::findSumMoneyFromLedgers($query_params), 2);
		$query_params['eq']['recType'] = 1;
		$this->expend = round(Repository::findSumMoneyFromLedgers($query_params), 2);
		$avg = $this->income - $this->expend;
		$avg = round($avg/intval((strtotime($endMonth) - strtotime($begMonth))/(3600*24*30) + 1), 2);
		return $avg;
	}

	public function getInOutDatas($begMonth, $endMonth, $useHouseFund, $category = null)
	{
		$time = time();
		$labels = array();
		$expends = array();
		$incomes = array();

		$begtime = strtotime($begMonth);
		$endtime = strtotime($endMonth);

		while (1)
		{
			$query_params = array();
			$beg_month = date('Y-m-01 00:00:00', $begtime);
			$end_month = date('Y-m-t 23:59:59', $begtime);
			if (!empty($category) && $category != '公积金')
			{
				$query_params['eq']['category'] = $category;
			}
			if ($category == '公积金')
			{
				$query_params['eq']['fromAcc'] = '0b45af0bd5e741dbbe8e796f13943736';
			}
			else if (!$useHouseFund)
			{
				$query_params['ne']['fromAcc'] = '0b45af0bd5e741dbbe8e796f13943736';
			}
			if ($category == '借出') {
				$query_params['eq']['recType'] = 4;
			} else if ($category == '转账') {
				$query_params['eq']['recType'] = 3;
			} else {
				$query_params['eq']['recType'] = 1;
			}
			$query_params['ge']['date'] = $beg_month;
			$query_params['le']['date'] = $end_month;
			$expends[] = round(Repository::findSumMoneyFromLedgers($query_params), 2);

			if ($category == '收款') {
				$query_params['eq']['recType'] = 5;
			} else {
				$query_params['eq']['recType'] = 2;
			}
			$incomes[] = round(Repository::findSumMoneyFromLedgers($query_params), 2);

			$labels[] = date('Y-m', $begtime);
			$begtime += 24*3600*intval(date('t', $begtime));
			if ($endtime - $begtime < 0)
				break;
		}
		return array($labels, $expends, $incomes);
	}
}
?>
